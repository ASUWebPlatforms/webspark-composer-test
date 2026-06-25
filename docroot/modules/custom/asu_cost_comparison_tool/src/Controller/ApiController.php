<?php

namespace Drupal\asu_cost_comparison_tool\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\Entity\User;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Controller for ASU Cost Comparison Tool.
 *  Handles webform submissions and user data retrieval.
 */
class ApiController extends ControllerBase {

  protected $database;
  protected $logger;
  protected $submissionHelper;
  protected $emailValidator;

  public function __construct(
    Connection $database,
    LoggerChannelFactoryInterface $logger_factory,
    $submission_helper,
    EmailValidatorInterface $email_validator
  ) {
    $this->database = $database;
    $this->logger = $logger_factory->get('asu_cost_comparison_tool');
    $this->submissionHelper = $submission_helper;
    $this->emailValidator = $email_validator;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('logger.factory'),
      $container->get('asu_cost_comparison_tool.WebformSubmissionHelper'),
      $container->get('email.validator')
    );
  }

  /**
   * Generate secure token and its SHA-256 hash.
   *
   * @return array
   *   ['token' => raw_token, 'hash' => token_hash]
   */
  protected function generateTokenPair(): array {
    $token = bin2hex(random_bytes(32)); // 64 hex chars
    $hash = hash('sha256', $token);
    return ['token' => $token, 'hash' => $hash];
  }

  /**
   * Receive data from React form via POST.
   *
   * No CSRF token required (per request). Use X-WEB-TOKEN for later retrieval/claim.
   */
  public function webformSubmit(Request $request) {
    // Method check
    if ($request->getMethod() !== 'POST') {
      return new JsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }

    // Content type / JSON parse
    $contentType = $request->headers->get('Content-Type', '');
    if (stripos($contentType, 'application/json') === FALSE) {
      return new JsonResponse(['status' => 'error', 'message' => 'Content-Type must be application/json'], 400);
    }

    $content = $request->getContent();
    $data = json_decode($content, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      return new JsonResponse(['status' => 'error', 'message' => 'Malformed JSON'], 400);
    }
    \Drupal::logger('asu_cost_comparison_tool')->notice('Received data: @data', ['@data' => print_r($data, TRUE)]);
    
    $user = \Drupal::currentUser();
    $uid = $user->isAuthenticated() ? $user->id() : 0;

    $config = \Drupal::config('asu_cost_comparison_tool.settings');
    $webform_id = $config->get('cost_webform_id');
    if (empty($webform_id)) {
      $this->logger->error('Webform ID not configured for asu_cost_comparison_tool.');
      return new JsonResponse(['status' => 'error', 'message' => 'Server misconfiguration'], 500);
    }

    try {
      $submission = $this->submissionHelper->createSubmission($webform_id, $data, $uid);
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to create submission: @msg', ['@msg' => $e->getMessage()]);
      return new JsonResponse(['status' => 'error', 'message' => 'Failed to create submission'], 500);
    }

    $pair = $this->generateTokenPair();
    $sid = $submission->id();
    
    // Store only token hash and created timestamp.
    $submission->setElementData('web_token', $pair['token']);
    $submission->setElementData('web_token_hash', $pair['hash']);
    $submission->setElementData('web_token_created', time());
    $submission->save();

    // Return raw token exactly once to client for later retrieval/claim.
    return new JsonResponse([
      'status' => 'success',
      'wsid' => $sid,
      'webTo' => $pair['token'],
    ], Response::HTTP_CREATED);
  }

  /**
   * Provide current user info for autofill.
   */
  public function getUserData() {
    $user = \Drupal::currentUser();
    if ($user->isAuthenticated()) {
      $account = User::load($user->id());
      $user_data = [
        'uid' => $user->id(),
        'name' => $account->getDisplayName(),
        'email' => $account->getEmail(),
        'authenticated' => TRUE,
      ];
    }
    else {
      $user_data = [
        'uid' => 0,
        'name' => '',
        'email' => '',
        'authenticated' => FALSE,
      ];
    }

    return new JsonResponse($user_data);
  }

  /**
   * Get latest submission matching the provided raw token (via X-WEB-TOKEN or webTo).
   */
  public function getUserSubmission(Request $request) {
    $user = \Drupal::currentUser();
    $uid = $user->isAuthenticated() ? $user->id() : 0;

    if ($uid === 0) {
      return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], 403);
    }

    $webTo = $request->headers->get('X-WEB-TOKEN') ?? $request->get('webTo');
    if (empty($webTo)) {
      return new JsonResponse(['status' => 'error', 'message' => 'Missing token'], 400);
    }

    $config = \Drupal::config('asu_cost_comparison_tool.settings');
    $webform_id = $config->get('cost_webform_id');

    $web_token_hash = hash('sha256', $webTo);

    $query = $this->database->select('webform_submission', 'ws');
    $query->addField('ws', 'sid');
    $query->join('webform_submission_data', 'wd', 'ws.sid = wd.sid');
    $query->condition('wd.name', 'web_token_hash');
    $query->condition('wd.value', $web_token_hash);
    $query->condition('ws.webform_id', $webform_id);
    $query->orderBy('ws.created', 'DESC');
    $query->range(0, 1);

    $sid = $query->execute()->fetchField();
    if (empty($sid)) {
      return new JsonResponse(['data' => NULL], Response::HTTP_NO_CONTENT);
    }

    $submission = WebformSubmission::load($sid);
    if (!$submission) {
      return new JsonResponse(['data' => NULL], Response::HTTP_NO_CONTENT);
    }

    $data = $submission->getData();
    if (is_array($data)) {
      unset($data['web_token_hash'], $data['web_token_created']);
    }

    if (!empty($data['payload']) && is_string($data['payload'])) {
      $decoded = json_decode($data['payload'], TRUE);
      if (json_last_error() === JSON_ERROR_NONE) {
        return new JsonResponse(['sid' => $sid, 'data' => $decoded], Response::HTTP_OK);
      }
    }

    return new JsonResponse(['sid' => $sid, 'data' => $data], Response::HTTP_OK);
  }

  /**
   * Update email address in webform submission.
   *
   * Ownership or 'administer webform' permission required.
   */
  /**
 * Update email address in webform submission.
 *
 * Allowed when:
 * - current user is the submission owner, OR
 * - current user has 'administer webform', OR
 * - a valid X-WEB-TOKEN is provided that matches the submission (for anonymous submissions).
 */
