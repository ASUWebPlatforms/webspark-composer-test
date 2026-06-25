<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Render\Markup;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionResultJsonPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function result_json_page($op = NULL) {

    // Build the page info array.
    $page = [
      'output' => (object) [
        'results' => '',
        'breakdown' => '',
      ],
      'values' => (object) \Drupal::service('getUrlParameters')->getUrlParameters(),
    ];

    $page['cid'] = 'results_page:' . md5(serialize($page['values']));

    // Get the results. If there are invalid values then go back to the search page,
    // otherwise cache the page info.
    $page['results'] = \Drupal::service('resultsLoad')->resultsLoad($page['values']);
    $total_value = $page['results']->html->acad_year_table['total_value'];
    $semTotals = $page['results']->html->acad_year_table['all_sem_totals'];
    
    $page['output']->results = ['op' => 'results', 'values' => $page['values'], 'results' => $page['results']];
    $page['output']->breakdown = ['op' => 'breakdown', 'values' => $page['values'], 'results' => $page['results']];
    $table_type = gettype($page['output']->results['results']->html->acad_year_table);
    if ($table_type == "object") {
      $main_data = "There is no data";
    }
    else {
      $main_data = $page['output']->results['results']->html->acad_year_table['#markup'];
    }
    // Code to get axios info
    // \Drupal::logger('tuition_result_data')->info('<pre>' . print_r($page['results'], TRUE) . '</pre>');.
    /*if ( empty($page['results']->acad_career)) {
    //code to track who is calling our API
    $request = \Drupal::request();

    //  Get headers
    $origin = $request->headers->get('Origin');
    $referer = $request->headers->get('Referer');
    $userAgent = $request->headers->get('User-Agent');
    $ip = $request->getClientIp();
    \Drupal::logger('results_access_local')->info("API accessed | IP: $ip | Origin: $origin | Referer: $referer | UA: $userAgent");
    }*/
    if (\Drupal::service('formDebugMode')->formDebugMode()) {
      foreach ($page['results']->breakdown as $breakdown) {
        \Drupal::service('formDebug')->formDebug(array_keys($breakdown));
      }
      \Drupal::service('formDebug')->formDebug($page['values']);
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
        $all_notes[$nkey] = $nvalue;
        $notes = $nvalue;
        // ksm($nvalue);
      }

      if (sizeof($all_notes) > 1) {
        $each_note = implode('<br />', $all_notes);
      }
      else {
        foreach ($all_notes as $key => $eachvalue) {
          $each_note = $eachvalue;
        }

      }

      $note_content = "<hr />$summer_data<div class='col-12'><p><strong>Notes: </strong></p><p>$each_note</p></div>";
    }
    else {
      $note_content = '';
    }

    if (!empty($page['output']->results['results']->new_notes)) {
      $new_note = "<div class='col-12'>" . $page['output']->results['results']->new_notes . "</div>";
    }
    else {
      $new_note = '';
    }

    $body = $main_data . $note_content . $new_note;
   
    // $body = $main_data;
    // $body .= $page['output']->results['results']->html->breakdown_tables['#markup'];
    return new JsonResponse(
                [
                  'resultsData' => Markup::create($body),
                  'totalValue' => $total_value,
                  'semesterTotals' => $semTotals,
                ]

    );
  }

}
