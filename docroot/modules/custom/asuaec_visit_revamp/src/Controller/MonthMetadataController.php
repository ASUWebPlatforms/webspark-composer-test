<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Utility\Xss;

/**
 * Returns all “metadata” needed for a given month:
 * - Event series info (seriesMetaCacheRef)
 * - Additional tours
 * - Barrett tours
 *
 * URL: /visit-revamp-api/month-metadata/{year}/{month}
 */
class MonthMetadataController extends ControllerBase {

  /**
   * Build month metadata payload.
   *
   * @param int|null $year
   * @param int|null $month
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getMonthMetadata($year = NULL, $month = NULL) {
    $year = (int) $year;
    $month = (int) $month;

    if (!$year || !$month || $month < 1 || $month > 12) {
      return new JsonResponse(['error' => 'Invalid year or month'], 400);
    }

    // --- 1. Find all eventinstances in this month (same idea as FilteredEventInstanceMonthController) ---

    // Build date range for the selected month in site timezone.
    $site_tz = new \DateTimeZone('America/Phoenix');
    $start_dt = new DrupalDateTime(sprintf('%04d-%02d-01 00:00:00', $year, $month), $site_tz);
    $end_dt = clone $start_dt;
    $end_dt->modify('last day of this month')->setTime(23, 59, 59);

    // Convert to storage format (UTC Y-m-d H:i:s) for eventinstance "date" field.
    $start_utc = clone $start_dt;
    $start_utc->setTimezone(new \DateTimeZone('UTC'));
    $end_utc = clone $end_dt;
    $end_utc->setTimezone(new \DateTimeZone('UTC'));

    $start_str = $start_utc->format('Y-m-d\TH:i:s');
    $end_str = $end_utc->format('Y-m-d\TH:i:s');

    $etm = \Drupal::entityTypeManager();

    // Query eventinstances in the month.
    $instance_storage = $etm->getStorage('eventinstance');
    $query = $instance_storage->getQuery();
    $query->accessCheck(TRUE);
    $query->condition('status', 1);
    // Between start and end date.
    $query->condition('date.value', $start_str, '>=');
    $query->condition('date.value', $end_str, '<=');

    $instance_ids = $query->execute();

    if (empty($instance_ids)) {
      // No instances -> nothing to return.
      return new JsonResponse([
        'series' => [],
        'additional_tours' => [],
        'barrett_tours' => [],
      ]);
    }

    $instances = $instance_storage->loadMultiple($instance_ids);

    // --- 2. Collect unique series IDs from instances ---

    $series_ids = [];

    foreach ($instances as $instance) {
      if ($instance->hasField('eventseries_id') && !$instance->get('eventseries_id')->isEmpty()) {
        $sid = $instance->get('eventseries_id')->target_id;
        if ($sid) {
          $series_ids[$sid] = $sid;
        }
      }
    }

    if (empty($series_ids)) {
      return new JsonResponse([
        'series' => [],
        'additional_tours' => [],
        'barrett_tours' => [],
      ]);
    }

    // --- 3. Load all eventseries in one go ---

    $series_storage = $etm->getStorage('eventseries');
    $series_entities = $series_storage->loadMultiple($series_ids);

    $series_payload = [];
    $additional_tour_payload = [];
    $barrett_tour_payload = [];

    // We will collect additional tour and Barrett IDs as we walk series.
    $all_additional_paragraph_ids = [];
    $all_barrett_series_ids = [];

    foreach ($series_entities as $series) {
      $sid = $series->id();
      $bundle = $series->bundle();

      // Resolve privacy entity to its label (e.g. "Public")
      $seriesPrivacyLabel = NULL;
      if ($series->hasField('field_privacy') && !$series->get('field_privacy')->isEmpty()) {
        $privacy_entity = $series->get('field_privacy')->entity;
        if ($privacy_entity) {
          $seriesPrivacyLabel = $privacy_entity->label();
        }
      }

      // Basic attributes used in CalendarPage.js
      $attrs = [
        'drupal_internal__id' => $sid,
        'type' => $bundle,
        'title' => $series->label(),
        // Fields in visit_event and barrett Event Series entity
        'field_campus' => $series->hasField('field_campus') && !$series->get('field_campus')->isEmpty()
          ? $series->get('field_campus')->value
          : NULL,

        // Visitor type as labels/values (handles ref + list/text)
        'field_visitor_type' => $this->getVisitorTypesFromSeries($series),

        'field_legend_toggle' => $series->hasField('field_legend_toggle') && !$series->get('field_legend_toggle')->isEmpty()
          ? array_map(static function ($item) { return $item['value'] ?? NULL; }, $series->get('field_legend_toggle')->getValue())
          : [],
        'field_capacity' => $series->hasField('field_capacity') && !$series->get('field_capacity')->isEmpty()
          ? (int) $series->get('field_capacity')->value
          : NULL,
        'field_test_mode' => $series->hasField('field_test_mode') && !$series->get('field_test_mode')->isEmpty()
          ? (bool) $series->get('field_test_mode')->value
          : FALSE,
        'field_privacy' => $seriesPrivacyLabel,
        'field_publish_date' => $series->hasField('field_publish_date') && !$series->get('field_publish_date')->isEmpty()
          ? $series->get('field_publish_date')->value
          : NULL,
        'field_display_title' => $series->hasField('field_display_title') && !$series->get('field_display_title')->isEmpty()
          ? $series->get('field_display_title')->value
          : NULL,
        'field_evtype' => $series->hasField('field_evtype') && !$series->get('field_evtype')->isEmpty()
          ? $series->get('field_evtype')->value
          : NULL,
        'field_show_only_under_expasu' => $series->hasField('field_show_only_under_expasu')
          && !$series->get('field_show_only_under_expasu')->isEmpty()
          ? (bool) $series->get('field_show_only_under_expasu')->value
          : FALSE,
        'field_event_description_html' => (
          $series->hasField('field_event_description_html') &&
          !$series->get('field_event_description_html')->isEmpty()
        ) ? Xss::filterAdmin($series->get('field_event_description_html')->value) : NULL,
        'field_start_time_for_eventid' => $series->hasField('field_start_time_for_eventid') && !$series->get('field_start_time_for_eventid')->isEmpty()
          ? (int) $series->get('field_start_time_for_eventid')->value
          : NULL,
        ];

      // --- Relationships: Additional tours (paragraphs) ---

      $additional_tours = [];
      if ($series->hasField('field_additional_tours') && !$series->get('field_additional_tours')->isEmpty()) {
        foreach ($series->get('field_additional_tours')->referencedEntities() as $para) {
          $pid = $para->id();
          $uuid = $para->uuid();
          $additional_tours[] = [
            'uuid' => $uuid,
            'paragraphId' => $pid,
          ];
          $all_additional_paragraph_ids[$pid] = $para;
          // We will fill $additional_tour_payload later from these paragraph entities.
        }
      }

      // --- Relationships: Barrett tours under Experience ASU (series references) ---

      $barrett_tours = [];
      if ($series->hasField('field_barrett_tours') && !$series->get('field_barrett_tours')->isEmpty()) {
        foreach ($series->get('field_barrett_tours')->referencedEntities() as $b_series) {
          $bid = $b_series->id();
          $barrett_tours[] = [
            'drupal_internal__id' => $bid,
            'uuid' => $b_series->uuid(),
            'title' => $b_series->label(),
          ];
          $all_barrett_series_ids[$bid] = $bid;
        }
      }

      $series_payload[$sid] = [
        'id' => $sid,
        'type' => $bundle,
        'attributes' => $attrs,
        'relationships' => [
          'field_additional_tours' => $additional_tours,
          'field_barrett_tours' => $barrett_tours,
        ],
      ];
    }

    // --- 4. Build Additional tour payload from paragraph entities ---
    // Extract values in the same structure/format as AdditionalTourController.
    if (!empty($all_additional_paragraph_ids)) {

      // Explicitly reload from Paragraph storage
      //   - The keys of $all_additional_paragraph_ids are paragraph IDs, so use them to load.
      $paragraph_storage = $etm->getStorage('paragraph');
      /** @var \Drupal\paragraphs\ParagraphInterface[] $additional_paras */
      $additional_paras = $paragraph_storage->loadMultiple(array_keys($all_additional_paragraph_ids));

