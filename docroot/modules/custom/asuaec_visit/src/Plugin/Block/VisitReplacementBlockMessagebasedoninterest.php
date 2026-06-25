<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Visit replacement block - Message based on interest.
 *
 * @Block(
 *   id = "visit_replacement_block_messagebasedoninterest",
 *   admin_label = @Translation("Message based on interest - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockMessagebasedoninterest extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {

    $html = <<<HTML
<div id="message-based-on-interest"></div>
HTML;

    return [
      '#markup' => $html,
      '#allowed_tags' => ['input', 'div', 'label', 'span'],
      '#cache' => ['max-age' => 0],
    ];
  }

}