public function emailUpdate(Request $request, $sid = '') {
  // Only allow POST or PATCH (adjust if you prefer PUT).
  $method = $request->getMethod();
  if (!in_array($method, ['POST', 'PATCH'])) {
    return new JsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
  }

  // Expect JSON body
  $contentType = $request->headers->get('Content-Type', '');
  if (stripos($contentType, 'application/json') === FALSE) {
    return new JsonResponse(['status' => 'error', 'message' => 'Content-Type must be application/json'], 400);
  }

  $raw = $request->getContent();
  $data = json_decode($raw, TRUE);
  if (json_last_error() !== JSON_ERROR_NONE) {
    return new JsonResponse(['status' => 'error', 'message' => 'Malformed JSON'], 400);
  }

  $email = isset($data['email']) ? trim($data['email']) : '';
  if (empty($email)) {
    return new JsonResponse(['status' => 'error', 'message' => 'Email is required'], 400);
  }

  // Validate email format
  $email_valid = \Drupal::service('email.validator')->isValid($email);
  if (!$email_valid) {
    return new JsonResponse(['status' => 'error', 'message' => 'Invalid email address'], 400);
  }

  // Load submission
  $submission = WebformSubmission::load(intval($sid));
  if (!$submission) {
    return new JsonResponse(['status' => 'error', 'message' => 'Submission not found'], 404);
  }

  $current_user = \Drupal::currentUser();
  $current_uid = $current_user->isAuthenticated() ? $current_user->id() : 0;

  // Admins may always update
  if ($current_user->hasPermission('administer webform')) {
    $allowed = TRUE;
  }
  else {
    // Ownership check
    $owner_id = NULL;
    if (method_exists($submission, 'getOwnerId')) {
      $owner_id = $submission->getOwnerId();
    }
    else {
      // Best-effort fallback for older Webform APIs
      $owner_id = property_exists($submission, 'uid') ? $submission->uid : NULL;
    }

    if (!empty($owner_id) && $current_uid && (int) $owner_id === (int) $current_uid) {
      // current user owns the submission
      $allowed = TRUE;
    }
    else {
      $allowed = FALSE;
    }
  }

  // If not allowed yet, allow update if a valid X-WEB-TOKEN is supplied and matches this submission.
  if (!$allowed) {
    $providedToken = $request->headers->get('X-WEB-TOKEN') ?? $request->get('webTo') ?? NULL;
    if (!empty($providedToken)) {
      // Get stored token hash (preferred) or legacy token
      $sdata = $submission->getData();
      $stored_token = is_array($sdata) && isset($sdata['web_token']) ? $sdata['web_token'] : NULL;

      $token_ok = FALSE;
      
      if (!$token_ok && !empty($stored_token)) {
        if (hash_equals((string) $stored_token, (string) $providedToken)) {
          $token_ok = TRUE;
        }
      }

      if ($token_ok) {
        // If allowed via token, also ensure user is authenticated (your requirement)
        // If you want to allow pure-token updates by anonymous users, remove the below check.
        if ($current_user->isAuthenticated()) {
          $allowed = TRUE;
        } else {
          return new JsonResponse(['status' => 'error', 'message' => 'Authentication required to update this submission'], 403);
        }
      }
    }
  }

  if (!$allowed) {
    return new JsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
  }

  // Passed checks -> update email
  $submission->setElementData('email', $email);
  $submission->save();

  return new JsonResponse(['status' => 'success', 'message' => 'Email updated successfully']);
}


  /**
   * Claim anonymous submission for logged-in user using provided raw token.
   */
  public function claimSubmission(Request $request, $sid) {
    $account = \Drupal::currentUser();
    if ($account->isAnonymous()) {
      return new JsonResponse(['error' => 'Not authenticated'], 401);
    }
    $uid = $account->id();

    $webTo = $request->headers->get('X-WEB-TOKEN') ?? $request->get('webTo');
    if (empty($webTo)) {
      return new JsonResponse(['error' => 'Missing token'], 400);
    }

    $submission = WebformSubmission::load($sid);
    if (!$submission) {
      return new JsonResponse(['error' => 'Submission not found'], 404);
    }

    $data = $submission->getData();
    $stored_token = is_array($data) && isset($data['web_token_hash']) ? $data['web_token_hash'] : '';

    if (empty($stored_token) || !hash_equals($stored_token, hash('sha256', $webTo))) {
      $this->logger->warning('Claim failed: token mismatch for sid {sid}', ['sid' => $sid]);
      return new JsonResponse(['error' => 'Invalid token'], 403);
    }

    if (method_exists($submission, 'setOwnerId')) {
      $submission->setOwnerId($uid);
    }
    else {
      $submission->uid = $uid;
    }
    $submission->save();


    return new JsonResponse(['status' => 'ok', 'sid' => $sid, 'claimed_by' => $uid]);
  }

}
