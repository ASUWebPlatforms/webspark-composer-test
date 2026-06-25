<?php
/**
 * @file
 * Theme and preprocess functions for forms.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Create a theme hook suggestion for the Software Applications view form.
 *
 * @param array $suggestions
 * @param array $variables
 * @return void
 */
function myapps_theme_suggestions_form_alter(array &$suggestions, array $variables): void
{
  $request = Drupal::routeMatch()->getRouteObject()->getDefault('view_id');

  if (!empty($request)) {
    $suggestions[] = 'form__' . $request;
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Add a theme hook suggestion for the Software Applications View form.
 *
 * @param array $suggestions
 * @param array $variables
 * @return void
 */
function myapps_theme_suggestions_form_element_alter(array &$suggestions, array $variables): void
{
  $request = Drupal::routeMatch()->getRouteObject()->getDefault('view_id');

  if (!empty($request)) {
    $suggestions[] = 'form_element__' . $request;
  }
}
