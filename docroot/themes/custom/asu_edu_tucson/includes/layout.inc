<?php 

/**
 * Implements hook_preprocess_HOOK().
 */
function asu_edu_tucson_preprocess_layout__twocol_bootstrap_section(&$variables) {
    $variables['attributes']['class'][] = 'asu-no-padding';
  }