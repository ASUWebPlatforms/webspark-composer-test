<?php

namespace Drupal\asu_tuition\Plugin\Menu\LocalAction;

use Drupal\Core\Menu\LocalActionDefault;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a local action plugin with a dynamic title.
 */
class AsuTuitionDynamicTabsLocal extends LocalActionDefault {

  /**
   * {@inheritdoc}
   */
  public function getTitle(?Request $request = NULL) {
    $entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    ksort($entities);
    return $entities;
  }

}
