<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionReadRecords {

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
  public function readRecords($table, $params = [], $include_additional = []) {
    // Check that the table exists.
    if (!\Drupal::service('tableExists')->tableExists($table)) {
      throw new Exception(t('Attempt to read records from the %table table which does not exist.',
      ['%table' => $table]));
    }
    $database = \Drupal::database();

    $query = $database->select($table, 't');
    // $query = db_select($table, 't')
    // $include_additional = array('key_by' => 'acad_year');
    // ksm($table);
    $arguments = [];

    if (is_array($params)) {
      // ksm($params);
      $tables = \Drupal::service('getSchema')->getSchema();
      // ksm($tables);
      // Turn the conditions into a query. Only include conditions that are real fields.
      foreach ($params as $key => $value) {
        // ksm($tables[$table]['fields']);.
        if (array_key_exists($key, $tables[$table]['fields'])) {

          $query->condition($key, $value);
          $arguments[] = $value;
        }
      }

      // Retrieve a field list based on the table's schema and set which field to
      // key by.
      $fields = \Drupal::service('getSchemaFieldsSql')->getSchemaFieldsSql($table);
      // ksm($fields);
      if (isset($include_additional['key_by']) && in_array($include_additional['key_by'], $fields)) {
        $key_by = $include_additional['key_by'];
      }
      else {
        $key_by = 'id';
      }
      $nfields = [implode(', ', $fields)];
      // ksm($nfields);
      // Retrieve the records.
      $query->fields('t', $fields);

      $records = [];
      $result = $query->execute()->fetchAll();

      foreach ($result as $record) {

        if (($key_by !== '' && $key_by !== NULL) && is_string($key_by)) {

          // $new_array = (array) $record;
          //  \Drupal::moduleHandler()->invokeAll('asu_tuition_read_record', $new_array);
          // $records[$key_by] =  $record;
          // $records[$record->$key_by] =  $record;
        }
        $records = (array) $record;
      }

      return $records;
    }

  }

}
