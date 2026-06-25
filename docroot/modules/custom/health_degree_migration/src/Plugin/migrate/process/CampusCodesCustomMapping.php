<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Maps campusCode values to specific taxonomy term IDs.
 *
 * @MigrateProcessPlugin(
 *   id = "campus_codes_custom_mapping"
 * )
 */
class CampusCodesCustomMapping extends ProcessPluginBase {

  /**
   * Transforms the campusesOffered array into specific taxonomy term IDs.
   *
   * @param mixed $value
   *   The value from the source (an array of campusesOffered).
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration executable.
   * @param \Drupal\migrate\Row $row
   *   The migration row.
   * @param string $destination_property
   *   The destination property.
   *
   * @return array|null
   *   An array of term IDs or NULL if no terms are found.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Initialize an empty array for term IDs.
    $term_ids = [];

    // Define custom mappings for specific campus codes.
    $custom_mappings = [
      'ONLNE' => 751,  // TID for 'Online'
      'SYNC' => 750,   // TID for 'Synchronous'
    ];

    // Define a default TID for other campus codes.
    $default_tid = 749;  // Default TID for all other campus codes.

    // Loop through the campusesOffered array to get each campusCode.
    if (!empty($value) && is_array($value)) {
      foreach ($value as $campus) {
        // Ensure the campus has a campusCode.
        if (isset($campus['campusCode'])) {
          $campus_code = $campus['campusCode'];

          // Check if campusCode has a custom mapping.
          if (isset($custom_mappings[$campus_code])) {
            // Use the custom TID for this campusCode.
            $term_ids[] = $custom_mappings[$campus_code];
          }
          else {
            // Use the default TID for all other campus codes.
            $term_ids[] = $default_tid;
          }
        }
      }
    }

    // Return an array of term IDs, or NULL if no terms were found.
    return !empty($term_ids) ? $term_ids : NULL;
  }
}
