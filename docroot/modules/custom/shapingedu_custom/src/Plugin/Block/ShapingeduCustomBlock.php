<?php

namespace Drupal\shapingedu_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "shapingedu_block",
 *   admin_label = @Translation("Activate Custom Styles and Features")
 * )
 */
class ShapingeduCustomBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'shapingedu_block',
      '#attached' => [
        'library' => [
          'shapingedu_custom/shapingedu-block',
        ],
      ],
    ];

  }

}