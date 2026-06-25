<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;

class UpdateTableColumnRelationships
{
  /**
   * Update the relationships between tables and columns.
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    // Get all nodes of type "data_dictionary_column"
    $nids = Drupal::entityQuery("node")
      ->condition("type", "data_dictionary_column")
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
      // Find the value of "field_table"
      $field_table = $node->get('field_table')->value;

      // Find the node ID of the node of type "data_dictionary_table" where the "title" matches "field_table"
      $table_nids = Drupal::entityQuery('node')
        ->condition('type', 'data_dictionary_table')
        ->condition('title', $field_table)
        ->accessCheck(false)
        ->execute();

      // If exists, load that node, insert the "data_dictionary_column" node ID into "field_child_columns", and save
      if (!empty($table_nids)) {
        $table_node = Node::load(reset($table_nids));

        if ($table_node->hasField('field_child_columns')) {
          $table_node->get('field_child_columns')->appendItem(['target_id' => $node->id()]);
          $table_node->save();
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
      Drupal::logger('analytics_operations')->notice('Update Table Column Relationships operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
