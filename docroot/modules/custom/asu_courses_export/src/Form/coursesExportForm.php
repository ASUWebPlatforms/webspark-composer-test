<?php

/**
 *@file
 *contains \Drupal\asu_customization\Form\courseEmailSettingsForm
 **/

 namespace Drupal\asu_courses_export\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;
 use Drupal\Core\Database\Database;
use Drupal\Core\Batch\BatchBuilder;
 use Drupal\node\Entity\Node;
 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class coursesExportForm extends FormBase{

  public function getFormId() {
    return 'courses_export_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => $this->t('<p>Choose an export option below.</p>'),
    ];
	  
	$form['state_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose AZ or other states courses'),
      '#options' => [
        'AZ' => $this->t('Arizona'),
        'other' => $this->t('Non Arizona courses'),
      ],
      '#default_value' => 'AZ', // Default selected value
    ];  

    $form['export_approved_courses'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Approved courses'),
      '#submit' => ['::submitApproved'],
	  '#attributes' => [
    	'class' => ['export-download'], 
  	  ],	
    ];

    $form['export_denied_courses'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Denied courses'),
      '#submit' => ['::submitDenied'],
      '#attributes' => [
    	'class' => ['export-download'], 
  	  ],		
    ];

    $form['export_deferred_courses'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Deferred courses'),
      '#submit' => ['::submitDeferred'],
	  '#attributes' => [
    	'class' => ['export-download'], 
  	  ], 
    ];
	  
	 $form['export_pending_courses'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Pending courses'),
      '#submit' => ['::submitPending'],
	  '#attributes' => [
    	'class' => ['export-download'], 
  	  ],
    ];  
	  
	
    return $form;
  }

    
      /*
     **{@inheritdoc}
	 ***Submit function to call batch process to download approved courses **
     */
     public function submitApproved(array &$form, FormStateInterface $form_state) {
		 
		//get state value forn the dropdown page
		$state_value = $form_state->getValue('state_field'); 
		
		if($state_value == 'AZ'){
			$condition = '=';
		}
		else{
			$condition = '<>';
		}
		$connection = Database::getConnection();

		$query = $connection->select('node_field_data', 'n');
		$query->leftJoin('node__field_approved_field', 'nfa','nfa.entity_id = n.nid');
		$query->leftJoin('node__field_high_school', 'nfhi','nfhi.entity_id = n.nid');
		$query->leftJoin('node__field_district', 'nfd','nfd.entity_id = n.nid');	
		$query->leftJoin('node__field_school_city', 'nfsc','nfsc.entity_id = n.nid');
		$query->leftJoin('node__field_school_state', 'nfst','nfst.entity_id = n.nid');	
		$query->fields('n', ['nid', 'title', 'created']);
		$query->fields('nfhi',['field_high_school_value']);
		$query->fields('nfd',['field_district_value']);
		$query->fields('nfsc',['field_school_city_value']);
		$query->fields('nfst',['field_school_state_value']);  
		$query->condition('n.type', 'course_competency_new');
		$query->condition('n.status', 1);
		$query->condition('nfa.field_approved_field_value','Yes','=') ;
		$query->condition('nfst.field_school_state_value','AZ',$condition);
		$query->orderBy('n.created', 'DESC');
		//$query->range(0, 500);   
		// Join the body field table
		
		$results = $query->execute()->fetchAllAssoc('nid');
        //ksm($results);
		
        $course_status = 'approved';
		$this->runBatch($results, $course_status, $state_value, 'Exporting '.$course_status.' courses...');
	 }
	 
	 /***Submit function to call batch process to download denied courses **/
	 public function submitDenied(array &$form, FormStateInterface $form_state) {
		
		$connection = Database::getConnection();
		 //get state value forn the dropdown page
		$state_value = $form_state->getValue('state_field'); 
		
		if($state_value == 'AZ'){
			$condition = '=';
		}
		else{
			$condition = '<>';
		}

		$query = $connection->select('node_field_data', 'n');
		$query->leftJoin('node__field_denied', 'nfdenied','nfdenied.entity_id = n.nid');
		$query->leftJoin('node__field_high_school', 'nfhi','nfhi.entity_id = n.nid');
		$query->leftJoin('node__field_district', 'nfd','nfd.entity_id = n.nid');	
		$query->leftJoin('node__field_school_city', 'nfsc','nfsc.entity_id = n.nid');
		$query->leftJoin('node__field_school_state', 'nfst','nfst.entity_id = n.nid');	
		$query->fields('n', ['nid', 'title', 'created']);
		$query->fields('nfhi',['field_high_school_value']);
		$query->fields('nfd',['field_district_value']);
		$query->fields('nfsc',['field_school_city_value']);
		$query->fields('nfst',['field_school_state_value']);   
		$query->condition('n.type', 'course_competency_new');
		$query->condition('n.status', 1);
		$query->condition('nfdenied.field_denied_value','Yes','='); 
		$query->condition('nfst.field_school_state_value','AZ',$condition);
		$query->orderBy('n.created', 'DESC');

		// Join the body field table
		
		$nids = $query->execute()->fetchAllAssoc('nid');
        $course_status = 'denied';
		
  	// \Drupal::logger('result_data')->info('<pre>' . print_r($nids, TRUE) . '</pre>');
		$this->runBatch($nids, $course_status, $state_value, 'Exporting '.$course_status.' courses...');
	 }
	 
	  
	 /***Submit function to call batch process to download deferred courses **/ 
	 public function submitDeferred(array &$form, FormStateInterface $form_state) {
		
		$connection = Database::getConnection();
		 //get state value forn the dropdown page
		$state_value = $form_state->getValue('state_field'); 
		
		if($state_value == 'AZ'){
			$condition = '=';
		}
		else{
			$condition = '<>';
		}

		$query = $connection->select('node_field_data', 'n');
		$query->leftJoin('node__field_deferred', 'nfdef','nfdef.entity_id = n.nid');
		$query->leftJoin('node__field_high_school', 'nfhi','nfhi.entity_id = n.nid');
		$query->leftJoin('node__field_district', 'nfd','nfd.entity_id = n.nid');	
		$query->leftJoin('node__field_school_city', 'nfsc','nfsc.entity_id = n.nid');
		$query->leftJoin('node__field_school_state', 'nfst','nfst.entity_id = n.nid');	
		$query->fields('n', ['nid', 'title', 'created']);
		$query->fields('nfhi',['field_high_school_value']);
		$query->fields('nfd',['field_district_value']);
		$query->fields('nfsc',['field_school_city_value']);
		$query->fields('nfst',['field_school_state_value']);   
		$query->condition('n.type', 'course_competency_new');
		$query->condition('n.status', 1);
		$query->condition('nfdef.field_deferred_value','Yes','='); 
		$query->condition('nfst.field_school_state_value','AZ',$condition);
		$query->orderBy('n.created', 'DESC');

		// Join the body field table
		
		$nids = $query->execute()->fetchAllAssoc('nid');
        $course_status = 'deferred';
  	// \Drupal::logger('result_data')->info('<pre>' . print_r($nids, TRUE) . '</pre>');
		$this->runBatch($nids, $course_status, $state_value, 'Exporting '.$course_status.' courses...');
	 }
	 
	 /***Submit function to call batch process to download pending courses **/
	  public function submitPending(array &$form, FormStateInterface $form_state) {
		
		$connection = Database::getConnection();
		  //get state value forn the dropdown page
		$state_value = $form_state->getValue('state_field'); 
		
		if($state_value == 'AZ'){
			$condition = '=';
		}
		else{
			$condition = '<>';
		}

		$query = $connection->select('node_field_data', 'n');
		$query->leftJoin('node__field_deferred', 'nfdef','nfdef.entity_id = n.nid');
		$query->leftJoin('node__field_approved_field', 'nfa','nfa.entity_id = n.nid');
		$query->leftJoin('node__field_denied', 'nfdenied','nfdenied.entity_id = n.nid');  
		$query->leftJoin('node__field_high_school', 'nfhi','nfhi.entity_id = n.nid');
		$query->leftJoin('node__field_district', 'nfd','nfd.entity_id = n.nid');	
		$query->leftJoin('node__field_school_city', 'nfsc','nfsc.entity_id = n.nid');
		$query->leftJoin('node__field_school_state', 'nfst','nfst.entity_id = n.nid');	
		$query->fields('n', ['nid', 'title', 'created']);
		$query->fields('nfhi',['field_high_school_value']);
		$query->fields('nfd',['field_district_value']);
		$query->fields('nfsc',['field_school_city_value']);
		$query->fields('nfst',['field_school_state_value']);  
		$query->condition('n.type', 'course_competency_new');
		$query->condition('n.status', 1);
		$query->condition('nfdef.field_deferred_value',NULL, 'IS NULL'); 
		$query->condition('nfdenied.field_denied_value',NULL, 'IS NULL');  
		$query->condition('nfa.field_approved_field_value',NULL, 'IS NULL') ;  
		$query->condition('nfst.field_school_state_value','AZ',$condition);
		$query->orderBy('n.created', 'DESC');

		// Join the body field table
		
		$nids = $query->execute()->fetchAllAssoc('nid');
		// ksm($nids); 
		 
        $course_status = 'pending';
  	// \Drupal::logger('result_data')->info('<pre>' . print_r($nids, TRUE) . '</pre>');
		$this->runBatch($nids, $course_status, $state_value, 'Exporting '.$course_status.' courses...');
	 }
	 
	 public function submitForm(array &$form, FormStateInterface $form_state) {
  		// No default submission logic needed.
	 }
	 
	 /** Batch process function **/
	 private function runBatch(array $nids, string $course_status, string $state_value, string $title) {
		  $operations = [];
		  foreach (array_chunk($nids,50) as $chunk) {
			$operations[] = [
			  [\Drupal\asu_courses_export\ExportBatch::class, 'processChunk'],
			  [$chunk, $course_status, $state_value],
			];
		  }

		  $batch = [
			'title' => $this->t($title),
			'operations' => $operations,
			'finished' => [\Drupal\asu_courses_export\ExportBatch::class, 'finishBatch'],
		  ];
		 
		  batch_set($batch);
		 
   }
	 
 }