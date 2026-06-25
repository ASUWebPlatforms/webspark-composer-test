<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdatePublishedStatus;

class UpdatePublishedStatusForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_published_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update the published status for content based upon set criteria, which is determined in the README file.'
      ),
    ];
    $form['node_action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => [
        'publish' => $this->t('Publish nodes'),
        'unpublish' => $this->t('Unpublish nodes'),
        'unpublish_and_hide' => $this->t('Unpublish nodes and hide from search'),
      ],
      '#required' => true,
    ];
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node IDs'),
      '#description' => $this->t('Comma separated list of node IDs.'),
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
    $nids = $form_state->getValue('nids');
    $nidsArray = array_map('trim', explode(',', $nids));
    $batchSize = $form_state->getValue('batch_size');

    UpdatePublishedStatus::batchInit($action, $nidsArray, $batchSize);
  }
}
