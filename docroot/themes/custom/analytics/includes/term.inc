<?php
/**
 * @file
 * Theme and preprocess functions for terms.
 */

/**
 * Generate a Favorites link to be manually placed in templates.
 *
 * @see https://www.drupal.org/node/295383#comment-12504094
 *
 * @param $variables
 *
 * @return void
 */
function analytics_preprocess_term(&$variables): void
{
  $term = $variables['term'];
  $favorites_link = [
    '#lazy_builder' => [
      'flag.link_builder:build',
      [
        'taxonomy_term',
        $term->id(),
        'favorite_terms',
      ]
    ],
    '#create_placeholder' => true,
  ];
  $variables['favorites_link'] = $favorites_link;
}
