<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionFormDebugMode {

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
  public function formDebugMode() {
    $config_data = \Drupal::config('asu_tuition.admin_settings');
    $debug_mode = $config_data->get('asu_tuition_debug_mode', FALSE);
    // ksm($debug_mode);
    /*if ($debug_mode) {
    drupal_set_message(t('ASU Tuition module in debug mode.'), 'warning', FALSE);
    }*/
    return $debug_mode;
  }

}
