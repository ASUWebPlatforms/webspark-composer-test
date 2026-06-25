<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

class FilteredEventInstanceMonthController extends ControllerBase {

  public function getEventsByMonth(Request $request, $year = NULL, $month = NULL) {
    $year  = (int) $year;
    $month = (int) $month;

    if (!$year || !$month) {
      return new JsonResponse(['error' => 'Missing year or month'], 400);
    }


    // Build datetime range for the selected month (UTC storage) (Bug fix on 2/9/2026)
    $monthStart = new \DateTime(sprintf('%04d-%02d-01 00:00:00', $year, $month), new \DateTimeZone('UTC'));
    $nextMonthStart = (clone $monthStart)->modify('first day of next month');

    // Storage format matches eventinstance date fields: "Y-m-d\TH:i:s"
    $start = $monthStart->format('Y-m-d\TH:i:s');
    $end   = $nextMonthStart->format('Y-m-d\TH:i:s'); // exclusive upper bound



    // // "Today" in site timezone (America/Phoenix) as Y-m-d
    // $today = (new \DateTime('now', new \DateTimeZone('America/Phoenix')))
    //   ->format('Y-m-d');

    // Query eventinstances that fall in this month
    $query = \Drupal::entityTypeManager()->getStorage('eventinstance')->getQuery();
    $query->accessCheck(TRUE);
    $query->condition('status', 1);
    $query->condition('date.value', $start, '>=');
    $query->condition('date.value', $end, '<'); // NOTE: < next month start (includes last day) (Bug fix on 2/9/2026)

    // $query->condition('date.end_value', $today, '>='); // and whose *end* date has not already passed. // Changed on 12/22/2025.

    // Exclude events that have already ended (FULL datetime comparison)
    $nowUtc = new \DateTime('now', new \DateTimeZone('UTC'));
    $nowStorage = $nowUtc->format('Y-m-d\TH:i:s');
    $query->condition('date.end_value', $nowStorage, '>=');

    $query->sort('date.value', 'ASC');

    $ids = $query->execute();
    $events = \Drupal::entityTypeManager()->getStorage('eventinstance')->loadMultiple($ids);

    $output = [];

    foreach ($events as $event) {
      // Dates
      $startDT = new \DateTime($event->get('date')->value, new \DateTimeZone('UTC'));
      $startDT->setTimezone(new \DateTimeZone('America/Phoenix'));

      $endDT = new \DateTime($event->get('date')->end_value, new \DateTimeZone('UTC'));
      $endDT->setTimezone(new \DateTimeZone('America/Phoenix'));

      // Barrett tours on instance (only if field exists)
      $barrett_ids = [];
      if ($event->hasField('field_barrett_tours_event_instan') && !$event->get('field_barrett_tours_event_instan')->isEmpty()) {
        foreach ($event->get('field_barrett_tours_event_instan') as $item) {
          $barrett_ids[] = $item->target_id;
        }
      }

      // Optional fields (bundle dependent)
      $overwrite_capacity = $event->hasField('field_overwrite_capacity')
        ? $event->get('field_overwrite_capacity')->value
        : null;

      $capacity_instance = $event->hasField('field_capacity_event_instance')
        ? $event->get('field_capacity_event_instance')->value
        : null;

      $overwrite_barrett_tour = $event->hasField('field_overwrite_barretttour')
        ? $event->get('field_overwrite_barretttour')->value
        : null;

      // Legend/toggle (11/26/2025)
      $overwrite_legend_toggle = $event->hasField('field_overwrite_legend_toggle')
        ? $event->get('field_overwrite_legend_toggle')->value
        : null;

      // Legend/toggle instance (multi-value List(text))
      $legend_toggle_instance = null;

      if ($event->hasField('field_legend_toggle_event_instan') && !$event->get('field_legend_toggle_event_instan')->isEmpty()) {
        $values = $event->get('field_legend_toggle_event_instan')->getValue(); // array of [ ['value' => '...'], ... ]
        $legend_toggle_instance = [];

        foreach ($values as $item) {
          if (!empty($item['value'])) {
            $legend_toggle_instance[] = $item['value'];
          }
        }

        // If nothing valid ended up in the array, keep it null for cleanliness.
        if (!$legend_toggle_instance) {
          $legend_toggle_instance = null;
        }
      }

      $output[] = [
        'drupal_internal_id' => $event->id(),
        'title' => $event->label(),
        'start' => $startDT->format(\DateTime::ATOM),
        'end' => $endDT->format(\DateTime::ATOM),
        'eventseries_id' => $event->get('eventseries_id')->target_id ?? null,
        'type' => $event->bundle(),
        'uuid' => $event->uuid(),
        'overwrite_capacity' => $overwrite_capacity,
        'capacity_instance' => $capacity_instance,
        'overwrite_barrett_tour' => $overwrite_barrett_tour,
        'barrett_tours_instance' => $barrett_ids,
        // Legend/toggle (11/26/2025)
        'overwrite_legend_toggle' => $overwrite_legend_toggle,
        'legend_toggle_instance' => $legend_toggle_instance,
      ];
    }

    return new JsonResponse(['data' => $output]);
  }
}