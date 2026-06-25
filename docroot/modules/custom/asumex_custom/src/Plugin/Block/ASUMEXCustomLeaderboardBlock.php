<?php

namespace Drupal\asumex_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "asumex_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class ASUMEXCustomLeaderboardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#theme' => 'asumex_block',
      '#attached' => [
        'library' => [
          'asumex_custom/asumex',
        ],
      ],
    ];

  }

}
