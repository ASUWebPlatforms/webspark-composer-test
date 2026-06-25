<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\analytics_operations\UpdateNodeAliasOption;

class UpdateNodeAliasOptionForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_node_alias_option_form';
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
      '#markup' => $this->t(
        'This operation will ensure that all nodes of the selected content type have the "Generate automatic URL alias" option enabled.'
      ),
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
      '#description' => $this->t(
        'Comma separated list of IDs. If left blank, all nodes of the selected content type will be processed.'
      ),
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
    $type = $form_state->getValue('content_type');
    $nids = array_map('trim', explode(',', $form_state->getValue('nids')));
    $batchSize = $form_state->getValue('batch_size');

    UpdateNodeAliasOption::batchInit($type, $nids, $batchSize);
  }
}
