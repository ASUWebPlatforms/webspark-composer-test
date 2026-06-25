<?php

namespace Drupal\analytics_tweaks\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AnalyticsTweaksSettingsForm.
 */
class AnalyticsTweaksSettingsForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_tweaks_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config('analytics_tweaks.settings');
    $content_types = Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    $options = [];

    foreach ($content_types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Types'),
      '#options' => $options,
      '#default_value' => array_keys(array_filter($config->get('types') ?: [])),
      '#description' => 'Select the content types that will only return a JSON response.'
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $config = $this->config('analytics_tweaks.settings');
    $types = $form_state->getValue('types');
    $selected_types = array_filter($types, function ($value) {
      return $value !== 0;
    });

    $config->set('types', $selected_types)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return ['analytics_tweaks.settings'];
  }
}
