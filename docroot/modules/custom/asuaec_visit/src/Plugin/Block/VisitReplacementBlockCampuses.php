<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Visit replacement block - Campuses.
 *
 * @Block(
 *   id = "visit_replacement_block_campuses",
 *   admin_label = @Translation("Campuses - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockCampuses extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {
    return [
      '#markup' => '<div id="campuses"></div>',
      '#cache' => ['max-age' => 0],
    ];
  }

}
