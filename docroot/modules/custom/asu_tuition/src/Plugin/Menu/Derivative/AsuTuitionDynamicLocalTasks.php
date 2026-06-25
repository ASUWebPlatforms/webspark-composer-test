<?php

namespace Drupal\asu_tuition\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Defines dynamic local tasks.
 */
class AsuTuitionDynamicLocalTasks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Implement dynamic logic to provide values for the same keys as in example.links.task.yml.
    $this->derivatives['asu_tuition.task_id'] = $base_plugin_definition;
    $this->derivatives['asu_tuition.task_id']['title'] = "I'm a tab";
    $this->derivatives['asu_tuition.task_id']['route_name'] = 'asu_tuition.admin_table_settings';
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
