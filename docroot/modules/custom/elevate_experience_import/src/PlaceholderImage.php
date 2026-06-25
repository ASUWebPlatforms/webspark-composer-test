<?php

namespace Drupal\elevate_experience_import;

use Drupal\node\NodeInterface;

/**
 * Picks a category-appropriate placeholder image for image-less Experiences.
 *
 * The client supplied three branded placeholder icons per Career-interest
 * category (the `collection` vocabulary). A card with no uploaded image shows
 * one of its category's three icons, chosen deterministically by node id so the
 * same node is stable and cards within a category vary. Experiences with no
 * category (e.g. certificate courses, whose CSV has no career-interest column)
 * fall back to a single neutral set.
 */
class PlaceholderImage {

  /**
   * Number of placeholder variants available per category.
   */
  const VARIANTS = 3;

  /**
   * Neutral fallback category for Experiences without a Career interest.
   */
  const DEFAULT_KEY = 'education';

  /**
   * Maps a `collection` term name to its placeholder file key.
   */
  const COLLECTION_TO_KEY = [
    'Arts, Design & Performance' => 'arts',
    'Business' => 'business',
    'Communication & Media' => 'communication-media',
    'Education' => 'education',
    'Entrepreneurship' => 'entrepreneurship',
    'Health & Wellness' => 'health-wellness',
    'Public, Social & Human Services' => 'public-social',
    'Sustainability, Environmental & Natural Resources' => 'sustainability',
    'Science, Technology, Engineering & Math' => 'stem',
  ];

  /**
   * Returns the placeholder image for a node, or NULL if it has its own image.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Experience node.
   *
   * @return array|null
   *   ['url' => string, 'alt' => string], or NULL when an image is uploaded.
   */
  public static function forNode(NodeInterface $node) {
    if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
      return NULL;
    }

    $key = self::DEFAULT_KEY;
    if ($node->hasField('field_collection') && !$node->get('field_collection')->isEmpty()) {
      $term = $node->get('field_collection')->first()->entity;
      if ($term && isset(self::COLLECTION_TO_KEY[$term->label()])) {
        $key = self::COLLECTION_TO_KEY[$term->label()];
      }
    }

    $variant = ((int) $node->id() % self::VARIANTS) + 1;
    $file = $key . '-' . $variant . '.jpg';
    $module_path = \Drupal::service('extension.list.module')->getPath('elevate_experience_import');

    return [
      'url' => base_path() . $module_path . '/images/placeholders/' . $file,
      'alt' => (string) $node->label(),
    ];
  }

}
