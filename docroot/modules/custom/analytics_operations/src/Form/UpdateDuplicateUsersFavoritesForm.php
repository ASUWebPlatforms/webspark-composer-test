<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\analytics_operations\UpdateDuplicateUsersFavorites;

class UpdateDuplicateUsersFavoritesForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_duplicate_users_favorites_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will reassign the Favorites for the given users.'),
    ];
    $form['flag'] = [
      '#type' => 'select',
      '#title' => $this->t('Favorite Type'),
      '#options' => [
        'favorites' => 'Favorite Content',
        'favorite_group' => 'Favorite Group',
        // 'favorite_terms' => 'Favorite Term',
      ],
      '#required' => true,
    ];
    $form['uid'] = [
      '#type' => 'number',
      '#title' => $this->t('User ID'),
      '#min' => 2,
      '#required' => true,
    ];
    $form['eids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Entity IDs'),
      '#description' => $this->t('Comma separated list of IDs.'),
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
    $flag = $form_state->getValue('flag');
    $uid = $form_state->getValue('uid');
    $eids = array_map('trim', explode(',', $form_state->getValue('eids')));
    $batch = $form_state->getValue('batch_size');

    UpdateDuplicateUsersFavorites::batchInit($flag, $uid, $eids, $batch);
  }
}
