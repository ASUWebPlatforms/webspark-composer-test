<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Visit replacement block - Day agenda area.
 *
 * @Block(
 *   id = "visit_replacement_block_dayagendararea",
 *   admin_label = @Translation("Day agenda area - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockDayagendaarea extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {

    $html = <<<HTML
<div class="day-agenda-result mb-10"></div>
HTML;

    return [
      '#markup' => $html,
      '#allowed_tags' => ['input', 'div', 'label', 'span'],
      '#cache' => ['max-age' => 0],
    ];
  }

}