      foreach ($additional_paras as $para) {
        if (!$para) {
          continue;
        }

        $uuid = $para->uuid();

        // time range: Build the array of { from, to } (seconds) in the format React expects.
        $time_range = [];
        if ($para->hasField('field_time_range') && !$para->get('field_time_range')->isEmpty()) {
          foreach ($para->get('field_time_range')->getValue() as $item) {
            $time_range[] = [
              'from' => (isset($item['from']) && $item['from'] !== '')
                ? (int) $item['from']
                : NULL,
              'to'   => (isset($item['to']) && $item['to'] !== '')
                ? (int) $item['to']
                : NULL,
            ];
          }
        }

        // Standardize the way field values are read using getString()/first().
        $addtour_name = NULL;
        if ($para->hasField('field_addtour_name') && !$para->get('field_addtour_name')->isEmpty()) {
          $addtour_name = $para->get('field_addtour_name')->first()->get('value')->getString();
        }

        $college_code = NULL;
        if ($para->hasField('field_college') && !$para->get('field_college')->isEmpty()) {
          $college_code = $para->get('field_college')->first()->get('value')->getString();
        }

        $need_radio = FALSE;
        if ($para->hasField('field_need_radio_button') && !$para->get('field_need_radio_button')->isEmpty()) {
          $need_radio = (bool) $para->get('field_need_radio_button')->first()->get('value')->getString();
        }

        $start_time_for_addtourid = NULL;
        if ($para->hasField('field_start_time_for_addtourid') && !$para->get('field_start_time_for_addtourid')->isEmpty()) {
          $start_time_for_addtourid = (int) $para->get('field_start_time_for_addtourid')->first()->get('value')->getString();
        }

        // Capture the actual data
        $additional_tour_payload[$uuid] = [
          'id' => $para->id(),
          'uuid' => $uuid,

          'field_addtour_name' => $addtour_name,
          'field_college' => $college_code,
          'field_need_radio_button' => $need_radio,
          'field_time_range' => $time_range,
          'field_start_time_for_addtourid' => $start_time_for_addtourid,
        ];
      }
    }    

    // --- 5. Build Barrett tour payload from referenced Barrett eventseries ---
    //
    // This now mirrors the important fields from BarrettTourController::byUuid(),
    // so CalendarPage.js can treat month-metadata Barrett tours exactly like
    // /visit-revamp-api/barrett-tour responses.

    if (!empty($all_barrett_series_ids)) {
      $barrett_series_storage = $etm->getStorage('eventseries');
      /** @var \Drupal\Core\Entity\EntityInterface[] $barrett_entities */
      $barrett_entities = $barrett_series_storage->loadMultiple($all_barrett_series_ids);

      foreach ($barrett_entities as $b_series) {
        $bid = $b_series->id();
        $uuid = $b_series->uuid();

        // Privacy label, same style as BarrettTourController.
        $privacy_label = NULL;
        if ($b_series->hasField('field_privacy') && !$b_series->get('field_privacy')->isEmpty()) {
          $privacy_entity = $b_series->get('field_privacy')->entity;
          if ($privacy_entity) {
            $privacy_label = $privacy_entity->label();
          }
        }

        $barrett_tour_payload[$uuid] = [
          'drupal_internal__id' => $bid,
          'uuid' => $uuid,
          'title' => $b_series->label(),
          'type' => $b_series->bundle(),

          // Recurring date information (used to derive time ranges in JS)
          'recur_type' => $b_series->hasField('recur_type') && !$b_series->get('recur_type')->isEmpty()
            ? (string) $b_series->get('recur_type')->value
            : NULL,

          'daily_recurring_date' => $this->buildRecurringComposite($b_series, 'daily_recurring_date', [
            'value' => NULL,
            'end_value' => NULL,
            'time' => '06:00 am',
            'duration' => 900,
            'end_time' => '06:00 am',
            'duration_or_end_time' => 'duration',
          ]),
          'weekly_recurring_date' => $this->buildRecurringComposite($b_series, 'weekly_recurring_date', [
            'value' => NULL,
            'end_value' => NULL,
            'time' => '06:00 am',
            'duration' => 900,
            'end_time' => '06:00 am',
            'duration_or_end_time' => 'duration',
            'days' => '',
          ]),
          'monthly_recurring_date' => $this->buildRecurringComposite($b_series, 'monthly_recurring_date', [
            'value' => NULL,
            'end_value' => NULL,
            'time' => '06:00 am',
            'duration' => 900,
            'end_time' => '06:00 am',
            'duration_or_end_time' => 'duration',
            'days' => '',
            'type' => '',
            'day_occurrence' => '',
            'day_of_month' => '',
          ]),

          // Start time used to compute eventid_timestamp on the JS side
          'field_start_time_for_eventid' => $b_series->hasField('field_start_time_for_eventid') && !$b_series->get('field_start_time_for_eventid')->isEmpty()
            ? (int) $b_series->get('field_start_time_for_eventid')->value
            : NULL,

          // Test mode flag (skip if true and user not privileged)
          'field_test_mode' => $b_series->hasField('field_test_mode') && !$b_series->get('field_test_mode')->isEmpty()
            ? (bool) $b_series->get('field_test_mode')->value
            : FALSE,

          'field_visitor_type' => $this->getVisitorTypesFromSeries($b_series),

          // Privacy label (e.g. "Public", "Private", "Registration closed")
          'field_privacy' => $privacy_label,

          'field_display_title' => $b_series->hasField('field_display_title') && !$b_series->get('field_display_title')->isEmpty()
            ? $b_series->get('field_display_title')->value
            : NULL,

          'field_evtype' => $b_series->hasField('field_evtype') && !$b_series->get('field_evtype')->isEmpty()
            ? $b_series->get('field_evtype')->value
            : NULL,
            
          'field_capacity' => $b_series->hasField('field_capacity') && !$b_series->get('field_capacity')->isEmpty()
            ? (int) $b_series->get('field_capacity')->value
            : NULL,
        ];
      }
    }

    // Final JSON response shape.
    $response = [
      'series' => $series_payload,
      'additional_tours' => $additional_tour_payload,
      'barrett_tours' => $barrett_tour_payload,
    ];

    return new JsonResponse($response);
  }

  /**
   * Safely extract visitor types from an eventseries entity.
   *
   * Handles both:
   *  - Entity reference fields (using target_id)
   *  - Text/list fields      (using value)
   */
  protected function getVisitorTypesFromSeries($series): array {
    if (
      !$series ||
      !$series->hasField('field_visitor_type') ||
      $series->get('field_visitor_type')->isEmpty()
    ) {
      return [];
    }

    $values = [];

    foreach ($series->get('field_visitor_type') as $item) {
      // Entity reference case (e.g. taxonomy term ref).
      if (isset($item->target_id) && $item->target_id) {
        $values[] = (string) $item->target_id;
        continue;
      }

      // List/text case.
      if (isset($item->value) && $item->value !== '') {
        $values[] = (string) $item->value;
      }
    }

    // Remove nulls/empties and duplicates.
    $values = array_filter($values, static function ($v) {
      return $v !== NULL && $v !== '';
    });

    return array_values(array_unique($values));
  }

  /**
   * Build a recurring-date composite similar to BarrettTourController.
   *
   * Returns an array like:
   *   [
   *     'value' => '2025-05-10T11:50:53-07:00',
   *     'end_value' => '2025-08-14T11:50:53-07:00',
   *     'time' => '11:15 am',
   *     'duration' => 900,
   *     'end_time' => '11:45 am',
   *     'duration_or_end_time' => 'end_time',
   *     'days' => 'monday,friday',
   *     ...
   *   ]
   */
  protected function buildRecurringComposite($entity, string $field, array $defaults = []): array {
    $d = $defaults + [
      'value' => NULL,
      'end_value' => NULL,
      'time' => '06:00 am',
      'duration' => 900,
      'end_time' => '06:00 am',
      'duration_or_end_time' => 'duration',
    ];

    if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
      $raw = $entity->get($field)->first()->toArray();

      // Normalize date/times with timezone offset (only value/end_value).
      if (array_key_exists('value', $raw)) {
        $d['value'] = self::isoWithOffset($raw['value']);
      }
      if (array_key_exists('end_value', $raw)) {
        $d['end_value'] = self::isoWithOffset($raw['end_value']);
      }

      foreach ([
        'time',
        'duration',
        'end_time',
        'duration_or_end_time',
        'buffer',
        'buffer_units',
        'days',
        'type',
        'day_occurrence',
        'day_of_month',
        'year_interval',
        'months',
      ] as $k) {
        if (array_key_exists($k, $raw)) {
          $d[$k] = $raw[$k];
          if (in_array($k, ['duration', 'buffer', 'year_interval'], TRUE)) {
            $d[$k] = ($d[$k] === '' || $d[$k] === NULL)
              ? ($k === 'year_interval' ? 1 : 0)
              : (int) $d[$k];
          }
        }
      }
    }

    return $d;
  }

  /**
   * Convert a stored date/time string into ISO 8601 with timezone offset,
   * similar to BarrettTourController::isoWithOffset().
   */
  private static function isoWithOffset(?string $value): ?string {
    if (!$value) {
      return NULL;
    }

    // Site default timezone (falls back to UTC).
    $tz = \Drupal::config('system.date')->get('timezone.default') ?: 'UTC';

    // If the string already has a timezone offset, keep it as-is.
    if (preg_match('/[Zz]|[+\-]\d{2}:\d{2}$/', $value)) {
      try {
        $dt = new DrupalDateTime($value);
        // Re-format to standard ISO with the same zone (preserves offset).
        return $dt->format(DATE_ATOM);
      }
      catch (\Exception $e) {
        return $value; // fail-safe
      }
    }

    // Assume storage is UTC when no offset is present, convert to site TZ.
    try {
      $dt = new DrupalDateTime($value, new \DateTimeZone('UTC'));
      $dt->setTimezone(new \DateTimeZone($tz));
      return $dt->format(DATE_ATOM); // e.g., 2025-05-10T11:50:53-07:00
    }
    catch (\Exception $e) {
      return $value; // fail-safe
    }
  }

}