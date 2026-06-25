<?php

namespace Drupal\chs_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "chs_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class CHSCustomLeaderboardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'chs_block',
      '#attached' => [
        'library' => [
          'chs_custom/chs',
        ],
      ],
    ];

  }

}