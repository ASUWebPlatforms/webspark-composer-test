<?php

namespace Drupal\asu_myapps\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Secrets are managed by the Pantheon Secrets Manager Plugin.
 * Use this only to set non-secret settings.
 */
class AsuMyappsSettingsForm extends ConfigFormBase
{
  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'asu_myapps.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'asu_myapps_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config(static::SETTINGS);

    $form['asu_myapps_disable_csrf'] = [
      '#type' => 'checkbox',
      '#title' => t('Disbale CSRF Tokens'),
      '#description' => t('Use this to force node edits to bypass CSRF token checks.'),
      '#default_value' => $config->get('asu_myapps_disable_csrf'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $this->config(static::SETTINGS)
      ->set('asu_myapps_disable_csrf', $form_state->getValue('asu_myapps_disable_csrf'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return [
      static::SETTINGS,
    ];
  }
}
