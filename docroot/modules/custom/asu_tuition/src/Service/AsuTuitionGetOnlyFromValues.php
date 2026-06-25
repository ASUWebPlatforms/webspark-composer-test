<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionGetOnlyFormValues {

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
  public function getFormValues($form_values) {
    $field_names = [
      'acad_year',
      'include_summer',
      'residency',
      'acad_career',
      'admit_term',
      'admit_level',
      'acad_level',
      'campus',
      'acad_prog',
      'honors',
      'program_fee',
      'corporate_partner',
    ];
    return array_intersect_key($form_values, array_fill_keys($field_names, NULL));
  }

}
