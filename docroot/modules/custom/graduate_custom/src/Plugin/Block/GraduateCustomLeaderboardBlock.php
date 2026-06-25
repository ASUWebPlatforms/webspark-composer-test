<?php

namespace Drupal\graduate_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "graduate_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class GraduateCustomLeaderboardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'graduate_block',
      '#attached' => [
        'library' => [
          'graduate_custom/graduate',
        ],
      ],
    ];

  }

}