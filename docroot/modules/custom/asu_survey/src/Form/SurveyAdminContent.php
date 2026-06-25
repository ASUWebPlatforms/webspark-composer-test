<?php

/**
 *@file
 *contains \Drupal\asu_survey\Form\SurveyAdminContent
 **/

 namespace Drupal\asu_survey\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Veterans survey 
  */
 
 class SurveyAdminContent extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_survey_admin_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_survey.admin_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_survey.admin_settings');
		 
		  $form['survey_form_nid'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter node id of survey node in which survey webform is added',
                '#maxlength' => 100,
                '#default_value' => $config->get('survey_form_nid'),
                       
         );
		 
		 
		 $form['survey_confirm_0'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter node id of survey confimration first page',
                '#maxlength' => 100,
                '#default_value' => $config->get('survey_confirm_0'),
                       
         );
		 
		 
		 $form['email_content'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter email content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('email_content'),
                '#format' => 'full_html',       
        );
		 
		 $form['email_content'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter full email content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('email_content'),
                '#format' => 'full_html',       
        );
		 
		 $form['undecided_email_content'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter undecided top email content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('undecided_email_content'),
                '#format' => 'full_html',       
        );
		 
		  $form['decided_email_content'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter decided top email content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('decided_email_content'),
                '#format' => 'full_html',       
        );
		 
		$form['survey_source_id'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter source id of the middleware',
                '#maxlength' => 100,
                '#default_value' => $config->get('survey_source_id'),
                       
        ); 
		
		$form['survey_middleware_url'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter middleware posting URL',
                '#maxlength' => 1000,
                '#default_value' => $config->get('survey_middleware_url'),
                       
        ); 
		 
		
         
        return parent::buildForm($form, $form_state);
     }
     
      /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
		 //ksm($form_state->getValues()); 
		 $values =  $form_state->getValues();
		 foreach($values as $key => $each_value){
			 $this->config('asu_survey.admin_settings')
				 ->set($key, $each_value)
				 ->save();
		 } 
		 
      }
      
  

 }