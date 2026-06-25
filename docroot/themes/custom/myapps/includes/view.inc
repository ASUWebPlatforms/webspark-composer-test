<?php
/**
 * @file
 * Theme and preprocess functions for views.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Add a theme hook suggestion for the View: Summary Unformatted.
 *
 * @param array $suggestions
 * @param array $variables
 * @return void
 */
function myapps_theme_suggestions_views_view_summary_unformatted_alter(array &$suggestions, array $variables): void
{
  $request = Drupal::routeMatch()->getRouteObject()->getDefault('view_id');

  if (!empty($request)) {
    $suggestions[] = 'views_view_summary_unformatted__' . $request;
  }
}
