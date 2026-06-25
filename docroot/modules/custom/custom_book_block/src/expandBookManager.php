<?php

namespace Drupal\custom_book_block;

use Drupal\book\BookManager;

// Override BookManager service.

class expandBookManager extends BookManager {

  /**
   * {@inheritdoc}
   */
  public function bookTreeAllData($bid, $link = NULL, $max_depth = NULL, $always_expand = 0) {
    $tree = &drupal_static(__METHOD__, []);
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Use $nid as a flag for whether the data being loaded is for the whole
    // tree.
    $nid = isset($link['nid']) ? $link['nid'] : 0;
    // Generate a cache ID (cid) specific for this $bid, $link, $language, and
    // depth.
    $cid = 'book-links:' . $bid . ':all:' . $nid . ':' . $language_interface->getId() . ':' . (int) $max_depth;

    if (!isset($tree[$cid])) {
      // If the tree data was not in the static cache, build $tree_parameters.
      $tree_parameters = [
        'min_depth' => 1,
        'max_depth' => $max_depth,
      ];
      if ($nid) {
        $active_trail = $this->getActiveTrailIds($bid, $link);
        // Setting the 'expanded' value to $active_trail would be same as core.
        if ($always_expand) {
          $tree_parameters['expanded'] = [];
        }
        else {
          $tree_parameters['expanded'] = $active_trail;
        }
        $tree_parameters['active_trail'] = $active_trail;
        $tree_parameters['active_trail'][] = $nid;
      }

      // Build the tree using the parameters; the resulting tree will be cached.
      $tree[$cid] = $this->bookTreeBuild($bid, $tree_parameters);
    }

    return $tree[$cid];
  }
}
