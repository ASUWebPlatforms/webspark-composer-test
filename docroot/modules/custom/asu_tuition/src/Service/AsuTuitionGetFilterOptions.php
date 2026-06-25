<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionGetFilterOptions {

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
  public function getFilterOptions($table, $field_name) {
    $options = [];
    $results = \Drupal::database()->select($table, 't')
      ->fields('t', [$field_name])
      ->groupBy($field_name)
      ->orderBy($field_name)
      ->execute()
      ->fetchCol();
    foreach ($results as $key => $arr_values) {
      $filter_options[$arr_values] = $arr_values;
    }
    return $filter_options;
  }

}
