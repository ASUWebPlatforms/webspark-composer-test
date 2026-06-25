<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionResultJsonApiPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function result_api_page() {

    // Build the page info array.
    $page = [
      'output' => '',
      'values' => (object) \Drupal::service('getUrlParameters')->getUrlParameters(),
    ];

    $page_acad_year = $page['values']->acad_year;
    if (!(strlen($page_acad_year) === 4) || empty($page['values']->acad_career)) {
      // Code to track who is calling our API.
      $request = \Drupal::request();

      // Get headers.
      $origin = $request->headers->get('Origin');
      $referer = $request->headers->get('Referer');
      $userAgent = $request->headers->get('User-Agent');
      $ip = $request->getClientIp();
      \Drupal::logger('api_access')->info("API accessed | IP: $ip | Origin: $origin | Referer: $referer | UA: $userAgent");
    }
    $page['results'] = \Drupal::service('resultsLoadJson')->resultsLoadJson($page['values']);

    if (!\Drupal::service('getValidValues')->getValidValues((array) $page['results']->values)) {
      $page['results'] = FALSE;
    }
    // $page['output'] .= theme('asu_tuition_results_page', array('values' => $page['values'], 'results' => $page['results']));
    $results = $page['results'];

    $test_callback = \Drupal::request()->request->get('callback');
    $callback = (isset($test_callback)) ? check_plain($test_callback) : '';
    // Wrap json with a function if this is a jsonp callback request.
    if (isset($callback) && $callback != '') {
      header('Content-type: text/javascript');
      echo $callback . '(' . JsonResponse($results) . ');';
    }
    else {
      // drupal_json_output($results);
      $response = new JsonResponse($results);

      // Set cache max-age to 1 hour (3600 seconds)
      // 1 hour.
      $response->setMaxAge(3600);
      // Allow shared/proxy caching.
      $response->setPublic();

      // (Optional) Add cache metadata headers
      $response->headers->set('X-Drupal-Cache-Tags', 'tuition_custom_tag');
      $response->headers->set('X-Drupal-Cache-Contexts', 'url.path user.roles');
      return $response;
      // Return new JsonResponse($results);
    }
    exit();

  }

}
