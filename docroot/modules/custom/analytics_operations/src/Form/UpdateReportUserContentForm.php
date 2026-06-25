<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdateReportUserContent;

class UpdateReportUserContentForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_report_user_content_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
        'This operation will update report content with user content. This one time operation is only needed after the report refresh.'
      ),
    ];
    $form['groups'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Groups'),
      '#description' => $this->t('Comma separated list of UUIDs to process.'),
      '#required' => true,
    ];
    $form['batch_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch Size'),
      '#default_value' => 10,
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
    $groups = $form_state->getValue('groups');
    $groupsArray = array_map('trim', explode(',', $groups));
    $batchSize = $form_state->getValue('batch_size');

    UpdateReportUserContent::batchInit($groupsArray, $batchSize);
  }
}
