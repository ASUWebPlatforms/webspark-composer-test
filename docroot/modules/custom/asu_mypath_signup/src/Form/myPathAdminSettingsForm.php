<?php

namespace Drupal\asu_mypath_signup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class myPathAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_path_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asu_mypath_signup.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_mypath_signup.settings');
    if ($config->get('mypath_api_key')) {
      $place_holder = "Value already exists, hiding for security.";
      $description = "Value already exists, hiding for security. You can enter new key and save it.";
    }
    else {
      $place_holder = "No value exists for this field currently";
      $description = "Enter MyPath API Key since no value exists for this field currently, so mypath will throw errors.";
    }

     $form['mypath_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MyAPth API Key'),
      // '#default_value' => base64_encode($config->get('mypath_api_key')),
      '#description' => $this->t($description),
      '#placeholder' => $place_holder,
    // Keep blank for security.
      '#default_value' => '',
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ]; 

    $form['maricopa_inst_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter Maricopa Institution IDs for MyPath API, separate by comma. E.g. "1001,1002,1003"'),
      '#default_value' => $config->get('maricopa_inst_ids'),
      '#description' => $this->t('Enter Maricopa Institution IDs for MyPath API, separate by comma. E.g. "1001,1002,1003"'),
    ];

    $form['maricopa_validate_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL to validate Maricopa ID'),
      '#default_value' => $config->get('maricopa_validate_url'),
      '#description' => $this->t('Enter Maricopa Validation API URL for MyPath API.'),
    ];

    $form['maricopa_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maricopa Data Posting API URL'),
      '#default_value' => $config->get('maricopa_api_url'),
      '#description' => $this->t('Enter Maricopa API URL for MyPath API.'),
    ];

    $form['maricopa_nomatch_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text to display when no Maricopa ID match is found'),
      '#default_value' => $config->get('maricopa_nomatch_text'),
      '#description' => $this->t('Enter the text to display when no Maricopa ID match is found during MyPath API validation.'),
      '#format' => 'full_html',
    ];

    $form['asurite'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ASURITE name for testing purpose, will be removed later'),
      '#default_value' => $config->get('asurite'),
      '#description' => $this->t('Enter ASURITE username for MyPath API authentication.'),
    ];

    $form['emplid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EMPLID for testing purpose, will be removed later'),
      '#default_value' => $config->get('emplid'),
      '#description' => $this->t('Enter EMPLID for MyPath API authentication.'),
    ];

   /*  $form['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Debug Mode'),
      '#default_value' => $config->get('debug_mode'),
      '#description' => $this->t('When enabled, the Maricopa field will be visible and data will be sent to the Maricopa API. In production environment, this should be disabled to hide the Maricopa field and prevent data from being sent to the Maricopa API.'),
    ]; */

    $form['enable_maricopa_field'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Maricopa MEID field (instead of redirect)'),
      '#default_value' => $config->get('enable_maricopa_field'),
    ];

    $form['desktop_consent_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Desktop Consent Text'),
      '#default_value' => $config->get('desktop_consent_text'),
      '#description' => $this->t('Enter the consent text to display on desktop devices.'),
      '#format' => 'full_html',
    ];

    $form['mobile_consent_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mobile Consent Text'),
      '#default_value' => $config->get('mobile_consent_text'),
      '#description' => $this->t('Enter the consent text to display on mobile devices.'),
      '#format' => 'full_html',
    ];


    /*$form['encoded'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Value encoded?'),
    '#default_value' => $config->get('encoded'),
    '#description' => $this->t('Accepts 0 or 1. 1 saves the data, 0 rejects changes.'),
    //'#default_value' => '', // keep blank for security

    ];*/

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('asu_mypath_signup.settings');
    $values = $form_state->getValues();
    foreach ($values as $key => $each_value) {
      $this->config('asu_mypath_signup.settings')
        ->set($key, $each_value)
        ->save();
    }
  }

}
