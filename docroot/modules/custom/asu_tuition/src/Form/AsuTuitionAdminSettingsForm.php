<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 *
 */
class AsuTuitionAdminSettingsForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_tuition.admin_settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => t('General settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['general']['asu_tuition_debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => t('Debug mode?'),
      '#default_value' => $config->get('asu_tuition_debug_mode', ''),
      '#description' => t('Set debug mode for testing in non-production environments.'),
    ];
    $form['general']['asu_tuition_page_title'] = [
      '#type' => 'textfield',
      '#title' => t('Page title'),
      '#default_value' => $config->get('asu_tuition_page_title', ''),
      '#description' => t('Provide the title that will be displayed on the calculator page'),
    ];
    $form['general']['asu_tuition_search_page_form_defaults'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator form defaults'),
      '#default_value' => $config->get('asu_tuition_search_page_form_defaults', []),
      '#description' => t('Enter one value per line, in the format form_key|description. <strong>HTML is not allowed.</strong>'),
    // '#element_validate' => array('asu_tuition_validate_allowed_values_with_check_plain'),
    ];
    $form['general']['asu_tuition_edit_table_add_count_max'] = [
      '#type' => 'textfield',
      '#title' => t('Max new data rows'),
      '#default_value' => $config->get('asu_tuition_edit_table_add_count_max', 1),
      '#description' => t('Enter the number of new rows that should be available when adding data to tables.'),
    ];

    $form['form_messages'] = [
      '#type' => 'details',
      '#title' => t('Estimator messages'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['form_messages']['asu_tuition_search_page_no_javascript_message'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator no javascript message'),
      '#default_value' => $config->get('asu_tuition_search_page_no_javascript_message', ''),
      '#description' => t('This message will be displayed when javascript is disabled or not available.'),
    ];
    $form['form_messages']['asu_tuition_search_page_header'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator header'),
      '#default_value' => $config->get('asu_tuition_search_page_header', ''),
      '#description' => t(''),
    ];
    $form['form_messages']['asu_tuition_search_page_instuctions'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator instructions'),
      '#default_value' => $config->get('asu_tuition_search_page_instuctions', ''),
      '#description' => t(''),
    ];
    $form['form_messages']['asu_tuition_search_page_form_titles'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator form titles'),
      '#default_value' => $config->get('asu_tuition_search_page_form_titles', []),
      '#description' => t('Enter one value per line, in the format form_key|title. HTML is allowed.'),
    // '#element_validate' => array('asu_tuition_validate_allowed_values_without_check_plain'),
    ];
    $form['form_messages']['asu_tuition_search_page_form_descriptions'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator form descriptions'),
      '#default_value' => $config->get('asu_tuition_search_page_form_descriptions', []),
      '#description' => t('Enter one value per line, in the format form_key|description. HTML is allowed.'),
    // '#element_validate' => array('asu_tuition_validate_allowed_values_without_check_plain'),
      '#format' => 'full_html',
    ];
    $form['form_messages']['asu_tuition_search_page_footer'] = [
      '#type' => 'textarea',
      '#title' => t('Calculator footer'),
      '#default_value' => $config->get('asu_tuition_search_page_footer', ''),
      '#description' => t(''),
    ];

    $form['results_messages'] = [
      '#type' => 'details',
      '#title' => t('Results messages'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['results_messages']['asu_tuition_results_page_no_javascript_message'] = [
      '#type' => 'textarea',
      '#title' => t('Results no javascript message'),
      '#default_value' => $config->get('asu_tuition_results_page_no_javascript_message', ''),
      '#description' => t('This message will be displayed when javascript is disabled or not available.'),
    ];
    $form['results_messages']['asu_tuition_results_page_header'] = [
      '#type' => 'textarea',
      '#title' => t('Results header'),
      '#default_value' => $config->get('asu_tuition_results_page_header', ''),
      '#description' => t(''),
    ];
    $form['results_messages']['asu_tuition_results_page_footer'] = [
      '#type' => 'textarea',
      '#title' => t('Results footer'),
      '#default_value' => $config->get('asu_tuition_results_page_footer', ''),
      '#description' => t('!excess_hours_tuition_note will be replaced with the Excess hours tuition note below.'),
    ];
    $form['results_messages']['asu_tuition_results_page_selected_options_titles'] = [
      '#type' => 'textarea',
      '#title' => t('Results selected options titles'),
      '#default_value' => $config->get('asu_tuition_results_page_selected_options_titles', []),
      '#description' => t('Enter one value per line, in the format form_key|title. <strong>HTML is not allowed.</strong>'),
    // '#element_validate' => array('asu_tuition_validate_allowed_values_with_check_plain'),
    ];
    $form['results_messages']['asu_tuition_results_page_online_tuition_note'] = [
      '#type' => 'textarea',
      '#title' => t('Online tuition note'),
      '#default_value' => $config->get('asu_tuition_results_page_online_tuition_note', ''),
      '#description' => t('This message will be placed on the tuition breakdown tab when the student is an online student.'),
    ];
    $form['results_messages']['asu_tuition_results_page_summer_tuition_note'] = [
      '#type' => 'textarea',
      '#title' => t('Summer tuition note'),
      '#default_value' => $config->get('asu_tuition_results_page_summer_tuition_note', ''),
      '#description' => t('This message will be placed on the tuition breakdown tab when summer tuition has been included.'),
    ];

    $form['notes'] = [
      '#type' => 'details',
      '#title' => t('Notes'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => t('These are notes that can be used in some of the messages above.'),
    ];
    $form['notes']['asu_tuition_results_page_excess_hours_tuition_note'] = [
      '#type' => 'textarea',
      '#title' => t('Excess hours tuition note'),
      '#default_value' => $config->get('asu_tuition_results_page_excess_hours_tuition_note', ''),
      '#description' => t(''),
    ];
    $form['table_operations'] = [
      '#type' => 'details',
      '#title' => t('Table Operations'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => t('Enable or disable the ability to perform queries and insert data into the tuition tables.'),
    ];
    $form['table_operations']['asu_tuition_operations_mode'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable table queries mode'),
      '#default_value' => $config->get('asu_tuition_operations_mode', ''),
      '#description' => t('Enable queries for performing operations on the tables.'),
    ];

    /*$form['cp'] = array(
    '#type' => 'details',
    '#title' => t('Corporate partnership settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    );
    $form['cp']['asu_tuition_cp_page_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Page title'),
    '#default_value' => $config->get('asu_tuition_cp_page_title', 'ASU Tuition Estimator'),
    '#description' => t('Provide the title that will be displayed on the calculator page'),
    );
    $form['cp']['asu_tuition_cp_search_page_form_defaults'] = array(
    '#type' => 'textarea',
    '#title' => t('Calculator form defaults'),
    '#default_value' => $config->get('asu_tuition_cp_search_page_form_defaults', array()),
    '#description' => t('Enter one value per line, in the format form_key|description. <strong>HTML is not allowed.</strong>'),
    //'#element_validate' => array('asu_tuition_validate_allowed_values_with_check_plain'),
    );
    $form['cp']['asu_tuition_cp_search_page_header'] = array(
    '#type' => 'textarea',
    '#title' => t('Calculator header'),
    '#default_value' => $config->get('asu_tuition_cp_search_page_header', ''),
    '#description' => t(''),
    );
    $form['cp']['asu_tuition_cp_results_page_header'] = array(
    '#type' => 'textarea',
    '#title' => t('Results header'),
    '#default_value' => $config->get('asu_tuition_cp_results_page_header', ''),
    '#description' => t(''),
    );
    $form['cp']['asu_tuition_cp_search_page_form_titles'] = array(
    '#type' => 'textarea',
    '#title' => t('Calculator form titles'),
    '#default_value' => $config->get('asu_tuition_cp_search_page_form_titles', array()),
    '#description' => t('Enter one value per line, in the format form_key|title. HTML is allowed.'),
    //  '#element_validate' => array('asu_tuition_validate_allowed_values_without_check_plain'),
    );
    $form['cp']['asu_tuition_cp_search_page_form_descriptions'] = array(
    '#type' => 'textarea',
    '#title' => t('Calculator form descriptions'),
    '#default_value' => $config->get('asu_tuition_cp_search_page_form_descriptions', array()),
    '#description' => t('Enter one value per line, in the format form_key|description. HTML is allowed.'),
    //  '#element_validate' => array('asu_tuition_validate_allowed_values_without_check_plain'),
    );*/

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $titles = $form_state->getValue('asu_tuition_search_page_form_titles');
    $descriptions = $form_state->getValue('asu_tuition_search_page_form_descriptions');
    foreach ($form_state->getValues() as $var_key => $var_values) {
      \Drupal::service('config.factory')->getEditable('asu_tuition.admin_settings')->set($var_key, $var_values)->save();
    }
    // \Drupal::service('config.factory')->getEditable('asu_tuition.admin_settings')->set('asu_tuition_search_page_form_titles' , $titles)->save();
    // \Drupal::service('config.factory')->getEditable('asu_tuition.admin_settings')->set('asu_tuition_search_page_form_descriptions' , $descriptions)->save();
  }

}
