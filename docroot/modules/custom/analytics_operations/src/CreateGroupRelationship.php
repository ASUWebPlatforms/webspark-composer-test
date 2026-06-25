<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

class CreateGroupRelationship
{
  /**
   * Create group relationships for the given content type.
   * This assumes that the content type has a field named "field_group".
   *
   * @param string $contentType
   * @param array $nids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(string $contentType, array $nids, int $batchSize = 50): void
  {
    // Check if "field_group" is attached to the content type
    $field_storage = FieldStorageConfig::loadByName('node', 'field_group');
    if (!$field_storage || !in_array($contentType, $field_storage->getBundles())) {
      Drupal::messenger()->addError('The field "field_group" needs to be attached to the content type.');
      return;
    }

    // If no NIDs are provided, fetch all NIDs for the content type
    if (empty($nids[0])) {
      $nids = Drupal::entityQuery("node")
        ->condition("type", $contentType)
        ->accessCheck(false)
        ->execute();
    }

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split NIDs into chunks and add as separate operations
    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $contentType]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param $contentType
   * @param $context
   *
   * @return void
   * @throws EntityStorageException
   */
  public static function batchProcess(array $nids, $contentType, &$context): void
  {
    $nodes = Node::loadMultiple($nids);
    $bundle = 'group_node:' . $contentType;

    foreach ($nodes as $node) {
      $group = $node->get('field_group')->entity;

      if ($group) {
        $relationship = $group->getRelationshipsByEntity($node, $bundle);
        if (empty($relationship)) {
          $group->addRelationship($node, $bundle);
        }
      }
    }

    $context['message'] = t('Processing nodes...');
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
      Drupal::logger('analytics_operations')->notice('Create Group Relationships operation complete.');
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
