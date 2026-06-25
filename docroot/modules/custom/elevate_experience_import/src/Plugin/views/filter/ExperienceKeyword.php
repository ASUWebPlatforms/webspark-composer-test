<?php

namespace Drupal\elevate_experience_import\Plugin\views\filter;

use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\Combine;

/**
 * Keyword search across an Experience's title, description and filter taxonomies.
 *
 * Behaves like the core "combine" filter (a single exposed text field) but also
 * matches the Experience type, Career interest, Program and Location term names,
 * so typing e.g. "Internships" or "STEM" finds the relevant Experiences.
 */
#[ViewsFilter("experience_keyword")]
class ExperienceKeyword extends Combine {

  /**
   * Term-reference fields whose names should be searched.
   */
  const TAXONOMY_FIELDS = [
    'field_experience_type',
    'field_collection',
    'field_program',
    'field_location',
  ];

  /**
   * Display-label aliases mapped to the canonical term name to also match.
   *
   * "STEM" is shown in the UI but stored as the long term name.
   */
  const LABEL_ALIASES = [
    'stem' => ['field_collection', 'Science, Technology, Engineering & Math'],
  ];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $keyword = trim((string) $this->value);
    if ($keyword === '') {
      return;
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'experience')
      ->accessCheck(TRUE);

    $group = $query->orConditionGroup()
      ->condition('title', $keyword, 'CONTAINS')
      ->condition('body.value', $keyword, 'CONTAINS');
    foreach (self::TAXONOMY_FIELDS as $field) {
      $group->condition($field . '.entity.name', $keyword, 'CONTAINS');
    }
    // Match UI-only label aliases (e.g. "STEM") against their stored term name.
    foreach (self::LABEL_ALIASES as $alias => [$field, $canonical]) {
      if (mb_stripos($alias, mb_strtolower($keyword)) === 0) {
        $group->condition($field . '.entity.name', $canonical, 'CONTAINS');
      }
    }
    $query->condition($group);

    $nids = $query->execute();
    $this->query->addWhere($this->options['group'], 'node_field_data.nid', $nids ?: [0], 'IN');
  }

}
