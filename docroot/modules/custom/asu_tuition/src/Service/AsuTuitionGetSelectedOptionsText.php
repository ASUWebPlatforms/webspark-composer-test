<?php

namespace Drupal\asu_tuition\Service;

/**
 * Use Symfony\Component\HttpFoundation\RedirectResponse;.
 */
class AsuTuitionGetSelectedOptionsText {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function getSelectedOptionsText($table, $params, $descr_key) {
    // ksm($table);
    // ksm($params);
    $record = \Drupal::service('readRecords')->readRecords($table, $params);

    if ($record) {
      // ksm($record);
      // return t($record[$descr_key]);.
      return $record[$descr_key];

    }
    else {
      return '';
    }

  }

}
