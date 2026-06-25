<?php

namespace Drupal\analytics_resources\Plugin\rest\resource;

use Drupal\rest\Annotation\RestResource;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Data Table Resource
 * @RestResource(
 *  id = "asu_analytics_data_table_resource",
 *  label = @Translation("ASU Analytics Data Table Resource"),
 *  uri_paths = {
 *    "canonical" = "/asu_analytics_api/data_table/{id}",
 *    "create" = "/asu_analytics_api/data_table",
 *    "update" = "/asu_analytics_api/data_table/{id}",
 *    "delete" = "/asu_analytics_api/data_table/{id}"
 *  }
 * )
 */
class DataTableResource extends AnalyticsResourceBase
{
  /**
   * Get a resource.
   *
   * @return ResourceResponse
   */
  public function get(): ResourceResponse
  {
    return new ResourceResponse(
      'This function has yet to be implemented, pending the outcome of the Collibra replacement'
    );
  }

  /**
   * Update a resource.
   *
   * @return ResourceResponse
   */
  public function post(): ResourceResponse
  {
    return new ResourceResponse(
      'This function has yet to be implemented, pending the outcome of the Collibra replacement'
    );
  }

  /**
   * Patch a resource.
   *
   * @return ResourceResponse
   */
  public function patch(): ResourceResponse
  {
    return new ResourceResponse(
      'This function has yet to be implemented, pending the outcome of the Collibra replacement'
    );
  }

  /**
   * Delete a resource.
   *
   * @return ResourceResponse
   */
  public function delete(): ResourceResponse
  {
    return new ResourceResponse(
      'This function has yet to be implemented, pending the outcome of the Collibra replacement'
    );
  }
}
