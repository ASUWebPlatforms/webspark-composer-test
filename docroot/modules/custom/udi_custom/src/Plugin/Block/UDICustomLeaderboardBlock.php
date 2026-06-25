<?php

namespace Drupal\udi_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "udi_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class UDICustomLeaderboardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'udi_block',
      '#attached' => [
        'library' => [
          'udi_custom/udi',
        ],
      ],
    ];

  }

}