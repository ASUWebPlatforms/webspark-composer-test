<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateSkipProcessException;

/**
 * Provides a process plugin for campus code transformation.
 *
 * @MigrateProcessPlugin(
 *   id = "campus_test"
 * )
 */
class CampusTest extends ProcessPluginBase {

  /**
   * Transforms the input value based on predefined campus code mappings.
   *
   * @param string $value
   *   The input campus code.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migrate executable.
   * @param \Drupal\migrate\Row $row
   *   The current row being processed.
   * @param string $destination_property
   *   The destination property being processed.
   *
   * @return array|null
   *   The mapped term IDs or NULL if no matching campus code is found.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Access the campusesOffered array from the row data.
    $campuses = $row->getSourceProperty('campusesOffered') ?? [];
    $acadPlanCode = $row->getSourceProperty('acadPlanCode');

    $campusCodeMappings = [
      'TEMPE' => 749, 'DTPHX' => 749, 'WEST' => 749, 'POLY' => 749, 'CALHC' => 749, 'EAC' => 749, 'MXCTY' => 749,
      'LOSAN' => 749, 'MESA' => 749, 'CAC' => 749, 'COCHS' => 749, 'PIMA' => 749, 'YAVAP' => 749, 'AWC' => 749,
      'TUCSN' => 749, 'HAINA' => 749, 'WASHD' => 749, 'NEAZ' => 749,
      'SYNC' => 750,
      'ONLNE' => 751,
    ];

    $mappedValues = [];

    // Check if any of the target campus codes exist in the campusesOffered array.
    if (is_array($campuses)) {
      foreach ($campuses as $campus) {
        if (!empty($campus['campusCode']) && isset($campusCodeMappings[$campus['campusCode']])) {
          $mappedValues[] = $campusCodeMappings[$campus['campusCode']];
        }
      }
    }

    // Remove duplicates and reindex the array.
    $mappedValues = array_values(array_unique($mappedValues));

    // Log the resulting mapped values array.
    \Drupal::logger('health_degree_migration')->info('AcadPlanCode: @acadPlanCode Mapped values: <pre>@mappedValues</pre>', [
      '@mappedValues' => print_r($mappedValues, TRUE),
      '@acadPlanCode' => $acadPlanCode,
    ]);

    // Return the mapped values if found; otherwise, skip the row.
    if (!empty($mappedValues)) {
      return $mappedValues;
    }

    // If no matching campus code is found, skip this row.
    throw new MigrateSkipProcessException('No relevant campus code found in campusesOffered.');
  }
}
