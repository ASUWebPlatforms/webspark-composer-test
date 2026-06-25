<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionEntityInfo {

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
  public function getEntityInfo() {
    foreach (\Drupal::service('getSchema')->getSchema() as $table_name => $table) {
      $entity_info[$table_name] = [
        'label' => 'ASU Tuition - ' . str_replace('_', ' ', str_replace('asu_tuition_', '', $table_name)),
        'entity class' => 'Entity',
        'controller class' => 'EntityAPIController',
        'base table' => $table_name,
        'entity keys' => [
          'id' => 'id',
        ],
        'module' => 'asu_tuition',
      ];
    }
    // ksm($entity_info);
    return $entity_info;
  }

}
