<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateBodyFormat
{
  /**
   * Update the body field text format for the given content type.
   *
   * @param $contentType
   * @param $newFormat
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit($contentType, $newFormat, int $batchSize = 50): void
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
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $newFormat]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param $newFormat
   * @param $context
   *
   * @return void
   * @throws EntityStorageException
   */
  public static function batchProcess(array $nids, $newFormat, &$context): void
  {
    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $node) {
      if ($node->hasField("body")) {
        $node->body->format = $newFormat;
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
      Drupal::logger('analytics_operations')->notice('Update Body Format operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
