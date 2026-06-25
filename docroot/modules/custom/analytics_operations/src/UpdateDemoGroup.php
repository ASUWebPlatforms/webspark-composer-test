<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Exception;

class UpdateDemoGroup
{
  /**
   * Add sample users to the Demo Group
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    $groups = Group::loadMultiple([131]);

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split NIDs into chunks and add as separate operations
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
   * @throws Exception
   */
  public static function batchProcess(array $groups, &$context): void
  {
    foreach ($groups as $group) {
      self::addGroupUser($group, 15073, 'content_owner_group_ty-power');
      self::addGroupUser($group, 15074, 'content_owner_group_ty-member');
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Assign users to the group with the given role.
   *
   * @param $group
   * @param $uid
   * @param $role_id
   *
   * @return void
   * @throws Exception
   */
  public static function addGroupUser($group, $uid, $role_id): void
  {
    $user = User::load($uid);

    if ($group && $user) {
      $membership = $group->getMember($user);

      if ($membership) {
        $group->removeMember($user);
        $group->save();
      }

      $group->addMember($user, ["group_roles" => $role_id]);
      $group->save();
    } else {
      throw new Exception(
        "The user or group does not exist."
      );
    }
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
      Drupal::logger('analytics_operations')->notice('Update Demo Group operation complete.');
      Drupal::messenger()->addMessage('All groups have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError('An error occurred while processing @operation', ['@operation' => $error_operation[0]]);
    }
  }
}
