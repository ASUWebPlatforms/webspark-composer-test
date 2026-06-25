<?php

/**  
 * @file  
 * Contains Drupal\leadershipcustom\Form\LeadershipcustomSettingsForm.  
 */  

namespace Drupal\leadership_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LeadershipcustomSettingsForm extends ConfigFormBase {

    public function getFormId() {
        return 'leadershipcustom_config_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('leadership_custom.settings');  // store data in custom.settings

    $form = parent::buildForm($form, $form_state);

    $form['hero_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hero Title'),
      '#description' => $this->t('Add the title to appear in the hero'),
      '#default_value' => $config->get('hero_title'),  
    ];

    $form['hero_block_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Hero Body'),
      '#format' => $config->get('hero_block_body.format'),
      '#description' => $this->t('Add the body copy and images for the hero'),
      '#default_value' => $config->get('hero_block_body'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('leadership_custom.settings');
    $config->set('hero_title', $form_state->getValue('hero_title')); 
    $config->set('hero_block_body', $form_state->getValue('hero_block_body')); 
    $config->save(); // save data in custom.settings

    return parent::submitForm($form, $form_state);

  }

  protected function getEditableConfigNames() {
    return ['leadership_custom.settings'];
  }

}