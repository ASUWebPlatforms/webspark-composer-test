<?php

namespace Drupal\analytics_tweaks\Signatures;

use Drupal;

class Signature
{
  /**
   * Add a user signature to be passed within requests.
   *
   * @param $asurite
   * @param $sig
   *
   * @return false|string
   */
  public static function sign($asurite, $sig): bool|string
  {
    $date = Drupal::time()->getCurrentTime();
    $full = $asurite . ":" . $date;
    $dig = hash_hmac('sha256', $full, $sig);
    $arr = [
      'asurite' => $asurite,
      'timestamp' => $date,
      'checksum' => $dig,
    ];

    return json_encode($arr);
  }
}
