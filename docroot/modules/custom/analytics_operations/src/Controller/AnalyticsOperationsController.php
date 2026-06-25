<?php

namespace Drupal\analytics_operations\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

class AnalyticsOperationsController extends ControllerBase
{
  public function index(): array
  {
    $route_collection = Drupal::service('router.route_provider')->getAllRoutes();
    $links = [];

    foreach ($route_collection as $route_name => $route) {
      if (str_starts_with($route_name, 'analytics_operations.operation_')) {
        $title = $route->getDefault('_title');

        if (!$title) {
          $title = ucfirst(str_replace('_', ' ', str_replace('analytics_operations.operation_', '', $route_name)));
        }

        $url = Url::fromRoute($route_name);
        $data[] = [
          'title' => $title,
          'url' => $url,
        ];
        $links[] = Link::fromTextAndUrl($this->t($title), $url)->toString();
      }
    }

    $markup = '<h2>Analytics Operations</h2>';
    $markup .= '<p>Use the list below to find available operations:</p>';
    $markup .= '<ul>';
    foreach ($links as $link) {
      $markup .= '<li>' . $link . '</li>';
    }
    $markup .= '</ul>';

    return [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }
}
