<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\node\Entity\Node;

class UpdateGrouplessReports
{
  /**
   * Ensure that reports without a group are unpublished and hidden from search.
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Get the initial set of Node IDs to process and batch them
    $nids = static::setup();
    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Get the initial set of Node IDs to process.
   *
   * @return int|array
   */
  public static function setup(): int|array
  {
    return Drupal::entityQuery("node")
      ->condition("type", 'report')
      ->condition("field_group", null, "IS NULL")
      ->accessCheck(false)
      ->execute();
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $chunk
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $chunk, &$context): void
  {
    $nodes = Node::loadMultiple($chunk);
    foreach ($nodes as $node) {
      static::process($node);
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process a node.
   *
   * @param $node
   *
   * @return void
   */
  public static function process($node): void
  {
    $node->setUnpublished();
    $node->set("field_hidden_from_gsearch", 1);
    $node->save();
  }

  /**
   * Finish callback for the batch.
   *
   * @param bool $success
   * @param array $results
   * @param array $operations
   *
   * @return void
   */
  public static function batchFinished(bool $success, array $results, array $operations): void
  {
    if ($success) {
      Drupal::logger('analytics_operations')->notice('Update Groupless Reports operation complete.');
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
