<?php

/**
 *@file
 *contains \Drupal\asu_masterform_postin\Form\MasterAdminContent
 **/

 namespace Drupal\asu_masterform_posting\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Veterans survey 
  */
 
 class MasterAdminContent extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_masterform_posting_admin_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_masterform_posting.admin_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
          $config = $this->config('asu_masterform_posting.admin_settings');
		 
		  $form['webform_form_nid'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter node ids of webform node that will be posted to master form.',
                '#maxlength' => 1000,
			    '#description' => t("Enter the node id of the webforms you wish to submit to talisma. If you have more than one node, separate them by comma without spaces"),
                '#default_value' => $config->get('webform_form_nid'),
                       
         );
		 
		 
		 
		
         
        return parent::buildForm($form, $form_state);
     }
     
      /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
         parent::submitForm($form, $form_state);
		 $values =  $form_state->getValues();
		 foreach($values as $key => $each_value){
			 $this->config('asu_masterform_posting.admin_settings')
				 ->set($key, $each_value)
				 ->save();
		 } 
		 
      }
      
  

 }