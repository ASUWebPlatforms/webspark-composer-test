<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */
class AsuSearchPageDescription {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function searchPageDescription($field_name = NULL) {

    $config_data = \Drupal::config('asu_tuition.admin_settings');
    $description = $config_data->get('asu_tuition_search_page_form_descriptions');
    $desc_values = \Drupal::service('listValues')->listValues($description);
    // Return implode("/n", $lines);
    // ksm($title_values);
    // ksm($desc_values);
    if (!empty($desc_values)) {
      $description_value = '<p>' . $desc_values[$field_name] . '</p>';

      // $description_value = '<div class="uds-tooltip-bg-white"><div class="uds-tooltip-container1"><button tabindex="0" class="uds-tooltip uds-tooltip-white" aria-describedby="tooltip-desc-'.$field_name.'"><span class="fa-stack"><svg class="svg-inline--fa fa-circle fa-w-16 fa-stack-2x" aria-hidden="false" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg><svg class="svg-inline--fa fa-info fa-w-6 fa-stack-1x" aria-hidden="false" focusable="true" data-prefix="fas" data-icon="info" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512" data-fa-i2svg=""><path fill="currentColor" d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path></svg></span><span class="uds-tooltip-visually-hidden">Notifications</span></button><div role="tooltip" class="uds-tooltip-description" id="tooltip-desc-'.$field_name.'"><span class="uds-tooltip-heading">'.$desc_values[$field_name].'</span></div></div></div>';
    }
    else {
      $description_value = '';
    }
    // Return (isset($desc_values[$field_name]) ? t($desc_values[$field_name]) : '');
    // ksm($description_value);
    return $description_value;
    // Return $description_value;.
    /*return [
    '#type' => '#markup',
    '#markup' => render($description_value),
    ];*/
  }

}
