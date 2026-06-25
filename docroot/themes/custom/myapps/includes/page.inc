<?php
/**
 * @file
 * Theme and preprocess functions for pages.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Add theme hook suggestions for various HTTP status codes.
 *
 * @param array $suggestions
 * @param array $variables
 * @return void
 */
function myapps_theme_suggestions_page_alter(array &$suggestions, array $variables): void
{
  $request = Drupal::request();

  if ($exception = $request->attributes->get('exception')) {
    $status_code = $exception->getStatusCode();

    if (in_array($status_code, array(401, 403, 404))) {
      $suggestions[] = 'page__' . $status_code;
    }
  }
}
