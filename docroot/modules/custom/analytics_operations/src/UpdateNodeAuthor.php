<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateNodeAuthor {

  /**
   * Update the node author for given nodes.
   *
   * @param int $oldUID
   * @param int $newUID
   * @param string $type
   * @param array $nids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(
    int $oldUID,
    int $newUID,
    string $type,
    array $nids,
    int $batchSize = 100
  ): void {
    // If no NIDs are provided, fetch all NIDs for the content type
    if (empty($nids[0])) {
      $nids = Drupal::entityQuery("node")
        ->condition("uid", $oldUID)
        ->condition("type", $type)
        ->accessCheck(FALSE)
        ->execute();
    }

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished'],
    ];

    // Split NIDs into chunks and add as separate operations
    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        [get_called_class(), 'batchProcess'],
        [$chunk, $newUID],
      ];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param int $newUID
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(
    array $nids,
    int $newUID,
    &$context
  ): void {
    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $node) {
      try {
        $node->set('uid', $newUID);
        $node->save();
      }
      catch (EntityStorageException $e) {
        Drupal::logger('analytics_operations')->error(
          'Failed to update node @nid: @message',
          [
            '@nid' => $node->id(),
            '@message' => $e->getMessage(),
          ]
        );
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
  public static function batchFinished($success, $results, $operations): void {
    if ($success) {
      Drupal::logger('analytics_operations')->notice(
        'Update Node Author operation complete.'
      );
      Drupal::messenger()->addMessage('All nodes have been processed.');
    }
    else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError(
        'An error occurred while processing @operation',
        ['@operation' => $error_operation[0]]
      );
    }
  }

}
