<?php

namespace Drupal\asu_tuition\Service;

/**
 * Service to load tuition labels.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionLabelLoad {

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
  public function getTuitionLabelLoad($result) {
    $output = '';
    switch ($result->values->residency) {
      case 'RES':
        $output = 'Resident';
        break;

      case 'NORES':
        $output = 'Nonresident';
        break;

      case 'WUE':
        $output = 'WUE';
        break;
    }

    if ($result->values->campus === 'ONLNE') {
      $output .= ' Online';
    }
    // ksm($ouput);
    return $output . ' Tuition';
  }

}
