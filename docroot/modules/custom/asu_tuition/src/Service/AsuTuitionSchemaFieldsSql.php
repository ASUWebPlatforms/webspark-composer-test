<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionSchemaFieldsSql {

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
  public function getSchemaFieldsSql($table, $prefix = NULL) {

    // $schema = drupal_get_module_schema('asu_tuition');
    // $just_fields = $schema[$table]['fields'];
    \Drupal::moduleHandler()->loadInclude('asu_tuition', 'install');
    $schema = \Drupal::moduleHandler()->invoke('asu_tuition', 'schema');
    $just_fields = $schema[$table]['fields'];

    $fields = array_keys($just_fields);

    if ($prefix) {
      $columns = [];
      foreach ($fields as $field) {
        $columns[] = "{$prefix}.{$field}";
      }
      return $columns;
    }
    else {
      return $fields;
    }
  }

}
