<?php

namespace Drupal\asuaec_asulocal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure ASU Local Degrees settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asuaec_asulocal_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asuaec_asulocal.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asuaec_asulocal.settings');

    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GraphQL endpoint'),
      '#default_value' => $config->get('endpoint') ?? '',
      '#description' => $this->t('Full AppSync GraphQL endpoint: <br/>Prod: https://6ksljfo4j5h5jawlcqmlmfbrwm.appsync-api.us-west-2.amazonaws.com/graphql'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $config->get('api_key') ?? '',
      '#description' => $this->t('AppSync API key. For production, consider storing this using the Key module rather than config.'),
      '#required' => TRUE,
    ];

    $form['note'] = [
      '#markup' => $this->t('<p><em>Tip:</em> Use the Key module to store secrets securely. This form stores the key in configuration and is intended for quick testing / dev only.</p>'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('asuaec_asulocal.settings')
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
