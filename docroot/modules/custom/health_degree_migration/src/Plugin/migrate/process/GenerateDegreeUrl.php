<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Generates a degree link based on degree type and academic plan code.
 *
 * @MigrateProcessPlugin(
 *   id = "generate_degree_url"
 * )
 */
class GenerateDegreeUrl extends ProcessPluginBase {

  /**
   * Transforms the incoming values to generate a degree URL.
   *
   * @param mixed $value
   *   The incoming value (acadPlanCode).
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migrate executable.
   * @param \Drupal\migrate\Row $row
   *   The row object containing other source fields.
   * @param string $destination_property
   *   The name of the destination property being processed.
   *
   * @return string|null
   *   The generated URL, or NULL if no valid degreeType is found.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Retrieve the acadPlanCode and degreeType from the migration source.
    $acadPlanCode = $value;
    $degreeType = $row->getSourceProperty('degreeType');

    // Initialize URL variable.
    $url = NULL;

    // Determine URL based on degreeType.
    switch ($degreeType) {
      case 'UG':
        $url = 'https://degrees.apps.asu.edu/bachelors/major/ASU00/' . $acadPlanCode;
        break;

      case 'GR':
        $url = 'https://degrees.apps.asu.edu/masters-phd/major/ASU00/' . $acadPlanCode;
        break;

      case 'UGCM':
        $url = 'https://degrees.apps.asu.edu/minors/major/ASU00/' . $acadPlanCode;
        break;
    }

    return $url;
  }
}
