<?php

namespace Drupal\leadership_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "resource_filter_block",
 *   admin_label = @Translation("Resource Filter Block")
 * )
 */
class LeadershipcustomResourceFilterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

 
  return [
      '#theme' => 'resource_filter_block',
      '#attached' => [
        'library' => [
          'leadership_custom/leadership',
        ],
      ],
    ];

  }

}