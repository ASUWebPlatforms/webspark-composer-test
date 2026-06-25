<?php

/**
 * @file
 * Theme and preprocess functions for views.
 */

/**
 * Implements hook_preprocess_views_view().
 * Add the view name to the variables array.
 *
 * @param array $variables
 */
function analytics_preprocess_views_view(array &$variables): void
{
  $view = $variables['view'];
  $variables['view_name'] = str_replace('_', '-', $view->id());
  $variables['view_machine_name'] = $view->id();
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 * Add the view name to the variables array.
 *
 * @param array $suggestions
 * @param array $variables
 */
function analytics_theme_suggestions_views_view_grid_alter(array &$suggestions, array $variables): void
{
  $view = $variables['view'];
  $suggestions[] = 'views_view_grid__' . $view->id() . '__' . $view->current_display;
  $suggestions[] = 'views_view_grid__' . $view->id();
}

/**
 * Add the ability to edit metadata check and flag.
 *
 * @param $variables
 *
 * @return void
 */
// function analytics_preprocess_views_view_fields(&$variables): void
// {
//   $metadata_enhancer_roles = [
//     'pwr_',
//     'administrator',
//   ];

//   $metadata_enhancer = false;

//   foreach ($variables['user']->getRoles() as $role) {
//     if (in_array($role, $metadata_enhancer_roles)) {
//       $metadata_enhancer = true;
//     }
//   }

//   $variables['metadata_enhancer'] = $metadata_enhancer;
//   $row_nid = (isset($variables['row']->{'entity:node/nid'})) ? $variables['row']->{'entity:node/nid'}[0] : false;
//   $variables['row_nid'] = $row_nid;

//   // Favorites flag for content
//   $flag_link = null;
//   $flag_link_service = Drupal::service('flag.link_builder');

//   if ($row_nid) {
//     $flag_link = $flag_link_service->build('node', $row_nid, 'favorite');
//   }

//   $variables['flag_link'] = $flag_link;
// }
