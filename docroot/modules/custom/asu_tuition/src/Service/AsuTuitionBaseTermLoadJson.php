<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionBaseTermLoadJson {

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
  public function getBaseTermLoadJson($result) {
    // Set base_term to 9999 in case the query returns an empty set or student
    // is not a resident undergraduate.
    $base_term = [
      'fall' => '9999',
      'spring' => '9999',
      'fall_honors' => '9999',
      'spring_honors' => '9999',
      'summer' => '9999',
      'summer_honors' => '9999',
    ];
    $values = $result->values;
    $tem = \Drupal::service('readRecords')->readRecords('asu_tuition_tuition_group', ['admit_term' => $values->admit_term, 'acad_level' => $values->admit_level]);
    // Find the base_term for degree-seeking undergraduate students only.
    $record = \Drupal::service('readRecords')->readRecords('asu_tuition_tuition_group', ['admit_term' => $values->admit_term, 'acad_level' => $values->admit_level]);

    if ($values->acad_career === 'UGRD') {
      /*if ($record = \Drupal::service('readRecords')->readRecords('asu_tuition_tuition_group', array('admit_term' => $values->admit_term, 'acad_level' => $values->admit_level))) {*/
      if ($record) {
        // Check to see if base term has expired for each semester.
        $base_term['fall_honors'] = (intval($result->acad_year->fall_term) > intval($record['end_term']) ? '9999' : $record['base_term']);
        $base_term['spring_honors'] = (intval($result->acad_year->spring_term) > intval($record['end_term']) ? '9999' : $record['base_term']);
        $base_term['summer_honors'] = (intval($result->acad_year->summer_term) > intval($record['end_term']) ? '9999' : $record['base_term']);
      }

      // Now set base term to honors base term if student is a resident,
      // otherwise clear base terms.
      if (TRUE || $values->residency === 'RES') {
        $base_term['fall'] = $base_term['fall_honors'];
        $base_term['spring'] = $base_term['spring_honors'];
        $base_term['summer'] = $base_term['summer_honors'];
      }
      else {
        $base_term['fall'] = $base_term['spring'] = $base_term['summer'] = FALSE;
      }
    }
    elseif ($values->acad_career === 'UGRDN' && $values->residency === 'RES') {
      $base_term['fall'] = $base_term['spring'] = $base_term['summer'] = '9999';
      $base_term['fall_honors'] = $base_term['spring_honors'] = $base_term['summer_honors'] = FALSE;
    }
    else {
      $base_term['fall'] = $base_term['spring'] = $base_term['summer'] = $base_term['fall_honors'] = $base_term['spring_honors'] = $base_term['summer_honors'] = FALSE;
    }
    return $base_term;
  }

}
