<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Core\Render\Markup;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionResultsLoad {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */

  /**
   * Public function __construct(RequestStack $requestStack) {
   * $this->requestStack = $requestStack;
   * }
   */
  public function resultsLoad($values) {
    // Build the data arrays.
    $results = new \stdClass();

    $results->values = $values;
    // $results = (object) ['key'=> 'value', 'type' => 'type'];
    $results->notes = [];
    // ksm($values);
    $config = \Drupal::config('asu_tuition.admin_settings');
    $excess_hours_notes = $config->get('asu_tuition_results_page_excess_hours_tuition_note');
    // ksm($excess_hours_notes);
    if (($values->acad_career == "UGRDN") || ($values->acad_career == "UGRD")) {
      $show_excess_note = "<p>" . $excess_hours_notes . "</p>";
    }
    else {
      $show_excess_note = '';
    }
    $results->new_notes = $show_excess_note;
    $results->selected_options = \Drupal::service('selectedOptionsItemsLoad')->selectedOptionsItemsLoad($results);
    // $results->acad_years = \Drupal::service('readRecords')->readRecords('asu_tuition_acad_year', array(),array('key_by' => 'acad_year');
    $results->acad_years = \Drupal::service('readRecords')->readRecords('asu_tuition_acad_year', ['acad_year' => $values->acad_year]);

    // $results->acad_year = $results->values->acad_year;
    // $results->acad_year = $results->acad_years[$results->values->acad_year];
    $results->acad_year = $results->acad_years['acad_year'];
    $results->full_time_credits = \Drupal::service('getFullTimeCreditLoad')->getFullTimeCreditLoad($results);
    $results->base_term = \Drupal::service('getBaseTermLoad')->getBaseTermLoad($results);
    $results->tuition_label = \Drupal::service('getTuitionLabelLoad')->getTuitionLabelLoad($results);
    $results->full_time_message = \Drupal::service('getTuitionFullTimeMessageLoad')->getTuitionFullTimeMessageLoad($results);
    // Load the breakdown first and build the tuition_fees array from it.
    // ksm($results->notes);.
    $results->breakdown = [
      'fall'   => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_years['fall_term']),
      'spring' => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_years['spring_term']),
      'summer' => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_years['summer_term']),
    ];
    // ksm($results->notes);.
    if (count($results->breakdown['fall']) < 2 || count($results->breakdown['spring']) < 2) {
      $results->breakdown = FALSE;
    }

    $results->tuition_fees = \Drupal::service('getFullTimeTuitionLoad')->getFullTimeTuitionLoad($results);

    if (count($results->tuition_fees) == 1) {
      $results->tuition_fees = FALSE;
    }
    // ksm($results->notes);
    // ksm($results,'res');
    // Build the html parts of the page. Load the max credits after all breakdown
    // and full-time tuition arrays have been built.
    $results->html = new \stdClass();
    $results->max_credits = \Drupal::service('getMaxCreditsLoad')->getMaxCreditsLoad($results);

    $results->html->acad_year_table = ($results->tuition_fees ? \Drupal::service('getBreakdownTable')->getBreakdownTable($results) : t('<p class="messages error alert alert-danger">There is no data for the options you selected because no tuition exists. Please try the calculator again.</p>'));
    $results->html->breakdown_tables = ($results->breakdown ? \Drupal::service('getFullTimeTuitionTable')->getFullTimeTuitionTable($results) : t('<p class="messages error alert alert-danger">There is no data for the options you selected. Please try the calculator again.</p>'));
    $results->html->notes = '';

    foreach ($results->notes as $note) {
      $note_data = $note;
    }
    // ksm($note_data);
    // ksm($results->notes);
    if ($results->notes) {
      $notes = [
        '#type' => 'item_list',
        '#items' => $results->notes,
      ];
      $results->html->notes = Markup::create('<h4 class="notes">Notes</h4>' . $note_data);
    }
    $results->html->selected_options = '';
    $selected_options = [
      'title' => t('About me'),
      'items' => [],
    ];
    foreach ($results->selected_options as $item) {
      // $selected_options['items'][] = t('<strong>@title:</strong><br /> !value', array('@title' => $item['#title'], '!value' => $item['#value'])); // Added CSS class. (Chizuko on 4/9/2019)
      $css_class = $item['#attributes']['class'][0] ?? '';
      $selected_options['items'][] = t('<span class=' . $css_class . '><strong>' . $item['#title'] . '</strong><br />' . $item['#value'] . '</span>');
    }

    if (!empty($selected_options['items'])) {

      // $results->html->selected_options .= theme('item_list', $selected_options);
      $html_list = [
        '#type' => 'item_list',
        '#items' => $selected_options['items'],
      ];
      $results->html->selected_options = $html_list;
    }

    return $results;
  }

}
