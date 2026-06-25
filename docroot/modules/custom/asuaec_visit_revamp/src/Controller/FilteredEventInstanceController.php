<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class FilteredEventInstanceController extends ControllerBase {

  public function getFilteredEvents(Request $request) {
    $query = \Drupal::entityTypeManager()->getStorage('eventinstance')->getQuery();
    $query->accessCheck(TRUE);
    $query->condition('status', 1);
    $query->condition('date.end_value', date('Y-m-d'), '>=');
    $query->sort('date.end_value', 'ASC');

    $ids = $query->execute();
    $events = \Drupal::entityTypeManager()->getStorage('eventinstance')->loadMultiple($ids);


    $data = [];
    foreach ($events as $event) {
      try{

        if (!$event->hasField('date') || !$event->get('date')->value) {
          \Drupal::logger('asuaec_visit_revamp')->warning('Event @id has no date', ['@id' => $event->id()]);
          continue;
        }

        if (!$event->hasField('title')) {
          \Drupal::logger('asuaec_visit_revamp')->warning('Event @id missing title field', ['@id' => $event->id()]);
          continue;
        }

  //      $date = new \DateTime($event->get('date')->value, new \DateTimeZone('America/Phoenix'));
  //      $date2 = new \DateTime($event->get('date')->end_value, new \DateTimeZone('America/Phoenix'));
        $start = new \DateTime($event->get('date')->value, new \DateTimeZone('UTC'));
        $start->setTimezone(new \DateTimeZone('America/Phoenix'));

        $end = new \DateTime($event->get('date')->end_value, new \DateTimeZone('UTC'));
        $end->setTimezone(new \DateTimeZone('America/Phoenix'));

        $field_barrett_tours_event_instan = [];
        if ($event->hasField('field_barrett_tours_event_instan')) {
          foreach ($event->get('field_barrett_tours_event_instan') as $item) {
            $field_barrett_tours_event_instan[] = $item->target_id;
          }
        }

        if (!$event->hasField('title')) {
          \Drupal::logger('asuaec_visit_revamp')->error('Event ID @id missing title field. Bundle: @bundle', [
            '@id' => $event->id(),
            '@bundle' => $event->bundle(),
          ]);
          continue; // skip it
        }

        $data[] = [
          'drupal_internal_id' => $event->id(), // Instance id
          'title' => $event->label() ?? 'Untitled',
          'start' => $start->format(\DateTime::ATOM),
          'end' => $end->format(\DateTime::ATOM),
          'eventseries_id' => $event->get('eventseries_id')->target_id ?? null,
          'type' => $event->bundle(),
          'uuid' => $event->uuid(),
          'overwrite_capacity' => $event->get('field_overwrite_capacity')->value ?? null,
          'capacity_instance' => $event->get('field_capacity_event_instance')->value ?? null,
          //'overwrite_barrett_tour' => $event->get('field_overwrite_barretttour')->value,
          'overwrite_barrett_tour' => $event->hasField('field_overwrite_barretttour')
            ? $event->get('field_overwrite_barretttour')->value
            : null,
          'barrett_tours_instance' => $field_barrett_tours_event_instan, // Barrett tour Series id
          'overwrite_conf_letter' => $event->get('field_overwrite_conf_letter')->value ?? null,
          'conf_letter_instance' => $event->get('field_conf_letter_event_instance')->target_id ?? null,
        ];

      } catch (\Throwable $e) {
        \Drupal::logger('asuaec_visit_revamp')->error('Error on event @id: @msg', [
          '@id' => $event->id(),
          '@msg' => $e->getMessage(),
        ]);
        continue;
      }
    } // END OF foreach ($events as $event)

    return new JsonResponse(['data' => $data]);
  }
}


