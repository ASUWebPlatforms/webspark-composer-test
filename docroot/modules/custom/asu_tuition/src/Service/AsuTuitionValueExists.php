<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionValueExists {

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
  public function getValueExists($field, $value, $display_only = TRUE) {
    $value_exists = FALSE;
    $sql = "SELECT %s FROM %s WHERE %s LIKE '%s' ";
    if ($display_only) {
      $sql .= " AND display = 1";
    }

    switch ($field) {
      case 'acad_year':
        $db_table = 'asu_tuition_acad_year';
        break;

      case 'residency':
        $db_table = 'asu_tuition_residency';
        break;

      case 'acad_career':
        $db_table = 'asu_tuition_acad_career';
        break;

      case 'campus':
        $db_table = 'asu_tuition_campus';
        break;

      case 'acad_prog':
        $db_table = 'asu_tuition_acad_prog';
        break;

      case 'admit_level':
        // The field being check in the database is acad_level, not admit_level.
        $field = 'acad_level';
        $db_table = 'asu_tuition_acad_level';
        break;

      case 'acad_level':
        $field = 'acad_level';
        $db_table = 'asu_tuition_acad_level';
        break;

      case 'corporate_partner':
        $field = 'corporate_partner';
        $db_table = 'asu_tuition_corporate_partner';
        break;

      default:
        $db_table = FALSE;
    }

    // Check database to see if value exits.
    if ($db_table) {
      $database = \Drupal::database();
      $query = $database->select($db_table, 't')
        ->fields('t')
        ->condition($field, $value);
      if ($display_only) {
        $query->condition('display', 1);
      }

      $result = $query->execute();
      $value_exists = (bool) $result->fetchAssoc();
    }

    return $value_exists;
  }

}
