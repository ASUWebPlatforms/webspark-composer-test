<?php

namespace Drupal\asu_tuition\Service;

/**
 *
 */
class AsuTuitionIncludeSummer {

  /**
   *
   */
  public function includeSummer($results, $reset = FALSE) {
    $include_summer = &drupal_static(__FUNCTION__);
    if (!isset($include_summer) || $reset) {
      if (isset($results->values->include_summer) && $results->values->include_summer == '1') {
        $include_summer = TRUE;
      }
      else {
        $include_summer = FALSE;
      }
    }
    // ksm($include_summer);
    return $include_summer;
  }

}
