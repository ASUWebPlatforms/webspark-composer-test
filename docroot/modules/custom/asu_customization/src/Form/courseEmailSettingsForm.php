<?php

/**
 *@file
 *contains \Drupal\asu_customization\Form\courseEmailSettingsForm
 **/

 namespace Drupal\asu_customization\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class courseEmailSettingsForm extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_customization_admin_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_customization.admin_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_customization.admin_settings');
         
         $form['admin_email_ids'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter email adddresses of admins who recieve approval emails. Separate multiple emails by comma',
                '#maxlength' => 10000,
			    '#size' => 10000, 
                '#default_value' => $config->get('admin_email_ids'),
                    
        );
         /*$form['approved_content'] = array(
			'#type' => 'details',
			'#title' => t('Approved email details'),
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
		 );	
		 
		$form['approved_content']['approved_email_subject'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter email adddresses of admins who recieve approval emails',
                '#maxlength' => 10000,
                '#default_value' => $config->get('approved_email_subject'),
                '#format' => 'full_html',       
        );
		 
		$form['approved_content']['approved_email_content'] =  array(
                '#type' => 'textarea',
                '#title' => 'Enter approved email conent',
                '#maxlength' => 10000,
                '#default_value' => $config->get('approved_email_content'),
                '#format' => 'full_html',       
        ); */
         
        return parent::buildForm($form, $form_state);
     }
     
      /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
		// ksm($form_state->getValues()); 
		 $values =  $form_state->getValues();
		 foreach($values as $key => $each_value){
			 $this->config('asu_customization.admin_settings')
				 ->set($key, $each_value)
				 ->save();
		 } 
		  
		 
		  
		/*$database = \Drupal::database();
		  
		 $hs_fields = $database->select('node__field_old_nid', 'fon')->fields('fon',['entity_id']);
		 $hs_fields->Join('mtha_data', 'md', 'md.nid = fon.field_old_nid_value'); 
		 $ds_query = $hs_fields->execute()->fetchAll();
		 
		 $batch = [
     		'title' => t('Updated MTHA...'),
			 'operations' => [],
			 'finished' => '\Drupal\asu_customization\batchNodesHs::updateCourseHsFinishedCallback',
   		 ]; 
			
		 foreach($ds_query as $results){
		 	//$state_city[$results->entity_id] = $results->field_state_value.'+'.$results->field_city_value;
			$nid = $results->entity_id; 
			$batch['operations'][] = ['\Drupal\asu_customization\batchNodesHs::updateCourseHs', [$nid]];
		 } 
		  
		  batch_set($batch);*/
		
    }
      
  

 }