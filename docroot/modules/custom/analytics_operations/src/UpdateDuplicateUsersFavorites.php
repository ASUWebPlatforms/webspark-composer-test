<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;

class UpdateDuplicateUsersFavorites
{
  public const MODULE = 'analytics_operations';
  public const NAME = 'Update Duplicate Users Favorites';

  /**
   * Reassign the Favorites for the given users.
   *
   * @param string $flag
   * @param int $uid
   * @param array $eids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(string $flag, int $uid, array $eids, int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Updating favorites...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split eids into chunks and add as separate operations
    $chunks = array_chunk($eids, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk, $flag, $uid]];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of entities.
   *
   * @param array $chunk
   * @param string $flag
   * @param int $uid
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $chunk, string $flag, int $uid, &$context): void
  {
    $flagService = Drupal::service("flag");
    $favorite = $flagService->getFlagById($flag);
    $user = User::load($uid);

    if ($flag == 'favorites') {
      $entities = Node::loadMultiple($chunk);
    } else {
      $entities = Group::loadMultiple($chunk);
    }

    foreach ($entities as $item) {
      // Check if the entity is already flagged by the user to avoid duplicates
      if (!$flagService->getFlagging($favorite, $item, $user)) {
        $flagService->flag($favorite, $item, $user);
      }
    }

    $context['message'] = t('Processing entities...');
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
