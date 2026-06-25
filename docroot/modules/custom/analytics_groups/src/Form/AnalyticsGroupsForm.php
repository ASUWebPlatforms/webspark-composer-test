<?php

namespace Drupal\analytics_groups\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_groups\Controller\AnalyticsGroupsController;

class AnalyticsGroupsForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_groups_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['group_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Group Name'),
      '#required' => true,
    ];
    $form['service_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EDNA Service Path'),
      '#description' => $this->t('Do not include the ".VWR" or ".PWR" extension.'),
      '#required' => true,
    ];
    $form['services'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Services'),
      '#options' => [
        'Drupal' => $this->t('Drupal'),
        'Tableau' => $this->t('Tableau'),
        'OneDrive' => $this->t('OneDrive'),
        'SSRS' => $this->t('SSRS'),
      ],
      '#default_value' => ['Drupal', 'Tableau', 'OneDrive', 'SSRS'],
      '#required' => true,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Analytics Group'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    // Add your validation logic here
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $data = [
      'group_name' => $form_state->getValue('group_name'),
      'service_path' => $form_state->getValue('service_path'),
      'services' => array_keys($form_state->getValue('services')),
    ];

    AnalyticsGroupsController::createGroup($data);
  }
}
