<?php

namespace Drupal\asu_graduate_faculty\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return [
      'asu_graduate_faculty.settings',
    ];
  }

  public function getFormId() {
    return 'asu_graduate_faculty_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_graduate_faculty.settings');

    $form['content'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Content Text'),
      '#default_value' => $config->get('content'),
      '#format' => 'full_html',
    ];

    // Common fields for title and description
    $fields = [
      'name' => $this->t('Name'),
      'phd_program' => $this->t('PhD Program'),
      'category' => $this->t('Category'),
    ];

    foreach ($fields as $key => $label) {
      $form[$key . '_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('@label Search Title', ['@label' => $label]),
        '#default_value' => $config->get($key . '_title'),
      ];

      $form[$key . '_description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('@label Search Description', ['@label' => $label]),
        '#default_value' => $config->get($key . '_description'),
      ];
      
    }

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('asu_graduate_faculty.settings');
    
    // Save configuration for each field
    $fields = ['name', 'phd_program', 'category'];
    foreach ($fields as $key) {
      $config->set($key . '_title', $form_state->getValue($key . '_title'))
             ->set($key . '_description', $form_state->getValue($key . '_description'));
    }
    $content = $form_state->getValue('content');
    $config->set('content', $content['value']);
    $config->save();

    parent::submitForm($form, $form_state);
  }
}
