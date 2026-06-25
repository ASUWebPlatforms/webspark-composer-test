<?php

namespace Drupal\analytics_operations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\analytics_operations\UpdateGroupRelationship;

class UpdateGroupRelationshipForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_group_relationship_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    // Fetch all content types
    $bundles = NodeType::loadMultiple();
    $bundleOptions = [];
    foreach ($bundles as $bundle) {
      $bundleOptions[$bundle->id()] = $bundle->label();
    }

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will update group relationships for the given nodes.<br><strong>Use this to remove group content, or to move content from one group to another</strong>.'),
    ];
    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $bundleOptions,
      '#required' => true,
    ];
    $form['operation'] = [
      '#type' => 'select',
      '#title' => $this->t('Operation to perform'),
      '#options' => [
        'delete'=> 'Remove content from a group',
        'transfer'=> 'Move content from one group to another',
      ],
      '#required' => true,
    ];
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node IDs'),
      '#description' => $this->t('Comma separated list of IDs for the operation.'),
      '#required' => true,
    ];
    $form['gid'] = [
      '#type' => 'number',
      '#title' => $this->t('Group ID'),
      '#description' => $this->t('The group the content currently belongs to.'),
      '#min' => 1,
      '#required' => true,
    ];
    $form['new_gid'] = [
      '#type' => 'number',
      '#title' => $this->t('New Group ID'),
      '#description' => $this->t('The group the content should be transfered to.'),
      '#min' => 1,
      '#states' => [
        'enabled' => [
          ':input[name="operation"]' => ['value' => 'transfer'],
        ],
        'required' => [
          ':input[name="operation"]' => ['value' => 'transfer'],
        ]
      ],
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
    $data = [
      'bundle' => $form_state->getValue('bundle'),
      'operation' => $form_state->getValue('operation'),
      'nids' => array_map('trim', explode(',', $form_state->getValue('nids'))),
      'gid' => $form_state->getValue('gid'),
      'new_gid' => $form_state->getValue('new_gid')
    ];

    UpdateGroupRelationship::batchInit($data);
  }
}
