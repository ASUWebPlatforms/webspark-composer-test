<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns filtered Event Series by ID.
 */
class FilteredEventSeriesController extends ControllerBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getEventSeries(Request $request) {
    $id = $request->query->get('id');
    $bundle = $request->query->get('type'); // visit_event or barrett

    if (!$id || !$bundle) {
      return new JsonResponse(['error' => 'Missing id or type.'], 400);
    }

    $storage = $this->entityTypeManager->getStorage('eventseries');
    $results = $storage->loadByProperties([
      'id' => $id,
      'type' => $bundle,
      'status' => 1,
    ]);

//    \Drupal::logger('cstest')->notice('results:<pre>' . print_r($results, true) . '</pre>');


    if (empty($results)) {
      return new JsonResponse(['data' => []]);
    }

    $eventseries = reset($results);

    $type = $eventseries->bundle();

    $additional_tours = [];
    $barrett_tours = [];
    if($type === 'visit_event') {
      // Additional tours
      if ($eventseries->hasField('field_additional_tours') && !$eventseries->get('field_additional_tours')->isEmpty()) {
        foreach ($eventseries->get('field_additional_tours')->referencedEntities() as $paragraph) {
          // Skip Additional Tours marked as Private (11/25/2025)
          if (
            $paragraph->hasField('field_privacy')
            && strtolower((string) $paragraph->get('field_privacy')->value) === 'private'
          ) {
            continue;
          }

          $additional_tours[] = [
            'uuid' => $paragraph->uuid(),
            'paragraphId' => $paragraph->id(),
            'title' => $paragraph->label(),
          ];
        }
      }


      // Load Barrett Tours (EventSeries of type barrett)
      if ($eventseries->hasField('field_barrett_tours') && !$eventseries->get('field_barrett_tours')->isEmpty()) {
        foreach ($eventseries->get('field_barrett_tours')->referencedEntities() as $barrett_series) {
          $barrett_tours[] = [
            'uuid' => $barrett_series->uuid(),
            'eventseriesId' => $barrett_series->id(),
            'title' => $barrett_series->label(),
          ];
        }
      }

    } // END OF if($type === 'visit_event')


    $field_visitor_type_values = $eventseries->get('field_visitor_type')->getValue(); // List (text) and multiple values allowed
    $field_visitor_type = array_map(function ($item) {
      return $item['value'];
    }, $field_visitor_type_values);

    $field_legend_toggle_values = $eventseries->get('field_legend_toggle')->getValue(); // List (text) and multiple values allowed
    $field_legend_toggle = array_map(function ($item) {
      return $item['value'];
    }, $field_legend_toggle_values);

    $field_show_only_under_expasu = '';
    if($type === 'barrett') {
      $field_show_only_under_expasu = (bool)$eventseries->get('field_show_only_under_expasu')->value; // Only for Barrett
    }

    // field_privacy
    $privacy_label = NULL;
    if (!$eventseries->get('field_privacy')->isEmpty()) {
      $privacy_entity = $eventseries->get('field_privacy')->entity;
      if ($privacy_entity) {
        $privacy_label = $privacy_entity->label();
      }
    }

    return new JsonResponse([
      'data' => [
        'id' => $eventseries->id(),
        'uuid' => $eventseries->uuid(),
        'title' => $eventseries->label(),
        'type' => $eventseries->bundle(),
        'status' => $eventseries->isPublished(),

        'attributes' =>[
          'field_campus' => $eventseries->get('field_campus')->getValue()[0]['value'],
          'field_test_mode' => (bool) $eventseries->get('field_test_mode')->value,
          'field_display_title' => $eventseries->get('field_display_title')->value,
          'field_start_time_for_eventid' => (int) $eventseries->get('field_start_time_for_eventid')->value,
          'field_visitor_type' => $field_visitor_type,
          'field_legend_toggle' => $field_legend_toggle,
          'field_show_only_under_expasu' => $field_show_only_under_expasu,
          'field_capacity' => $eventseries->get('field_capacity')->value,
          'field_privacy' => $privacy_label,
          'field_publish_date' => $eventseries->bundle() === 'visit_event' ? ($eventseries->get('field_publish_date')->value ?? null) : null, // <--- field_publish_date exists ONLY on visit_event series, not in barrett series.
        ],

        // Include relationship-style fields
        'relationships' => [
          'field_additional_tours' => $additional_tours,
          'field_barrett_tours' => $barrett_tours,
        ],
      ],
    ]);
  }

}
