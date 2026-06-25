<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionQueryTables extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function queryTable($table_name_value) {
    dpm($table_name_value);
    return [];
  }

}
