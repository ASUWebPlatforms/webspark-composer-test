<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionGetSchema {

  /**
   * Get the asu_tuition module's schema info.
   *
   * @param $reset
   *   If TRUE then rebuild the schema info.
   */
  public function getSchema($reset = FALSE) {
    static $tables = [];
    $database = \Drupal::database();
    // Reset or initialize the schema cache.
    if ($reset || empty($tables)) {
      \Drupal::moduleHandler()->loadInclude('asu_tuition', 'install');
      $schema = \Drupal::moduleHandler()->invoke('asu_tuition', 'schema');
      $all_tables = $database->schema()->findTables('asu_tuition_%');
      foreach ($all_tables as $table_name) {
        $schemaTables[$table_name] = $schema[$table_name];
      }
      unset($schemaTables['asu_tuition_fee_code_old']);
      unset($schemaTables['asu_tuition_fee_rate_old']);
      foreach ($schemaTables as $table_name => $table) {

        $tables[$table_name] = $table;

      }

    }
    return $tables;
  }

}
