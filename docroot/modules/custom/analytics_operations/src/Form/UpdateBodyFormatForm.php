<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\filter\Entity\FilterFormat;
use Drupal\analytics_operations\UpdateBodyFormat;

class UpdateBodyFormatForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_body_format_form';
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

    // Fetch all text formats
    $textFormats = FilterFormat::loadMultiple();
    $textFormatOptions = [];
    foreach ($textFormats as $format) {
      $textFormatOptions[$format->id()] = $format->label();
    }

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will update the body format of all nodes for the given content type.'),
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $contentTypeOptions,
      '#required' => true,
    ];
    $form['text_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Text Format'),
      '#options' => $textFormatOptions,
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
    $selectedTextFormat = $form_state->getValue('text_format');
    $batchSize = $form_state->getValue('batch_size');

    UpdateBodyFormat::batchInit($selectedContentType, $selectedTextFormat, $batchSize);
  }
}
