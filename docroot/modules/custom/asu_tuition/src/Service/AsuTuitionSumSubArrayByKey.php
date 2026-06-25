<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionSumSubArrayByKey {

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
  public function getSumSubArrayByKey($array, $key) {
    $sum = 0;
    foreach ($array as $sub_array) {
      if (!empty($sub_array[$key])) {
        $sum += $sub_array[$key];
      }
    }
    // ksm($sum);
    return $sum;
  }

}
