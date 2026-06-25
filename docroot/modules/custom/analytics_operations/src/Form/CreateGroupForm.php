<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\CreateGroup;

class CreateGroupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'analytics_operations_create_group_form';
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
        'This operation will import a Group from the old Pantheon website. Only use this form as part of the Acquia migration.'
      ),
    ];
    $form['source_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Source URL'),
      '#description' => $this->t(
        'Enter the URL to source the Groups from. If blank, will default to https://live-asu-analytics.ws.asu.edu'
      ),
      '#required' => FALSE,
    ];
    $form['guuids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Group UUIDs'),
      '#description' => $this->t(
        'Comma separated list of group UUIDs to process.'
      ),
      '#required' => TRUE,
    ];
    $form['batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch Size'),
      '#default_value' => 10,
      '#min' => 1,
      '#max' => 100,
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
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $sourceUrl = $form_state->getValue('source_url');
    $guuids = $form_state->getValue('guuids');
    $guuidsArray = array_map('trim', explode(',', $guuids));
    $batchSize = $form_state->getValue('batch_size');

    CreateGroup::batchInit($sourceUrl, $guuidsArray, $batchSize);
  }

}
