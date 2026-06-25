<?php

namespace Drupal\analytics_groups\Services;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

class ResourceService
{
  /**
   * Return a Group ID by its provided Container ID.
   *
   * The Container IDs should be unique, thus only one Group should be returned.
   * To return more than one Group, remove the reset() function.
   *
   * @param string $container_id
   * @return int|array|null
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function loadGroupByContainerId(string $container_id): int|array|null
  {
    if (!$container_id) return null;

    $entity_type_manager = Drupal::entityTypeManager();
    $group_storage = $entity_type_manager->getStorage('group');

    $query = $group_storage->getQuery();

    $or_group = $query->orConditionGroup()
      ->condition('field_doc_library_container_id', $container_id)
      ->condition('field_report_server_container_id', $container_id)
      ->condition('field_tableau_container_id', $container_id);

    $query->condition('type', 'content_owner_group_ty');
    $query->condition($or_group);
    $query->accessCheck(false);

    $entity_ids = $query->execute();

    if (empty($entity_ids)) return null;

    return reset($entity_ids);
  }
}
