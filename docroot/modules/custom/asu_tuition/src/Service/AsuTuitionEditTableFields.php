<?php

namespace Drupal\asu_tuition\Service;

/**
 *
 */
class AsuTuitionEditTableFields {

  /**
   * Get the asu_tuition module's table info.
   *
   * @param $reset
   *   If TRUE then rebuild the table info.
   */
  public function editTableFields($table = FALSE) {
    /*$schema =  drupal_get_module_schema('asu_tuition');

    $full_table = $table;
    //ksm($full_table);
    return $schema[$full_table]['fields'];*/

    /** D10 version. **/

    \Drupal::moduleHandler()->loadInclude('asu_tuition', 'install');
    $schema = \Drupal::moduleHandler()->invoke('asu_tuition', 'schema');
    return $schema[$table]['fields'];
  }

}
