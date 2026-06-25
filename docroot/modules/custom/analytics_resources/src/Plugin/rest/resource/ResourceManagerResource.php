<?php

namespace Drupal\analytics_resources\Plugin\rest\resource;

use Drupal\rest\Annotation\RestResource;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Resource Manager Resource
 * @RestResource(
 *  id = "asu_analytics_resource_manager_resource",
 *  label = @Translation("ASU Analytics Resource Manager Resource"),
 *  uri_paths = {
 *    "canonical" = "/asu_analytics_api/resource_manager",
 *    "create" = "/asu_analytics_api/resource_manager"
 *  }
 * )
 */
class ResourceManagerResource extends AnalyticsResourceBase
{
  /**
   * Responds to GET requests.
   *
   * @return ResourceResponse
   */
  public function get(): ResourceResponse
  {
    $response = ['message' => 'This GET endpoint still needs to be implemented'];
    return new ResourceResponse($response);
  }

  /**
   * Responds to POST requests.
   *
   * @return ResourceResponse
   */
  public function post(): ResourceResponse
  {
    $response = ['message' => 'This POST endpoint still needs to be implemented'];
    return new ResourceResponse($response);
  }

  /**
   * Responds to PATCH requests.
   *
   * @return ResourceResponse
   */
  public function patch(): ResourceResponse
  {
    $response = ['message' => 'This PATCH endpoint still needs to be implemented'];
    return new ResourceResponse($response);
  }
}
