<?php

namespace Drupal\analytics_resources\Plugin\rest\resource;

use Drupal;
use Drupal\rest\Annotation\RestResource;
use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Provides a Workflow Resource
 * @RestResource(
 *  id = "asu_analytics_workflow_resource",
 *  label = @Translation("ASU Analytics Workflow Resource"),
 *  uri_paths = {
 *    "canonical" = "/asu_analytics_api/workflow",
 *    "get"= "/asu_analytics_api/workflow",
 *  }
 * )
 */
class WorkflowResource extends AnalyticsResourceBase
{
  /**
   * Responds to GET requests.
   *
   * @param Request $request
   *
   * @return ModifiedResourceResponse
   */
  public function get(Request $request): ModifiedResourceResponse
  {
    $service = Drupal::service('analytics_resources.resource_service');
    $message = 'Workflow resources queried successfully.';

    // Get pagination parameters from query
    $page = (int)$request->query->get('page', 1);
    $items_per_page = (int)$request->query->get('items_per_page', 50);

    // Minimum of 50 items per page, no upper limit
    $page = max(1, $page);
    $items_per_page = max(50, $items_per_page);

    try {
      // Calculate offset (page 1 starts at offset 0)
      $offset = ($page - 1) * $items_per_page;

      $data = $service->loadWorkflowResources($items_per_page, $offset);
      $total = $service->getWorkflowResourcesCount();
      $total_pages = ceil($total / $items_per_page);

      // Build pagination URLs
      $base_url = $request->getSchemeAndHttpHost() . '/asu_analytics_api/workflow';
      $pagination = [
        'total_items' => $total,
        'items_per_page' => $items_per_page,
        'total_pages' => $total_pages,
        'current_page' => $page
      ];

      // Add previous page URL if not on first page
      if ($page > 1) {
        $pagination['prev_page_url'] = $base_url . '?' . http_build_query([
            'page' => $page - 1,
            'items_per_page' => $items_per_page,
          ]);
      }

      // Add next page URL if not on last page
      if ($page < $total_pages) {
        $pagination['next_page_url'] = $base_url . '?' . http_build_query([
            'page' => $page + 1,
            'items_per_page' => $items_per_page,
          ]);
      }

      Drupal::logger('asu_analytics_api')->info($message);

      return new ModifiedResourceResponse([
        'status' => Response::HTTP_OK,
        'message' => $message,
        'data' => $data,
        'pagination' => $pagination
      ]);
    } catch (Throwable $e) {
      Drupal::logger('asu_analytics_api')->error($e->getMessage());
      return new ModifiedResourceResponse([
        'status' => Response::HTTP_NO_CONTENT,
        'message' => $e->getMessage(),
        'data' => null,
        'pagination' => null
      ]);
    }
  }
}
