<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateHiddenFromSearch
{
  /**
   * Update nodes that may be missing a value for "field_hidden_from_gsearch".
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit($contentType, int $batchSize = 50): void
  {
    $nids = Drupal::entityQuery("node")
      ->condition("type", $contentType)
      ->accessCheck(false)
      ->execute();

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split NIDs into chunks and add as separate operations
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
    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $node) {
      if ($node->get('field_hidden_from_gsearch')->isEmpty()) {
        $node->set('field_hidden_from_gsearch', 0);
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
      Drupal::logger('analytics_operations')->notice('Update Hidden from Search operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
