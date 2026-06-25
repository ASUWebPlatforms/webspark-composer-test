<?php

namespace Drupal\asuaec_visit_revamp\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a "You have selected" block.
 *
 * @Block(
 *   id = "you_have_selected_block",
 *   admin_label = @Translation("You have selected"),
 *   category = @Translation("Visit Revamp")
 * )
 */
class YouHaveSelectedBlock extends BlockBase {

  public function build() {
    return [
      'placeholder' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'selected-events-summary',
        ],
        // IMPORTANT: make sure it actually renders even when empty.
        '#value' => '',
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}