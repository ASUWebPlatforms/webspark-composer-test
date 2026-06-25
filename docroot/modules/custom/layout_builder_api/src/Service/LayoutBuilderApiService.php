<?php

namespace Drupal\layout_builder_api\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;

/**
 * Service for Layout Builder API operations.
 */
class LayoutBuilderApiService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * LayoutBuilderApiService constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cache = $cache;
  }

  /**
   * Get cached layout data for a node.
   */
  public function getCachedLayoutData($node_id) {
    $cache_key = "layout_builder_api:node:{$node_id}";
    $cached = $this->cache->get($cache_key);

    if ($cached) {
      return $cached->data;
    }

    $node = Node::load($node_id);
    if (!$node) {
      return NULL;
    }

    // Generate layout data (implement your logic here)
    $layout_data = $this->generateLayoutData($node);

    // Cache for 1 hour
    $this->cache->set($cache_key, $layout_data, time() + 3600, ['node:' . $node_id]);

    return $layout_data;
  }

  /**
   * Generate layout data for a node.
   */
  protected function generateLayoutData(Node $node) {
    // This would contain the logic to extract layout data
    // Similar to the controller method but optimized for caching
    return [];
  }

  /**
   * Invalidate layout cache for a node.
   */
  public function invalidateLayoutCache($node_id) {
    $cache_key = "layout_builder_api:node:{$node_id}";
    $this->cache->delete($cache_key);
  }

}
