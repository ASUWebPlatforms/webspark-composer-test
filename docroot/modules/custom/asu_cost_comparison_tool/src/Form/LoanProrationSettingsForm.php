<?php

namespace Drupal\asu_cost_comparison_tool\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure default tuition and housing values.
 */
class LoanProrationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc} */
  public function getFormId() {
    return 'asu_cost_comparison_tool_defaults_form';
  }

  /**
   * {@inheritdoc} */
  protected function getEditableConfigNames() {
    return ['asu_cost_comparison_tool.loan_proration_settings'];
  }

  /**
   * {@inheritdoc} */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_cost_comparison_tool.loan_proration_settings');

    $form[] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p>Set default values for the loan proration tool for ASU.</p>'),
    ];

    $form['reactFormFields'] = [
      '#type' => 'details',
      '#title' => $this->t('React form field mappings'),
      '#open' => TRUE,
    ];

    $form['reactFormFields']['field_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Form field labels, separate key and value by |, and each field by comma. E.g. "studentType|Student type,loanAmount|Loan Amount"'),
      '#default_value' => $config->get('field_labels'),
    ];

    $form['reactFormFields']['student_type'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Student type options, separate key and value by |, and each option by comma. E.g. "dep|Dependent,indep|Independent"'),
      '#default_value' => $config->get('student_type'),
    ];

    $form['reactFormFields']['loan_residency'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Residency status options, separate key and value by |, and each option by comma. E.g. "res|Resident,non-az|Non-resident"'),
      '#default_value' => $config->get('loan_residency'),
 
    ];

    $form['reactFormFields']['loan_dependency'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Dependency'),
      '#default_value' => $config->get('loan_dependency'),
    ];

    $form['reactFormFields']['credits_completed'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credits Completed, separate each option by comma. E.g. "0,1-30,31-60,61-90"'),
      '#default_value' => $config->get('credits_completed'),
    ];

    // Housing defaults by campus.
    $form['reactFormFields']['semester'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Semester options, separate key and value by |, and each option by comma. E.g. "fall|Fall,spring|Spring"'),
      '#default_value' => $config->get('semester'),
    ];

    $form['reactFormFields']['credits_help_text_undergrad'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credits Help Text - Undergraduate'),
      '#default_value' => $config->get('credits_help_text_undergrad'),
      '#format' => 'full_html',
    ];

    $form['reactFormFields']['credits_help_text_graduate'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credits Help Text - Graduate'),
      '#default_value' => $config->get('credits_help_text_graduate'),
      '#format' => 'full_html',
    ];

    $form['reactFormFields']['importantNotes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Important Notes'),
      '#default_value' => $config->get('importantNotes'),
      '#format' => 'full_html',
    ];

    $form['max_loan_amounts'] = [
      '#type' => 'details',
      '#title' => $this->t('Maximum Loan Amounts'),
      '#open' => TRUE,
    ];

    $form['max_loan_amounts']['undergradDepOne'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate dependent student, 1st year - 12 credits/semester'),
      '#default_value' => $config->get('undergradDepOne'),
    ];

    $form['max_loan_amounts']['undergradDepTwo'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate dependent student, 2nd year - 12 credits/semester'),
      '#default_value' => $config->get('undergradDepTwo'),
    ];

    $form['max_loan_amounts']['undergradDepThreePlus'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate dependent student, 3rd/4th year - 12 credits/semester'),
      '#default_value' => $config->get('undergradDepThreePlus'),
    ];

    $form['max_loan_amounts']['undergradIndependentOne'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate independent student, 1st year - 12 credits/semester'),
      '#default_value' => $config->get('undergradIndependentOne'),
    ];

    $form['max_loan_amounts']['undergradIndependentTwo'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate independent student, 2nd year - 12 credits/semester'),
      '#default_value' => $config->get('undergradIndependentTwo'),
    ];

    $form['max_loan_amounts']['undergradIndependentThreePlus'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate independent student, 3rd/4th year - 12 credits/semester'),
      '#default_value' => $config->get('undergradIndependentThreePlus'),
    ];
    
    $form['max_loan_amounts']['graduate'] = [
      '#type' => 'number',
      '#title' => $this->t('Graduate student (all years) - 9 credits/semester'),
      '#default_value' => $config->get('graduate'),
    ];

    $form['undergrad_max_full_credit_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate Max full-time credit limit.'),
      '#default_value' => $config->get('undergrad_max_full_credit_limit'),
    ];

    $form['graduate_max_full_credit_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Graduate Max full-time credit limit.'),
      '#default_value' => $config->get('graduate_max_full_credit_limit'),
    ];

    $form['undergrad_least_full_credit_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Undergraduate Least full-time credit limit.'),
      '#default_value' => $config->get('undergrad_least_full_credit_limit'),
    ];

    $form['graduate_least_full_credit_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Graduate Least full-time credit limit.'),
      '#default_value' => $config->get('graduate_least_full_credit_limit'),
    ];
    
    $form['campus_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Campus list, separate key and value by |'),
      '#default_value' => $config->get('campus_list'),
    ];


    $form['credit_semester_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Credit per semester limit for loan proration'),
      '#default_value' => $config->get('credit_semester_limit'),
    ];

    $form['submit_button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit Button Text'),
      '#default_value' => $config->get('submit_button_text'),
      '#size' => 250,
    ];

    $form['current_acad_year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Current Academic Year'),
      '#default_value' => $config->get('current_acad_year'),
      '#size' => 10,
    ];

    $form['results_disclaimer'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Results Disclaimer'),
      '#default_value' => $config->get('results_disclaimer'),
      '#format' => 'full_html',
    ];

    $form['results_about'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Results About Text'),
      '#default_value' => $config->get('results_about'),
      '#format' => 'full_html',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc} */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Optional validations (e.g., numbers >= 0) — HTML number enforces min, but double-check.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc} */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // ksm($form_state->getValues());
    $values = $form_state->getValues();
    foreach ($values as $key => $each_value) {
      $this->config('asu_cost_comparison_tool.loan_proration_settings')
        ->set($key, $each_value)
        ->save();
    }

  }

}
