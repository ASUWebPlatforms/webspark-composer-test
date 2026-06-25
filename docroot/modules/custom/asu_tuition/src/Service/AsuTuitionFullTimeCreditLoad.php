<?php

namespace Drupal\asu_tuition\Service;

/**
 * Get full time credits.
 */
class AsuTuitionFullTimeCreditLoad {

  /**
   *
   */
  public function getFullTimeCreditLoad($result) {
    // ksm($result);
    switch ($result->values->acad_career) {
      case 'GRAD':
        return 9;

      case 'LAW':
        return ($result->values->residency === 'RES' ? 9 : 15);

      case 'UGRD':
      case 'PB':
      default:
        return 12;
    }
  }

}
