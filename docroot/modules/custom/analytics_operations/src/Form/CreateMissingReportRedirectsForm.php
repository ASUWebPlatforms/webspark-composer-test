<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\CreateMissingReportRedirects;

class CreateMissingReportRedirectsForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_create_missing_report_redirects_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will add missing redirects from the existing website for the given Group reports.'
      ),
    ];
    $form['gid'] = [
      '#type' => 'number',
      '#title' => $this->t('Group ID'),
      '#min' => 1,
      '#required' => true,
    ];
    $form['batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch Size'),
      '#default_value' => 50,
      '#min' => 1,
      '#required' => true,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $gid = $form_state->getValue('gid');
    $batchSize = $form_state->getValue('batch_size');

    CreateMissingReportRedirects::batchInit($gid, $batchSize);
  }
}
