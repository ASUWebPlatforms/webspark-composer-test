<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

class UpdateGroupRelationship
{
  /**
   * Update group relationships for the given content type.
   * This assumes that the content type has a field named "field_group".
   *
   * @param array $data {
   *     @type string $operation The operation to perform.
   *     @type array $nids The node IDs to process.
   *     @type int $gid The current group ID.
   *     @type int|null $new_gid The new group ID (required if operation is 'transfer').
   * }
   *
   * @return void
   */
  public static function batchInit(array $data): void
  {
    ['bundle' => $bundle, 'operation' => $operation, 'nids' => $nids, 'gid' => $gid, 'new_gid' => $new_gid] = $data;

    // Check if "field_group" is attached to the content type
    $field_storage = FieldStorageConfig::loadByName('node', 'field_group');
    if (!$field_storage || !in_array($bundle, $field_storage->getBundles())) {
      Drupal::messenger()->addError('The field "field_group" needs to be attached to the content type.');
      return;
    }

    if (!in_array($operation, ['delete', 'transfer'], true)) {
      Drupal::messenger()->addError('The operation failed, please try again later.');
      return;
    }

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split NIDs into chunks and add as separate operations
    $chunks = array_chunk($nids, 50);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $bundle, $operation, $gid, $new_gid]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param string $bundle
   * @param string $operation
   * @param string $gid
   * @param string $new_gid
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $nids, string $bundle, string $operation, string $gid, string $new_gid, &$context): void
  {
    $nodes = Node::loadMultiple($nids);
    $bundle = 'group_node:' . $bundle;

    if ($operation == 'delete') {
      self::delete($nodes, $bundle, $gid);
    }

    if ($operation == 'transfer') {
      self::transfer($nodes, $bundle, $gid, $new_gid);
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Remove a node from a group.
   *
   * @param array $nodes
   * @param string $bundle
   * @param string $gid
   *
   * @return void
   */
  public static function delete(array $nodes, string $bundle, string $gid): void
  {
    foreach ($nodes as $node) {
      // Intentionally doing this for each node to ensure that groups match
      $group = $node->get('field_group')->entity;

      if (empty($group) || $group->id() !== $gid) {
        continue;
      }

      $relationships = $group->getRelationshipsByEntity($node, $bundle);
      if ($relationships) {
        foreach ($relationships as $rel) {
          $rel->delete();
        }
        $node->set('field_group', []);
        $node->save();
      }
    }
  }

  /**
   * Transfer content from one group to another.
   *
   * @param array $nodes
   * @param string $bundle
   * @param string $gid
   * @param string $new_gid
   *
   * @return void
   */
  public static function transfer(array $nodes, string $bundle, string $gid, string $new_gid): void
  {
    $new_group = Group::load($new_gid);
    if (empty($new_group)) {
      return;
    }

    foreach ($nodes as $node) {
      $group = $node->get('field_group')->entity;

      if (empty($group) || $group->id() !== $gid) {
        continue;
      }

      // Remove from the current group
      $relationships = $group->getRelationshipsByEntity($node, $bundle);
      if ($relationships) {
        foreach ($relationships as $rel) {
          $rel->delete();
        }
      }

      // Add to the new group
      $new_relationships = $new_group->getRelationshipsByEntity($node, $bundle);
      if (empty($new_relationships)) {
        $new_group->addRelationship($node, $bundle);
      }

      // Update the field_group value
      $node->set('field_group', ['target_id' => $new_gid]);
      $node->save();
    }
  }

  /**
   * Finish callback for the batch.
   *
   * @param $success
   * @param $results
   * @param $operations
   *
   * @return void
   */
  public static function batchFinished($success, $results, $operations): void
  {
    if ($success) {
      Drupal::logger('analytics_operations')->notice('Update Group Relationships operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError(
        'An error occurred while processing @operation',
        ['@operation' => $error_operation[0]]
      );
    }
  }
}
