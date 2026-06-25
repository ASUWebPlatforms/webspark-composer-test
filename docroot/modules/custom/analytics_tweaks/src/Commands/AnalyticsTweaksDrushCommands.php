<?php

namespace Drupal\analytics_tweaks\Commands;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drush\Commands\DrushCommands;

class AnalyticsTweaksDrushCommands extends DrushCommands
{
  /**
   * List fields for a specific content type.
   *
   * @command list-fields
   * @aliases lf
   *
   * @param $content_type string The machine name of the content type.
   * @param false[] $options
   * @options name-only Only show the fields name.
   * @options label-only Only show the fields label.
   * @options type-only Only show the fields type.
   * @usage drush list-fields article
   *
   * @return void
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function listFields(
    string $content_type,
    array $options = ['name-only' => false, 'label-only' => false, 'type-only' => false]
  ): void {
    $content_type_entity = Drupal::entityTypeManager()->getStorage('node_type')->load($content_type);

    if ($content_type_entity) {
      $content_type_label = $content_type_entity->label();
      $this->output()->writeln("Content Type: $content_type_label ($content_type)\n-----");
    } else {
      $this->output()->writeln("Content type not found: $content_type");
      return;
    }

    $field_configs = Drupal::entityTypeManager()->getStorage('field_config')->loadByProperties([
      'entity_type' => 'node',
      'bundle' => $content_type,
    ]);

    if (empty($field_configs)) {
      $this->output()->writeln("No fields found for the content type: $content_type");
      return;
    }

    foreach ($field_configs as $field_config) {
      $field_name = $field_config->getName();
      $field_label = $field_config->getLabel();
      $field_type = $field_config->getType();
      $output = "$field_label ($field_name):$field_type";

      if ($options['name-only']) {
        $output = $field_name;
      }

      if ($options['label-only']) {
        $output = $field_label;
      }

      if ($options['type-only']) {
        $output = $field_type;
      }

      $this->output()->writeln($output);
    }
  }
}
