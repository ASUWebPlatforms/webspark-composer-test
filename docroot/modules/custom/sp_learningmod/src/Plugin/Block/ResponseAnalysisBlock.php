<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Response Analysis' Block.
 *
 * @Block(
 *   id = "response_analysis_block",
 *   admin_label = @Translation("Response Analysis Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ResponseAnalysisBlock extends BlockBase
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
        '#theme' => 'response_analysis',
        '#submitted' => FALSE,
      ];
    }

    $query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $query->execute()->fetchCol();

    if (empty($selected_nids)) {
      return [
        '#theme' => 'response_analysis',
        '#submitted' => FALSE,
      ];
    }

    \Drupal::logger('sp_learningmod')->notice('User @uid has selected responses: @nids', [
      '@uid' => $uid,
      '@nids' => implode(', ', $selected_nids),
    ]);

    $risk_map = [4 => 'High', 3 => 'Moderate', 2 => 'Low', 1 => 'None'];
    $success_map = [4 => 'High', 3 => 'Moderate', 2 => 'Low', 1 => 'None'];
    $impact_map = [4 => 'High', 3 => 'Moderate', 2 => 'Limited', 1 => 'None'];
    $impactterm_map = [4 => 'Long-term', 3 => 'Moderate', 2 => 'Short-term', 1 => 'None'];
    $lawenforce_map = [4 => 'High', 3 => 'Moderate', 2 => 'Low', 1 => 'Irresponsible'];
    $crimeprevent_map = [4 => 'High', 3 => 'Moderate', 2 => 'Low', 1 => 'Derelict'];

    $table_rows = [];

    foreach ($selected_nids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $table_rows[] = [
          'title' => $node->getTitle(),
          'risk' => $risk_map[$node->get('field_risk')->value] ?? 'N/A',
          'success' => $success_map[$node->get('field_success')->value] ?? 'N/A',
          'impact' => $impact_map[$node->get('field_impact')->value] ?? 'N/A',
          'impact_length' => $impactterm_map[$node->get('field_impactterm')->value] ?? 'N/A',
          'law_enforcement' => $lawenforce_map[$node->get('field_lawenforce')->value] ?? 'N/A',
          'crime_prevention' => $crimeprevent_map[$node->get('field_crimeprevent')->value] ?? 'N/A',
        ];
      }
    }

    $table_header = [
      'title' => $this->t('Your Responses'),
      'risk' => $this->t('Risk'),
      'success' => $this->t('Probability of Success'),
      'impact' => $this->t('Impact'),
      'impact_length' => $this->t('Impact Length'),
      'law_enforcement' => $this->t('Law Enforcement Preference'),
      'crime_prevention' => $this->t('Crime Prevention Preference'),
    ];

    return [
      '#theme' => 'response_analysis',
      '#submitted' => TRUE,
      '#title' => $this->t('Response Analysis'),
      '#description' => $this->t('The table below illustrates the various characteristics of each of the responses you chose to include in your plan. Descriptions of each characteristic follows the table.'),
      '#table_header' => $table_header,
      '#table_rows' => $table_rows,
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
