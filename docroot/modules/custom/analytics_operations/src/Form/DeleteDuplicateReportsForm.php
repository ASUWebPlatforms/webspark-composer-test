<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\DeleteDuplicateReports;

class DeleteDuplicateReportsForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_delete_duplicate_reports_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will delete duplicate reports.'),
    ];
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node IDs'),
      '#description' => $this->t('Comma separated list of IDs.'),
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
    $nids = $form_state->getValue('nids');
    $idsArray = array_map('trim', explode(',', $nids));
    $batchSize = $form_state->getValue('batch_size');

    DeleteDuplicateReports::batchInit($idsArray, $batchSize);
  }
}
