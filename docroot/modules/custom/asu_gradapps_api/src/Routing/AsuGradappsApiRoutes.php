<?php

namespace Drupal\asu_gradapps_api\Routing;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\Reference;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Provides routes for the ASU API module.
 */
class AsuGradappsApiRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = array();

    $routes['asu_gradapps_api.create_announcement'] = array(
      'title' => 'Create Announcement',
      'path' => '/asu_gradapps_api/announcements',
      'defaults' => array(
        '_controller' => 'Drupal\asu_gradapps_api\Controller\AsuGradappsApiController::createAnnouncement',
      ),
      'requirements' => array(
        '_access' => 'TRUE',
      ),
      'methods' => array('POST'),
    );

    $routes['asu_gradapps_api.create_gf_data'] = array(
      'title' => 'Create GF Data',
      'path' => '/asu_gradapps_api/gf_data',
      'defaults' => array(
        '_controller' => 'Drupal\asu_gradapps_api\Controller\AsuGradappsApiController::createGFData',
      ),
      'requirements' => array(
        '_access' => 'TRUE',
      ),
      'methods' => array('POST'),
    );

    $routes['asu_gradapps_api.create_plancode_data'] = array(
      'title' => 'Create Plancode Data',
      'path' => '/asu_gradapps_api/plancodes',
      'defaults' => array(
        '_controller' => 'Drupal\asu_gradapps_api\Controller\AsuGradappsApiController::createPlancodes',
      ),
      'requirements' => array(
        '_access' => 'TRUE',
      ),
      'methods' => array('POST'),
    );

    $routes['asu_gradapps_api.create_categories_data'] = array(
      'title' => 'Create Categories Data',
      'path' => '/asu_gradapps_api/categories',
      'defaults' => array(
        '_controller' => 'Drupal\asu_gradapps_api\Controller\AsuGradappsApiController::createCategories',
      ),
      'requirements' => array(
        '_access' => 'TRUE',
      ),
      'methods' => array('POST'),
    );

    return $routes;
  }
}