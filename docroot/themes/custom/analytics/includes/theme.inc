<?php
/**
 * @file
 * Custom theme hooks.
 */

/**
 * Add suggestion for page content type (bundle).
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * @param array $suggestions
 * @param array $variables
 *
 * @return void
 */
// function analytics_theme_suggestions_page_alter(array &$suggestions, array $variables): void
// {
//   if ($node = Drupal::routeMatch()->getParameter('node') and Drupal::routeMatch()->getRouteName(
//     ) == 'entity.node.canonical') {
//     $content_type = $node->bundle();
//     $suggestions[] = 'page__' . $content_type;
//   }
// }

/**
 * Implements template_preprocess_page_title().
 *
 * @param $variables
 *
 * @return void
 */
// function analytics_preprocess_page_title(&$variables): void
// {
//   // Override page title for report nodes, use field_display_title instead
//   if ($node = Drupal::routeMatch()->getParameter('node')) {
//     // Drupal returns the nid as string if it's a revision page
//     if (gettype($node) != 'string') {
//       $content_type = $node->bundle();

//       if ($content_type == 'report') {
//         $variables['title'] = $node->field_display_title->getString();
//       }
//     }
//   }

//   // Override page title for user pages
//   $account = Drupal::routeMatch()->getParameter('user');
//   if ($account) {
//     if (gettype($account) != 'string') {
//       $variables['title'] = $account->field_name->getString();
//     }
//   }
// }
