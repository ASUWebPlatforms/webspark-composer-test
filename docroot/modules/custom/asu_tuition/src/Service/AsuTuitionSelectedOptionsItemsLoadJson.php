<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionSelectedOptionsItemsLoadJson {

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
  public function selectedOptionsItemsLoadJson($result) {
    $options = [];
    // ksm($result);
    $config = \Drupal::config('asu_tuition.admin_settings');
    $titles_config = $config->get('asu_tuition_search_page_form_defaults', []);
    $titles = \Drupal::service('listValues')->listValues($titles_config);
    // ksm($titles);
    // ksm(\Drupal::service('listValues')->listValues($titles),'titls');
    // ksm(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_year', array('acad_year' => $result->values->acad_year), 'descr'));
    // ksm($titles);
    // ksm($result->values->acad_year);
    $options['acad_year'] = [
      '#title' => 'Academic year',
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_year', ['acad_year' => $result->values->acad_year], 'descr')),
    ];
    // ksm($options);
    if (!empty($result->values->include_summer)) {
      $options['include_summer'] = [
        '#title' => 'Include Summer Tuition',
        '#value' => t('True'),
      ];
    }
    $options['residency'] = [
      '#title' => 'Residency',
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_residency', ['residency' => $result->values->residency], 'descr')),
    ];
    $options['campus'] = [
      '#title' => 'Location',
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_campus', ['campus' => $result->values->campus], 'descr')),
    ];
    $options['acad_career'] = [
      '#title' => 'Academic career',
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_career', ['acad_career' => $result->values->acad_career], 'descr')),
    ];
    if (!empty($result->values->admit_term)) {
      $options['admit_term'] = [
        '#title' => 'admit_term',
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_admit_term', ['admit_term' => $result->values->admit_term], 'descr')),
      ];
    }
    if (!empty($result->values->admit_level)) {
      $options['admit_level'] = [
        '#title' => 'admit_level',
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_level', ['acad_level' => $result->values->admit_level], 'descr')),
      ];
    }
    if (!empty($result->values->acad_level)) {
      $options['acad_level'] = [
        '#title' => 'acad_level',
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_level', ['acad_level' => $result->values->acad_level], 'descr')),
      ];
    }
    // 'College' in 'About me' area
    if (!empty($result->values->acad_prog)) {
      // $descr = asu_tuition_get_selected_option_text('asu_tuition_acad_prog', array('acad_prog' => $result->values->acad_prog), 'descr');
      // $url = asu_tuition_get_selected_option_text('asu_tuition_acad_prog', array('acad_prog' => $result->values->acad_prog), 'url');
      $options['acad_prog'] = [
        '#title' => 'College',
      // '#value' => (empty($url)) ? t($descr) : l($descr, $url, array('attributes' => array('target' => '_blank'))),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_prog', ['acad_prog' => $result->values->acad_prog], 'descr')),
      ];
    }
    if (!empty($result->values->program_fee)) {
      $url = \Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_prog', ['acad_prog' => $result->values->acad_prog], 'url');

      $descr = \Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->program_fee], 'descr');
      $options['program_fee'] = [
        '#title' => 'program_fee',
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->program_fee], 'descr')),
      // '#value' => (empty($url)) ? t($descr) :  \Drupal\Core\Link::fromTextAndUrl($descr, $url, array('attributes' => array('target' => '_blank'))),
      ];
    }
    if (!empty($result->values->online_prog)) {
      $options['online_prog'] = [
        '#title' => 'online_prog',
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->online_prog], 'descr')),
      ];
    }
    // Only when Honors College is selected, it appears in 'About me' area.
    if (!empty($result->values->honors)) {
      $options['honors'] = [
        '#title' => 'honors',
        '#value' => t('True'),
        '#attributes' => ['class' => ['honor-selected']],
      ];
    }

    // ksm($options);
    return $options;
  }

}
