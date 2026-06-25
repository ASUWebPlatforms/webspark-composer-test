<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal; // Add this line

/**
 * Maps campus codes to existing taxonomy term TIDs.
 *
 * @MigrateProcessPlugin(
 *   id = "custom_campus_tid_mapper"
 * )
 */
class CustomCampusTidMapper extends ProcessPluginBase {
  /**
   * Return a mapped value (749) once, even if the input value matches multiple items in the array.
   *
   * @param string $value The value to map
   * @param array $inPersonCodes An array of in-person campus codes
   * @return int The mapped value (749) if the $value is in the $inPersonCodes array, otherwise the mapped value for other campus codes
   */
  protected function returnMappedValueOnce($value, array $inPersonCodes)
  {
    // Check if the $value is in the $inPersonCodes array
    if (in_array($value, $inPersonCodes, true)) {
      // Return the mapped value (749) and stop checking the array
      return 749;
    }

    // Map campus codes to specific TIDs for other values
    $mapping = [
      'TEMPE' => 749, 'DTPHX' => 749, 'WEST' => 749, 'POLY' => 749, 'CALHC' => 749, 'EAC' => 749, 'MXCTY' => 749,
      'LOSAN' => 749, 'MESA' => 749, 'CAC' => 749, 'COCHS' => 749, 'PIMA' => 749, 'YAVAP' => 749, 'AWC' => 749,
      'TUCSN' => 749, 'HAINA' => 749, 'WASHD' => 749, 'NEAZ' => 749,
      'SYNC' => 750,
      'ONLNE' => 751,
      // Add additional mappings as needed
    ];

    // Perform the mapping based on the value
    if (isset($mapping[$value])) {
      return $mapping[$value];
    }

    // Optionally skip if no mapping found
    throw new MigrateSkipProcessException();
  }

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
//    \Drupal::logger('custom_campus_tid_mapper')->info('Row data: <pre>@row</pre>', [
//      '@row' => print_r($row, TRUE),
//    ]);
//    \Drupal::logger('custom_campus_tid_mapper')->info('Value: @value', [
//      '@value' => print_r($value, TRUE),
//    ]);
    $campuses = $row->getSourceProperty('campusesOffered') ?? [];
    $acadPlanCode = $row->getSourceProperty('acadPlanCode');
    // Define in-person campus codes that should map to 749
    $inPersonCodes = ['TEMPE', 'DTPHX', 'WEST', 'POLY', 'CALHC', 'EAC', 'MXCTY',
      'LOSAN', 'MESA', 'CAC', 'COCHS', 'PIMA', 'YAVAP', 'AWC',
      'TUCSN', 'HAINA', 'WASHD', 'NEAZ'];

//    $result = $inPersonCodes;

    $mappedValues = [];

    // Check if any of the target campus codes exist in the campusesOffered array.
    if (is_array($campuses)) {
      foreach ($campuses as $campus) {
        if (!empty($campus['campusCode']) && isset($inPersonCodes[$campus['campusCode']])) {
          $mappedValues[] = $inPersonCodes[$campus['campusCode']];
        }
      }
    }
    // Remove duplicates and reindex the array.
    $mappedValues = array_values(array_unique($mappedValues));

//    Drupal::logger('custom_campus_tid_mapper')->info('Mapped in-person campus codes: <pre>@value</pre>', [
//      '@value' => print_r($value, TRUE),
//    ]);

    // Optional logging for other mappings
//    \Drupal::logger('custom_campus_tid_mapper')->info('AcadPlanCode: @acadPlanCode - Mapped campus code "@value" to "@result".', [
//      '@value' => $value,
//      '@result' => $result,
//      '@acadPlanCode' => $acadPlanCode,
//    ]);

    // Log the resulting mapped values array.
//    \Drupal::logger('custom_campus_tid_mapper')->info('AcadPlanCode: @acadPlanCode Mapped values: <pre>@mappedValues</pre>', [
//      '@mappedValues' => $this->returnMappedValueOnce($value, $result),
//      '@acadPlanCode' => $acadPlanCode,
//    ]);

    // Return the mapped value (749) if the $value is in the $inPersonCodes array, otherwise map other campus codes
    return $this->returnMappedValueOnce($value, $mappedValues);
  }
}
