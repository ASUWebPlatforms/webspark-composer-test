<?php

namespace Drupal\sp_learningmod\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * API Controller for user state - bypasses Varnish cache.
 * 
 * All endpoints return JSON with proper no-cache headers
 * that Varnish will respect.
 * 
 * Security measures:
 * - CSRF token validation on state-changing operations (POST)
 * - User authentication required for sensitive data
 * - No sensitive PII exposed in responses
 */
class UserStateApiController extends ControllerBase
{

  protected $database;
  protected $currentUser;
  protected $fileUrlGenerator;
  protected $csrfToken;

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database'),
      $container->get('current_user'),
      $container->get('file_url_generator'),
      $container->get('csrf_token')
    );
  }

  public function __construct(
    Connection $database,
    AccountProxyInterface $currentUser,
    FileUrlGenerator $fileUrlGenerator,
    CsrfTokenGenerator $csrfToken
  ) {
    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->csrfToken = $csrfToken;
  }

  /**
   * Creates a JSON response with no-cache headers.
   * 
   * These headers tell Varnish to NOT cache this response.
   */
  private function createNoCacheResponse(array $data): JsonResponse
  {
    $response = new JsonResponse($data);

    // Headers that Varnish respects
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');
    // Vary by cookie ensures different users get different responses
    $response->headers->set('Vary', 'Cookie');

    return $response;
  }

  /**
   * Validates CSRF token from request.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return bool
   */
  private function validateCsrfToken(Request $request): bool
  {
    $token = $request->headers->get('X-CSRF-Token')
      ?? $request->request->get('csrf_token')
      ?? $request->query->get('csrf_token');

    if (!$token) {
      return FALSE;
    }

    return $this->csrfToken->validate($token, 'sp_learningmod');
  }

  /**
   * Creates an error response for CSRF validation failure.
   */
  private function csrfErrorResponse(): JsonResponse
  {
    return $this->createNoCacheResponse([
      'success' => FALSE,
      'error' => 'Invalid or missing CSRF token',
      'code' => 403,
    ]);
  }

  /**
   * Validates that request is AJAX (basic check).
   */
  private function isAjaxRequest(Request $request): bool
  {
    return $request->headers->get('X-Requested-With') === 'XMLHttpRequest';
  }

  /**
   * Get CSRF token for subsequent POST requests.
   * 
   * This should be called first to get a token for state-changing operations.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getCsrfToken(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'token' => NULL,
      ]);
    }

    $token = $this->csrfToken->get('sp_learningmod');

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      'token' => $token,
    ]);
  }

  /**
   * Get current user budget state.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getBudgetState(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'budget' => 100,
        'percentage' => 0,
      ]);
    }

    $budget = $this->database->select('sp_learningmod_budget', 'b')
      ->fields('b', ['budget'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchField();

    $budget = $budget !== FALSE ? (int) $budget : 100;
    $percentage = max(0, 100 - $budget);

    // Include CSRF token for convenience (used in subsequent POST requests)
    $token = $this->csrfToken->get('sp_learningmod');

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      // Don't expose uid - not needed by frontend
      'budget' => $budget,
      'percentage' => $percentage,
      'colorClass' => $this->getBudgetColorClass($percentage),
      'csrfToken' => $token,
    ]);
  }

  /**
   * Get visited nodes for current user.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getVisitedNodes(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'items' => [],
      ]);
    }

    $nids = $this->database->select('sp_learningmod_visited_nodes', 'v')
      ->fields('v', ['nid'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchCol();

    $items = [];
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if (!$node) {
        continue;
      }

      $image_url = $this->getNodeImageUrl($node);

      $items[] = [
        'nid' => $nid,
        'title' => $node->getTitle(),
        'description' => $node->hasField('field_description') ? $node->get('field_description')->value : '',
        'cost' => $node->hasField('field_sp_budget_value') ? $node->get('field_sp_budget_value')->value : '',
        'image' => $image_url,
        'link' => $node->toUrl()->toString(),
      ];
    }

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      'items' => $items,
      'count' => count($items),
    ]);
  }

  /**
   * Track node visit and update budget via AJAX.
   * 
   * Requires CSRF token for security.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function trackNodeVisit(Request $request): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'User not authenticated',
      ]);
    }

    // Validate CSRF token for POST requests
    if ($request->isMethod('POST') && !$this->validateCsrfToken($request)) {
      return $this->csrfErrorResponse();
    }

    $nid = $request->request->get('nid');
    if (!$nid) {
      $nid = $request->query->get('nid');
    }

    if (!$nid || !is_numeric($nid)) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'Invalid node ID',
      ]);
    }

    // Sanitize nid
    $nid = (int) $nid;

    // Validate node exists and is of allowed type
    $node = Node::load($nid);
    if (!$node) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'Node not found',
      ]);
    }

    // Verify node is of an allowed type (security check)
    $allowed_types = ['sp_analyze_interview', 'sp_analyze_other_research', 'sp_analyze_report'];
    if (!in_array($node->bundle(), $allowed_types)) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'Invalid node type',
      ]);
    }

    // Check if already visited
    $already_visited = $this->database->select('sp_learningmod_visited_nodes', 'v')
      ->condition('uid', $uid)
      ->condition('nid', $nid)
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($already_visited > 0) {
      $budget = $this->getCurrentBudget($uid);
      return $this->createNoCacheResponse([
        'success' => TRUE,
        'action' => 'already_visited',
        'budget' => $budget,
        'percentage' => max(0, 100 - $budget),
        'colorClass' => $this->getBudgetColorClass(max(0, 100 - $budget)),
      ]);
    }

    $budgetValue = $node->hasField('field_sp_budget_value')
      ? (int) $node->get('field_sp_budget_value')->value
      : 0;

    // Get current budget
    $budget = $this->getCurrentBudget($uid);

    // First visit with full budget? Reset visited nodes
    if ($budget == 100) {
      $this->database->delete('sp_learningmod_visited_nodes')
        ->condition('uid', $uid)
        ->execute();
    }

    // Deduct from budget
    $budget -= $budgetValue;

    // Record visit
    $this->database->insert('sp_learningmod_visited_nodes')
      ->fields([
        'uid' => $uid,
        'nid' => $nid,
        'visited_at' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    // Update budget
    $this->database->merge('sp_learningmod_budget')
      ->key('uid', $uid)
      ->fields(['budget' => $budget])
      ->execute();

    // Determine action based on budget
    $action = 'none';
    if ($budget <= -20) {
      // User is fired - reset everything
      $this->resetUserData($uid);
      $action = 'fired';
      $budget = 100;
    } elseif ($budget <= -10) {
      $action = 'final_warning';
    } elseif ($budget <= 10) {
      $action = 'warning';
    }

    $percentage = max(0, 100 - $budget);

    return $this->createNoCacheResponse([
      'success' => TRUE,
      'authenticated' => TRUE,
      'action' => $action,
      'budget' => $budget,
      'percentage' => $percentage,
      'colorClass' => $this->getBudgetColorClass($percentage),
      'nodeTitle' => $node->getTitle(),
      'nodeCost' => $budgetValue,
    ]);
  }

  /**
   * Get warning state for current user.
   * 
   * Uses database instead of session for reliability.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getWarningState(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'warning_shown' => FALSE,
        'final_warning_shown' => FALSE,
      ]);
    }

    // Get warning state from database instead of session
    $state = $this->database->select('sp_learningmod_warning_state', 'w')
      ->fields('w', ['warning_shown', 'final_warning_shown'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAssoc();

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      'warning_shown' => $state ? (bool) $state['warning_shown'] : FALSE,
      'final_warning_shown' => $state ? (bool) $state['final_warning_shown'] : FALSE,
    ]);
  }

  /**
   * Update warning state (mark warning as shown).
   * 
   * Requires CSRF token for security.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function setWarningState(Request $request): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'User not authenticated',
      ]);
    }

    // Validate CSRF token for POST requests
    if ($request->isMethod('POST') && !$this->validateCsrfToken($request)) {
      return $this->csrfErrorResponse();
    }

    $warning_type = $request->request->get('type') ?? $request->query->get('type');

    // Whitelist validation
    if (!in_array($warning_type, ['warning', 'final_warning'], TRUE)) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'Invalid warning type',
      ]);
    }

    $field = $warning_type . '_shown';

    $this->database->merge('sp_learningmod_warning_state')
      ->key('uid', $uid)
      ->fields([$field => 1])
      ->execute();

    return $this->createNoCacheResponse([
      'success' => TRUE,
      'type' => $warning_type,
    ]);
  }

  /**
   * Reset user's module progress.
   * 
   * Requires CSRF token for security.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function resetProgress(Request $request): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'success' => FALSE,
        'error' => 'User not authenticated',
      ]);
    }

    // Validate CSRF token (this is a destructive operation)
    if (!$this->validateCsrfToken($request)) {
      return $this->csrfErrorResponse();
    }

    $this->resetUserData($uid);

    return $this->createNoCacheResponse([
      'success' => TRUE,
      'message' => 'Progress reset successfully',
      'budget' => 100,
      'percentage' => 0,
    ]);
  }

  /**
   * Get selected responses for Build My Plan.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getSelectedResponses(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'responses' => [],
      ]);
    }

    $response_ids = $this->database->select('sp_learningmod_selected_responses', 'r')
      ->fields('r', ['response_nid'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchCol();

    $responses = [];
    foreach ($response_ids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $responses[] = [
          'nid' => $nid,
          'title' => $node->getTitle(),
        ];
      }
    }

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      'responses' => $responses,
    ]);
  }

  /**
   * Get council meeting progress.
   * 
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getCouncilProgress(): JsonResponse
  {
    $uid = $this->currentUser->id();

    if ($uid == 0) {
      return $this->createNoCacheResponse([
        'authenticated' => FALSE,
        'progress' => [],
      ]);
    }

    $progress = $this->database->select('sp_learningmod_council_progress', 'c')
      ->fields('c')
      ->condition('uid', $uid)
      ->execute()
      ->fetchAssoc();

    $answers = $this->database->select('sp_learningmod_council_answers', 'a')
      ->fields('a', ['question_id', 'answer'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAllKeyed();

    return $this->createNoCacheResponse([
      'authenticated' => TRUE,
      'progress' => $progress ?: [],
      'answers' => $answers,
    ]);
  }

  /**
   * Helper: Get current budget for user.
   */
  private function getCurrentBudget(int $uid): int
  {
    $budget = $this->database->select('sp_learningmod_budget', 'b')
      ->fields('b', ['budget'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchField();

    return $budget !== FALSE ? (int) $budget : 100;
  }

  /**
   * Helper: Get budget color class based on percentage.
   */
  private function getBudgetColorClass(int $percentage): string
  {
    if ($percentage > 100) {
      return 'bg-danger';
    } elseif ($percentage >= 80) {
      return 'bg-warning';
    }
    return 'bg-success';
  }

  /**
   * Helper: Get image URL from node.
   */
  private function getNodeImageUrl($node): ?string
  {
    $image_field = NULL;

    switch ($node->bundle()) {
      case 'sp_analyze_report':
        $image_field = 'field_report_image';
        break;
      case 'sp_analyze_other_research':
        $image_field = 'field_image';
        break;
      case 'sp_analyze_interview':
        $image_field = 'field_profile_image';
        break;
    }

    if (!$image_field || !$node->hasField($image_field) || $node->get($image_field)->isEmpty()) {
      return NULL;
    }

    $media = $node->get($image_field)->entity;
    if (!$media || !$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
      return NULL;
    }

    $file = $media->get('field_media_image')->entity;
    return $file ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()) : NULL;
  }

  /**
   * Helper: Reset all user data.
   */
  private function resetUserData(int $uid): void
  {
    $tables = [
      'sp_learningmod_council_answers',
      'sp_learningmod_council_progress',
      'sp_learningmod_selected_responses',
      'sp_learningmod_submitted_plans',
      'sp_learningmod_visited_nodes',
      'sp_learningmod_warning_state',
    ];

    foreach ($tables as $table) {
      $this->database->delete($table)
        ->condition('uid', $uid)
        ->execute();
    }

    // Reset budget to 100
    $this->database->merge('sp_learningmod_budget')
      ->key('uid', $uid)
      ->fields(['budget' => 100])
      ->execute();

    // Delete user's CQ nodes
    $this->deleteUserCQNodes($uid);
  }

  /**
   * Helper: Delete user's Critical Questions nodes.
   */
  private function deleteUserCQNodes(int $uid): void
  {
    $allowed_types = [
      'sp_cq_clients_johns',
      'sp_cq_current_response',
      'sp_cq_drugs',
      'sp_cq_environment',
      'sp_cq_pimps',
      'sp_cq_police_community_members',
      'sp_cq_sexual_transactions',
      'sp_cq_street_prostitutes',
    ];

    $query = $this->entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', $allowed_types, 'IN');
    $query->condition('uid', $uid);
    $query->accessCheck(TRUE);
    $nids = $query->execute();

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {
        $node->delete();
      }
    }
  }
}
