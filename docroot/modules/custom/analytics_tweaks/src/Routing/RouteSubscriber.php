<?php

namespace Drupal\analytics_tweaks\Routing;

use Drupal;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase
{
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void
  {
    if ($route = $collection->get('entity.node.canonical')) {
      $config = Drupal::config('analytics_tweaks.settings');
      $types = array_keys(array_filter($config->get('types') ?: []));

      if (!empty($types)) {
        $route->setOption('node_type', $types);
        $route->setDefault(
          '_controller',
          '\Drupal\analytics_tweaks\Controller\AnalyticsTweaksController::renderNodeAsJson'
        );
      }
    }
  }
}
