<?php

namespace Drupal\asu_health_era;
use Drupal\asu_react_core\ReactComponent;

class ReactComponentAsuHealthEra implements ReactComponent {

  public function buildSettings(&$variables) {
    $block = $variables['content']['#block_content'];
    $rand_id = random_int(0, PHP_INT_MAX);
    $asu_health_era = new \stdClass();
    if ($block->field_card && $block->field_card->entity) {
      $asu_health_era->cardId = $block->field_card->entity->uuid();
    }
    $settings = [];
    $settings['components']['content_section'][$rand_id] = $asu_health_era;
    $variables['content']['#attached']['drupalSettings']['asu'] = $settings;
    $variables['content']['#attached']['library'][] = 'asu_health_era/card';
  }
}
