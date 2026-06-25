<?php

namespace Drupal\analytics_groups\Plugin\rest\resource;

use Drupal;
use Drupal\analytics_resources\Plugin\rest\resource\AnalyticsResourceBase;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a Group Resource
 *
 * @RestResource(
 *  id = "asu_analytics_group_resource",
 *  label = @Translation("ASU Analytics Group Resource"),
 *  uri_paths = {
 *    "canonical" = "/asu_analytics_api/group/{id}",
 *  }
 * )
 */
class GroupResource extends AnalyticsResourceBase
{
  /**
   * Responds to GET requests.
   *
   * @param $id
   * @return ModifiedResourceResponse|ResourceResponse
   */
  public function get($id)
  {
    $resource_service = Drupal::service('analytics_groups.resource_service');
    $group = $resource_service->loadGroupByContainerId($id);

    if (!$group) {
      $response = new ModifiedResourceResponse('Group Not Found');
      $response->setStatusCode(Response::HTTP_NOT_FOUND);
      return $response;
    }

    return new ResourceResponse($group);
  }
}
