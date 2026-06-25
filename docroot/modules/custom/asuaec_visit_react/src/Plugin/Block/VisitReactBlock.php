<?php

namespace Drupal\asuaec_visit_react\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ASU AEC Visit ReactBlock' block.
 *
 * @Block(
 * id = "visit_react_block",
 * admin_label = @Translation("AEC Visit React block"),
 * )
 */
class VisitReactBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::config('asuaec_visit_revamp.settings');

    // Config value is stored as a simple list/textarea, one path per line.
    $paths = $config->get('calendar_paths') ?? ['react-calendar'];
    // Normalize to array of trimmed strings.
    $paths = array_values(array_filter(array_map('trim', (array) $paths)));

    $build = [];
    $build['visit_react_block'] = [
      '#markup' => '<div id="visit-react-root"></div>',
      '#attached' => [
        'library' => 'asuaec_visit_react/visit-react-lib',
        'drupalSettings' => [
          'visitRevamp' => [
            'maxMonthYear' => $config->get('max_month'),
            'calendarPaths' => $paths,

            // Added on 5/11/2026.
            'calendarInterests' => $config->get('calendar_interests') ?? [],
            'calendarCampuses' => $config->get('calendar_campuses') ?? [],
            'calendarPresets' => $config->get('calendar_presets') ?? [],

            // Added on 3/11/2026.
            'barrettDescriptions' => [
              'barrett_top_tempe' => $config->get('barrett_top_tempe') ?? '',
              'barrett_top_dpc' => $config->get('barrett_top_dpc') ?? '',
              'barrett_top_west' => $config->get('barrett_top_west') ?? '',
              'barrett_top_poly' => $config->get('barrett_top_poly') ?? '',
              'barrett_nested_tempe' => $config->get('barrett_nested_tempe') ?? '',
              'barrett_nested_dpc' => $config->get('barrett_nested_dpc') ?? '',
              'barrett_nested_west' => $config->get('barrett_nested_west') ?? '',
              'barrett_nested_poly' => $config->get('barrett_nested_poly') ?? '',
            ],

          ],
        ],
      ],
    ];
    return $build;

  }

}
