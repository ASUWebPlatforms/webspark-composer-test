<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Component\Utility\Html;

/**
 *
 */
class AsuTuitionGetUrlParameters {

  /**
   *
   */
  public function getUrlParameters($raw_values = FALSE) {
    $values = [
      'acad_year' => (!empty(\Drupal::request()->query->get('acad_year')) ? Html::escape(strtoupper(\Drupal::request()->query->get('acad_year'))) : ''),
      'include_summer' => (!empty(\Drupal::request()->query->get('include_summer')) ? Html::escape(\Drupal::request()->query->get('include_summer')) : '0'),
      'residency' => (!empty(\Drupal::request()->query->get('residency')) ? Html::escape(strtoupper(\Drupal::request()->query->get('residency'))) : ''),
      'acad_career' => (!empty(\Drupal::request()->query->get('acad_career')) ? Html::escape(strtoupper(\Drupal::request()->query->get('acad_career'))) : ''),
      'admit_term' => (!empty(\Drupal::request()->query->get('admit_term')) ? Html::escape(\Drupal::request()->query->get('admit_term')) : ''),
      'admit_level' => (!empty(\Drupal::request()->query->get('admit_level')) ? Html::escape(\Drupal::request()->query->get('admit_level')) : ''),
      'acad_level' => (!empty(\Drupal::request()->query->get('acad_level')) ? Html::escape(\Drupal::request()->query->get('acad_level')) : ''),
      'honors' => (!empty(\Drupal::request()->query->get('honors')) ? Html::escape(strtoupper(\Drupal::request()->query->get('honors'))) : ''),
      'campus' => (!empty(\Drupal::request()->query->get('campus')) ? Html::escape(strtoupper(\Drupal::request()->query->get('campus'))) : ''),
      'acad_prog' => (!empty(\Drupal::request()->query->get('acad_prog')) ? Html::escape(strtoupper(\Drupal::request()->query->get('acad_prog'))) : ''),
      'program_fee' => (!empty(\Drupal::request()->query->get('program_fee')) ? Html::escape(strtoupper(\Drupal::request()->query->get('program_fee'))) : ''),
      'corporate_partner' => (!empty(\Drupal::request()->query->get('corporate_partner')) ? Html::escape(strtoupper(\Drupal::request()->query->get('corporate_partner'))) : ''),
      'credit_hr' => (!empty(\Drupal::request()->query->get('credit_hr')) ? Html::escape(strtoupper(\Drupal::request()->query->get('credit_hr'))) : ''),
    ];
    // ksm($values);
    // Only return the unmanipulated values.
    if ($raw_values) {
      return $values;
    }

    // Blank out honors if student is not degree-seeking undergraduate.
    if ($values['acad_career'] !== 'UGRD') {
      $values['honors'] = '';

      // Blank out more fields if student is non-degree.
      /*if ($values['acad_career'] === 'UGRDN') {
      $values['program_fee'] = '';
      $values['online_prog'] = '';
      }*/
    }

    return $values;
  }

}
