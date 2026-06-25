<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\analytics_operations\UpdateNodeAuthor;

class UpdateNodeAuthorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'analytics_operations_update_node_author_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state
  ): array {
    // Fetch all content types
    $contentTypes = NodeType::loadMultiple();
    $contentTypeOptions = [];
    foreach ($contentTypes as $contentType) {
      $contentTypeOptions[$contentType->id()] = $contentType->label();
    }

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update the author of a node from one user to another.'
      ),
    ];
    $form['old_user'] = [
      '#type' => 'number',
      '#title' => $this->t('Old User ID'),
      '#description' => $this->t(
        'The ID of the user to re-assign content from.'
      ),
      '#min' => 1,
      '#required' => TRUE,
    ];
    $form['new_user'] = [
      '#type' => 'number',
      '#title' => $this->t('New User ID'),
      '#description' => $this->t('The ID of the user to re-assign content to.'),
      '#min' => 1,
      '#required' => TRUE,
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $contentTypeOptions,
      '#required' => TRUE,
    ];
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node IDs'),
      '#description' => $this->t(
        'Comma separated list of node IDs. If blank, all nodes will be processed.'
      ),
      '#required' => FALSE,
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
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $oldUID = $form_state->getValue('old_user');
    $newUID = $form_state->getValue('new_user');
    $type = $form_state->getValue('content_type');
    $nids = array_map('trim', explode(',', $form_state->getValue('nids')));
    $batchSize = $form_state->getValue('batch_size');

    UpdateNodeAuthor::batchInit($oldUID, $newUID, $type, $nids, $batchSize);
  }

}
