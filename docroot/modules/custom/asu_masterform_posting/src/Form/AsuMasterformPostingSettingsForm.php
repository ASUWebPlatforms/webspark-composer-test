<?php

namespace Drupal\asu_masterform_posting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AsuMasterformPostingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asu_masterform_posting_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_masterform_posting.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_masterform_posting.settings');

    $allowed_paths = $config->get('allowed_paths') ?? [
      '/road-to-asu',
      '/masterform-registration',
      '/BEET-event',
      '/beet-registration',
    ];

    $target_form_ids = $config->get('target_form_ids') ?? [
      'webform_submission_sun_devil_send_off_node_217217_add_form',
      'webform_submission_sun_devil_send_off_node_217217_edit_form',
      'webform_submission_beet_node_264061_add_form',
      'webform_submission_beet_node_264061_edit_form',
    ];

    $form['allowed_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed paths for PII redirect'),
      '#description' => $this->t('One internal path per line, e.g. /road-to-asu'),
      '#default_value' => implode("\n", $allowed_paths),
    ];

    $form['target_form_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Target Webform IDs'),
      '#description' => $this->t('One form ID per line, e.g. webform_submission_sun_devil_send_off_node_217217_add_form'),
      '#default_value' => implode("\n", $target_form_ids),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Normalize textarea → array (trim, remove empty lines).
    $allowed_paths = preg_split("/\r\n|\r|\n/", (string) $form_state->getValue('allowed_paths'));
    $allowed_paths = array_values(array_filter(array_map('trim', $allowed_paths)));

    $target_form_ids = preg_split("/\r\n|\r|\n/", (string) $form_state->getValue('target_form_ids'));
    $target_form_ids = array_values(array_filter(array_map('trim', $target_form_ids)));

    $this->configFactory->getEditable('asu_masterform_posting.settings')
      ->set('allowed_paths', $allowed_paths)
      ->set('target_form_ids', $target_form_ids)
      ->save();

    parent::submitForm($form, $form_state);
  }

}