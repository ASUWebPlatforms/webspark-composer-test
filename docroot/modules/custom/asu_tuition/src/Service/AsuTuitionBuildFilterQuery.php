<?php

namespace Drupal\asu_tuition\Service;

/**
 *
 */
class AsuTuitionBuildFilterQuery {

  /**
   *
   */
  public function buildFilterQuery($table) {
    // ksm($table);
    if (empty($_SESSION['asu_tuition_edit_table_filter'][$table])) {
      return;
    }

    $fields = \Drupal::service('editTableFields')->editTableFields($table);

    // Build query.
    $where = $args = [];

    // If (!empty($_SESSION['asu_tuition_edit_table_filter'][$table])) {.
    if (!empty($_SESSION['asu_tuition_edit_table_filter'][$table]['filters']) &&
    is_array($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'])) {

      foreach ($_SESSION['asu_tuition_edit_table_filter'][$table]['filters'] as $key => $filter) {
        // Make sure filter is an array.
        if (!is_array($filter)) {
          $filter = [$filter];
        }
        // ksm($filter);
        $filter_where = [];
        foreach ($filter as $value) {

          $filter_where[$key] = $value;
          $args[] = $value;
        }

        if (!empty($filter_where)) {
          // $where[] = '(' . implode(' OR ', $filter_where) . ')';
          $where[$key] = $filter_where;
        }
      }
    }
    else {
      $where = '';

    }

    return [
      'where' => $where,

    ];
  }

}
