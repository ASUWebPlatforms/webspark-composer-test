<?php

namespace Drupal\asu_cost_comparison_tool\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure default tuition and housing values.
 */
class CostDefaultValuesForm extends ConfigFormBase {

  /**
   * {@inheritdoc} */
  public function getFormId() {
    return 'asu_cost_comparison_tool_defaults_form';
  }

  /**
   * {@inheritdoc} */
  protected function getEditableConfigNames() {
    return ['asu_cost_comparison_tool.settings'];
  }

  /**
   * {@inheritdoc} */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_cost_comparison_tool.settings');

    $form[] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p>Set default values for the cost comparison tool for ASU.</p>'),
    ];

    $form['tuition_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Tuition defaults (by residency)'),
      '#open' => TRUE,
    ];

    $form['tuition_section']['tuition_az'] = [
      '#type' => 'number',
      '#title' => $this->t('Arizona resident (AZ)'),
      '#default_value' => $config->get('tuition_az'),
      '#min' => 0,
    ];
    $form['tuition_section']['tuition_non_az'] = [
      '#type' => 'number',
      '#title' => $this->t('Non-resident (non-az)'),
      '#default_value' => $config->get('tuition_non_az'),
      '#min' => 0,
    ];
    $form['tuition_section']['tuition_intl'] = [
      '#type' => 'number',
      '#title' => $this->t('International (intl)'),
      '#default_value' => $config->get('tuition_intl'),
      '#min' => 0,
    ];

    // Housing defaults by campus.
    $form['housing_and_meals_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Housing defaults (by campus type)'),
      '#open' => TRUE,
    ];

    $form['housing_and_meals_section']['oncampus_living'] = [
      '#type' => 'number',
      '#title' => $this->t('Living on campus'),
      '#default_value' => $config->get('oncampus_living'),
      '#min' => 0,
    ];
    $form['housing_and_meals_section']['with_parents_living'] = [
      '#type' => 'number',
      '#title' => $this->t('Living with parents'),
      '#default_value' => $config->get('with_parents_living'),
      '#min' => 0,
    ];
    $form['housing_and_meals_section']['off_campus_living'] = [
      '#type' => 'number',
      '#title' => $this->t('Living off-campus'),
      '#default_value' => $config->get('off_campus_living'),
      '#min' => 0,
    ];

    // Default selected radio values.
    $form['books_and_supplies'] = [
      '#type' => 'number',
      '#title' => $this->t('ASU Books and supplies'),
      '#default_value' => $config->get('books_and_supplies'),
    ];

    $form['transportation'] = [
      '#type' => 'number',
      '#title' => $this->t('Transportation costs'),
      '#default_value' => $config->get('transportation'),

    ];

    $form['CustomText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom footer text if ASU and both other schools are within  $1500 difference'),
      '#description' => $this->t('Custom footer text if ASU and both other schools are within  $1500 difference. You can use HTML formatting here.'),
      '#default_value' => $config->get('CustomText'),
      '#format' => 'full_html',
    ];

    $form['CustomText2'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom footer text if ASU cost is >$1500 more than either other school option'),
      '#description' => $this->t('Custom footer text if ASU cost is >$1500 more than either other school option. You can use HTML formatting here.'),
      '#default_value' => $config->get('CustomText2'),
      '#format' => 'full_html',
    ];

    $form['CustomText3'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom footer text if both school cost is >$1500'),
      '#description' => $this->t('Custom footer text if both school cost is >$1500. You can use HTML formatting here.'),
      '#default_value' => $config->get('CustomText3'),
      '#format' => 'full_html',
    ];

    $form['tuition_fees_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tuition and Fees Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Tuition and Fees in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('tuition_fees_tooltip'),
      '#format' => 'full_html',
    ];

    $form['housing_meals_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Housing and Meals Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Housing and Meals in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('housing_meals_tooltip'),
    ];

    $form['books_supplies_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Books and Supplies Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Books and Supplies in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('books_supplies_tooltip'),
    ];

    $form['transportation_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Transportation Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Transportation in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('transportation_tooltip'),
    ];

    $form['subsidies_loans_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Subsidies Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Subsidies in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('subsidies_loans_tooltip'),
    ];

    $form['unsubsidized_loans_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Unsubsidized Loans Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Unsubsidized Loans in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('unsubsidized_loans_tooltip'),
    ];

    $form['parent_plus_loans_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Parent PLUS Loans Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Parent PLUS Loans in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('parent_plus_loans_tooltip'),
    ];

    $form['scholar_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Scholarships Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Scholarships in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('scholar_tooltip'),
    ];

    $form['grant_tooltip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Grants Tooltip Text'),
      '#description' => $this->t('This text will appear in the tooltip for Grants in the cost comparison tool. You can use HTML formatting here.'),
      '#default_value' => $config->get('grant_tooltip'),
    ];

    $form['cost_webform_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cost Webform ID'),
      '#description' => $this->t('Enter the Webform ID to be used for the cost comparison tool submissions storage.'),
      '#default_value' => $config->get('cost_webform_id'),
    ];

    $form['email_reply_to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reply-to email address'),
      '#description' => $this->t('Enter reply-to email address.'),
      '#default_value' => $config->get('email_reply_to'),
    ];

    $form['email_from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From email address'),
      '#description' => $this->t('Enter from email address.'),
      '#default_value' => $config->get('email_from'),
    ];

    $form['cas_login_url'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Cas login url'),
      '#description' => $this->t('Cas login url'),
      '#default_value' => $config->get('cas_login_url'),
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
      $this->config('asu_cost_comparison_tool.settings')
        ->set($key, $each_value)
        ->save();
    }

  }

}
