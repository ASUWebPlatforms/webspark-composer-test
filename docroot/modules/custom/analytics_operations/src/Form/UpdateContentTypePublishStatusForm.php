<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\analytics_operations\UpdateContentTypePublishStatus;

class UpdateContentTypePublishStatusForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_content_type_publish_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    // Fetch all content types
    $contentTypes = NodeType::loadMultiple();
    $contentTypeOptions = [];
    foreach ($contentTypes as $contentType) {
      $contentTypeOptions[$contentType->id()] = $contentType->label();
    }

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will update the published status for all nodes of a given content type.'),
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $contentTypeOptions,
      '#required' => true,
    ];
    $form['node_action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => [
        'publish' => $this->t('Publish all nodes'),
        'unpublish' => $this->t('Unpublish all nodes'),
      ],
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
    $selectedContentType = $form_state->getValue('content_type');
    $action = $form_state->getValue('node_action');
    $batchSize = $form_state->getValue('batch_size');

    UpdateContentTypePublishStatus::batchInit($selectedContentType, $action, $batchSize);
  }
}
