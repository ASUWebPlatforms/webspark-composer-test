<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateReportSearchStatus
{
  public const MODULE = 'analytics_operations';
  public const NAME = 'Update Report Search Status';

  /**
   * Update the search status of reports.
   *
   * @param string $action
   * @param array $rids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(string $action, array $rids, int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split data into chunks and add as separate operations
    $data = static::getData($rids);
    $chunks = array_chunk($data, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $action]];
    }

    batch_set($batch);
  }

  /**
   * Get the IDs of the nodes to be processed.
   *
   * @param array $rids
   *
   * @return array
   */
  public static function getData(array $rids): array
  {
    return Drupal::entityQuery("node")
      ->condition("type", "report")
      ->condition("field_external_id", $rids, "IN")
      ->accessCheck(false)
      ->execute();
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $data
   * @param string $action
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $data, string $action, &$context): void
  {
    $nodes = Node::loadMultiple($data);
    foreach ($nodes as $node) {
      if ($action == 'publish') {
        static::processData($node);
      } else {
        static::processDataAsHidden($node);
      }
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process the report.
   *
   * @param Node $node
   *
   * @return void
   */
  public static function processData(Node $node): void
  {
    try {
      $node->setPublished();
      $node->save();
    } catch (EntityStorageException $e) {
      Drupal::logger(self::MODULE)->error($e->getMessage());
    }
  }

  /**
   * Process the report as hidden.
   *
   * @param Node $node
   *
   * @return void
   */
  public static function processDataAsHidden(Node $node): void
  {
    try {
      $node->setPublished();
      $node->set("field_hidden_from_gsearch", 1);
      $node->save();
    } catch (EntityStorageException $e) {
      Drupal::logger(self::MODULE)->error($e->getMessage());
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
