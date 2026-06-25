<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Service to save query history into config.
 */
class QueryLogger {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Current user service (account proxy).
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory, MessengerInterface $messenger, AccountProxyInterface $current_user) {
    $this->database = $database;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->currentUser = $current_user;
  }

  /**
   * Save query SQL + meta to config history for a table.
   *
   * Accepts Query objects (SelectQuery/UpdateQuery...), StatementInterface
   * (result of ->execute()), or a plain SQL string. This method is defensive
   * and won't call methods that don't exist on the passed object.
   *
   * @param mixed $query_obj
   *   Query object, statement, or SQL string.
   * @param string|null $table_name
   *   Table name used to build config key. Defaults to 'general'.
   *
   * @return bool
   *   TRUE on success.
   */
  public function saveQueryHistoryToConfig($query_obj, ?string $table_name = NULL): bool {
    $table_name = $table_name ?: 'general';
    $config_name = 'asu_tuition.' . $table_name . '_query_history';
    $config = $this->configFactory->getEditable($config_name);

    $args = [];
    $sql = '';

    // If it's a Query object (SelectQuery, UpdateQuery, etc.), it exposes arguments().
    if (is_object($query_obj) && method_exists($query_obj, 'arguments')) {
      try {
        $args = $query_obj->arguments() ?? [];
      } catch (\Throwable $e) {
        $args = [];
      }

      try {
        $sql = (string) $query_obj;
      } catch (\Throwable $e) {
        $sql = '[query object — SQL not available]';
      }
    }
    // If it's a StatementInterface (already executed), we can't get bound args reliably.
    elseif (is_object($query_obj) && $query_obj instanceof StatementInterface) {
      try {
        $sql = (string) $query_obj;
      } catch (\Throwable $e) {
        $sql = '[statement object — SQL not available]';
      }
      $args = [];
    }
    // If it's a plain SQL string.
    elseif (is_string($query_obj)) {
      $sql = $query_obj;
      $args = [];
    }
    else {
      $sql = print_r($query_obj, TRUE);
      $args = [];
    }

    // Replace placeholders with quoted values for readability.
    if (!empty($args) && is_array($args)) {
      foreach ($args as $key => $value) {
        $quoted = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        $sql = str_replace($key, $quoted, $sql);
      }
    }

    $record = [
      'query' => $sql,
      'time' => date('Y-m-d H:i:s'),
      'user' => $this->currentUser->getDisplayName(),
    ];

    $history_key = $table_name . '_query_history';
    $history = $config->get($history_key) ?? [];

    array_unshift($history, $record);
    $history = array_slice($history, 0, 10);
   //dpm($history, 'query history for ' . $table_name);
    $config->set($history_key, $history)->save();

    return TRUE;
  }

}
