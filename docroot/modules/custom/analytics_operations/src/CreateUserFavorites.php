<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

class CreateUserFavorites
{
  /**
   * Create user favorites from the existing website.
   *
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $batchSize = 50): void
  {
    $ready = static::checkExistingUserFavoritesTable();
    if (!$ready) {
      Drupal::messenger()->addError('The "existing_user_favorites" table does not exist.');
      return;
    }

    $favorites = static::getExistingUserFavorites();

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split favorites into chunks and add as separate operations
    $chunks = array_chunk($favorites, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Checks for the existence of the "existing_user_favorites" table.
   *
   * @return bool
   */
  public static function checkExistingUserFavoritesTable(): bool
  {
    $connection = Database::getConnection();
    $schema = $connection->schema();

    if (!$schema->tableExists('existing_user_favorites')) {
      Drupal::logger('analytics_operations')->error('The "existing_user_favorites" table does not exist.');
      return false;
    }

    return true;
  }

  /**
   * Get the existing user favorites from the database.
   *
   * @return array
   */
  public static function getExistingUserFavorites(): array
  {
    $connection = Database::getConnection();

    $query = $connection
      ->select("existing_user_favorites", "euf")
      ->fields("euf"); // Fetch all fields from the existing_user_favorites table.
    $result = $query->execute()->fetchAll();

    $favorites = [];
    foreach ($result as $record) {
      $favorites[] = [
        "node_id" => $record->node_id,
        "node_data" => json_decode($record->node_data, true),
        "user_data" => json_decode($record->user_data, true)
      ];
    }

    return $favorites;
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $favorites
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $favorites, &$context): void
  {
    foreach ($favorites as $favorite) {
      static::processExistingUserFavorites($favorite);
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process the existing user favorites.
   *
   * @param array $favorite
   *
   * @return void
   */
  public static function processExistingUserFavorites(array $favorite): void
  {
    $userData = $favorite["user_data"];
    $nodeData = $favorite["node_data"];
    $results = [];

    if ($nodeData["content_type"] === "page") {
      try {
        $results = Drupal::entityTypeManager()
          ->getStorage("node")
          ->loadByProperties([
            "title" => $nodeData["title"]
          ]);
      } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
        Drupal::logger("analytics_operations")->error(
          "Error loading node by properties: @error",
          ["@error" => $e->getMessage()]
        );
        return;
      }
      if (empty($results)) {
        Drupal::logger("analytics_operations")->warning(
          "Unable to find a match for page title: @title",
          ["@title" => $nodeData["title"]]
        );
        return;
      }
    } elseif ($nodeData["content_type"] === "report") {
      try {
        $results = Drupal::entityTypeManager()
          ->getStorage("node")
          ->loadByProperties([
            "type" => "report",
            "field_external_id" => $nodeData["field_report_id"]
          ]);
      } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
        Drupal::logger("analytics_operations")->error(
          "Error loading node by properties: @error",
          ["@error" => $e->getMessage()]
        );
        return;
      }
      if (empty($results)) {
        Drupal::logger("analytics_operations")->warning(
          "Unable to find a match for report with field_external_id: @field_external_id",
          ["@field_external_id" => $nodeData["field_report_id"]]
        );
        return;
      }
    }

    foreach ($results as $node) {
      static::createFavorite($node, $userData);
    }
  }

  /**
   * Create a favorite association for a node and users using the Flag module.
   *
   * @param Node $node
   * @param array $userData
   *
   * @return void
   */
  public static function createFavorite(Node $node, array $userData): void
  {
    $flagService = Drupal::service("flag");
    $flag = $flagService->getFlagById("favorites");

    foreach ($userData as $user) {
      $userEntity = user_load_by_name($user["name"]);
      if ($userEntity && $flag) {
        // Check if the node is already flagged by the user to avoid duplicates
        if (!$flagService->getFlagging($flag, $node, $userEntity)) {
          $flagService->flag($flag, $node, $userEntity);
        }
      }
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
      Drupal::logger('analytics_operations')->notice('Create User Favorites operation complete.');
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
