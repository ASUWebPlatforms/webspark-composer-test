<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionMoneyFormat {

  /**
   *
   */
  public function tuitionMoneyFormat($value, $dollar_sign = TRUE) {

    // Return ($dollar_sign ? money_format('%!.0n', $value) : money_format('%!.0n', $value));.
    return ($dollar_sign ? number_format($value, 0) : $value);
  }

}
