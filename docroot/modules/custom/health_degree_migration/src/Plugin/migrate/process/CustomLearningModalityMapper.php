<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Provides a 'CustomLearningModalityMapper' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "custom_learning_modality_mapper"
 * )
 */
class CustomLearningModalityMapper extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $campusesOffered = $row->getSourceProperty('campusesOffered') ?? [];
    $degreeDescriptionShort = strtoupper(trim($row->getSourceProperty('degreeDescriptionShort')));
    $asuOnlineAcadPlanUrl = $row->getSourceProperty('asuOnlineAcadPlanUrl');
    $acadPlanCode = $row->getSourceProperty('acadPlanCode');
    $existingTerms = $row->getSourceProperty('existing_terms') ?? [];
    $newTermIds = [];

    if (empty($campusesOffered) && $degreeDescriptionShort === 'CERT' && !empty($asuOnlineAcadPlanUrl)) {
      $newTermIds[] = 751;
    } elseif (!empty($campusesOffered)) {
      foreach ($campusesOffered as $campus) {
        $campusCode = strtoupper(trim($campus['campusCode']));

        if ($campusCode === 'SYNC') {
          $newTermIds[] = 750;
        } elseif ($campusCode === 'ONLNE') {
          $newTermIds[] = 751;
        } else {
          $newTermIds[] = 749;
        }
      }
    }

    $mergedTermIds = array_unique(array_merge($newTermIds));
    $validTerms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($mergedTermIds);
    $validTermIds = array_keys($validTerms);

    // Ensure indexes are sequential.
    $validTermIds = array_values($validTermIds);

    \Drupal::logger('custom_learning_modality_mapper')->info('AcadPlanCode: ' . $acadPlanCode . ' - Valid TIDs: <pre>' . print_r($validTermIds, TRUE) . '</pre>');

    return array_map(function ($tid) {
      return ['target_id' => $tid];
    }, $validTermIds);
  }
}
