<?php

/**
 *@file
 *contains \Drupal\asu_contact_webform_custom_options\Form\ContactFormNodes
 **/

 namespace Drupal\asu_contact_webform_custom_options\Form;
 
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 
 
 /**
  *Defines a form to configure contact forms node ids as node ids change for each environment.
  */
 
 class ContactFormNodes extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_contact_webform_custom_options_contact_form_formnid_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_contact_webform_custom_options.contact_form_formnid_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_contact_webform_custom_options.contact_form_formnid_settings');
       
        
        $form['undergrad_contact_form_nid'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter Undergrad form node id',
                '#maxlength' => 10,
                '#default_value' => $config->get('undergrad_contact_form_nid'),
                       
        );
        
        $form['grad_contact_form_nid'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter Graduate form node id',
                '#maxlength' => 10,
                '#default_value' => $config->get('grad_contact_form_nid'),
                       
        );
         
       
         
        
        return parent::buildForm($form, $form_state);
     }
     
 
 
   /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
         parent::submitForm($form, $form_state);
		 $undergrad_nid = $form_state->getValue('undergrad_contact_form_nid');
         $grad_nid = $form_state->getValue('grad_contact_form_nid');
        
          \Drupal::service('config.factory')->getEditable('asu_contact_webform_custom_options.contact_form_formnid_settings')->set('undergrad_contact_form_nid' , $undergrad_nid)->save();
		  \Drupal::service('config.factory')->getEditable('asu_contact_webform_custom_options.contact_form_formnid_settings')->set('grad_contact_form_nid' , $grad_nid)->save();
        
      }
      
 }