<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionBreakdownLoad {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */

  /**
   * Public function __construct(RequestStack $requestStack) {
   * $this->requestStack = $requestStack;
   * }
   */
  public function getTuitionBreakdownLoad($result, $term) {
    $values = $result->values;

    $notes = [];
    $tuition_values = [];
    $database = \Drupal::database();
    // If the array is missing information, return an empty array.
    if (empty($values)) {
      return [];
    }
    // ksm($term);
    $values->strm = $term;
    
    // If non-degree undergrad, set acad_career to undergrad.
    $temp_acad_career = ($values->acad_career === 'UGRDN' ? 'UGRD' : $values->acad_career);
    $values->query_acad_career = ($values->acad_career === 'UGRDN' ? 'UGRD' : $values->acad_career);

    if (substr($term, -1) === '7') {

      $base_term['current'] = $result->base_term['fall'];
      $values->query_base_term = $result->base_term['fall'];
      $base_term['current_honors'] = $result->base_term['fall_honors'];
      $values->query_honors_base_term = $result->base_term['fall_honors'];
    }
    elseif (substr($term, -1) === '4') {
      $base_term['current'] = $result->base_term['spring'];
      $values->query_base_term = $result->base_term['spring'];
      $base_term['current_honors'] = $result->base_term['spring_honors'];
      $values->query_honors_base_term = $result->base_term['spring_honors'];
    }
    else {
      $base_term['current'] = $result->base_term['summer'];
      $values->query_base_term = $result->base_term['summer'];
      $base_term['current_honors'] = $result->base_term['summer_honors'];
      $values->query_honors_base_term = $result->base_term['summer_honors'];
    }
    
    // Build the SQL strings.
    $rate_types = $database->query("SELECT * FROM {asu_tuition_rate_type} ORDER BY weight, rate_type")->fetchAllAssoc('rate_type');
    // $rate_type = "P8";
    // ksm($rate_types);
    foreach ($rate_types as $fee_code => $rate_type) {
      $args = [];
      // ksm($rate_type);
      foreach ($values as $key => $value) {
        // ksm($value);
        $arg_key = ':' . $key;
        if (strstr($rate_type->query_string, $arg_key)) {
          $args[$arg_key] = $value;
        }
      }

      // ksm($rate_type);
      // ksm($rate_type->query_string);
      if ($rate_type->query_string) {
        // ksm($fee_code);
        $rate_types[$fee_code]->query = $database->query($rate_type->query_string, $args);

        $rate_types[$fee_code]->query_results = $rate_types[$fee_code]->query->fetchAll();

      }
      // ksm( $rate_types);
      // ksm($rate_types[$fee_code]);.
    }

    // Add all fees to the values array.
    foreach ($rate_types as $key => $fee) {
      // ksm($fee->query_results);
      // ksm($fee);
      if (!empty($fee->query_results)) {
        // ksm($fee->query_results);
        // Add fee description from first row of results.
        $tuition_values[$fee->query_results[0]->fee_code]['descr'] = t('@description', ['@description' => $fee->query_results[0]->descr]);
        $tuition_values[$fee->query_results[0]->fee_code][$fee->query_results[0]->enrlld_hrs] = $fee->query_results[0]->fee_rate;
        // Add any fee code notes to the notes array.
        if (!empty($fee->query_results[0]->note)) {
          $notes[$fee->query_results[0]->fee_code] = $fee->query_results[0]->note;
        }
        // Add the all the fee rates for each enrolled hours.
        foreach ($fee->query_results as $row) {
          $tuition_values[$row->fee_code][$row->enrlld_hrs] = $row->fee_rate;
        }
        // ksm($tuition_values);
      }
    }
 
    // Remove any fees with no description.
    foreach ($tuition_values as $key => $value) {
      // Remove any fees with no description.
      if (empty($value['descr'])) {
        unset($tuition_values[$key]);
      }
    }
    
    // Add the total to the array.
    $tuition_values['total'] = ['descr' => t('Total Tuition & Fees')];
    $num_of_columns = count(reset($tuition_values));
    // ksm($num_of_columns);
    for ($i = 1; $i < $num_of_columns; $i++) {
      $tuition_values['total'][$i] = \Drupal::service('getSumSubArrayByKey')->getSumSubArrayByKey($tuition_values, $i);
      
    }
    
    // Save the notes to the result object.
    $result->notes = array_merge((array) $result->notes, $notes);
    return $tuition_values;
  }

}
