<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class LoanFilerOptionsList {

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
  public function getFormCampusList() {

    $database = \Drupal::database();
    $config_data = \Drupal::config('asu_tuition.admin_settings');
    /*$settings = array(
    'api_version' => ASU_TUITION_API_VERSION,
    );*/

    
    // Get campus options.
    $result_c = $database->query("SELECT DISTINCT ac.acad_career, c.campus, c.descr, c.weight
      FROM {asu_tuition_campus} c
      JOIN {asu_tuition_acad_prog_campus} apc ON apc.campus = c.campus
      JOIN {asu_tuition_acad_prog} ap ON ap.acad_prog = apc.acad_prog
      JOIN {asu_tuition_acad_career} ac ON ac.acad_career_group = ap.acad_career
      ORDER BY ac.acad_career, c.weight, c.descr");

    $settings['campus'] = ['' => t('Select One')];
    foreach ($result_c->fetchAll() as $row_c) {
      $settings['campus'][$row_c->acad_career][$row_c->campus] = $row_c->descr;
    }
  return $settings;
  }

  public function getCollegeList() {

    // Get acad_prog options.
    $result_ap = $database->query("SELECT DISTINCT ac.acad_career, c.campus, ap.acad_prog, ap.descr
      FROM {asu_tuition_acad_career} AS ac
      JOIN {asu_tuition_acad_prog} AS ap ON (ap.display = '1' AND ap.acad_career = ac.acad_career_group)
      JOIN {asu_tuition_acad_prog_campus} AS apc ON (ac.display = '1' AND apc.acad_prog = ap.acad_prog)
      JOIN {asu_tuition_campus} AS c ON (c.display = '1')
      WHERE (apc.campus = c.campus
        OR apc.campus = ''
      )
      ORDER BY ac.acad_career, c.campus, ap.descr");

    $settings['acad_prog'] = ['' => t('Select One')];
    foreach ($result_ap->fetchAll() as $row_ap) {
      $settings['acad_prog'][$row_ap->acad_career][$row_ap->campus][$row_ap->acad_prog] = $row_ap->descr;
    }

  }

  public function getFormProgramFeeList() {

    // Get program_fee options.
    $result_pf = $database->query("SELECT DISTINCT ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.fee_code, fc.descr
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

    $settings['program_fee'] = ['' => t('None/Not Listed')];
    foreach ($result_pf->fetchAll() as $row_pf) {
      $settings['program_fee'][$row_pf->acad_year][$row_pf->residency][$row_pf->acad_career][$row_pf->campus][$row_pf->acad_prog][$row_pf->fee_code] = $row_pf->descr;
    }

}

}