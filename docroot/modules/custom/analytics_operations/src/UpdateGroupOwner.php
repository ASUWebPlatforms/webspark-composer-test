<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\group\Entity\Group;

class UpdateGroupOwner
{
  /**
   * Update all group owners to user 1.
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    $groups = Group::loadMultiple();

    // Define batch operations
    $batch = [
      'title' => t('Updating groups...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split groups into chunks and add as separate operations
    $chunks = array_chunk($groups, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of groups.
   *
   * @param array $groups
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $groups, &$context): void
  {
    $uid = 1;

    foreach ($groups as $group) {
      if ($group->getOwnerId() != $uid) {
        $group->setOwnerId($uid);
        $group->save();
      }
    }

    $context['message'] = t('Processing groups...');
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
      Drupal::logger('analytics_operations')->notice('Update Group Owner operation complete.');
      Drupal::messenger()->addMessage('All groups have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
