<?php

/**
 * @file
 * Custom theme hooks. Originally found in theme.inc in renovation theme.
 */

/**
 * Makes the is_front variable available to all templates.
 */
function lib_subwebspark_preprocess(&$variables, $hook) {
  try {
    $variables['is_front'] = \Drupal::service('path.matcher')->isFrontPage();
  }
  catch (Exception $e) {
    $variables['is_front'] = FALSE;
  }
  // Ensure the cache varies correctly (new in Drupal 8.3).
  $variables['#cache']['contexts'][] = 'url.path.is_front';
}

/**
 * Implements hook_preprocess_block()
 *
 * Add landmark navigation and accessible label to topic menu.
 */
function lib_subwebspark_preprocess_block(&$variables, $hook) {
  if ($variables['plugin_id'] === 'system_menu_block:topics-menu') {
    $variables['attributes']['role'] = 'navigation';
    $variables['attributes']['aria-labelledby'] = 'topic-label';
    $variables['title_attributes']['id'] = 'topic-label';
  }
}
