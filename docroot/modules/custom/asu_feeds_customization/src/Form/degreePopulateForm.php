<?php

/**
 *@file
 *contains \Drupal\asu_feeds_customization\Form\degreePopulateForm
 **/

 namespace Drupal\asu_feeds_customization\Form;
 
 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class degreePopulateForm extends FormBase{
   
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         
		  $form['actions'] = [
			  '#type' => 'actions',
		  ];
         
         $form['actions']['ugrad_degree_refresh'] = [
			'#type' => 'submit',
			'#value' => $this->t('Populate/refresh asu UNDERGRAD degrees table'),
			'#submit' => ['::ugradFormSubmit'],
		];
		 $form['actions']['grad_degree_refresh'] = [
			'#type' => 'submit',
			'#value' => $this->t('Populate/refresh ASU GRAD degrees table'),
			'#submit' => ['::gradFormSubmit'],
		];
		$form['actions']['terms_data'] = [
		  '#type' => 'submit',
		  '#value' => $this->t('Populate/refresh ASU campuses'),
		  '#submit' => ['::campusFormSubmit'],
		]; 
		return $form;
   }
	 
  public function getFormId() {
      return 'asu_feeds_customization_form';
  }
	 
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * This would normally be replaced by code that actually does something
     * with the title.
     */
  }
   
    /**
     * Implements submit callback for Undergrad refresh button.
     *
     * @param array $form
     *   Form render array.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   Current state of the form.
     */
    public function ugradFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();
		
		$url = 'https://api.myasuplat-dpl.asu.edu/api/codeset/colleges';

        // Get JSON data from the URL
        $jsonData = file_get_contents($url);// Decode JSON data into a PHP associative array
       
        if ($jsonData === false) {
            // Handle error
            echo "Failed to fetch data from the API.";
        } 
        
       
        $data = json_decode($jsonData, true);
         if ($data === null) {
            // Handle error
            echo "Error decoding JSON data";
        } else {
            // JSON data is successfully retrieved and decoded
            // Process and use the $data array as needed
            // Display the array
       // ksm($data);
	   foreach($data as $colData){
            $college[$colData['acadOrgCode']] = $colData['acadOrgCode'];
        }
	   
		 $batch = [
     		'title' => t('Inserting undergrad degrees...'),
			 'operations' => [],
			 'finished' => '\Drupal\asu_feeds_customization\batchUndergradDegrees::undergradDegreesFinishedCallback',
   		 ]; 
		
		foreach($college as $eachCollege => $eachCollegeValue){
			$batch['operations'][] = ['\Drupal\asu_feeds_customization\batchUndergradDegrees::undergradDegreesData',[$eachCollege]];
		}
 		
		batch_set($batch);
		 }
       
    }

	/** Batch process submit function for grad degrees **/  
	  public function gradFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();
		
		$url = 'https://api.myasuplat-dpl.asu.edu/api/codeset/colleges';

        // Get JSON data from the URL
        $jsonData = file_get_contents($url);// Decode JSON data into a PHP associative array
       
        if ($jsonData === false) {
            // Handle error
            echo "Failed to fetch data from the API.";
        } 
        
       
        $data = json_decode($jsonData, true);
         if ($data === null) {
            // Handle error
            echo "Error decoding JSON data";
        } else {
            // JSON data is successfully retrieved and decoded
            // Process and use the $data array as needed
            // Display the array
        }
	   foreach($data as $colData){
            $college[$colData['acadOrgCode']] = $colData['acadOrgCode'];
        }
	   
		 $batch = [
     		'title' => t('Inserting grad degrees...'),
			 'operations' => [],
			 'finished' => '\Drupal\asu_feeds_customization\batchUndergradDegrees::undergradDegreesFinishedCallback',
   		 ]; 
		
		foreach($college as $eachCollege => $eachCollegeValue){
			$batch['operations'][] = ['\Drupal\asu_feeds_customization\batchUndergradDegrees::gradDegreesData',[$eachCollege]];
		}
 		
		batch_set($batch);
       
    }
	 
	/** Batch process submit function for ASU campuses **/  
	  public function campusFormSubmit(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();
		
		
	   
		 $batch = [
     		'title' => t('Inserting grad degrees...'),
			 'operations' => [],
			 'finished' => '\Drupal\asu_feeds_customization\batchUndergradDegrees::undergradDegreesFinishedCallback',
   		 ]; 
		 $url = 'https://api.myasuplat-dpl.asu.edu/api/codeset/campuses';
		 $batch['operations'][] = ['\Drupal\asu_feeds_customization\batchUndergradDegrees::campusData',[$url]];
 		
		batch_set($batch);
       
    } 
     
    
      
  

 }