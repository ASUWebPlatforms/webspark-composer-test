<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdateReportSearchStatus;

class UpdateReportSearchStatusForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_report_search_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update the search status of reports. This is a one time operation post the report refresh.'
      ),
    ];
    $form['node_action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => [
        'publish' => $this->t('Publish'),
        'publish_hidden' => $this->t('Publish, but hide from search'),
      ],
      '#required' => true,
    ];
    $form['rids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Report IDs'),
      '#description' => $this->t('Comma separated list of report IDs.'),
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
    $action = $form_state->getValue('node_action');
    $rids = array_map('trim', explode(',', $form_state->getValue('rids')));
    $batchSize = $form_state->getValue('batch_size');

    UpdateReportSearchStatus::batchInit($action, $rids, $batchSize);
  }
}
