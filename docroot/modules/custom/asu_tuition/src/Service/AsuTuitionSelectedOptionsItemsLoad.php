<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionSelectedOptionsItemsLoad {

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
  public function selectedOptionsItemsLoad($result) {
    $options = [];

    $config = \Drupal::config('asu_tuition.admin_settings');
    $titles_config = $config->get('asu_tuition_search_page_form_defaults', []);
    $titles = \Drupal::service('listValues')->listValues($titles_config);

    // ksm(\Drupal::service('listValues')->listValues($titles),'titls');
    // ksm(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_year', array('acad_year' => $result->values->acad_year), 'descr'));
    // ksm($titles);
    $options['acad_year'] = [
      '#title' => ($titles['acad_year'] ?? ''),
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_year', ['acad_year' => $result->values->acad_year], 'descr')),
    ];

    if (!empty($result->values->include_summer)) {
      $options['include_summer'] = [
        '#title' => ($titles['include_summer'] ?? ''),
        '#value' => t('True'),
      ];
    }
    $options['residency'] = [
      '#title' => ($titles['residency'] ?? ''),
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_residency', ['residency' => $result->values->residency], 'descr')),
    ];
    $options['campus'] = [
      '#title' => ($titles['campus'] ?? ''),
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_campus', ['campus' => $result->values->campus], 'descr')),
    ];
    $options['acad_career'] = [
      '#title' => ($titles['acad_career'] ?? ''),
      '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_career', ['acad_career' => $result->values->acad_career], 'descr')),
    ];
    if (!empty($result->values->admit_term)) {
      $options['admit_term'] = [
        '#title' => ($titles['admit_term'] ?? ''),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_admit_term', ['admit_term' => $result->values->admit_term], 'descr')),
      ];
    }
    if (!empty($result->values->admit_level)) {
      $options['admit_level'] = [
        '#title' => ($titles['admit_level'] ?? ''),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_level', ['acad_level' => $result->values->admit_level], 'descr')),
      ];
    }
    if (!empty($result->values->acad_level)) {
      $options['acad_level'] = [
        '#title' => ($titles['acad_level'] ?? ''),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_level', ['acad_level' => $result->values->acad_level], 'descr')),
      ];
    }
    // 'College' in 'About me' area
    if (!empty($result->values->acad_prog)) {
      // $descr = asu_tuition_get_selected_option_text('asu_tuition_acad_prog', array('acad_prog' => $result->values->acad_prog), 'descr');
      // $url = asu_tuition_get_selected_option_text('asu_tuition_acad_prog', array('acad_prog' => $result->values->acad_prog), 'url');
      $options['acad_prog'] = [
        '#title' => ($titles['acad_prog'] ?? ''),
      // '#value' => (empty($url)) ? t($descr) : l($descr, $url, array('attributes' => array('target' => '_blank'))),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_prog', ['acad_prog' => $result->values->acad_prog], 'descr')),
      ];
    }
    if (!empty($result->values->program_fee)) {
      $url = \Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_acad_prog', ['acad_prog' => $result->values->acad_prog], 'url');

      $descr = \Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->program_fee], 'descr');
      $options['program_fee'] = [
        '#title' => ($titles['program_fee'] ?? ''),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->program_fee], 'descr')),
      // '#value' => (empty($url)) ? t($descr) :  \Drupal\Core\Link::fromTextAndUrl($descr, $url, array('attributes' => array('target' => '_blank'))),
      ];
    }
    if (!empty($result->values->online_prog)) {
      $options['online_prog'] = [
        '#title' => ($titles['online_prog'] ?? ''),
        '#value' => t(\Drupal::service('getSelectedOptionsText')->getSelectedOptionsText('asu_tuition_fee_code', ['fee_code' => $result->values->online_prog], 'descr')),
      ];
    }
    // Only when Honors College is selected, it appears in 'About me' area.
    if (!empty($result->values->honors)) {
      $options['honors'] = [
        '#title' => ($titles['honors'] ?? ''),
        '#value' => t('True'),
        '#attributes' => ['class' => ['honor-selected']],
      ];
    }

    return $options;
  }

}
