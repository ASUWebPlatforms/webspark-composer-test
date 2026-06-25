<?php

namespace Drupal\analytics_operations;

use Drupal;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\Group;
use Drupal\Core\Database\Database;
use Drupal\redirect\Entity\Redirect;

class CreateMissingReportRedirects
{
  public const MODULE = 'analytics_operations';
  public const NAME = 'Create Missing Report Redirects';

  /**
   * Create missing redirects for the reports for the selected group.
   *
   * @param int $gid
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(int $gid, int $batchSize = 50): void
  {
    $ready = static::checkExistingTables();
    if (!$ready) {
      Drupal::messenger()->addError('The "existing_aliases" and/or "existing_redirects" table does not exist.');
      return;
    }

    // Define batch operations
    $batch = [
      'title' => t('Updating nodes...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split data into chunks and add as separate operations
    $data = static::getData($gid);
    $chunks = array_chunk($data, $batchSize);
    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Checks for the existence of the required tables.
   *
   * @return bool
   */
  public static function checkExistingTables(): bool
  {
    $connection = Database::getConnection();
    $schema = $connection->schema();

    if (!$schema->tableExists('existing_aliases') || !$schema->tableExists('existing_redirects')) {
      return false;
    }

    return true;
  }

  /**
   * Get the data to process.
   *
   * @param int $gid
   *
   * @return array
   */
  public static function getData(int $gid): array
  {
    $group = Group::load($gid);
    return Drupal::entityQuery("node")
      ->condition("type", "report")
      ->condition("field_group", $group->id())
      ->accessCheck(false)
      ->execute();
  }

  /**
   * Process a subset of items.
   *
   * @param array $data
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $data, &$context): void
  {
    $nodes = Node::loadMultiple($data);
    foreach ($nodes as $node) {
      try {
        static::processNode($node);
      } catch (EntityMalformedException|EntityStorageException $e) {
        Drupal::logger(self::MODULE)->error($e->getMessage());
      }
    }

    $context['message'] = t('Processing nodes...');
  }

  /**
   * Process the node.
   *
   * @param Node $node
   *
   * @return void
   * @throws EntityStorageException
   * @throws EntityMalformedException
   */
  public static function processNode(Node $node): void
  {
    $internalPath = $node->toUrl()->getInternalPath();
    $aliasResults = Drupal::database()
      ->select("existing_aliases", "ea")
      ->fields("ea", ["alias", "entity_id"])
      ->condition("field_report_id", $node->field_external_id->value)
      ->execute()
      ->fetchAll();

    if (empty($aliasResults)) {
      return;
    }

    foreach ($aliasResults as $aliasResult) {
      // The data here has an extra character we want to strip
      static::createRedirect($internalPath, ltrim($aliasResult->alias, "/"));

      $redirectResults = Drupal::database()
        ->select("existing_redirects", "er")
        ->fields("er", ["redirect_to"])
        ->condition("entity_id", $aliasResult->entity_id)
        ->execute()
        ->fetchAll();

      if (empty($redirectResults)) {
        return;
      }

      foreach ($redirectResults as $redirectResult) {
        static::createRedirect($internalPath, $redirectResult->redirect_to);
      }
    }
  }

  /**
   * Create the redirect.
   *
   * @param string $redirectTo
   * @param string $redirectFrom
   * @param int $status
   *
   * @return void
   * @throws EntityStorageException
   */
  public static function createRedirect(string $redirectTo, string $redirectFrom, int $status = 301): void
  {
    $redirects = Drupal::service("redirect.repository")->findBySourcePath(
      $redirectFrom
    );

    if (!empty($redirects)) {
      return;
    }

    $redirect = Redirect::create([
      "redirect_source" => $redirectFrom,
      "redirect_redirect" => "internal:/" . $redirectTo,
      "status_code" => $status,
      "language" => 'en',
    ]);
    $redirect->save();
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
