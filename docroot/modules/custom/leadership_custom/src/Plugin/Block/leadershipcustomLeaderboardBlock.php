<?php

namespace Drupal\leadership_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "leaderboard_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class LeadershipcustomLeaderboardBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'leaderboard_block',
      '#attached' => [
        'library' => [
          'leadership_custom/leadership',
        ],
      ],
    ];

  }

}