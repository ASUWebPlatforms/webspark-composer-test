<?php

namespace Drupal\asu_masterform_posting\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Link;
use Drupal\node\NodeViewBuilder;

/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Submit the form submissions to master form",
 *   label = @Translation("Submit the form submissions to master form"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Submit the form submissions to master form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class individualFormMasterPostingWebformHandler extends WebformHandlerBase {

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * {@inheritdoc}
     */

    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
	//function asu_masterform_posting_webform_handler_invoke_post_save_alter(\Drupal\webform\Plugin\WebformHandlerInterface $handler, array &$args) {
		/** Get masterform field settings to see if capacity is calcuted in nodes or webform **/
		$config = \Drupal::config('asu_masterform_posting.fields_admin_settings');
		
		
        /** Get webform submission values and current wenform id**/
		$webform = $this->getWebform();
        $web_id = $webform->id();
		$values =  $webform_submission->getData();
		//ksm($values);
		$check_capacity = $config->get($web_id.'-capacity_check');
		$check_capacity_explode = explode('-',$check_capacity);
		
		$form_object = $form_state->getFormObject();
		$webform_submission->setSticky(!$webform_submission->isSticky())->save();
       	$sid = $webform_submission->id();
		
		
		// declare the form variables array matching master form field keys
//		$variables_array = array('first_name','middle_name','last_name','street_address','	street_address_2','city','state','country','zipcode','phone','email_address','dob','semester_expected_to_attend_asu','question','academic_interest','campus','college','major_program','high_school','institution','webform_url','event_id','dietary_restrictions','menu_preference_of_first_guest','menu_preference_of_second_guest','special_needs','parent_email','number_of_guests','asurite','form_url','event_name','parking_info','event_date','event_type','attendee_id','event_capacity','event_capacity_form','event_node_id','event_start_date','event_start_time','event_location','event_end_date','event_end_time','opportunityType','service_type'); // Modified by Chizuko on 2/20/2025.
      
		$variables_array = array('first_name','middle_name','last_name','street_address','	street_address_2','city','state','country','zipcode','phone','email_address','dob','semester_expected_to_attend_asu','question','academic_interest','campus','college','major_program','high_school','institution','webform_url','event_id','dietary_restrictions','menu_preference_of_first_guest','menu_preference_of_second_guest','special_needs','parent_email','parent_email_2','number_of_guests','asurite','form_url','event_name','parking_info','event_date','event_type','attendee_id','event_capacity','event_capacity_form','event_node_id','event_start_date','event_start_time','event_location','event_end_date','event_end_time','opportunityType','service_type'); // Modified by Chizuko on 3/18/2025.
		
		
		foreach($variables_array as $variable){
			//ksm($variable);
			$web[$variable] = $web_id.'-'.$variable;
		   
			$nweb[$variable] = $config->get($web[$variable],'');
			//$data_set = $config->get($web[$variable],'');
			
		}
		//ksm($nweb);
		// Match current webform form key with master form settings form key
		foreach($nweb as $key => $var_value){
			if(in_array($key, $variables_array)){
				
				if($var_value != "0"){
					$new_key = $var_value;
					$data_new[$key] = $new_key;
				}
				
			}
			
		}
		//ksm($data_new);
		foreach($data_new as $field_key => $mkey){
			//$new_submission[$field_key] = $values[$mkey];
			$new_submission[$field_key] = !empty($values[$mkey]) ? $values[$mkey] : '';
		}
		//ksm($new_submission);
//		$node_id = intval($values['nid']); // Changed it to the following in order not to get php warning, but it doesn't look like $node_id is being used in the code. 4/7/2025.
    $node_id = isset($values['nid']) ? intval($values['nid']) : '';
		$form_node_id = isset($values['event_node_id']) ? $values['event_node_id'] : ''; // Added trinary operator to check to see if $values['event_node_id'] is set on 1/17/2025 by Chizuko
		$guests = $new_submission['number_of_guests'];
//		if($check_capacity_explode[1] == "nodesOnly"){ // Changed on 3/24/2025. 
    if (!empty($check_capacity_explode[1]) && $check_capacity_explode[1] == "nodesOnly") {
			$capacity = intval($new_submission['event_capacity']);
		}
//		if($check_capacity_explode[1] == "formOnly"){ // Changed on 3/24/2025.
		if(!empty($check_capacity_explode[1]) && $check_capacity_explode[1] == "formOnly"){
			$capacity = intval($config->get($web_id.'-event_capacity_form'));
		}
		//ksm($capacity);
		$event_id = $new_submission['event_id'];
		$event_nid = !empty($new_submission['event_node_id'])?$new_submission['event_node_id']:$form_node_id;
		//ksm($event_nid);
		$attendee_id = $event_nid.'-'.$sid;
		$new_submission['attendee_id'] = $attendee_id;
		$new_submission['number_of_guests'] = intval($guests);
//		$new_submission['phone'] = preg_replace('[\D]', '', $new_submission['phone']); // Changed on 3/24/2025.
    if (!empty($new_submission['phone'])) {
      $new_submission['phone'] = preg_replace('/\D/', '', $new_submission['phone']);
    }      
		
		//update node spots left field value by querying database
		$database = \Drupal::database();
		$query = $database->select('webform_submission_data', 'wsd');
        $query->fields('wsd', ['sid','value']);
		$query->condition('wsd.name','nid','=');
		$query->condition('wsd.value', $event_nid,'=');
		$query->condition('wsd.webform_id',$web_id,'=');
		$result = $query->execute();
		foreach($result as $rdata){
			$sub_id[$rdata->sid] = $rdata->sid;
		}
		//ksm($sub_id);
    $submission_count = 0; // Added on 3/24/2025
		if(!empty($sub_id)){
			$submission_count = count($sub_id); //count total submissions
		
			$guestquery = $database->select('webform_submission_data', 'wsda');
			$guestquery->fields('wsda',['value']);
			$guestquery->condition('wsda.name',$nweb['number_of_guests'],'=');
			$guestquery->condition('wsda.sid', $sub_id,'IN');
			$guestquery->condition('wsda.webform_id',$web_id,'=');
			$guestresult = $guestquery->execute();

			foreach($guestresult as $gkey=>$guestdata){
				$guestCount[$gkey] = $guestdata->value;
			}
			if(sizeof($guestCount) > 1){
				$guestCountValue = intval(array_sum($guestCount));
			}
			else{
				$guestCountValue = intval($guestCount);
			}
			//ksm('gc1',$guestCountValue);
		}
		else{
			$guestCountValue = 0;
		}
		//ksm('gc2',$guestCountValue);
		
		$submission_guest_value = $submission_count + $guestCountValue;
		//ksm('sgv',$submission_guest_value);
    if(isset($capacity)) { // Added if on 3/24/2025
  		$spots_left = intval($capacity) - $submission_guest_value;   
      //ksm('sl',$spots_left);
      $new_submission['event_capacity'] = intval($capacity);
      $new_submission['capacity'] = intval($capacity);
    }      
		//update remaining spots field in the node if capacity is from node
		//ksm('cc',$check_capacity_explode[1]);
//		if($check_capacity_explode[1] == "nodesOnly"){ // Changed on 3/24/2025.
		if(!empty($check_capacity_explode[1]) && $check_capacity_explode[1] == "nodesOnly"){
			//ksm($event_nid);
			$node_data = Node::load(intval($event_nid));
			$node_data->set('field_remaining_spots', $spots_left);
			$node_data->set('field_total_registrants', $submission_guest_value);
			$node_data->save();
			
		}
		
		//update remaining spots field in the masterform field mappings if capacity is from wbform
//		if($check_capacity_explode[1] == "formOnly"){ // Changed on 3/24/2025.
		if(!empty($check_capacity_explode[1]) && $check_capacity_explode[1] == "formOnly"){
			//$config->set($web_id.'-remaining_spots',$spots_left)->save();
			$rem_spots = $web_id.'-remaining_spots';
			$config_factory = \Drupal::configFactory();
			//$config_factory->getEditable('asu_masterform_fields.settings')->set($rem_spots, $spots_left)->save();
			$config_factory->getEditable('asu_masterform_posting.fields_admin_settings')->set($rem_spots, $spots_left)->save();
			//close webform if capacity is reached
			if($spots_left <= 0){
				$webform = \Drupal::entityTypeManager()->getStorage('webform')->load($web_id);
				// Only disable the webform if its currently enabled
				
				if ($webform->status() === true) {
					$webform->setStatus('closed');
					$webform->save();
				}
			}
			else{
				if ($webform->status() === false) {
					$webform->setStatus('open');
					$webform->save();
				}
			}
		}
//		ksm($new_submission);
		//update attendee_id in sundevil sendoff form
		//$sundevil_sendoff_form = Webform::load($sid);
		$webform_submission->setElementData('attendee_id', $attendee_id); 
		$webform_submission->save();
		
		//create new master form submsiion with above data
		$master_webform_id = 'master_form';
		$webform = Webform::load($master_webform_id);
		// Create webform submission.
		$master_values = [
		  'webform_id' => $master_webform_id,
		  'data' => $new_submission
		];

		/** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
		$master_webform_submission = WebformSubmission::create($master_values);
		$master_webform_submission->save();
		
		
		
	}
}