<?php

namespace Drupal\analytics_operations\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdateHiddenFromSearch;
use Drupal\node\Entity\NodeType;

class UpdateHiddenFromSearchForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_hidden_from_search_form';
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
      $field_definitions = Drupal::service('entity_field.manager')->getFieldDefinitions('node', $contentType->id());

      if (isset($field_definitions['field_hidden_from_gsearch'])) {
        $contentTypeOptions[$contentType->id()] = $contentType->label();
      }
    }

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update nodes that may be missing a value for "field_hidden_from_gsearch".'
      ),
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $contentTypeOptions,
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
    $batchSize = $form_state->getValue('batch_size');

    UpdateHiddenFromSearch::batchInit($selectedContentType, $batchSize);
  }
}
