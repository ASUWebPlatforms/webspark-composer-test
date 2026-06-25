<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns a JSON:API(/jsonapi/paragraph/additional_tour)-compatible payload for Additional tour Paragraph
 */
class AdditionalTourController extends ControllerBase {

  public function byUuid(string $uuid): JsonResponse {
    /** @var \Drupal\paragraphs\ParagraphInterface|null $p */
    $p = \Drupal::service('entity.repository')->loadEntityByUuid('paragraph', $uuid);
    if (!$p || $p->bundle() !== 'additional_tour') {
      return new JsonResponse(['error' => 'Not found'], 404);
    }

    // --- Helpers ---
    $bool = static function ($v): bool {
      // JSON:API booleans are real booleans.
      // Drupal stores many as "0"/"1" strings.
      return filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((string)$v === '1');
    };
    $intOrNull = static function ($v) {
      return ($v === '' || $v === null) ? null : (int) $v;
    };
    $stringOrNull = static function ($v) {
      return ($v === '' || $v === null) ? null : (string) $v;
    };
    $timeRangeToSeconds = static function ($item) use ($intOrNull) {
      // Try direct {from,to} integers first (as many time-range fields use).
      $arr = $item->toArray();
      if (array_key_exists('from', $arr) || array_key_exists('to', $arr)) {
        return [
          'from' => isset($arr['from']) ? (int) $arr['from'] : null,
          'to'   => isset($arr['to'])   ? (int) $arr['to']   : null,
        ];
      }
      // Fallback: convert value/end_value timestamps or times to seconds from midnight.
      // Works for date/datetime fields representing same-day ranges.
      $toSec = static function (?string $value) {
        if (!$value) return null;
        // Accept "HH:MM[:SS]" or a date/time string.
        // If it's time-only, read it directly; if it's datetime, parse time component.
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
          [$h, $m, $s] = array_pad(explode(':', $value), 3, '0');
          return ((int)$h)*3600 + ((int)$m)*60 + (int)$s;
        }
        $ts = strtotime($value);
        if ($ts === false) return null;
        $h = (int) date('G', $ts);
        $m = (int) date('i', $ts);
        $s = (int) date('s', $ts);
        return $h*3600 + $m*60 + $s;
      };

      $value = $item->get('value')?->getString();
      $end   = $item->get('end_value')?->getString();
      return [
        'from' => $toSec($value),
        'to'   => $toSec($end),
      ];
    };

    // Parent info (matches JSON:API relationship shape you showed).
    $parent = $p->getParentEntity();
    $parent_type = $parent ? $parent->getEntityTypeId() : null;       // e.g. "node" or "paragraph" or "eventseries"
    $parent_bundle = $parent ? $parent->bundle() : null;               // e.g. "eventseries"
    $parent_field_name = method_exists($p, 'getParentFieldName') ? $p->getParentFieldName() : null;

    // field_time_range normalization
    $field_time_range = null;
    if (!$p->get('field_time_range')->isEmpty()) {
      $field_time_range = $timeRangeToSeconds($p->get('field_time_range')->first());
      // If both ended up null, keep it null to mirror JSON:API behavior for empty.
      if ($field_time_range['from'] === null && $field_time_range['to'] === null) {
        $field_time_range = null;
      }
    }

    // Build JSON:API-like payload with correct types.
    $attrs = [
      'created'                         => $p->getCreatedTime() ? date(DATE_ATOM, $p->getCreatedTime()) : null,
      'default_langcode'                => (bool) $p->isDefaultTranslation(),
      'drupal_internal__id'             => $intOrNull($p->id()),
      // 'drupal_internal__revision_id'    => $intOrNull($p->getRevisionId()),
      'field_addtour_name'              => $stringOrNull($p->get('field_addtour_name')->value ?? null),
      'field_addtour_type'              => $stringOrNull($p->get('field_addtour_type')->value ?? null),
      'field_capacity'                  => $intOrNull($p->get('field_capacity')->value ?? null),
      'field_college'                   => $stringOrNull($p->get('field_college')->value ?? null),
      'field_need_radio_button'         => $bool($p->get('field_need_radio_button')->value ?? false),
      'field_overwrite_agenda_addtour'  => $bool($p->get('field_overwrite_agenda_addtour')->value ?? false),
      'field_overwrite_capacity_addtour'=> $bool($p->get('field_overwrite_capacity_addtour')->value ?? false),
      'field_overwrite_time_addtour'    => $bool($p->get('field_overwrite_time_addtour')->value ?? false),
      'field_privacy'                   => $stringOrNull($p->get('field_privacy')->value ?? null),
      'field_start_time_for_addtourid'  => $intOrNull($p->get('field_start_time_for_addtourid')->value ?? null),
      'field_time_range'                => $field_time_range, // <-- now {from,to} or null
      'langcode'                        => $p->language()->getId(),
      'parent_field_name'               => $parent_field_name,     // e.g. "field_additional_tours"
      'parent_type'                     => $parent_bundle,         // e.g. "eventseries"
      'status'                          => (bool) $p->isPublished(),
      'revision_translation_affected'   => true,
    ];

    $data = [
      'jsonapi' => ['version' => '1.0'],
      'data' => [
        'type' => 'paragraph--additional_tour',
        'id' => $p->uuid(),
        'attributes' => $attrs,
        'relationships' => [
          'parent' => [
            'data' => $parent ? [
              'type' => ($parent_type . '--' . $parent_bundle),
              'id'   => $parent->uuid(),
            ] : null,
          ],
        ],
      ],
    ];

    return new JsonResponse($data, 200, ['Content-Type' => 'application/vnd.api+json']);
  }
}