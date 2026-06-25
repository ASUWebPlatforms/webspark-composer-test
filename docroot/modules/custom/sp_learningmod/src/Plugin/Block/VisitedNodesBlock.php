<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Visited Nodes' block.
 * 
 * This block renders an empty container that is populated via AJAX.
 * This allows the block to be cached while the content stays fresh.
 *
 * @Block(
 *   id = "visited_nodes_block",
 *   admin_label = @Translation("Visited Nodes Block")
 * )
 */
class VisitedNodesBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    return [
      '#theme' => 'visited_nodes_block',
      '#items' => [],
      '#attached' => [
        'library' => [
          'sp_learningmod/ajax_state',
          'sp_learningmod/budget_banner',
        ],
      ],
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => ['sp_learningmod'],
        'max-age' => 3600,
      ],
    ];
  }
}
