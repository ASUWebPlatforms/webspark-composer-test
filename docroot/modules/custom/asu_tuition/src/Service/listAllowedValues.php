<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class listAllowedValues {

  /**
   *
   *
   * @return array after exploding the array
   */
  public function listValues($values = NULL) {
    $explode_array = [];
    $tlines = explode(PHP_EOL, $values);
    foreach ($tlines as $key => $tvalue) {
      $all_lines[$tvalue] = $tvalue;
      $explode_array = explode('|', $tvalue);
      $explode_array1 = explode('|', $tvalue);
      $trimmed_data = trim($explode_array[1]);
      $newArray[$explode_array1[0]] = $trimmed_data;

    }
    return $newArray;
  }

}
