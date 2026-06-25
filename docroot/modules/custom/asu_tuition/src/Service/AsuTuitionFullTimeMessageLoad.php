<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionFullTimeMessageLoad {

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
  public function getTuitionFullTimeMessageLoad($result) {
    switch ($result->values->acad_career) {
      case 'GRAD':
        return '9 credits for graduate students';

      case 'LAW':
        return '9 credits for resident law students and 15 credits for nonresident law students';

      case 'UGRD':
      case 'PB':
      default:
        return '12 credits for undergraduate students';
    }
  }

}
