<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\flag\Entity\Flagging;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

class UpdateFlaggingFields
{
  public const MODULE = 'analytics_operations';
  public const NAME = 'Update Flagging Fields';

  /**
   * Add missing field data to existing user favorites.
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split data into chunks and add as separate operations
    $data = static::getData();
    $chunks = array_chunk($data, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Get the existing user favorites from the database.
   *
   * @return array
   */
  public static function getData(): array
  {
    $data = [];
    try {
      $data = Drupal::entityTypeManager()->getStorage("flagging")->loadMultiple();
    } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      Drupal::logger(self::MODULE)->error($e->getMessage());
    }

    return $data;
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $data
   * @param $context
   *
   * @return void
   * @throws EntityMalformedException
   */
  public static function batchProcess(array $data, &$context): void
  {
    foreach ($data as $item) {
      static::processData($item);
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process the existing user favorites.
   *
   * @param Flagging $item
   *
   * @return void
   * @throws EntityMalformedException
   */
  public static function processData(Flagging $item): void
  {
    $flagged_entity = $item->getFlaggable();
    $item->set("field_entity_title", $flagged_entity->label());
    $item->set("field_entity_url", $flagged_entity->toUrl()->toString());

    if ($item->getFlagId() === "favorites") {
      $item->set("field_entity_type", $flagged_entity->bundle());
      $item->set(
        "field_entity_description",
        $flagged_entity->get("field_description")->value
      );
      if ($flagged_entity->hasField("field_group")) {
        $item->set(
          "field_entity_group",
          $flagged_entity->get("field_group")->getValue()
        );
      }
    }

    if ($item->getFlagId() === "favorite_group") {
      $item->set("field_entity_type", "group");
      $item->set(
        "field_entity_description",
        $flagged_entity->get("field_group_description")->value
      );
    }

    if ($item->getFlagId() === "favorite_terms") {
      $item->set("field_entity_type", $flagged_entity->bundle());
      $termDescription = strip_tags($flagged_entity->get("description")->value);
      $item->set("field_entity_description", $termDescription);
    }

    try {
      $item->save();
    } catch (EntityStorageException $e) {
      Drupal::logger(self::MODULE)->error($e->getMessage());
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
