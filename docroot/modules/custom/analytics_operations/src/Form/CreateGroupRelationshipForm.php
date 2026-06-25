<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\analytics_operations\CreateGroupRelationship;

class CreateGroupRelationshipForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_create_group_relationship_form';
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
      '#markup' => $this->t('This operation will create group relationships for nodes of the given content type.<br>Use this to assign content to groups when they have been added to the website without an initial group assignment.<br><strong>Do not use this form to remove group content, or to move content from one group to another.</strong>'),
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $contentTypeOptions,
      '#required' => true,
    ];
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node IDs'),
      '#description' => $this->t('Comma separated list of IDs. If left blank, all nodes of the selected content type will be processed.'),
      '#required' => false,
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
    $nids = array_map('trim', explode(',', $form_state->getValue('nids')));
    $batchSize = $form_state->getValue('batch_size');

    CreateGroupRelationship::batchInit($selectedContentType, $nids, $batchSize);
  }
}
