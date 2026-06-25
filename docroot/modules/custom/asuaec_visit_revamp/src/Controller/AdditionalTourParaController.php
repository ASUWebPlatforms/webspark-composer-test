<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Custom JSON endpoints for Additional Tour capacity logic.
 */
class AdditionalTourParaController extends ControllerBase {

  /**
   * GET /visit-revamp-api/addtour-resolved-capacity/{addtour_eventid}
   * Example: /visit-revamp-api/addtour-resolved-capacity/356-2142-1758910500
   *
   * Returns the effective capacity for an Additional Tour on the date derived
   * from the eventid timestamp (converted to America/Phoenix).
   */
  public function getAddTourResolvedCapacity(string $addtour_eventid): JsonResponse {
    // Parse "{series_id}-{additional_tour_paragraph_id}-{eventid_timestamp}".
    if (!preg_match('/^(\d+)-(\d+)-(\d+)$/', $addtour_eventid, $m)) {
      return $this->error("Invalid addtour_eventid format. Expect seriesId-paraId-timestamp (e.g., 356-2142-1758910500).", 400);
    }
    [$all, $series_id, $para_id, $eventid_ts] = $m;
    $series_id = (int) $series_id;
    $para_id   = (int) $para_id;
    $eventid_ts = (int) $eventid_ts;

    // Convert timestamp -> date in America/Phoenix (YYYY-MM-DD).
    try {
      $dt = (new \DateTimeImmutable('@' . $eventid_ts))
        ->setTimezone(new \DateTimeZone('America/Phoenix'));
      $target_date = $dt->format('Y-m-d');
    } catch (\Throwable $e) {
      return $this->error('Invalid timestamp for event date.', 400);
    }

    // Load the Additional Tour paragraph.
    $additional_tour = $this->loadParagraph($para_id, 'additional_tour');
    if (!$additional_tour) {
      return $this->error("Additional tour paragraph {$para_id} not found.", 404);
    }

    // Base capacity on the Additional Tour.
    $base_capacity = NULL;
    if ($additional_tour->hasField('field_capacity')) {
      $base_capacity = isset($additional_tour->get('field_capacity')->value)
        ? (int) $additional_tour->get('field_capacity')->value
        : NULL;
    }

    // Overwrite flag.
    $overwrite_enabled = (bool) ($additional_tour->get('field_overwrite_capacity_addtour')->value ?? FALSE);

    $resolved_capacity = $base_capacity;
    $source = 'base';
    $matched_child = NULL;

    if ($overwrite_enabled && $additional_tour->hasField('field_capacity_overwrite_para')) {
      foreach ($additional_tour->get('field_capacity_overwrite_para') as $item) {
        $child_id = (int) ($item->target_id ?? 0);
        if (!$child_id) {
          continue;
        }
        // Some sites use 'additional_tour_capacity_overwrite' or 'addtour_capacity_overwrite'.
        $child = $this->loadParagraph($child_id, 'additional_tour_capacity_overwrite', TRUE)
          ?: $this->loadParagraph($child_id, 'addtour_capacity_overwrite', TRUE);
        if (!$child) {
          continue;
        }

        // Compare date-only.
        $date_only = $child->hasField('field_date_only') ? ($child->get('field_date_only')->value ?? NULL) : NULL;
        if ($date_only !== $target_date) {
          continue;
        }

        $child_capacity = $child->hasField('field_capacity')
          ? (int) ($child->get('field_capacity')->value ?? 0)
          : NULL;

        if ($child_capacity !== NULL) {
          $resolved_capacity = $child_capacity;
          $source = 'overwrite';
          $matched_child = [
            'paragraph_id' => (int) $child->id(),
            'uuid' => $child->uuid(),
            'date_only' => $date_only,
            'capacity' => $child_capacity,
          ];
          // First match wins; break, or pick your own precedence rule.
          break;
        }
      }
    }

    return $this->json([
      'addtour_eventid' => $addtour_eventid,
      'series_id' => $series_id,
      'additional_tour_para_id' => $para_id,
      'eventid_timestamp' => $eventid_ts,
      'event_date_America_Phoenix' => $target_date,
      'overwrite_enabled' => $overwrite_enabled,
      'base_capacity' => $base_capacity,
      'resolved_capacity' => $resolved_capacity,
      'source' => $source, // 'overwrite' or 'base'
      'matched_overwrite' => $matched_child, // null if base
    ]);
  }




  // --------------------
  // Helpers
  // --------------------

  protected function loadParagraph(int $id, string $expected_bundle = '', bool $soft = FALSE): ?EntityInterface {
    $para = $this->entityTypeManager()->getStorage('paragraph')->load($id);
    if (!$para) {
      return NULL;
    }
    if ($expected_bundle && $para->bundle() !== $expected_bundle) {
      return $soft ? NULL : NULL;
    }
    return $para;
  }

  protected function json(array $payload, int $status = 200): JsonResponse {
    $response = new JsonResponse($payload, $status);
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    return $response;
  }

  protected function error(string $message, int $status = 400): JsonResponse {
    return $this->json(['error' => $message], $status);
  }

}
