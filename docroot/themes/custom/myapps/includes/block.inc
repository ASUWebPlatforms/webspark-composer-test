<?php
/**
 * @file
 * Theme and preprocess functions for blocks.
 */

/**
 * Implements hook_preprocess_HOOK() for block.html.twig.
 *
 * Process data from the Application Instructions block.
 * This data is processed here and passed to the Software Application template,
 * where it is ulitmately passed to the Instructions Box partial.
 *
 * @param $variables
 * @return void
 */
function myapps_preprocess_block(&$variables): void
{
  // Pass the current domain
  $domain = Drupal::request()->getSchemeAndHttpHost();
  $variables['domain'] = $domain;

  // Pass the current node object
  $route_match = Drupal::routeMatch();
  if ($route_match->getParameter('node')) {
    $node = $route_match->getParameter('node');
    $variables['node'] = $node;
  }

  // Get the block's plugin ID.
  $plugin_id = $variables['elements']['#plugin_id'];
  $block_id = 'd6676fe6-c2cc-4429-8482-a071cf3f6499';
  $target_plugin_id = 'block_content:' . $block_id;

  // Only target the Application Instructions block.
  if ($plugin_id === $target_plugin_id) {
    // Get the current route match.
    $route_match = Drupal::routeMatch();
    $node = $route_match->getParameter('node');

    if ($node) {
      $data = myapps_get_application_instructions($node);

      // Loop through the data array and set the corresponding variables.
      foreach ($data as $key => $value) {
        $variables[$key] = $value;
      }
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * Create a theme hook suggestion for the Application Instructions
 * custom block.
 *
 * @param array $suggestions
 * @param array $variables
 * @return void
 */
function myapps_theme_suggestions_block_alter(array &$suggestions, array $variables): void
{
  // Get the current block
  $block = $variables['elements']['#base_plugin_id'];

  // Check if the block is a custom block from the block library.
  if ($block === 'block_content') {
    // Get the custom block's UUID.
    $uuid = $variables['elements']['content']['#block_content']->uuid();
    $custom_block = Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid);

    if ($custom_block) {
      // Get the "info" field value which is the name of the block in the GUI.
      $info = $custom_block->info->value;

      // Replace any non-alphanumeric characters with an underscore.
      $info_sanitized = preg_replace('/[^a-z0-9_]+/', '_', strtolower($info));

      // Add the custom block's "info" field value to the theme suggestions.
      $suggestions[] = 'block__block_content__' . $info_sanitized;
    }
  }
}

/**
 * Get the application instructions for the current node.
 *
 * Process instruction data for a given node.
 *
 * @param $node
 * @return array
 */
function myapps_get_application_instructions($node): array
{
  $fields = [
    'asu_owned' => ['systems_instruct', 'systems_link'],
    'personal' => ['systems_instructi', 'systems_link'],
    'faculty' => ['instruction', 'software_link'],
    'staff' => ['instructions', 'software_link'],
    'student' => ['instructions', 'software_link'],
    'run_app' => ['instructions', ''],
  ];

  $data = [];

  foreach ($fields as $key => $suffixes) {
    [$instructions_suffix, $link_suffix] = $suffixes;
    $instructions_field = "field_{$key}_{$instructions_suffix}";
    $link_field = $link_suffix ? "field_{$key}_{$link_suffix}" : "field_{$key}";

    if (!$node->$instructions_field->isEmpty()) {
      $data["{$key}_instructions"] = $node->$instructions_field->value;

      if ($link_field && !$node->$link_field->isEmpty()) {
        $data["{$key}_link"] = $node->$link_field->uri;
        $data["{$key}_title"] = ($key == 'run_app') ? 'Launch the app' : $node->$link_field->title;
      }
    }
  }

  return $data;
}
