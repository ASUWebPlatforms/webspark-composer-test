<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionFullTimeTuitionLoad {

  /**
   *
   */
  public function getFullTimeTuitionLoad($result) {
    $tuition_values = [];
    $full_time_credits = $result->full_time_credits;
    if (!empty($result->breakdown['fall'])) {
      reset($result->breakdown['fall']);
    }

    // \Drupal::logger('result')->info('<pre>' . print_r($result, true) . '</pre>');
    foreach ($result->breakdown['fall'] as $key => $fall_row) {
      // Make sure to use the *999 fee_code is being used when the student's base
      // term changes between semesters.
      // @todo is this method correct for all fee types?
      if (!isset($result->breakdown['spring'][$key]) && substr(key($result->breakdown['spring']), -3) == '999') {
        $spring_key = key($result->breakdown['spring']);
        $spring_row = $result->breakdown['spring'][$spring_key];
      }
      else {
        $spring_row = $result->breakdown['spring'][$key];
      }
      
      if (!\Drupal::service('includeSummer')->includeSummer($result)) {
        $spring_row = current($result->breakdown['spring']);
        next($result->breakdown['spring']);

        $tuition_values[$key] = [
          'descr' => $fall_row['descr'],
          'fall' => $fall_row[$full_time_credits],
          'spring' => $spring_row[$full_time_credits],
          'total' => $fall_row[$full_time_credits] + $spring_row[$full_time_credits],
        ];
      }
      else {
        $spring_row = current($result->breakdown['spring']);
        next($result->breakdown['spring']);

        $summer_row = current($result->breakdown['summer']);
        next($result->breakdown['summer']);
        $summerRow = $summer_row ? $summer_row[$full_time_credits] : 0;
        $tuition_values[$key] = [
          'descr' => $fall_row['descr'],
          'fall' => $fall_row[$full_time_credits],
          'spring' => $spring_row[$full_time_credits],
          'summer' => $summerRow,
          'total' => $fall_row[$full_time_credits] + $spring_row[$full_time_credits] + $summerRow,
        ];
      }
    }
    // ksm($tuition_values);
    return $tuition_values;
  }

}
