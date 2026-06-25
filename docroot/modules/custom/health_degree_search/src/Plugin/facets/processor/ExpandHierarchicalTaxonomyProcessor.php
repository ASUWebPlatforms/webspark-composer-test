<?php

namespace Drupal\health_degree_search\Plugin\facets\processor;

use Drupal\taxonomy\Entity\Term;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\FacetInterface;

/**
 * Expands selected taxonomy term IDs to include children recursively.
 *
 * @FacetsProcessor(
 *   id = "expand_hierarchical_taxonomy",
 *   label = @Translation("Expand to child taxonomy terms"),
 *   description = @Translation("When a taxonomy term is selected, include its children in the filter."),
 *   stages = {
 *     "build" = 40
 *   }
 * )
 */
class ExpandHierarchicalTaxonomyProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $query) {
    // Check for active selections.
    if (empty($query['filter'])) {
      return $query;
    }

    $field_id = $facet->getFieldIdentifier();
    $filters = &$query['filter'];

    // If the field isn't being filtered, exit early.
    if (!isset($filters[$field_id])) {
      return $query;
    }

    $tids = (array) $filters[$field_id];
    $expanded_tids = [];

    foreach ($tids as $tid) {
      $expanded_tids[] = $tid;
      $expanded_tids = array_merge($expanded_tids, $this->getAllChildren($tid));
    }

    $filters[$field_id] = array_unique($expanded_tids);

    return $query;
  }

  /**
   * Recursively fetch all child term IDs.
   */
  protected function getAllChildren($parent_tid) {
    $child_tids = [];

    $children = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadChildren($parent_tid);

    foreach ($children as $child) {
      $child_tid = $child->id();
      $child_tids[] = $child_tid;
      $child_tids = array_merge($child_tids, $this->getAllChildren($child_tid));
    }

    return $child_tids;
  }

}
