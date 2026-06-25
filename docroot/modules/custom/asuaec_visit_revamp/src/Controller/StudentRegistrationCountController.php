<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Connection;

/**
 * Returns student registration count (including guests) by event ID.
 */
class StudentRegistrationCountController {

  /**
   * @param Request $request
   * @return JsonResponse
   *
   * Get registration count for top-level events such as Exp ASU, Barrett solo by event ID.
   * https://visit-asu-csdev60.ddev.site/visit-revamp-api/student-registrations-count?id=356-1753110000
   */
  public function getCount(Request $request) {
    $event_id = $request->query->get('id');

    if (empty($event_id)) {
      return new JsonResponse(['error' => 'Missing id parameter'], 400);
    }

    // Get node IDs matching the event_id.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'student_registered_visits')
      ->condition('field_student_event_id', $event_id);

    $nids = $query->execute();

    $total_count = 0;

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        $guest_count = 0;
        if ($node->hasField('field_student_guests') && !$node->get('field_student_guests')->isEmpty()) {
          $guest_count = (int) $node->get('field_student_guests')->value;
        }
        $total_count += 1 + $guest_count; // 1 for student + # of guests
      }
    }

    return new JsonResponse(['count' => $total_count]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   *
   * Get registration count for Additional tour.
   * We count "Additional tour registrant" type node.
   * https://visit-asu-csdev60.ddev.site/visit-revamp-api/student-registrations-count-addtour?id=356-1753110000
   */
  public function getCountAddTour(Request $request) {
    $event_id = $request->query->get('id');

    if (empty($event_id)) {
      return new JsonResponse(['error' => 'Missing id parameter'], 400);
    }

    // Get node IDs matching the event_id.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'additional_tour_registrant')
      ->condition('field_additional_tour_id', $event_id);

    $nids = $query->execute();

    $total_count = 0;

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        $guest_count = 0;
        if ($node->hasField('field_student_guests') && !$node->get('field_student_guests')->isEmpty()) {
          $guest_count = (int) $node->get('field_student_guests')->value;
        }
        $total_count += 1 + $guest_count; // 1 for student + # of guests
      }
    }

    return new JsonResponse(['count' => $total_count]);
  }


  /**
   * Batch counts for top-level event registrations.
   * GET: /visit-revamp-api/student-registrations-counts?ids=696-1770403500,771-1769439600
   * (Also supports POST JSON: {"ids":[...]} )
   */
  public function getStudentRegistrationsCountBatch(Request $request) {
    $ids = $this->extractIds($request);
    if (empty($ids)) {
      return new JsonResponse(['counts' => []]);
    }

    $counts = array_fill_keys($ids, 0);

    $db = \Drupal::database();

    // Count "students" + sum "guests" grouped by field_student_event_id.
    $q = $db->select('node_field_data', 'n');
    $q->innerJoin('node__field_student_event_id', 'eid', 'eid.entity_id = n.nid');
    $q->leftJoin('node__field_student_guests', 'g', 'g.entity_id = n.nid');

    $q->addField('eid', 'field_student_event_id_value', 'event_id');
    $q->addExpression('COUNT(DISTINCT n.nid)', 'students');
    $q->addExpression('COALESCE(SUM(g.field_student_guests_value), 0)', 'guests');

    $q->condition('n.type', 'student_registered_visits');
    $q->condition('eid.field_student_event_id_value', $ids, 'IN');
    $q->groupBy('eid.field_student_event_id_value');

    $result = $q->execute();
    foreach ($result as $row) {
      $total = (int) $row->students + (int) $row->guests;
      $counts[$row->event_id] = $total;
    }

    return new JsonResponse(['counts' => $counts]);
  }

  // /**
  //  * Batch counts for additional tour registrations.
  //  * GET: /visit-revamp-api/student-registrations-counts-addtour?ids=791-2142-1772211600,811-2142-1771693200
  //  * (Also supports POST JSON: {"ids":[...]} )
  //  */
  // public function getAdditionalTourRegistrationsCountBatch(Request $request) {
  //   $ids = $this->extractIds($request);
  //   if (empty($ids)) {
  //     return new JsonResponse(['counts' => []]);
  //   }

  //   $counts = array_fill_keys($ids, 0);

  //   $db = \Drupal::database();

  //   $q = $db->select('node_field_data', 'n');
  //   $q->innerJoin('node__field_additional_tour_id', 'aid', 'aid.entity_id = n.nid');
  //   $q->leftJoin('node__field_student_guests', 'g', 'g.entity_id = n.nid');

  //   $q->addField('aid', 'field_additional_tour_id_value', 'addtour_id');
  //   $q->addExpression('COUNT(DISTINCT n.nid)', 'students');
  //   $q->addExpression('COALESCE(SUM(g.field_student_guests_value), 0)', 'guests');

  //   $q->condition('n.type', 'additional_tour_registrant');
  //   $q->condition('aid.field_additional_tour_id_value', $ids, 'IN');
  //   $q->groupBy('aid.field_additional_tour_id_value');

  //   $result = $q->execute();
  //   foreach ($result as $row) {
  //     $total = (int) $row->students + (int) $row->guests;
  //     $counts[$row->addtour_id] = $total;
  //   }

  //   return new JsonResponse(['counts' => $counts]);
  // }

  /**
   * Helper: accept ids from ?ids=a,b,c OR POST {"ids":[...]}
   */
  private function extractIds(Request $request): array {
    // 1) GET ?ids=...
    $idsParam = (string) $request->query->get('ids', '');
    $ids = [];

    if ($idsParam !== '') {
      $ids = array_filter(array_map('trim', explode(',', $idsParam)));
    }

    // 2) POST JSON {"ids":[...]}
    if (empty($ids)) {
      $content = (string) $request->getContent();
      if ($content !== '') {
        $json = json_decode($content, true);
        if (is_array($json) && !empty($json['ids']) && is_array($json['ids'])) {
          $ids = array_filter(array_map('trim', $json['ids']));
        }
      }
    }

    // de-dupe
    $ids = array_values(array_unique($ids));
    return $ids;
  }
}
