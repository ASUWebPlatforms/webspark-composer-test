<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Visit replacement block - Mini calendar area.
 *
 * @Block(
 *   id = "visit_replacement_block_minicalendararea",
 *   admin_label = @Translation("Mini calendar area - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockMinicalendararea extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {

    $html = <<<HTML
<div class="minicalendar-area">

  <div class="d-flex mb-1" id="switch-selfguided">
    <label class="switch">
      <input type="checkbox" />
      <span class="slider round"></span>
    </label>
    <div class="m-1">Self-guided tours</div>
  </div>

  <div class="d-flex mb-1" id="switch-inperson">
    <label class="switch">
      <input type="checkbox" />
      <span class="slider round"></span>
    </label>
    <div class="m-1">In-person walking tours</div>
  </div>

  <div class="d-flex mb-1" id="switch-inperson-academic">
    <label class="switch">
      <input type="checkbox" />
      <span class="slider round"></span>
    </label>
    <div class="m-1">In-person walking tour with academic fair</div>
  </div>

  <div class="d-flex mb-1" id="switch-barrett">
    <label class="switch">
      <input type="checkbox" />
      <span class="slider round"></span>
    </label>
    <div class="m-1">Barrett, The Honors College experience</div>
  </div>

  <div class="d-flex mb-1" id="switch-generic">
    <label class="switch">
      <input type="checkbox" />
      <span class="slider round"></span>
    </label>
    <div class="m-1">Signature event</div>
  </div>

  <div class="minicalendar-result">&nbsp;</div>

</div>
HTML;

    return [
      '#type' => 'inline_template',
      '#template' => $html,
      '#cache' => ['max-age' => 0],
    ];
  }

}
