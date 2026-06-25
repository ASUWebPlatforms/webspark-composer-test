<?php
/**
 * @file
 * Theme and preprocess functions for HTML.
 */

/**
 * Implements hook_preprocess_HOOK() for html.html.twig.
 *
 * Create a page where the CheckAccess function can operate.
 * In Drupal 9, we can no longer initiate a direct download like the old site,
 * so we need this page to act as a middle man.
 *
 * @param $variables
 * @return void
 */
function myapps_preprocess_html(&$variables): void
{
  $route_name = Drupal::routeMatch()->getRouteName();

  // Match the page title format found in the meta tag settings.
  if ($route_name === 'asu_myapps.check_access') {
    $site_name = Drupal::config('system.site')->get('name');

    $variables['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'title',
        '#value' => 'Software Download' . ' | ' . $site_name,
      ],
    ];
  }
}
