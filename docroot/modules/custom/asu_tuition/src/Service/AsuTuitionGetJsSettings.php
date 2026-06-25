<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionGetJsSettings {

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
  public function getJsSettings($corporate_partnership = FALSE, $reset = FALSE) {

    $database = \Drupal::database();
    $config_data = \Drupal::config('asu_tuition.admin_settings');
    /*$settings = array(
    'api_version' => ASU_TUITION_API_VERSION,
    );*/

    // Get settings from the cache if there is one.
    $cid = 'js_settings' . ($corporate_partnership ? 'corporate_partnership' : '');
    // $cache = asu_tuition_get_cache_by_id($cid);
    /*if ($cache) {
    $settings = $cache;
    }
    else {*/
    // Get form defaults.
    $settings['form_defaults'] = $config_data->get('asu_tuition_search_page_form_defaults', []);

    // Get corporate partner info.
    /* if ($corporate_partnership) {
    $result = $database->query("SELECT DISTINCT cp.corporate_partner, cp.descr, cp.weight
    FROM {asu_tuition_corporate_partner} AS cp
    ORDER BY cp.weight, cp.descr");

    $settings['corporate_partner'] = array('' => t('Select One'));
    foreach ($result as $row) {
    $settings['corporate_partner'][$row->corporate_partner] = $row->descr;
    }

    $result = $database->query("SELECT DISTINCT cp.corporate_partner, ay.acad_year, r.residency, ac.acad_career, c.campus, cpa.award_code
    FROM asu_tuition_acad_year AS ay
    JOIN asu_tuition_residency AS r ON (r.display = '1')
    JOIN asu_tuition_acad_career AS ac ON (ac.display = '1')
    JOIN asu_tuition_campus AS c ON (c.display = '1')
    JOIN asu_tuition_corporate_partner_award cpa ON (
    cpa.acad_year = ay.acad_year
    AND cpa.acad_career = ac.acad_career
    AND cpa.campus = c.campus
    AND cpa.residency = r.residency
    )
    JOIN asu_tuition_corporate_partner cp ON (
    cp.corporate_partner = cpa.corporate_partner
    AND cp.display = 1
    )
    WHERE ay.display = '1'
    ORDER BY cp.corporate_partner, ay.acad_year, r.residency, ac.acad_career, c.campus, cpa.award_code");

    $settings['corporate_partner_fields'] = array();
    foreach ($result->fetchAll() as $row) {
    $settings['corporate_partner_fields'][$row->corporate_partner][$row->acad_year][$row->residency][$row->acad_career][$row->campus] = $row->award_code;
    }

    }*/

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
   
    // Get wue info.
    $result_wue = $database->query("SELECT ac.acad_career, apc.campus, ap.acad_prog
      FROM {asu_tuition_acad_career} AS ac
      JOIN {asu_tuition_acad_prog} AS ap ON (ap.acad_career = ac.acad_career)
      JOIN {asu_tuition_acad_prog_campus} AS apc ON (apc.acad_prog = ap.acad_prog)
      WHERE apc.wue = 1
      GROUP BY acad_career, campus, acad_prog
      ORDER BY acad_career, campus, acad_prog");

    $settings['wue'] = [];
    foreach ($result_wue->fetchAll() as $row_wue) {
      $settings['wue'][$row_wue->acad_career][$row_wue->campus][$row_wue->acad_prog] = $row_wue->acad_prog;
    }

    // Get base_term_fee_code info.
    $result_fc = $database->query("SELECT DISTINCT ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.fee_code
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

    $settings['base_term_fee_code'] = [];
    foreach ($result_fc->fetchAll() as $row_fc) {
      $settings['base_term_fee_code'][$row_fc->acad_year][$row_fc->residency][$row_fc->acad_career][$row_fc->campus][$row_fc->acad_prog] = $row_fc->fee_code;
    }

    // Get summer_checkbox info.
    $result_sc = $database->query("SELECT ay.acad_year,
      CASE
        WHEN LENGTH(TRIM(ay.summer_term)) > 0 THEN  1
        ELSE 0
      END AS display_summer
      FROM {asu_tuition_acad_year} AS ay
      WHERE ay.display = 1
      ORDER BY ay.acad_year");

    $settings['include_summer'] = [];
    foreach ($result_sc->fetchAll() as $row_sc) {
      $settings['include_summer'][$row_sc->acad_year] = $row_sc->display_summer;
    }

    // asu_tuition_set_cache_by_id($cid, $settings);
    // }.
    return $settings;
  }

}
