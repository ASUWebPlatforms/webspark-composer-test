<?php

namespace Drupal\sp_learningmod\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;

class UserBudgetService
{
  protected $database;
  protected $currentUser;
  protected $entityTypeManager;

  public function __construct(Connection $database, AccountProxyInterface $currentUser, EntityTypeManager $entityTypeManager)
  {
    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Gets the current budget of the user.
   */
  public function getBudget()
  {
    $query = $this->database->select('sp_learningmod_budget', 'b')
      ->fields('b', ['budget'])
      ->condition('uid', $this->currentUser->id())
      ->execute()
      ->fetchField();
    return $query !== FALSE ? $query : 100;
  }

  /**
   * Verifies if the user already visited the node.
   */
  public function hasVisitedNode($nid)
  {
    $query = $this->database->select('sp_learningmod_visited_nodes', 'v')
      ->condition('uid', $this->currentUser->id())
      ->condition('nid', $nid)
      ->countQuery()
      ->execute()
      ->fetchField();
    return $query > 0;
  }

  /**
   * Resets the users module data.
   */
  private function resetUserData()
  {
    $uid = $this->currentUser->id();

    $this->database->delete('sp_learningmod_council_answers')
      ->condition('uid', $uid)
      ->execute();

    $this->database->delete('sp_learningmod_council_progress')
      ->condition('uid', $uid)
      ->execute();

    $this->database->delete('sp_learningmod_selected_responses')
      ->condition('uid', $uid)
      ->execute();

    $this->database->delete('sp_learningmod_submitted_plans')
      ->condition('uid', $uid)
      ->execute();

    $this->resetUserNodes();
  }

  /**
   * Deletes the Critical Questions nodes created.
   */
  private function resetUserNodes()
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

    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $allowed_types, 'IN');
    $query->condition('uid', $this->currentUser->id());
    $query->accessCheck(TRUE);
    $nids = $query->execute();

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {
        $node->delete();
      }
    }
  }

  /**
   * Updates the user budget on node visited.
   */
  public function updateBudget($nid)
  {
    $budget = $this->getBudget();
    $session = \Drupal::requestStack()->getCurrentRequest()->getSession();

    if ($budget == 100) {
      $this->database->delete('sp_learningmod_visited_nodes')
        ->condition('uid', $this->currentUser->id())
        ->execute();

      if ($session) {
        $session->remove('warning_shown');
        $session->remove('final_warning_shown');
      }
    }

    if ($this->hasVisitedNode($nid)) {
      return ['budget' => $budget, 'action' => 'none'];
    }

    $node = Node::load($nid);
    $budgetValue = $node->get('field_sp_budget_value')->value ?? 0;
    $budget -= $budgetValue;

    $this->database->insert('sp_learningmod_visited_nodes')
      ->fields(['uid' => $this->currentUser->id(), 'nid' => $nid])
      ->execute();

    $this->database->merge('sp_learningmod_budget')
      ->key('uid', $this->currentUser->id())
      ->fields(['budget' => $budget])
      ->execute();

    if ($budget <= -20) {
      $this->database->update('sp_learningmod_budget')
        ->fields(['budget' => 100])
        ->condition('uid', $this->currentUser->id())
        ->execute();

      $this->database->delete('sp_learningmod_visited_nodes')
        ->condition('uid', $this->currentUser->id())
        ->execute();

      if ($session) {
        $session->remove('warning_shown');
        $session->remove('final_warning_shown');
      }

      $this->resetUserData();

      return ['budget' => $budget, 'action' => 'fired'];
    } elseif ($budget <= -10) {
      return ['budget' => $budget, 'action' => 'final-warning'];
    } elseif ($budget <= 10) {
      return ['budget' => $budget, 'action' => 'warning'];
    }

    return ['budget' => $budget, 'action' => 'none'];
  }
}
