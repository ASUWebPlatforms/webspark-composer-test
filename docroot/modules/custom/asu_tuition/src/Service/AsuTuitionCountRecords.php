<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionCountRecords {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */

  /**
   * Public function __construct(RequestStack $requestStack) {
   * $this->requestStack = $requestStack;
   * }
   */
  public function countRecords($table) {
    // Check that the table exists.
    if (!\Drupal::service('tableExists')->tableExists($table)) {
      throw new Exception(t('Attempt to read records from the %table table which does not exist.',
      ['%table' => $table]));
    }
    $database = \Drupal::database();

    /*$query= $database->select($table, 't')
    ->fields('t')
    ->execute();
    ksm($query);*/
    $query = $database->query("SELECT id FROM {$table}");
    $result = $query->fetchAll();

    $record_count = count($result);
    /*$result = $query->execute()->fetchAll();
    $record_count = count($result);*/
    return $record_count;
  }

}
