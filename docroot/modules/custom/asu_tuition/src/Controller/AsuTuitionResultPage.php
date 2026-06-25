<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Render\Markup;
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionResultPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function result_page($op = NULL) {
    /* drupal_set_title(variable_get('asu_tuition_page_title', ASU_TUITION_PAGE_TITLE_DEFAULT));
    drupal_add_css(drupal_get_path('module', 'asu_tuition') . '/asu_tuition.css');*/

    // Build the page info array.
    $page = [
      'output' => (object) [
        'results' => '',
        'breakdown' => '',
      ],
      // 'values' => (object) asu_tuition_get_parameters(),
      'values' => (object) \Drupal::service('getUrlParameters')->getUrlParameters(),
    ];
    $page['cid'] = 'results_page:' . md5(serialize($page['values']));

    /*$cache = asu_tuition_get_cache_by_id($page['cid']);

    if ($cache) {
    $page = $cache;
    }
    else {*/
    // Get the results. If there are invalid values then go back to the search page,
    // otherwise cache the page info.
    $page['results'] = \Drupal::service('resultsLoad')->resultsLoad($page['values']);
    $page['output']->results = ['op' => 'results', 'values' => $page['values'], 'results' => $page['results']];
    $page['output']->breakdown = ['op' => 'breakdown', 'values' => $page['values'], 'results' => $page['results']];
    ksm($page['results']);
    ksm($page['output']->results);

    if (\Drupal::service('formDebugMode')->formDebugMode()) {
      foreach ($page['results']->breakdown as $breakdown) {
        \Drupal::service('formDebug')->formDebug(array_keys($breakdown));
      }
      \Drupal::service('formDebug')->formDebug($page['values']);
    }

    $table_type = gettype($page['output']->results['results']->html->acad_year_table);
    if ($table_type == "object") {
      $main_data = "There is no data";
    }
    else {
      $main_data = $page['output']->results['results']->html->acad_year_table['#markup'];
    }

    $config = \Drupal::config('asu_tuition.admin_settings');

    if ($page['values']->include_summer == 1) {
      $summer_data = "<div class='col-12'><p>" . $config->get('asu_tuition_results_page_summer_tuition_note') . "</p></div>";
    }
    else {
      $summer_data = '';
    }

    if (!empty($page['output']->results['results']->notes)) {
      foreach ($page['output']->results['results']->notes as $nkey => $nvalue) {
        $notes = $nvalue;
      }
      $note_content = "<hr />$summer_data<div class='col-12'><p><strong>Notes: </strong></p><p>$notes</p></div>";
    }
    else {
      $note_content = '';
    }

    $body = $main_data . $note_content;

    return [
      '#markup' => Markup::create($body),
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'asu_tuition/tuitionResultPage',
        ],
      ],
    ];

  }

}
