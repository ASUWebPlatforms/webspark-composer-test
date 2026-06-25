<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Returns a JSON:API(/jsonapi/eventseries/barrett/)-compatible payload for barrett Event series.
 */
class BarrettTourController extends ControllerBase {

  public function byUuid(string $uuid): JsonResponse {
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $repo */
    $repo = \Drupal::service('entity.repository');

    // Load custom content entity "eventseries" by UUID.
    /** @var \Drupal\Core\Entity\EntityInterface|null $e */
    $e = $repo->loadEntityByUuid('eventseries', $uuid);
    if (!$e || $e->bundle() !== 'barrett') {
      return new JsonResponse(['error' => 'Not found'], 404);
    }

    // --- helpers ---
    $str = static function ($v) {
      return ($v === '' || $v === null) ? null : (string) $v;
    };
    $int = static function ($v) {
      return ($v === '' || $v === null) ? null : (int) $v;
    };
    $bool = static function ($v): bool {
      if ($v === null) return false;
      if (is_bool($v)) return $v;
      return ((string) $v) === '1';
    };
    $stringList = static function (EntityInterface $e, string $field): array {
      if (!$e->hasField($field) || $e->get($field)->isEmpty()) return [];
      $out = [];
      foreach ($e->get($field)->getValue() as $item) {
        // taxonomy term refs come as target_id/target_uuid in JSON:API.
        if (isset($item['value'])) {
          $out[] = (string) $item['value'];
        }
        elseif (isset($item['target_id']) && method_exists($e->get($field), 'referencedEntities')) {
          // Fallback to labels if storing references.
          foreach ($e->get($field)->referencedEntities() as $ref) {
            $out[] = $ref->label();
          }
          break;
        }
      }
      return $out;
    };
    $metatag = static function (EntityInterface $e): array {
      if (!$e->hasField('metatag') || $e->get('metatag')->isEmpty()) return [];
      // JSON:API serializes Metatag as an array of { tag, attributes: {...} }
      $val = $e->get('metatag')->first()->getValue();
      if (isset($val['value']) && is_array($val['value'])) {
        return $val['value'];
      }
      return []; // safe fallback
    };

    // Complex recurring date composites: trying to mirror keys JSON:API emits.
    $composite = function (EntityInterface $e, string $field, array $defaults = []) {
      $d = $defaults + [
        'value' => null,
        'end_value' => null,
        'time' => '06:00 am',
        'duration' => 900,
        'end_time' => '06:00 am',
        'duration_or_end_time' => 'duration',
      ];

      if ($e->hasField($field) && !$e->get($field)->isEmpty()) {
        $raw = $e->get($field)->first()->toArray();

        // Normalize date/times with timezone offset.
        if (array_key_exists('value', $raw)) {
          $d['value'] = self::isoWithOffset($raw['value']);
        }
        if (array_key_exists('end_value', $raw)) {
          $d['end_value'] = self::isoWithOffset($raw['end_value']);
        }

        // Copy the rest, casting where appropriate.
        foreach (['time','duration','end_time','duration_or_end_time','buffer','buffer_units','days','type','day_occurrence','day_of_month','year_interval','months'] as $k) {
          if (array_key_exists($k, $raw)) {
            $d[$k] = $raw[$k];
            if (in_array($k, ['duration','buffer','year_interval'], true)) {
              $d[$k] = ($d[$k] === '' || $d[$k] === null) ? ($k === 'year_interval' ? 1 : 0) : (int) $d[$k];
            }
          }
        }
      }

      return $d;
    };

    $datePairs = static function (EntityInterface $e, string $field): array {
      // Returns [{ value: 'YYYY-MM-DD', end_value: 'YYYY-MM-DD' }, ...]
      if (!$e->hasField($field) || $e->get($field)->isEmpty()) return [];
      $out = [];
      foreach ($e->get($field)->getValue() as $item) {
        $out[] = [
          'value' => isset($item['value']) ? (string) $item['value'] : null,
          'end_value' => isset($item['end_value']) ? (string) $item['end_value'] : (isset($item['value']) ? (string) $item['value'] : null),
        ];
      }
      return $out;
    };

    // body as text_long with value/format/processed.
    $body = null;
    if ($e->hasField('body') && !$e->get('body')->isEmpty()) {
      $b = $e->get('body')->first();
      $body = [
        'value' => $b->get('value')->getString(),
        'format' => $b->get('format')->getString() ?: 'basic_html',
        'processed' => $b->get('processed')->getString() ?: $b->get('value')->getString(),
      ];
    }

    // field_event_description_html (text_long/processed)
    $descHtml = null;
    if ($e->hasField('field_event_description_html') && !$e->get('field_event_description_html')->isEmpty()) {
      $d = $e->get('field_event_description_html')->first();
      $descHtml = [
        'value' => $d->get('value')->getString(),
        'format' => $d->get('format')->getString() ?: 'basic_html',
        'processed' => $d->get('processed')->getString() ?: $d->get('value')->getString(),
      ];
    }

    // field_privacy
    $privacy_label = NULL;
    if (!$e->get('field_privacy')->isEmpty()) {
      $privacy_entity = $e->get('field_privacy')->entity;
      if ($privacy_entity) {
        $privacy_label = $privacy_entity->label();
      }
    }


    // Attributes block, with casts to match JSON:API output.
    $attributes = [
      'drupal_internal__id'                => $int($e->id()),
      'drupal_internal__vid'               => $int($e->getRevisionId()),
      'langcode'                           => $e->language()->getId(),
      'revision_timestamp'                 => $e->getRevisionCreationTime() ? date(DATE_ATOM, $e->getRevisionCreationTime()) : null,
      'revision_log'                       => $e->getRevisionLogMessage() ?: null,
      'status'                             => $e->isPublished(),
      'title'                              => $str($e->label()),
      'body'                               => $body, // or null
      'recur_type'                         => $e->hasField('recur_type') ? $str($e->get('recur_type')->value ?? null) : null,

      'consecutive_recurring_date'         => $composite($e, 'consecutive_recurring_date', [
        'value' => null, 'end_value' => null, 'time' => '06:00 am', 'end_time' => '11:45 pm',
        'duration' => 5, 'duration_units' => 'minute', 'buffer' => 0, 'buffer_units' => 'minute',
      ]),
      'daily_recurring_date'               => $composite($e, 'daily_recurring_date', [
        'value' => null, 'end_value' => null, 'time' => '06:00 am',
        'duration' => 900, 'end_time' => '06:00 am', 'duration_or_end_time' => 'duration',
      ]),
      'weekly_recurring_date'              => $composite($e, 'weekly_recurring_date', [
        'value' => null, 'end_value' => null, 'time' => '06:00 am',
        'duration' => 900, 'end_time' => '06:00 am', 'duration_or_end_time' => 'duration', 'days' => '',
      ]),
      'monthly_recurring_date'             => $composite($e, 'monthly_recurring_date', [
        'value' => null, 'end_value' => null, 'time' => '06:00 am',
        'duration' => 900, 'end_time' => '06:00 am', 'duration_or_end_time' => 'duration',
        'days' => '', 'type' => '', 'day_occurrence' => '', 'day_of_month' => '',
      ]),
      'yearly_recurring_date'              => $composite($e, 'yearly_recurring_date', [
        'value' => null, 'end_value' => null, 'time' => '06:00 am',
        'duration' => 900, 'end_time' => '06:00 am', 'duration_or_end_time' => 'duration',
        'days' => '', 'type' => '', 'day_occurrence' => '', 'day_of_month' => '',
        'year_interval' => 1, 'months' => '',
      ]),

      'custom_date'                        => $e->hasField('custom_date') ? $e->get('custom_date')->getValue() : [],
      'excluded_dates'                     => $datePairs($e, 'excluded_dates'),
      'included_dates'                     => $datePairs($e, 'included_dates'),

      'created'                            => $e->getCreatedTime() ? date(DATE_ATOM, $e->getCreatedTime()) : null,
      'changed'                            => $e->getChangedTime() ? date(DATE_ATOM, $e->getChangedTime()) : null,
      'revision_translation_affected'      => true,
      'default_langcode'                   => $e->isDefaultTranslation(),

      'metatag'                            => $metatag($e),

      // Key fields your UI uses:
      'event_registration'                 => $e->hasField('event_registration') ? $str($e->get('event_registration')->value ?? null) : null,
      'field_add_to_calendar_descr'        => $e->hasField('field_add_to_calendar_descr') ? $str($e->get('field_add_to_calendar_descr')->value ?? null) : null,
      'field_campus'                       => $e->hasField('field_campus') ? $str($e->get('field_campus')->value ?? null) : null,
      'field_capacity'                     => $e->hasField('field_capacity') ? (int) ($e->get('field_capacity')->value ?? 0) : 0,
      'field_display_title'                => $e->hasField('field_display_title') ? $str($e->get('field_display_title')->value ?? null) : null,
      'field_event_description_html'       => $descHtml,
      'field_evtype'                       => $e->hasField('field_evtype') ? $str($e->get('field_evtype')->value ?? null) : null,
      'field_legend_toggle'                => $stringList($e, 'field_legend_toggle'),
      'field_show_only_under_expasu'       => $e->hasField('field_show_only_under_expasu') ? $bool($e->get('field_show_only_under_expasu')->value ?? 0) : false,
      'field_start_time_for_eventid'       => $e->hasField('field_start_time_for_eventid') ? (int) ($e->get('field_start_time_for_eventid')->value ?? 0) : 0,
      'field_test_mode'                    => $e->hasField('field_test_mode') ? $bool($e->get('field_test_mode')->value ?? 0) : false,
      'field_visitor_type'                 => $stringList($e, 'field_visitor_type'),
      'field_privacy'                      => $privacy_label,
    ];

    $host = \Drupal::request()->getSchemeAndHttpHost();
    $self = $host . '/visit-revamp-api/barrett-tour/' . $e->uuid();

    $data = [
      'jsonapi' => [
        'version' => '1.0',
        'meta' => [
          'links' => [
            'self' => ['href' => 'http://jsonapi.org/format/1.0/'],
          ],
        ],
      ],
      'data' => [
        'type' => 'eventseries--barrett',
        'id' => $e->uuid(),
        'links' => [
          'self' => [
            // Mirror JSON:API’s resourceVersion style is optional; here we just point to our endpoint.
            'href' => $self,
          ],
        ],
        'attributes' => $attributes,
      ],
    ];

    return new JsonResponse($data, 200, ['Content-Type' => 'application/vnd.api+json']);
  }


  private static function isoWithOffset(?string $value): ?string {
    if (!$value) return null;
    // Site default timezone (falls back to UTC).
    $tz = \Drupal::config('system.date')->get('timezone.default') ?: 'UTC';

    // If the string already has a timezone offset, keep it as-is.
    if (preg_match('/[Zz]|[+\-]\d{2}:\d{2}$/', $value)) {
      try {
        $dt = new DrupalDateTime($value);
        // Re-format to standard ISO with the same zone (preserves offset).
        return $dt->format(DATE_ATOM);
      } catch (\Exception $e) {
        return $value; // fail-safe
      }
    }

    // Assume storage is UTC when no offset is present, convert to site TZ.
    try {
      $dt = new DrupalDateTime($value, new \DateTimeZone('UTC'));
      $dt->setTimezone(new \DateTimeZone($tz));
      return $dt->format(DATE_ATOM); // e.g., 2025-05-10T11:50:53-07:00
    } catch (\Exception $e) {
      return $value; // fail-safe
    }
  }
  
}