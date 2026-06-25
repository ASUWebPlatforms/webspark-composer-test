<?php

namespace Drupal\asu_campus_fit\Controller;

use Drupal\Core\Render\Markup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the custom module.
 */
class CampusFitAjaxCampusController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function campus_fit_ajax_campus_page($class_node_id = NULL, $sid = NULL, $urlcampus = NULL) {
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $sid_val = $request->query->get('sid');
    $campus = $request->query->get('campus');
    if (empty($class_node_id)) {
      $nid = \Drupal::request()->query->get('nid');
    }
    else {
      $nid = $class_node_id;
    }
    if (!empty($campus)) {
      $_SESSION['ajaxCampus'] = htmlspecialchars($campus, ENT_QUOTES, 'UTF-8');
    }

    // \Drupal::logger('$class_node_id')->info('<pre>' . print_r($class_node_id, TRUE) . '</pre>');
    // \Drupal::logger('ajaxnid')->info('<pre>' . print_r($nid, TRUE) . '</pre>');
    $stype = \Drupal::request()->query->get('stype');
    // ksm($stype);
    if (!empty($stype)) {
      if ($stype == "Earn a bachelor’s degree.") {
        $degree_link = "https://degrees.apps.asu.edu/bachelors/major-list/Campus/";
      }
      if ($stype == "Earn an advanced degree (masters, PhD, etc.).") {
        $degree_link = "https://degrees.apps.asu.edu/masters-phd/major-list/Campus/";
      }

    }
    else {
      $degree_link = "https://degrees.asu.edu/";
    }

    if (!empty($nid)) {
      // $node = \Drupal\node\Entity\Node::load($nid);
      /* $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $build = $builder->view($node, 'full');
      $output = \Drupal::service('renderer')->renderRoot($build);

      $body = $output; */
      // Simulate a request to the node route.
      $node = Node::load($nid);
      $request = Request::create("/node/{$nid}");
      $request->attributes->set('_route', 'entity.node.canonical');
      $request->attributes->set('node', $node);

      // Inject the fake request so Layout Builder and blocks get proper context.
      \Drupal::service('request_stack')->push($request);

      // Build the node render array.
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $build = $view_builder->view($node, 'full');

      // Render the build array.
      $output = \Drupal::service('renderer')->renderRoot($build);
    }

    // $results['body'] = \Drupal\Core\Render\Markup::create($body);
    $results['body'] = Markup::create($output);
    // \Drupal::logger('$results')->info('<pre>' . print_r($results['body'], TRUE) . '</pre>');
    $results['degree_link'] = $degree_link;
    // ksm($results);
    return new JsonResponse(
                [
    // 'resultsData' => \Drupal\Core\Render\Markup::create($body),
                  'resultsData' => $results,
                ]

    );
    /*$response_data = [
    'resultsData' => \Drupal::service('renderer')->renderRoot($results),
    ];
    return new JsonResponse($response_data);*/

  }

}
