<?php

namespace Drupal\asuaec_briteverify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure BriteVerify settings for this site.
 */
class BriteVerifySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asuaec_briteverify.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asuaec_briteverify_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asuaec_briteverify.settings');

    $form['briteverify_key_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BriteVerify API Key (Production)'),
      '#description' => $this->t('Enter the API key for BriteVerify production environment.'),
      '#default_value' => $config->get('briteverify_key_prod'),
      '#required' => TRUE,
    ];

    $form['briteverify_key_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BriteVerify API Key (Development)'),
      '#description' => $this->t('Enter the API key for BriteVerify development/testing environment.'),
      '#default_value' => $config->get('briteverify_key_dev'),
      '#required' => TRUE,
    ];

    $form['environment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Environment'),
      '#description' => $this->t('Select the current environment. In development mode, test emails (@test.asu.edu) are automatically validated as valid.'),
      '#options' => [
        'prod' => $this->t('Production'),
        'dev' => $this->t('Development'),
      ],
      '#default_value' => $config->get('environment') ?? 'prod',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('asuaec_briteverify.settings')
      ->set('briteverify_key_prod', $form_state->getValue('briteverify_key_prod'))
      ->set('briteverify_key_dev', $form_state->getValue('briteverify_key_dev'))
      ->set('environment', $form_state->getValue('environment'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}