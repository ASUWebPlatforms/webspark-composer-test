<?php

namespace Drupal\analytics_tweaks\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AnalyticsTweaksController.
 */
class AnalyticsTweaksController extends ControllerBase
{
  /**
   * Returns a JSON representation of the node.
   *
   * @param NodeInterface $node
   *
   * @return array|JsonResponse
   */
  public function renderNodeAsJson(NodeInterface $node): JsonResponse|array
  {
    $config = Drupal::config('analytics_tweaks.settings');
    $supported_types = array_keys(array_filter($config->get('types') ?: []));
    $data = [];

    if (in_array($node->bundle(), $supported_types)) {
      if ($node->bundle() == 'api_json') {
        $data = [
          'title' => $node->label(),
          'data' => [],
        ];

        foreach ($node->get('field_json_item')->referencedEntities() as $paragraph) {
          $data['data'][] = [
            'key' => $paragraph->get('field_key')->getString(),
            'value' => $paragraph->get('field_value')->getString(),
          ];
        }
      }

      return new JsonResponse($data);
    }

    return $this->entityTypeManager()->getViewBuilder('node')->view($node);
  }
}
