<?php
namespace Drupal\aventri_event_embed\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Class AventriConfigForm.
*/
class AventriConfigForm extends ConfigFormBase {
 /**
  * {@inheritdoc}
  */
protected function getEditableConfigNames() { return [
     'aventri_event_embed.config',
   ];}
   /**
    * {@inheritdoc}
    */
  public function getFormId() { return 'aventri_config_form';
  }
   /**
    * {@inheritdoc}
    */
  public function buildForm(array $form, FormStateInterface $form_state) {
     $config = $this->config('aventri_event_embed.config');
     $form['account'] = [
       '#type' => 'textfield',
       '#title' => $this->t('Account ID'),
       '#default_value' => $config->get('account'),
       '#maxlength' => NULL,
       '#size' => 60,
       '#maxlength' => 60,
  ];
     return parent::buildForm($form, $form_state);
   }
   /**
    * {@inheritdoc}
    */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     parent::submitForm($form, $form_state);
     $this->config('aventri_event_embed.config')
       ->set('account', $form_state->getValue('account'))
       ->save();
  } }