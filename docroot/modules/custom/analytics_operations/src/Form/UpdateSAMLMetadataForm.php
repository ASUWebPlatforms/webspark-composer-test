<?php

namespace Drupal\analytics_operations\Form;

use Drupal\analytics_operations\UpdateSAMLMetadata;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\GuzzleException;

class UpdateSAMLMetadataForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'analytics_operations_update_saml_metadata_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This operation will update the content of the SAML metadata file.'),
    ];
    $form['metadata_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Remote Metadata URL'),
      '#description' => $this->t('Enter the URL where the metadata can be found.'),
      '#required' => true,
      '#default_value' => '',
    ];
    $form['metadata_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Local Metadata File'),
      '#description' => $this->t('Enter the path to the local metadata file to be updated, relative to the Drupal installation.'),
      '#required' => true,
      '#default_value' => '../private/simplesamlphp/metadata/saml20-idp-remote.php',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws GuzzleException
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $metadata = $form_state->getValue('metadata_url');
    $file = $form_state->getValue('metadata_file');

    UpdateSAMLMetadata::init($metadata, $file);
  }
}
