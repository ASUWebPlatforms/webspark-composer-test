<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdateReportMetadata;

class UpdateReportMetadataForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'analytics_operations_update_report_metadata_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state
  ): array {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update Drupal specific metadata for reports. For use during the Acquia migration.'
      ),
    ];
    $form['source_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Source URL'),
      '#description' => $this->t(
        'Enter the URL to source the reports from. If blank, will default to https://live-asu-analytics.ws.asu.edu'
      ),
      '#required' => FALSE,
    ];
    $form['process'] = [
      '#type' => 'select',
      '#title' => $this->t('What data to process'),
      '#options' => [
        'group' => 'Process all reports from a group',
        'single' => 'Process a single report',
      ],
      '#required' => TRUE,
    ];
    $form['groups'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Groups'),
      '#description' => $this->t(
        'Comma separated list of Group UUIDs to process.'
      ),
      '#states' => [
        'enabled' => [
          ':input[name="process"]' => ['value' => 'group'],
        ],
        'required' => [
          ':input[name="process"]' => ['value' => 'group'],
        ],
      ],
    ];
    $form['ruuid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Report UUID'),
      '#description' => $this->t(
        'UUID of the single report to process.'
      ),
      '#states' => [
        'enabled' => [
          ':input[name="process"]' => ['value' => 'single'],
        ],
        'required' => [
          ':input[name="process"]' => ['value' => 'single'],
        ],
      ],
    ];
    $form['batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch Size'),
      '#default_value' => 100,
      '#min' => 1,
      '#max' => 500,
      '#required' => TRUE,
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
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $sourceUrl = $form_state->getValue('source_url');
    $groups = array_map('trim', explode(',', $form_state->getValue('groups')));
    $ruuid = $form_state->getValue('ruuid');
    $batchSize = $form_state->getValue('batch_size');

    UpdateReportMetadata::batchInit(
      $sourceUrl,
      $groups,
      $ruuid,
      $batchSize
    );
  }

}
