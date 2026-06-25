<?php

namespace Drupal\analytics_groups\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Analytics Groups routes.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_relationship.create_page')) {
      $pages = clone $route;
      $pages->setPath('group/{group}/group_page/create');
      $pages->setDefault('base_plugin_id', 'group_node:group_page');
      $collection->add('entity.group_relationship.analytics_groups_create_group_page', $pages);

      $trainings = clone $route;
      $trainings->setPath('group/{group}/group_training_material/create');
      $trainings->setDefault('base_plugin_id', 'group_node:group_training_material');
      $collection->add('entity.group_relationship.analytics_groups_create_group_training_material', $trainings);
    }

    if ($route = $collection->get('entity.group_relationship.add_page')) {
      $pages = clone $route;
      $pages->setPath('group/{group}/group_page/add');
      $pages->setDefault('base_plugin_id', 'group_node:group_page');
      $collection->add('entity.group_relationship.analytics_groups_add_group_page', $pages);

      $trainings = clone $route;
      $trainings->setPath('group/{group}/group_training_material/add');
      $trainings->setDefault('base_plugin_id', 'group_node:group_training_material');
      $collection->add('entity.group_relationship.analytics_groups_add_group_training_material', $trainings);

      $reports = clone $route;
      $reports->setPath('group/{group}/report/add');
      $reports->setDefault('base_plugin_id', 'group_node:report');
      $collection->add('entity.group_relationship.analytics_groups_add_report', $reports);
    }
  }
}
