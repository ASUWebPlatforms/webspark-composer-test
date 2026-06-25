<?php

namespace Drupal\asu_format_advising\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FASettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_format_advising.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asu_format_advising_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_format_advising.settings');

    $form['introduction'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Introduction'),
      '#default_value' => $config->get('introduction.value'),
      '#format' => 'full_html',
    ];

    $form['aside_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Aside Help Text'),
      '#default_value' => $config->get('aside_text.value'),
      '#format' => 'full_html',
    ];

    $form['create_document'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Create Document: Word'),
      '#default_value' => $config->get('create_document.value'),
      '#format' => 'full_html',
    ];

    $form['create_document_latex'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Create Document: LaTex'),
      '#default_value' => $config->get('create_document_latex.value'),
      '#format' => 'full_html',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('asu_format_advising.settings');

    $config->set('introduction', $form_state->getValue('introduction'));
    $config->set('aside_text', $form_state->getValue('aside_text'));
    $config->set('create_document', $form_state->getValue('create_document'));
    $config->set('create_document_latex', $form_state->getValue('create_document_latex'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
