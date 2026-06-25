<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipProcessException;

/**
 * Maps campus codes to specific taxonomy term IDs with a default fallback.
 *
 * @MigrateProcessPlugin(
 *   id = "custom_campus_code_to_taxonomy"
 * )
 */
class CustomCampusCodeToTaxonomy extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Define your specific mappings here
    $specific_mappings = [
      'ONLNE' => 123, // Replace 123 with the actual TID for ONLNE
      'SYNC' => 456,  // Replace 456 with the actual TID for SYNC
    ];
    $default_tid = 789; // Replace 789 with your default TID for all other values

    $term_ids = [];

    if (!is_array($value) || empty($value)) {
      throw new MigrateSkipProcessException('Invalid or empty campusesOffered data.');
    }

    foreach ($value as $campus) {
      if (!isset($campus['campusCode'])) {
        continue; // Skip this item if campusCode is not set
      }

      $campus_code = $campus['campusCode'];

      if (isset($specific_mappings[$campus_code])) {
        $term_ids[] = $specific_mappings[$campus_code];
      } else {
        $term_ids[] = $default_tid;
      }

      // Log the mapping for debugging
      \Drupal::logger('health_degree_migration')->notice('Mapped campus code @code to TID @tid', [
        '@code' => $campus_code,
        '@tid' => end($term_ids),
      ]);
    }

    if (empty($term_ids)) {
      throw new MigrateSkipProcessException('No valid taxonomy terms found for the given campus codes.');
    }

    return array_unique($term_ids);
  }
}
