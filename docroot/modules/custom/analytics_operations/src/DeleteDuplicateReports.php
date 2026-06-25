<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\GroupRelationship;

class DeleteDuplicateReports
{
  /**
   * Delete duplciated reports.
   *
   * @param array $nids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(array $nids, int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Deleting nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param $context
   *
   * @return void
   * @throws EntityStorageException
   */
  public static function batchProcess(array $nids, &$context): void
  {
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if (!$node) {
        continue;
      }

      $relationships = GroupRelationship::loadByEntity($node);
      if (empty($relationships)) {
        continue;
      }

      foreach ($relationships as $relationship) {
        $relationship->delete();
      }
      $node->delete();
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
      Drupal::logger('analytics_operations')->notice('Delete Duplicate Reports operation complete.');
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
