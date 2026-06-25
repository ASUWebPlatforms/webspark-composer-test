<?php
/**
 * @file
 * Theme and preprocess functions for nodes.
 */

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Add theme suggestions.
 * Implements hook_theme_suggestions_HOOK_alter() for node templates.
 *
 * @param array $suggestions
 * @param array $variables
 *
 * @return void
 */
function analytics_theme_suggestions_node_alter(array &$suggestions, array $variables): void
{
  $node = $variables['elements']['#node'];

  if ($node instanceof NodeInterface) {
    $content_type = $node->getType();
    $allowed_content_types = ['data_dictionary_column', 'data_dictionary_table', 'data_dictionary_schema'];

    if (in_array($content_type, $allowed_content_types)) {
      $suggestions[] = 'node__data_dictionary';
      $suggestions[] = 'node__data_dictionary__full';
    }
  }
}

/**
 * Generate a Favorites link to be manually placed in templates.
 *
 * @see https://www.drupal.org/node/295383#comment-12504094
 *
 * @param $variables
 *
 * @return void
 */
function analytics_preprocess_node(&$variables): void
{
  $node = $variables['node'];
  $favorites_link = [
    '#lazy_builder' => [
      'flag.link_builder:build',
      [
        $node->getEntityTypeId(),
        $node->id(),
        'favorites',
      ]
    ],
    '#create_placeholder' => true,
  ];
  $variables['favorites_link'] = $favorites_link;
}

/**
 * Group the reports by category.
 * This will be used to generate the sidebar menu.
 * Implements hook_preprocess_node__dashboard_tableau_menu_nav().
 *
 * @param $variables
 *
 * @return void
 */
function analytics_preprocess_node__dashboard_tableau_menu_nav(&$variables): void
{
  $node = $variables['node'];
  $report_groups = [];

  foreach ($node->get('field_menu_items_config')->getValue() as $item) {
    $paragraph = Paragraph::load($item['target_id']);
    $isVisible = $paragraph->get('field_visible')->getValue();

    // Check if the paragraph is visible
    if ($isVisible[0]['value']) {
      $category = $paragraph->get('field_category')->getString();
      $title = $paragraph->get('field_title')->getString();
      $ready = $paragraph->get('field_ready')->getValue();
      $url = $paragraph->get('field_tableau_dashboard_url')->getString();

      $report_groups[$category][] = [
        'title' => $title,
        'ready' => $ready[0]['value'],
        'url' => $url,
      ];
    }
  }

  $variables['report_groups'] = $report_groups;
}

/**
 * Get the author of the report.
 *
 * @param $node
 *
 * @return false|mixed
 * @throws InvalidPluginDefinitionException
 * @throws PluginNotFoundException
 */
function getReportAuthor($node): mixed
{
  $author = $node->getOwner();
  $email = $node->get('field_created_by')?->getString();

  if ($email) {
    // Grab only the username from the email address
    $parts = explode('@', $email);
    $username = $parts[0];
    $user_storage = Drupal::entityTypeManager()->getStorage('user');
    $users = $user_storage->loadByProperties(['name' => $username]);

    if (!empty($users)) {
      $user = reset($users);
      $author = $user;
    }
  }

  return $author;
}

/**
 * Prepare metadata for Data Dictionary nodes.
 * NOTE: Currently not in use, but may be needed in the future.
 *
 * @param $node
 *
 * @return array
 */
// function getDataDictionaryMeta($node): array
// {
//   $meta = [];
//   $databaseFieldDefinition = $node->getFieldDefinition('field_database');
//   $databaseFieldAllowedValues = $databaseFieldDefinition->getSetting('allowed_values');
//   $databaseFieldValue = $node->get('field_database')?->getString();
//   $databaseFieldLabel = $databaseFieldAllowedValues[$databaseFieldValue] ?? $databaseFieldValue;

//   $meta['database'] = $databaseFieldLabel;
//   $meta['schema'] = $node->hasField('field_schema_name') ? $node->get('field_schema_name')?->getString() : $node->get(
//     'field_schema'
//   )?->getString();
//   $meta['table'] = $node->hasField('field_table') ? $node->get('field_table')?->getString() : null;

//   return $meta;
// }
