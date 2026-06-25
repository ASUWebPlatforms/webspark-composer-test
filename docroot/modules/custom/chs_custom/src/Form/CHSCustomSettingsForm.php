<?php

/**  
 * @file  
 * Contains Drupal\chs_custom\Form\CHSCustomSettingsForm.  
 */  

namespace Drupal\chs_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class chsCustomSettingsForm extends ConfigFormBase {

    public function getFormId() {
        return 'chscustom_config_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('chs_custom.settings');  // store data in custom.settings

    $form = parent::buildForm($form, $form_state);

    $form['hero_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('Placeholder - For future use'),
      '#default_value' => $config->get('hero_title'),  
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('chs_custom.settings');
    $config->set('hero_title', $form_state->getValue('hero_title')); 
    $config->save(); // save data in custom.settings

    return parent::submitForm($form, $form_state);

  }

  protected function getEditableConfigNames() {
    return ['chs_custom.settings'];
  }

}