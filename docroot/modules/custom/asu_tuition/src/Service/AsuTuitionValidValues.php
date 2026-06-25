<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionValidValues {

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
  public function getValidValues($values) {
    $is_valid = TRUE;

    // These check to see if the values is supplied, if it is supplied, check to see if it exists in the database.
    if (empty($values['acad_year']) || !strlen($values['acad_year']) === 4) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Academic Year field. Please reselect your options.', 'error');
    }
    else {
      if (!\Drupal::service('getValueExists')->getValueExists('acad_year', $values['acad_year'], FALSE)) {
        $is_valid = FALSE;
        \Drupal::messenger()->addMessage('The supplied Academic Year of "' . $values['acad_year'] . '" was not valid. Please reselect your options.', 'error');
      }
    }

    if (empty($values['residency'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Residency field. Please reselect your options.', 'error');
    }
    else {
      if (!\Drupal::service('getValueExists')->getValueExists('residency', $values['residency'])) {
        $is_valid = FALSE;
        \Drupal::messenger()->addMessage('The supplied Residency of "' . $values['residency'] . '" was not valid. Please reselect your options.', 'error');
      }
    }

    if (empty($values['acad_career'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Academic Career field. Please reselect your options.', 'error');
    }
    else {
      if (!\Drupal::service('getValueExists')->getValueExists('acad_career', $values['acad_career'])) {
        $is_valid = FALSE;
        \Drupal::messenger()->addMessage('The supplied Academic Career of "' . $values['acad_career'] . '" was not valid. Please reselect your options.', 'error');
      }
    }

    if (empty($values['campus'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Campus field. Please reselect your options.', 'error');
    }
    else {
      if (!\Drupal::service('getValueExists')->getValueExists('campus', $values['campus'])) {
        $is_valid = FALSE;
        \Drupal::messenger()->addMessage('The supplied Campus of "' . $values['campus'] . '" was not valid. Please reselect your options.', 'error');
      }
    }

    // Check for acad_prog if student is not non-degree.
    if (empty($values['acad_prog']) && $values['acad_career'] !== 'UGRDN') {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the College field. Please reselect your options.', 'error');
    }
    elseif (!empty($values['acad_prog']) && $values['acad_career'] !== 'UGRDN') {
      if (!\Drupal::service('getValueExists')->getValueExists('acad_prog', $values['acad_prog'])) {
        $is_valid = FALSE;
        \Drupal::messenger()->addMessage('The supplied College of "' . $values['acad_prog'] . '" was not valid. Please reselect your options.', 'error');
      }
    }

    // Check for admit_term and admit_level for undergraduate students.
    if ($values['acad_year'] < 2016 && $values['acad_career'] === 'UGRD' && empty($values['admit_term'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Term of Admission field. Please reselect your options.', 'error');
    }

    if ($values['acad_year'] < 2016 && $values['acad_career'] === 'UGRD' && empty($values['admit_level'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('You have been redirected to this form because the URL was missing the Level at Admission field. Please reselect your options.', 'error');
    }

    // These just check to see if the value exists in the database.
    if (!empty($values['admit_level']) && \Drupal::service('getValueExists')->getValueExists('admit_level', $values['admit_level'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('The supplied Admit Level of "' . $values['admit_level'] . '" was not valid. Please reselect your options.', 'error');
    }

    if (!empty($values['housing_plan']) && \Drupal::service('getValueExists')->getValueExists('housing_plan', $values['housing_plan'])) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('The supplied Housing Plan of "' . $values['housing_plan'] . '" was not valid. Please reselect your options.', 'error');
    }

    // Check to make sure WUE is not passed before 2010-2011.
    if ($values['residency'] === 'WUE' && $values['acad_year'] < 2011) {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('WUE is only availble begining with the 2010-2011 academic year. Please reselect your options.', 'error');
    }

    // Check to make sure WUE is only for degree-seeking undergraduate students.
    if ($values['residency'] === 'WUE' && $values['acad_career'] !== 'UGRD') {
      $is_valid = FALSE;
      \Drupal::messenger()->addMessage('WUE is only availble for degree-seeking undergraduate students. Please reselect your options.', 'error');
    }

    return $is_valid;
  }

}
