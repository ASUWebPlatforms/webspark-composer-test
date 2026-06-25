<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateNodeAliasOption
{
  public const MODULE = 'analytics_operations';
  public const NAME = 'Update Node Alias Option';

  /**
   * Ensure that all nodes of the selected content type have the
   * "Generate automatic URL alias" option enabled.
   *
   * @param string $type
   * @param array $nids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(string $type, array $nids, int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // If no NIDs are provided, fetch all NIDs for the content type
    if (empty($nids[0])) {
      $nids = static::getData($type);
    }

    // Split data into chunks and add as separate operations
    $chunks = array_chunk($nids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Get the IDs of the nodes to be processed.
   *
   * @param string $type
   *
   * @return array
   */
  public static function getData(string $type): array
  {
    return Drupal::entityQuery("node")
      ->condition("type", $type)
      ->accessCheck(false)
      ->execute();
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $nids
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $nids, &$context): void
  {
    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $node) {
      static::processData($node);
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process the node.
   * Set the value of the automatic alias option to true.
   * Note that this will automatically generate the alias for the node.
   *
   * @param Node $node
   *
   * @return void
   */
  public static function processData(Node $node): void
  {
    if ($node->hasField("path")) {
      try {
        $node->set("path", ["pathauto" => 1]);
        $node->save();
      } catch (EntityStorageException $e) {
        Drupal::logger(self::MODULE)->error($e->getMessage());
      }
    }
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
      Drupal::logger(self::MODULE)->notice('@name operation complete.', ['@name' => self::NAME]);
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
