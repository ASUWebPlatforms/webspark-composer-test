<?php

namespace Drupal\analytics_resources\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AnalyticsResourcesForm extends ConfigFormBase
{
  /**
   * Config settings.
   *
   * @var string
   */
  public const SETTINGS = 'analytics_resources.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_resources_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
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
