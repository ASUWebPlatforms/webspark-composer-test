<?php

// The namespace is Drupal\[module_key]\[Directory\Path(s)]
namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */
class AsuSearchPageTitle {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function searchPageTitle($field_name = NULL) {
    // Build $value (not shown)
    $config_data = \Drupal::config('asu_tuition.admin_settings');
    // ksm($config_data);
    $titles = $config_data->get('asu_tuition_search_page_form_titles');
    $title_values = \Drupal::service('listValues')->listValues($titles);

    return (isset($title_values[$field_name]) ? t($title_values[$field_name]) : '');

  }

}
