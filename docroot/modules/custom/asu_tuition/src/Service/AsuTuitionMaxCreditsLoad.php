<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionMaxCreditsLoad {

  /**
   *
   */
  public function getMaxCreditsLoad($result) {
    // Maximum credits to display is defaulted to 7.
    $max_credits = 7;
    // ksm($result);
    // Always show 18 credits for online campus starting with the 2011 academic year.
    // Always show 18 credits if fee types of P1 or P4 are being displayed and
    // career is GRAD.
    // Always show 18 credits if program fee is in a list of them provided by SBS.
    $fee_types = \Drupal::service('getFeeTypesLoad')->getFeeTypesLoad($result);
    if (($result->values->campus === 'ONLNE' && $result->values->acad_year >= 2011) ||
          ($result->values->acad_career === 'GRAD' && (in_array('P1', $fee_types) || in_array('P4', $fee_types))) ||
          (in_array($result->values->program_fee, ['UP0036']))) {
      $max_credits = 18;
    }
    // Always show 12 credits for Lake Havasu and EAC campus.
    elseif (in_array($result->values->campus, ['CALHC', 'EAC'])) {
      $max_credits = 12;
    }
    else {
      switch ($result->values->acad_career) {
        case 'LAW':
          $max_credits = 18;
          break;

        default:
          switch ($result->values->residency) {
            case 'RES':
              $max_credits = 7;
              break;

            default:
              $max_credits = 12;
          }
      }
    }
    // ksm($max_credits);
    return $max_credits;
  }

}
