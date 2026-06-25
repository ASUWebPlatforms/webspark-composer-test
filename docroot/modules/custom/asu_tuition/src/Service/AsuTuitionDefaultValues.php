<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionDefaultValues {

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
  public function defaultValues($field = NULL, $corporate_partnership = FALSE) {
    $request = \Drupal::request();
    $config_data = \Drupal::config('asu_tuition.admin_settings');
    // ksm($config_data);
    /*if (isset($request->$field)) {
    return $request->$field;
    }
    else {
    $defaults = ($corporate_partnership) ? $config_data->get('asu_tuition_cp_search_page_form_defaults', array()) : $config_data->get('asu_tuition_search_page_form_defaults', array());
    return isset($defaults[$field]) ? $defaults[$field] : '';
    }*/
  }

}
