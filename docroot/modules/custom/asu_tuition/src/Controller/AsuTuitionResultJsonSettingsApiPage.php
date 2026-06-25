<?php

namespace Drupal\asu_tuition\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides route responses for the Example module.
 */
class AsuTuitionResultJsonSettingsApiPage extends ControllerBase {

  /**
   * Returns a JSON data.
   *
   * @return array
   *   A simple renderable array.
   */
  public function result_api_results_page($reset = FALSE) {
    $settings = [
      'api_version' => '1.20',
    ];
    $database = \Drupal::database();
    $config = \Drupal::config('asu_tuition.admin_settings');
    $form_values = $config->get('asu_tuition_search_page_form_defaults', []);
    $form_set = \Drupal::service('listValues')->listValues($form_values);
    // $settings['form_defaults'] = $form_set;
    $form_values_new = \Drupal::service('getUrlParameters')->getUrlParameters();
    $url_form_values['acad_year'] = $form_values_new['acad_year'];
    $url_form_values['residency'] = $form_values_new['residency'];
    $url_form_values['acad_career'] = $form_values_new['acad_career'];

    $settings['form_defaults'] = !empty($form_values_new['acad_year']) ? $url_form_values : $form_set;

    // ksm($settings['form_defaults']);
    // Get campus options.
    $result_campus = $database->query("SELECT DISTINCT ac.acad_career, c.campus, c.descr, c.weight
		  FROM {asu_tuition_campus} c
		  JOIN {asu_tuition_acad_prog_campus} apc ON apc.campus = c.campus
		  JOIN {asu_tuition_acad_prog} ap ON ap.acad_prog = apc.acad_prog
		  JOIN {asu_tuition_acad_career} ac ON ac.acad_career_group = ap.acad_career
		  ORDER BY ac.acad_career, c.weight, c.descr");
    $campus_data = $result_campus->fetchAll();
    $settings['campus'] = ['' => t('Select One')];
    foreach ($campus_data as $campus_row) {
      $settings['campus'][$campus_row->acad_career][$campus_row->campus] = $campus_row->descr;
    }

    // Get acad_prog options.
    $result_prog = $database->query("SELECT DISTINCT ac.acad_career, c.campus, ap.acad_prog, ap.descr
		  FROM {asu_tuition_acad_career} AS ac
		  JOIN {asu_tuition_acad_prog} AS ap ON (ap.display = '1' AND ap.acad_career = ac.acad_career_group)
		  JOIN {asu_tuition_acad_prog_campus} AS apc ON (ac.display = '1' AND apc.acad_prog = ap.acad_prog)
		  JOIN {asu_tuition_campus} AS c ON (c.display = '1')
		  WHERE (apc.campus = c.campus
			OR apc.campus = ''
		  )
		  ORDER BY ac.acad_career, c.campus, ap.descr");

    $prog_data = $result_prog->fetchAll();
    $settings['acad_prog'] = ['' => t('Select One')];
    foreach ($prog_data as $prow) {
      $settings['acad_prog'][$prow->acad_career][$prow->campus][$prow->acad_prog] = $prow->descr;
    }

    // Get program_fee options.
    $result_fee = $database->query("SELECT DISTINCT ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.fee_code, fc.descr
		  FROM asu_tuition_acad_year AS ay
		  JOIN asu_tuition_residency AS r ON (ay.display = '1' AND r.display = '1')
		  JOIN asu_tuition_acad_career AS ac ON (ac.display = '1')
		  JOIN asu_tuition_campus AS c ON (c.display = '1')
		  JOIN asu_tuition_acad_prog_campus AS apc ON (apc.campus = c.campus)
		  JOIN asu_tuition_acad_prog AS ap ON (ap.acad_prog = apc.acad_prog AND ap.acad_career = ac.acad_career)
		  JOIN asu_tuition_fee_code AS fc ON (fc.acad_career = ac.acad_career
			AND fc.campus = c.campus
			AND fc.acad_prog = apc.acad_prog
			AND (fc.residency = r.residency OR fc.residency = ''))
		  JOIN asu_tuition_rate_type AS rt ON (rt.rate_type = fc.fee_type
			AND rt.program_fee_dropdown = 1)
		  WHERE EXISTS (SELECT 'X' FROM asu_tuition_fee_rate AS fr WHERE fr.fee_code = fc.fee_code AND fr.acad_year = ay.acad_year)
		  ORDER BY ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.descr");
    $fee_data = $result_fee->fetchAll();
    $settings['program_fee'] = ['' => t('None/Not Listed')];
    foreach ($fee_data as $frow) {
      $settings['program_fee'][$frow->acad_year][$frow->residency][$frow->acad_career][$frow->campus][$frow->acad_prog][$frow->fee_code] = $frow->descr;
    }
    // ksm($settings['program_fee']);
    // Get wue info.
    $result_wue = $database->query("SELECT ac.acad_career, apc.campus, ap.acad_prog
		  FROM {asu_tuition_acad_career} AS ac
		  JOIN {asu_tuition_acad_prog} AS ap ON (ap.acad_career = ac.acad_career)
		  JOIN {asu_tuition_acad_prog_campus} AS apc ON (apc.acad_prog = ap.acad_prog)
		  WHERE apc.wue = 1
		  GROUP BY acad_career, campus, acad_prog
		  ORDER BY acad_career, campus, acad_prog");

    $wue_data = $result_wue->fetchAll();
    $settings['wue'] = [];
    foreach ($wue_data as $wrow) {
      $settings['wue'][$wrow->acad_career][$wrow->campus][$wrow->acad_prog] = $wrow->acad_prog;
    }

    // Get base_term_fee_code info.
    $result_fee_code = $database->query("SELECT DISTINCT ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.fee_code
		  FROM {asu_tuition_acad_year} AS ay
		  JOIN {asu_tuition_residency} AS r ON (r.display = '1')
		  JOIN {asu_tuition_acad_career} AS ac ON (ac.display = '1')
		  JOIN {asu_tuition_campus} AS c ON (c.display = '1')
		  JOIN {asu_tuition_acad_prog_campus} AS apc ON (apc.campus = c.campus)
		  JOIN {asu_tuition_acad_prog} AS ap ON (ap.acad_prog = apc.acad_prog AND ap.acad_career = ac.acad_career)
		  JOIN {asu_tuition_fee_code} AS fc ON (fc.fee_type IN ('T1', 'T3')
			AND fc.acad_career = ac.acad_career
			AND fc.campus = c.campus
			AND fc.acad_prog = apc.acad_prog
			AND fc.residency = r.residency
			AND fc.base_term NOT IN ('', '9999')
		  )
		  JOIN {asu_tuition_fee_rate} AS fr ON (fr.fee_code = fc.fee_code AND fr.fee_type = fc.fee_type AND fr.acad_year = ay.acad_year)
		  WHERE ay.display = '1'
		  ORDER BY ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog");

    $fee_code_data = $result_fee_code->fetchAll();

    $settings['base_term_fee_code1'] = [];
    foreach ($fee_code_data as $fcrow) {
      $settings['base_term_fee_code1'][$fcrow->acad_year][$fcrow->residency][$fcrow->acad_career][$fcrow->campus][$fcrow->acad_prog] = $fcrow->fee_code;
    }
    // ksm($settings['base_term_fee_code1']);.
    // Get summer_checkbox info.
    $summer_result = $database->query("SELECT ay.acad_year, CASE WHEN LENGTH(TRIM(ay.summer_term)) > 0 THEN  1 ELSE 0 END AS display_summer FROM {asu_tuition_acad_year} AS ay WHERE ay.display = 1 ORDER BY ay.acad_year");
    $summer_data = $summer_result->fetchAll();
    $settings['include_summer'] = [];
    foreach ($summer_data as $summer_row) {
      $settings['include_summer'][$summer_row->acad_year] = $summer_row->display_summer;
    }

    $test_callback = \Drupal::request()->request->get('callback');
    $callback = (isset($test_callback)) ? check_plain($test_callback) : '';

    // Wrap json with a function if this is a jsonp callback request.
    if (isset($callback) && $callback != '') {
      header('Content-type: text/javascript');
      echo $callback . '(' . JsonResponse($settings) . ');';
    }
    else {

      return new JsonResponse($settings);
    }
    exit();

  }

}
