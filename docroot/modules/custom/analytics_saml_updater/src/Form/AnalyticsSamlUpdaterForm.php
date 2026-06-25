<?php

namespace Drupal\analytics_saml_updater\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AnalyticsSamlUpdaterForm extends ConfigFormBase
{
  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'analytics_saml_updater.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_saml_updater_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config(static::SETTINGS);

    $form['metadata_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Metadata URL'),
      '#description' => $this->t('Enter the URL where the XML metadata file can be downloaded.'),
      '#default_value' => $config->get('analytics_saml_updater_metadata_url'),
      '#required' => true,
    ];
    $form['metadata_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Metadata File'),
      '#description' => $this->t('Enter the path to the file to write the metadata to.'),
      '#default_value' => $config->get('analytics_saml_updater_metadata_file'),
      '#required' => true,
    ];
    $form['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Update mode'),
      '#options' => [
        'automatic' => 'Automatic',
        'manual' => 'Manual',
      ],
      '#default_value' => 'automatic',
      '#required' => true,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $mode = $form_state->getValue('mode');
    $this->config(static::SETTINGS)
      ->set('analytics_saml_updater_metadata_url', $form_state->getValue('metadata_url'))
      ->set('analytics_saml_updater_metadata_file', $form_state->getValue('metadata_file'))
      ->save();
    if($mode == 'manual') {

    }
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
