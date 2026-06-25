<?php

namespace Drupal\asumex_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for ASU Mexico custom settings.
 */
class ASUMEXCustomSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asumexcustom_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['asumex_custom.settings'];
  }

  /**
   * Builds the settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asumex_custom.settings');

    $form = parent::buildForm($form, $form_state);

    $form['hero_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('Placeholder'),
      '#default_value' => $config->get('hero_title'),
    ];

    return $form;
  }

  /**
   * Form submission handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('asumex_custom.settings')
      ->set('hero_title', $form_state->getValue('hero_title'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
