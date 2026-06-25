<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionFeeTypesLoad {

  /**
   *
   */
  public function getFeeTypesLoad($result) {
    $fee_types = [];
    // ksm($result->tuition_fees);.
    $fee_codes = array_keys($result->tuition_fees);
    $database = \Drupal::database();
    if ($fee_codes) {
      // $result = db_query('SELECT DISTINCT fee_type FROM {asu_tuition_fee_code} WHERE fee_code IN (' . db_placeholders($fee_codes, 'char') . ')', $fee_codes);
      $result = $database->select('asu_tuition_fee_code', 'f')
        ->fields('f')
        ->distinct()
        ->condition('f.fee_code', $fee_codes, 'IN')
        ->execute();
      foreach ($result as $record) {
        $fee_types[] = $record->fee_type;
      }
    }
    // ksm($fee_types);
    return $fee_types;
  }

}
