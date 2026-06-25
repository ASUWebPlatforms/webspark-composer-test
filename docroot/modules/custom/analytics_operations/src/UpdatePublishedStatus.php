<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdatePublishedStatus
{
  /**
   * Update the published status for content based upon set criteria.
   *
   * @param string $action
   * @param array $nids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(string $action, array $nids, int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split NIDs into chunks and add as separate operations
    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $action]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param string $action
   * @param $context
   *
   * @return void
   * @throws EntityStorageException
   */
  public static function batchProcess(array $nids, string $action, &$context): void
  {
    $nodes = Node::loadMultiple($nids);

    if ($action == 'publish') {
      foreach ($nodes as $node) {
        $node->setPublished();
        $node->save();
      }
    }

    if ($action == 'unpublish') {
      foreach ($nodes as $node) {
        $node->setUnpublished();
        $node->save();
      }
    }

    if ($action == 'unpublish_and_hide') {
      foreach ($nodes as $node) {
        $node->setUnpublished();
        $node->set('field_hidden_from_gsearch', 1);
        $node->save();
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
      Drupal::logger('analytics_operations')->notice('Update Published Status operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
