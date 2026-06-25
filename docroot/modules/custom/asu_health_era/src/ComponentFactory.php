<?php


namespace Drupal\asu_health_era;


class ComponentFactory {

  /**
   * @param string $id
   * @return ReactComponent
   */
  static public function load(string $id) {
    $types = [
      'asu_health_era' => '\Drupal\asu_health_era\ReactComponentAsuHealthEra',
    ];

    if (!in_array($id, array_keys($types))) {
      return;
    }

    $classname = $types[$id];
    if ($classname) {
      return new $classname();
    }
  }
}
