<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure contact forms node ids as node ids change for each environment.
 */
class AsuTuitionSearchPage extends FormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $corporate_partnership = FALSE) {

    // Add javascript settings for use in asu_tuition.js.
    $form['#attached']['library'][] = 'asu_tuition/tuitionSearchPage';
    $form['#attached']['drupalSettings']['asu_tuition'] = \Drupal::service('getJsSettings')->getJsSettings($corporate_partnership);
    $default_settings_vlaue = \Drupal::service('getJsSettings')->getJsSettings($corporate_partnership);
    $single_values = explode('|', $default_settings_vlaue['form_defaults']);
    $single_year = preg_split('/\s+/', $single_values[1]);
    $form['#attached']['drupalSettings']['asu_tuition']['acad_yar_default'] = $single_year[0];
    $form['#prefix'] = '<div class="container"><div class="row"><div class="col-12 col-lg-4">';
    $form['#attributes'] = [
      'class' => ['uds-form'],
    ];
    // ksm($single_year);
    /*$form['year'] = array(
    '#type' => 'fieldset',
    '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('year'),
    '#tree' => FALSE,
    '#attributes' => array('id' => 'options-year-fieldset', 'class' => array('option-fieldset')),
    '#description' => \Drupal::service('searchPageDescription')->searchPageDescription('year'),

    );*/
    // $form['year']['acad_year'] = array(
    $form['acad_year'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('acad_year'),

      '#options' => \Drupal::service('getOptions')->getOptions(NULL, "SELECT acad_year, descr FROM {asu_tuition_acad_year} WHERE display = 1 ORDER BY weight, acad_year DESC", 'acad_year', 'descr', NULL),
    // '#options' => array('1' => '1'),
      '#required' => TRUE,
    // '#default_value' => \Drupal::Service('defaultValues')->defaultValues('acad_year', $corporate_partnership),
      '#default_value' => $single_year[0],
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('acad_year')),
      '#description_display' => 'before',

    ];
    // $form['year']['include_summer'] = array(
    $form['include_summer'] = [
      '#type' => 'checkbox',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('include_summer'),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('include_summer', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('include_summer')),

    ];

    // $form['student']['residency'] = array(
    $form['residency'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('residency'),
      '#options' => \Drupal::service('getOptions')->getOptions(NULL, "SELECT residency, descr FROM {asu_tuition_residency} WHERE display = 1 ORDER BY weight, descr", 'residency', 'descr', NULL),
      '#required' => TRUE,
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('residency', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('residency')),

      '#description_display' => 'before',
    ];

    // New residency field.
    /*$form['qtr_residency'] = array(
    '#type' => 'checkbox',
    '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('qtr_residency'),
    '#default_value' => \Drupal::Service('defaultValues')->defaultValues('qtr_residency'),
    '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('qtr_residency')),

    );    */
    // $form['student']['acad_career'] = array(
    $form['acad_career'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('acad_career'),
      '#options' => \Drupal::service('getOptions')->getOptions(NULL, "SELECT acad_career, descr FROM {asu_tuition_acad_career} WHERE display = 1 ORDER BY weight, descr", 'acad_career', 'descr', NULL),
      '#required' => TRUE,
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('acad_career', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('acad_career')),

      '#description_display' => 'before',
    ];
    // Only show academic level field for corporate partnerships.
    /*if ($corporate_partnership) {
    //$form['student']['acad_level'] = array(
    $form['acad_level'] = array(
    '#type' => 'select',
    '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('acad_level'),
    '#options' => \Drupal::service('getOptions')->getOptions('Select One', "SELECT acad_level, descr FROM {asu_tuition_acad_level} WHERE display = 1 AND acad_career = 'UGRD' ORDER BY weight, descr", NULL, 'acad_level', 'descr'),
    '#required' => TRUE,
    '#default_value' => \Drupal::Service('defaultValues')->defaultValues('acad_level', $corporate_partnership),
    '#description' => \Drupal::service('searchPageDescription')->searchPageDescription('acad_level'),

    );
    }*/

    /*$form['program'] = array(
    '#type' => 'fieldset',
    '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('program'),
    '#tree' => FALSE,
    '#attributes' => array('id' => 'options-program-fieldset', 'class' => array('form-autocomplete', 'option-fieldset')),
    '#description' => \Drupal::service('searchPageDescription')->searchPageDescription('program'),
    );*/
    // $form['program']['campus'] = array(
    $form['campus'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('campus'),
      '#options' => \Drupal::service('getOptions')->getOptions('Select One', "SELECT campus, descr FROM {asu_tuition_campus} WHERE display = 1 ORDER BY weight, descr", 'campus', 'descr', NULL),
      '#required' => TRUE,
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('campus', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('campus')),
      '#description_display' => 'before',
    ];

    // $form['program']['acad_prog'] = array(
    $form['acad_prog'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('acad_prog'),
      '#options' => \Drupal::service('getOptions')->getOptions('None/Not Listed', "SELECT acad_prog, descr FROM {asu_tuition_acad_prog} WHERE (display = 1) ORDER BY descr", 'acad_prog', 'descr', NULL),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('acad_prog'),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('acad_prog')),
      '#description_display' => 'before',
      '#required' => TRUE,
    ];
    // $form['program']['admit_term'] = array(
    $form['admit_term'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('admit_term'),
      '#options' => \Drupal::service('getOptions')->getOptions('Select One', "SELECT admit_term, descr FROM {asu_tuition_admit_term} WHERE display = 1 ORDER BY admit_term DESC", 'admit_term', 'descr', NULL),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('admit_term', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('admit_term')),
      
      '#description_display' => 'before',
    ];
    // $form['program']['admit_level'] = array(
    $form['admit_level'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('admit_level'),
      '#options' => \Drupal::service('getOptions')->getOptions('Select One', "SELECT acad_level, descr FROM {asu_tuition_acad_level} WHERE display = 1 AND acad_career = 'UGRD' ORDER BY weight, descr", 'acad_level', 'descr', NULL),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('admit_level', $corporate_partnership),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('admit_level')),

      '#description_display' => 'before',
    ];

    // $form['program']['honors'] = array(
    $form['honors'] = [
      '#type' => 'checkbox',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('honors'),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('honors'),
    // '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('honors')),
      '#description_display' => 'before',
    // '#ajax' => $ajax_settings,
    ];

    // $form['program']['program_fee'] = array(
    $form['program_fee'] = [
      '#type' => 'select',
      '#title' => \Drupal::service('searchPageTitle')->searchPageTitle('program_fee'),
      '#options' => \Drupal::service('getOptions')->getOptions('None/Not Listed', "SELECT DISTINCT fc.fee_code, fc.descr FROM {asu_tuition_fee_code} AS fc JOIN {asu_tuition_rate_type} AS rt ON (rt.rate_type = fc.fee_type AND rt.program_fee_dropdown = 1) ORDER BY fc.descr", 'fee_code', 'descr', NULL),
      '#description' => t(\Drupal::service('searchPageDescription')->searchPageDescription('program_fee')),
      '#default_value' => \Drupal::Service('defaultValues')->defaultValues('program_fee'),
      '#description_display' => 'before',
    // '#ajax' => $ajax_settings,
    // '#description_display' => 'before',
    ];

    /*$form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('See your estimated cost breakdown'),
    );

     */

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Calculate',
    /*'#ajax' => array(
    'callback' => '::ajax_tuition_results',
    'wrapper' => 'results_div',
    ),*/
      '#markup' => '<input class="tuition-calculator-button button js-form-submit form-submit btn-maroon btn btn-primary" data-drupal-selector="edit-submit" type="submit" id="edit-submit" name="op" value="Calculate 1">',
          // '#suffix' => '</div>',
          // '#attributes' => array('class' => array('tuition-calculator-button')),
      '#attributes' => [
        'class' => ['tuition-calculator-button'],
        'onclick' => 'return false;',

      ],
       /* '#ajax' => [
       'callback' => '::ajaxTuitionSubmit',
       // The ID of the <div/> into which search results should be inserted.
       'wrapper' => 'results_div',
       ],*/

    ];

    $form['reset'] = [
      '#type' => 'submit',
      '#value' => 'Reset',

      '#markup' => '<input class="tuition-reset-button button js-form-submit form-submit btn-maroon btn btn-primary" data-drupal-selector="edit-submit" type="submit" id="edit-reset-submit" name="op" value="Reset">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['tuition-reset-button'],
        'onclick' => 'return false;',

      ],

    ];

    $form['actions']['submit']['#attributes']['disabled'] = 'disabled';
    $form['#suffix'] = '</div>';

    $form['result-heading'] = [
      '#prefix' => '<div class="col-12 col-lg-7 offset-lg-1"><div class="tuition-results-rhs">',
      '#markup' => '<h3 class="tuition-result-heading">Your estimated tuition for the <span class="acad-year-text">2021-2022</span> academic year (<span class="credit-text">12</span> credit hours per semester)</h3>',
    ];

    //$credit_hr_help_text = '<div class="uds-tooltip-bg-grey"><div class="uds-tooltip-container"><button tabindex="0" class="uds-tooltip uds-tooltip-grey" aria-describedby="tooltip-desc-credthr"><span class="fa-stack"><svg class="svg-inline--fa fa-circle fa-stack-2x" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg></span><span class="uds-tooltip-visually-hidden">Notifications</span></button><div role="tooltip" class="uds-tooltip-description" id="tooltip-desc-credthr"><span class="uds-tooltip-heading">Choose your credit hour</span></div></div></div>';

    $credit_hr_help_text = '<div class="uds-tooltip-container" aria-hidden="false"><button type="button" class="uds-tooltip uds-tooltip-black no-print" aria-describedby="tooltip-desc-credthr" aria-label="Tuition & fees info"><span class="fa-stack" aria-hidden="true"><svg class="svg-inline--fa fa-circle fa-stack-2x" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg><svg class="svg-inline--fa fa-info fa-stack-1x" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512" data-fa-i2svg=""><path fill="currentColor" d="M48 80a48 48 0 1 1 96 0A48 48 0 1 1 48 80zM0 224c0-17.7 14.3-32 32-32H96c17.7 0 32 14.3 32 32V448h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H64V256H32c-17.7 0-32-14.3-32-32z"></path></svg></span><span class="visually-hidden">More info</span></button><div role="tooltip" class="uds-tooltip-description" id="tooltip-desc-credthr"><span class="uds-tooltip-heading">Choose your credit hour</span></div></div></div>';

    $form['credit_hrs'] = [
      '#type' => 'select',
      '#title' => 'Tuition breakdown by credit hour',
      '#options' => [1 => '1 hour', 2 => '2 hours', 3 => '3 hours', 4 => '4 hours', 5 => '5 hours', 6 => '6 hours', 7 => '7+ hours'],
      '#attributes' => [
        'class' => ['tuition-credit-hrs'],
      ],
      '#default_value' => [7 => '7+ hours'],
      '#description' => t($credit_hr_help_text),
      '#description_display' => 'after',
    ];

    $form['search_results'] = [
      '#prefix' => '<div id="results_div">',
    // '#markup' => '<img src="https://live-asuocms.ws.asu.edu/sites/default/files/2021-08/wic_tc_image_4.jpeg" alt="tuition calculator placeholder image" class="img-fluid" data-v-1a0316f1="" data-v-3033d586=""><div class="initial_help_div_text"><div class="initial_help_p_text"><div class="tr-hr">&nbsp;</div><br />Simply input a few pieces of information into the tuition calculator and choose “Calculate” to see the estimated cost breakdown.</div></div>',
      '#markup' => '<img src="/sites/default/files/2022-08/tuition_background.png" alt="Tuition calculator placeholder image" class="img-fluid" data-v-1a0316f1="" data-v-3033d586=""><div class="initial_help_div_text"><div class="initial_help_p_text"><div class="tr-hr">&nbsp;</div><br />Simply input a few pieces of information into the tuition calculator and choose “Calculate” to see the estimated cost breakdown.</div></div>',
    ];

    $form['#suffix'] = '</div></div></div></div>';

    return $form;
  }

  /**
   *
   */
  public function ajaxTuitionSubmit(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $form_values = $form_state->getValues();
    return $form['search_results'];
  }

  /*public function asu_tuition_search_form_ajax_callback(array &$form, FormStateInterface $form_state) {
  // The form has already been submitted and updated. We can return the replaced
  // item as it is.
  return $form['replace_wrapper'];
  }*/

  /**
   * Validate the form and give errors back to the user.
   */
  public function asu_tuition_search_form_validate(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValue;

    $failed = FALSE;

    // Do some validation on undergraduates only.
    if ($form_state->getValue['acad_career'] === 'UGRD' && $form_state->getValue['acad_year'] < 2016) {
      // Check for admit_term when user is an undergraduate.
      if (empty($form_state->getValue['admit_term'])) {
        form_set_error('admit_term', t('Admit term field is required for degree-seeking undergraduates.'));
        $failed = TRUE;
      }

      if (empty($form_state->getValue['admit_level'])) {
        form_set_error('admit_level', t('Admit level field is required for degree-seeking undergraduates.'));
        $failed = TRUE;
      }

      // Check to see if acad_year is before the admit term for degree seeking undergrads.
      $result = db_query('SELECT COUNT(*) FROM {asu_tuition_admit_term} WHERE admit_term = :admit_term AND acad_year <= :acad_year',
      [':admit_term' => $form_state->getValue['admit_term'], ':acad_year' => $form_state->getValue['acad_year']])->fetchColumn();
      if ($result == 0) {
        form_set_error('acad_year', t('You have chosen an academic year that occurred before the term of your admittance. Please select a later academic year.'));
        $failed = TRUE;
      }
    }

    // Check for college for degee-seeking students.
    if ($form_state->getValue['acad_career'] !== 'UGRDN' && empty($form_state->getValue['acad_prog'])) {
      form_set_error('acad_prog', t('College field is required.'));
      $failed = TRUE;
    }

    // Check to make sure WUE is only for degree-seeking undergraduate students.
    if ($form_state->getValue['acad_career'] !== 'UGRD' && $form_state->getValue['residency'] === 'WUE') {
      form_set_error('acad_career',
      t('WUE is only available for degree-seeking undergraduates.'));
      $failed = TRUE;
    }

    // Display a general error message if any validation failed.
    if ($failed) {
      form_set_error('',
      t('If you are having difficulties making the correct choices, please <a href="@url">reset the calculator</a>.', ['@url' => url('tuition')]));
    }
  }

  /**
   * What to do when the form is submitted.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    /*if (asu_tuition_get_debug_mode()) {
    asu_tuition_debug($form_values);
    }
    // Build URL with forms_values as parameters, not including form IDs, and redirect to results page.
    if (!isset($form_state->getValue['corporate_partner']) || (isset($form_state->getValue['corporate_partner']) && !$form_state->getValue['corporate_partner'])) {
    //drupal_goto('tuition/results', array('query' => _asu_tuition_get_only_form_values($form_values)));
    $url = Url::fromRoute('asu_tuition.results_page', _asu_tuition_get_only_form_values($form_values));
    $form_state->setRedirectUrl($url);
    //  $response = new RedirectResponse($path);
    //  return $response->send();
    }
    else {
    //drupal_goto('cp-tuition/results', array('query' => _asu_tuition_get_only_form_values($form_values)));
    $url = Url::fromRoute('asu_tuition.cp_tuition_results', _asu_tuition_get_only_form_values($form_values));
    $form_state->setRedirectUrl($url);
    }*/
  }

  /*public function asu_tuition_search_form_reset_submit($form_id, &$form_state) {
  //$values = $form_state['values'];
  drupal_set_message(t('The calculator has been reset.'));
  drupal_goto(empty($form_state->getValue['corporate_partner']) ? 'tuition' : 'cp-tuition');
  }*/




}
