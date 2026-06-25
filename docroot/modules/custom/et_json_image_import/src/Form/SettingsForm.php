<?php

declare(strict_types=1);

namespace Drupal\et_json_image_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\Exception\FileException;

/**
 * Configure Json Image Media Import settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'et_json_image_import_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['et_json_image_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['json_file_upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload custom file'),
      '#description' => $this->t('Upload a json file of images and alt text scraped from WP site.'),
      '#upload_location' => 'public://json',
      '#default_value' => $this->config('et_json_image_import.settings')->get('json_file_upload'),
      '#required' => TRUE,
      '#upload_validators' => [
        'FileExtension' => ['json'],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $file_id_array = $form_state->getValue('json_file_upload');

    if (!empty($file_id_array) && isset($file_id_array[0])) {
      $file_id = $file_id_array[0];
      // Load the file entity.
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);

      if ($file) {
        $new_uri = "public://json/et_json_image_import.json";
        try {
          $new_filename = "et_json_image_import.json";
          $fileSystem = \Drupal::service('file_system');
          $renamed_uri = $fileSystem->move($file->getFileUri(), $new_uri, FileExists::Replace);

          if ($renamed_uri) {
            // Update the file entity with the new URI and filename in the database.
            $file->setFilename($new_filename);
            $file->setFileUri($renamed_uri);
            // Set the file status to permanent.
            $file->setPermanent();
            $file->save();
            $this->messenger()->addStatus($this->t('File successfully renamed to %name.', ['%name' => $new_filename]));
          }
        }
        catch (\Exception $e) {
          $this->messenger()->addError($this->t('Failed to rename the file.'));
          \Drupal::logger('my_module')->error($e->getMessage());
        }
      }
    }

    parent::submitForm($form, $form_state);
    $this->config('et_json_image_import.settings')
      ->set('json_file_upload', $file_id_array)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
