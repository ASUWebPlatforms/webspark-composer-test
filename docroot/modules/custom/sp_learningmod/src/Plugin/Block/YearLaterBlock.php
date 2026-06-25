<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;

/**
 * Provides a 'Year Later' Block.
 *
 * @Block(
 *   id = "year_later_block",
 *   admin_label = @Translation("Year Later Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class YearLaterBlock extends BlockBase
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
        '#theme' => 'year_later',
        '#submitted' => FALSE,
      ];
    }

    $query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $query->execute()->fetchCol();

    if (empty($selected_nids)) {
      return [
        '#theme' => 'year_later',
        '#submitted' => FALSE,
      ];
    }

    $metrics = ['cad', 'serious', 'visp12am', 'visj12amv', 'visj12amf', 'volparap', 'robbery', 'assaults'];

    $query = $connection->select('node__field_cost', 'cost')
      ->fields('cost', ['entity_id', 'field_cost_value'])
      ->condition('cost.entity_id', $selected_nids, 'IN');

    foreach ($metrics as $metric) {
      foreach ([1, 6, 12] as $time) {
        $table_alias = "{$metric}{$time}";
        $field_name = "field_{$metric}{$time}_value";
        $table_name = "node__field_{$metric}{$time}";

        $query->addField($table_alias, $field_name, $table_alias);
        $query->leftJoin($table_name, $table_alias, "cost.entity_id = {$table_alias}.entity_id");
      }
    }

    $results = $query->execute()->fetchAllAssoc('entity_id');

    $aggregated_metrics = [];

    foreach ($results as $nid => $data) {
      foreach ($metrics as $metric) {
        foreach ([1, 6, 12] as $time) {
          $key = "{$metric}{$time}";
          $aggregated_metrics[$key] = isset($aggregated_metrics[$key])
            ? $aggregated_metrics[$key] + (int) $data->$key
            : (int) $data->$key;
        }
      }
    }

    $averages = [];
    foreach ($aggregated_metrics as $key => $value) {
      $count = count($selected_nids);
      $averages[$key] = $count ? round($value / $count, 2) : 'N/A';
    }

    $table_header = [
      'metric' => $this->t('Metric'),
      'initval' => $this->t('At the time responses were implemented'),
      'onemo' => $this->t('1 Month Later'),
      'sixmo' => $this->t('6 Months Later'),
      'year' => $this->t('1 Year Later'),
    ];

    $table_rows = [
      // 🔹 Row 1: Complaints (Category)
      [
        'metric' => $this->t('Complaints'),
        'initval' => '',
        'onemo' => '',
        'sixmo' => '',
        'year' => '',
        'group' => TRUE,
      ],
      // 🔹 Row 2: Calls for police service
      [
        'metric' => $this->t('Calls for police service related to prostitution in target area (CAD records)'),
        'initval' => '141',
        'onemo' => $averages['cad1'] ?? 'N/A',
        'sixmo' => $averages['cad6'] ?? 'N/A',
        'year' => $averages['cad12'] ?? 'N/A',
      ],
      // 🔹 Row 3: Citizen Survey Data (Category)
      [
        'metric' => $this->t('Citizen Survey Data'),
        'initval' => '',
        'onemo' => '',
        'sixmo' => '',
        'year' => '',
        'group' => TRUE,
      ],
      // 🔹 Row 4: Seriousness of problem
      [
        'metric' => $this->t('Seriousness of problem (0-10 scale with 0 not a problem and 10 most serious)'),
        'initval' => '8.3',
        'onemo' => $averages['serious1'] ?? 'N/A',
        'sixmo' => $averages['serious6'] ?? 'N/A',
        'year' => $averages['serious12'] ?? 'N/A',
      ],
      // 🔹 Row 5: Visibility (Category)
      [
        'metric' => $this->t('Visibility'),
        'initval' => '',
        'onemo' => '',
        'sixmo' => '',
        'year' => '',
        'group' => TRUE,
      ],
      // 🔹 Row 6: Visibility of prostitutes 12am
      [
        'metric' => $this->t('Visibility of prostitutes 12am'),
        'initval' => '16',
        'onemo' => $averages['visp12am1'] ?? 'N/A',
        'sixmo' => $averages['visp12am6'] ?? 'N/A',
        'year' => $averages['visp12am12'] ?? 'N/A',
      ],
      // 🔹 Row 7: Visibility of johns in vehicle 12am
      [
        'metric' => $this->t('Visibility of johns in vehicle 12am'),
        'initval' => '14',
        'onemo' => $averages['visj12amv1'] ?? 'N/A',
        'sixmo' => $averages['visj12amv6'] ?? 'N/A',
        'year' => $averages['visj12amv12'] ?? 'N/A',
      ],
      // 🔹 Row 8: Visibility of johns on foot 12am
      [
        'metric' => $this->t('Visibility of johns on foot 12am'),
        'initval' => '11',
        'onemo' => $averages['visj12amf1'] ?? 'N/A',
        'sixmo' => $averages['visj12amf6'] ?? 'N/A',
        'year' => $averages['visj12amf12'] ?? 'N/A',
      ],
      // 🔹 Row 9: Crimes (Category)
      [
        'metric' => $this->t('Crimes'),
        'initval' => '',
        'onemo' => '',
        'sixmo' => '',
        'year' => '',
        'group' => TRUE,
      ],
      // 🔹 Row 10: Number of robberies
      [
        'metric' => $this->t('Number of robberies'),
        'initval' => '14',
        'onemo' => $averages['robbery1'] ?? 'N/A',
        'sixmo' => $averages['robbery6'] ?? 'N/A',
        'year' => $averages['robbery12'] ?? 'N/A',
      ],
      // 🔹 Row 11: Number of assaults
      [
        'metric' => $this->t('Number of assaults'),
        'initval' => '26',
        'onemo' => $averages['assaults1'] ?? 'N/A',
        'sixmo' => $averages['assaults6'] ?? 'N/A',
        'year' => $averages['assaults12'] ?? 'N/A',
      ],
    ];

    return [
      '#theme' => 'year_later',
      '#submitted' => TRUE,
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
