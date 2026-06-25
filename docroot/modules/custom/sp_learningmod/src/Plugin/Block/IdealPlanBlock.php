<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Provides an 'Ideal Plan' Block.
 *
 * @Block(
 *   id = "ideal_plan_block",
 *   admin_label = @Translation("Ideal Plan Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class IdealPlanBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $connection = Database::getConnection();

    $submitted_query = $connection->select('sp_learningmod_submitted_plans', 'p')
      ->fields('p', ['uid'])
      ->condition('p.uid', $uid, '=')
      ->execute()
      ->fetchField();

    if (!$submitted_query) {
      return [
        '#theme' => 'ideal_plan',
        '#submitted' => FALSE,
      ];
    }

    $query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $query->execute()->fetchCol();

    if (empty($selected_nids)) {
      return [
        '#theme' => 'ideal_plan',
        '#submitted' => FALSE,
      ];
    }

    $responses = [];
    foreach ($selected_nids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $title = $node->getTitle();
        $cost = $node->get('field_cost')->value;
        $is_ideal = $node->get('field_isideal')->value;
        $feedback = $node->get('field_feedback')->value;
        $pros = $node->get('field_pros')->value;
        $cons = $node->get('field_cons')->value;
        $anticipated_outcome = $node->get('field_anticipatedoutcome')->value;

        $match = ($is_ideal)
          ? '<i class="fas fa-check-circle text-success"></i> <strong>Yes</strong>'
          : '<i class="fas fa-times-circle text-danger"></i> <strong>No</strong>';

        $responses[] = [
          'nid' => $nid,
          'title' => $title,
          'cost' => $cost,
          'match' => $match,
          'feedback' => $feedback,
          'pros' => $pros,
          'cons' => $cons,
          'anticipated_outcome' => $anticipated_outcome,
        ];
      }
    }

    return [
      '#theme' => 'ideal_plan',
      '#submitted' => TRUE,
      '#title' => $this->t('"Ideal" Plan'),
      '#description' => $this->t('We have estimated what the best responses might be, but acknowledge that a combination of alternative responses could yield comparable results. We don\'t want to give the game away by telling you what our ideal plan is, so we have compared your plan to ours, and the table below shows which of your responses matched ours.<br><br>Click on a response title to see detailed feedback for that response.'),
      '#responses' => $responses,
      '#attached' => [
        'library' => [
          'sp_learningmod/ideal_plan_modal',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account)
  {
    return AccessResult::allowed();
  }
}
