<?php

namespace Drupal\asu_tuition\Service;

use Drupal\Core\Render\Markup;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionResultsLoadJson {

  /**
   *
   */
  public function resultsLoadJson($values) {
    // Build the data arrays.
    $results = new \stdClass();
    unset($values->credit_hr);
    $results->values = $values;
    // $results = (object) ['key'=> 'value', 'type' => 'type'];
    $results->notes = [];

    $results->selected_options = \Drupal::service('selectedOptionsItemsLoadJson')->selectedOptionsItemsLoadJson($results);
    // ksm($results->selected_options);.
    $results->acad_years = \Drupal::service('readRecordsJson')->readRecordsJson('asu_tuition_acad_year', [], ['key_by' => 'acad_year']);
    // $results->acad_years = \Drupal::service('readRecordsJson')->readRecordsJson('asu_tuition_acad_year', array('acad_year' => $values->acad_year));
    // $results->acad_years = \Drupal::service('readRecords')->readRecords('asu_tuition_acad_year', array('acad_year' => $values->acad_year));
    // ksm($results->acad_years);
    // $results->acad_year = $results->values->acad_year;
    if (!(strlen($results->values->acad_year) === 4)) {
      $config = \Drupal::config('asu_tuition.admin_settings');
      $form_values = $config->get('asu_tuition_search_page_form_defaults', []);
      $form_set = \Drupal::service('listValues')->listValues($form_values);
      $results->values->acad_year = $form_set['acad_year'];
    }
    $results->acad_year = $results->acad_years[$results->values->acad_year];

    $results->full_time_credits = \Drupal::service('getFullTimeCreditLoad')->getFullTimeCreditLoad($results);
    $results->base_term = \Drupal::service('getBaseTermLoadJson')->getBaseTermLoadJson($results);
    $results->tuition_label = \Drupal::service('getTuitionLabelLoad')->getTuitionLabelLoad($results);
    $results->full_time_message = \Drupal::service('getTuitionFullTimeMessageLoad')->getTuitionFullTimeMessageLoad($results);
    // Load the breakdown first and build the tuition_fees array from it.
    // ksm($results);
    /* ksm($results->acad_years['fall_term']);
    ksm($results->acad_years['spring_term']);
    ksm($results->acad_years['summer_term']);*/
    $results->breakdown = [
      'fall'   => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_year->fall_term),
      'spring' => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_year->spring_term),
      'summer' => \Drupal::service('getTuitionBreakdownLoad')->getTuitionBreakdownLoad($results, $results->acad_year->summer_term),
    ];

    if (count($results->breakdown['fall']) < 2 || count($results->breakdown['spring']) < 2) {
      $results->breakdown = FALSE;
    }

    $results->tuition_fees = \Drupal::service('getFullTimeTuitionLoad')->getFullTimeTuitionLoad($results);
    // ksm($results);
    if (count($results->tuition_fees) == 1) {
      $results->tuition_fees = FALSE;
    }
    // ksm($results,'res');
    // Build the html parts of the page. Load the max credits after all breakdown
    // and full-time tuition arrays have been built.
    $results->html = new \stdClass();
    $results->max_credits = \Drupal::service('getMaxCreditsLoad')->getMaxCreditsLoad($results);

    $results->html->acad_year_table = ($results->tuition_fees ? \Drupal::service('getBreakdownTableJson')->getBreakdownTableJson($results) : t('<p class="messages error alert alert-danger">There is no data for the options you selected. Please try the calculator again.</p>'));
    $results->html->breakdown_tables = ($results->breakdown ? \Drupal::service('getFullTimeTuitionTableJson')->getFullTimeTuitionTableJson($results) : t('<p class="messages error alert alert-danger">There is no data for the options you selected. Please try the calculator again.</p>'));
    $results->html->notes = '';
    foreach ($results->notes as $note) {
      $note_data = $note;
    }
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

    $selected_options['items'] = '<div class="item-list"><h3>About me</h3><ul>';
    foreach ($results->selected_options as $item) {
      // $selected_options['items'][] = t('<strong>@title:</strong><br /> !value', array('@title' => $item['#title'], '!value' => $item['#value'])); // Added CSS class. (Chizuko on 4/9/2019)
      $css_class = $item['#attributes']['class'][0] ?? '';
      // $selected_options['items'][] = t('<span class='.$css_class.'><strong>'.$item['#title'].'</strong><br />'.$item['#value'].'</span>');
      $selected_options['items'] .= '<li><span class=' . $css_class . '><strong>' . $item['#title'] . '</strong><br />' . $item['#value']->__toString() . '</span></li>';
    }
    $selected_options['items'] .= '</ul></div>';
    if (!empty($selected_options['items'])) {

      // $results->html->selected_options .= theme('item_list', $selected_options);
      $html_list = [
        '#type' => '#markup',
        '#items' => $selected_options['items'],
      ];

      $results->html->selected_options = $html_list['#items'];

    }
    // ksm($results);
    return $results;
    /* }
    else{
    \Drupal::logger('results info')->info("Year missing in the url");
    return '<p>Year value sent is wrong</p>';
    }*/
  }

}
