<?php

namespace Drupal\asu_customization\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\RequestException;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Course submission handler",
 *   label = @Translation("Create course competency node after form submission"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Create course competency node after form submission"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class courseWebformHandler extends WebformHandlerBase {

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
	
	 
/*
    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

		
       	$values =  $webform_submission->getData();
       	
       	ksm($values);
		
	}*/
	
	public function preSave(WebformSubmissionInterface $webform_submission) {  
	// public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
        $data = $webform_submission->getData();
		//ksm($data);
		$entered_high_school = array();
		$hs_data = array();
		$hs_data = $data['high_school_1'];
		foreach($hs_data as $key => $result){
			$data_value[] = $result;
		}
		$requested_code = array();
		$data['high_school_1'] = $data_value;
        $webform_submission->setData($data);
		$course_title = $data['course_title'];
		$pre_req = $data['pre_requisite'];
		$virtual = $data['is_this_course_a_virtual_online_course'];
		$requested_code = $data['requested_competency_code'];
		foreach($requested_code as $key => $code_value){
			$request_code[$key]['target_id'] = $code_value;
		}
		
		$us_school = $data['is_this_school_listed_in_us_'];
		$multiple_us_schools = $data['is_this_course_taught_across_multiple_schools_with_your_district'];
		$grade = $data['what_is_the_standard_grade_level_when_this_course_is_taken_'];
		$is_it_us_school = $data['is_this_school_listed_in_us_'];
		$country_us = $data['country'];
		$state = $data['state'];
		$district_drop = $data['district'];
		$district_entered = $data['enter_district'];
		$high_school_1 = $data['high_school_1'];
		$entered_high_school = $data['enter_high_school'];
		$single_hs = $data['us_high_school'];
		$hs_city = $data['us_city'];
		$hs_address = $data['us_address'];
		$zipcode = $data['zipcode'];
		$international_hs = $data['enter_your_high_school_secondary_school_name'];
		$international_country_Drop = $data['taxcountry'];
		$text_entered_country = $data['enter_country'];
		$international_state = $data['state_providence'];
		$international_city = $data['city'];
		$international_address = $data['address'];
		$international_zipcode = $data['zipcode_pincode'];
		$contact_title = $data['contact_title'];
		$first_name = $data['first_name'];
		$last_name = $data['last_name'];
		$phone = $data['contact_phone'];
		$email = $data['e_mail'];
		$course_desc = $data['course_description'];
		$upload_file = $data['upload_file'];
		$hs_name = array();
		$temp_hs = array();
		
		if(!empty($hs_address)){
			$school_address = $hs_address;
		}
		elseif(!empty($international_address)){
			$school_address = $international_address;
		}
		else{
			$school_address = '';
		}
		
		if(!empty($zipcode)){
			$hs_zipcode = $zipcode;
		}
		elseif(!empty($international_zipcode)){
			$hs_zipcode = $zipcode;
		}
		else{
			$hs_zipcode = '';
		}
		
		if(!empty($country_us)){
			$country = $country_us;
		}
		elseif(!empty($text_entered_country)){
			$country = $text_entered_country;
		}
		else{
			$country = '';
		}
		
		//check if high school name or district name was entered manaully. If so, set the node to "Needs approval mode"
		if(!empty($district_entered) || !empty($entered_high_school)){
			$needs_approval1 = "Yes";
			$node_status1 = 0;
		}
		else{
			$needs_approval1 = '';
			$node_status1 = 1;
		}
		
		if(!empty($district_drop)){
			if($district_drop == "Not listed"){
				if(!empty($district_entered)){
					$temp_district = $district_entered;
					$needs_approval2 = "Yes";
					$node_status2 = 0;
				}
				else{
					$temp_district = '';
					$node_status2 = 1;
					$needs_approval2 = '';
					/*$needs_approval = "";
					$node_status = 1;*/
				}
			
			}
			else{
				$selected_district = $district_drop;
				$temp_district = '';
				$needs_approval2 = '';
			}
		}
		
		else{
			$selected_district = '';
			$temp_district = '';
			$needs_approval2 = '';
		}
		
		if(!empty($high_school_1)){
			// unset($high_school_1[array_search( 'Not listed', $high_school_1 )] );
			// $hs_name = $high_school_1;
			if(in_array('Not listed',$high_school_1)){
				 $key = array_search('Not listed', $high_school_1);
				 unset($high_school_1[$key]);
			}
			$hs_name = $high_school_1;
		}
		
		
		if($us_school == "Yes"){
			$country = 352;
		}
		elseif(!empty($international_country_Drop)){
				if($international_country_Drop != "Not listed"){
					$country = $international_country_Drop;
				}
		}
		else{
			$country = '';
		}
		
		if(!empty($text_entered_country)){
			$enter_country = $text_entered_country;
			$needs_approval3 = "Yes";
			$node_status3 = 0;
		}
		else{
			$enter_country = '';
			$needs_approval3 = '';
			$node_status3 = 1;
		}
		
		if(($needs_approval3 == "Yes") || ($needs_approval2 == "Yes") |($needs_approval1 == "Yes")){
			$node_status = 0;
			$appoval_needed = "Yes";
		}
		else{
			$node_status = 1;
			$appoval_needed = "";
		}
		
		if(!empty($entered_high_school)){
			$temp_hs = $entered_high_school;
		}
		else{
			$temp_hs = '';
		}
		
		if(!empty($single_hs)){
			if($single_hs != "Not listed"){
				$hs_name = $single_hs;
			}
			
		}
		if(!empty($international_hs)){
			$hs_name = $international_hs;
		}
		if(!empty($international_state)){
			$submitted_state = $international_state;
		}
		elseif(!empty($state)){
			$submitted_state = $state;
		}
		else{
			$submitted_state = '';
		}
		
		//set value for international course
		if(!empty($international_hs)){
			$international_course = "Yes";
		}
		else{
			$international_course = '';
		}
		
		if(!empty($international_city)){
			$selected_city = $international_city;
		}
		elseif(!empty($hs_city)){
			$selected_city = $hs_city;
		}
		else{
			$selected_city = '';
		}
		
		
		if(!empty($upload_file)){
			foreach($upload_file as $fid){
				$fileid[] = $fid;
				$file = File::load($fid);
				$file->setPermanent();
				$file->save();
			}
		}
		else{
			$fid = '';
		}
		
		$new_course = Node::create(['type' => 'course_competency_new']);
		$new_course->set('title', $course_title);
		$new_course->set('field_course_title', $course_title);
		$new_course->set('field_school_district_address', $school_address);
		$new_course->set('field_upload_syllabus', $fileid);
		$new_course->set('field_first_name', $first_name);
		$new_course->set('field_school_state',$submitted_state);
		$new_course->set('field_school_city',$selected_city);
		$new_course->set('field_school_city_import',$selected_city);
		$new_course->set('field_district',$selected_district);
		$new_course->set('field_district_import',$selected_district);
		$new_course->set('field_high_school',$hs_name);
		$new_course->set('field_online_virtual', $virtual);
		$new_course->set('field_high_school_zip_code',$hs_zipcode);
		$new_course->set('field_last_name',$last_name);
		$new_course->set('field_pre_requisite',$pre_req);
		$new_desc_data = [
            'value' => $course_desc,
            'format' => 'full_html',
        ];
		$new_course->set('field_course_description', $new_desc_data);
		$new_course->set('field_requested_competency_code',$request_code);
		$new_course->set('field_needs_data_approval',$appoval_needed);
		$new_course->set('field_international_school',$international_course);
		$new_course->set('field_temporary_high_school',$temp_hs);
		$new_course->set('field_temporary_district',$temp_district);
		$new_course->set('status',$node_status);
		$new_course->set('field_what_is_the_standard_grade',$grade);
		$new_course->set('field_temporary_country', $enter_country);
		$new_course->set('field_choose_country', $country);
		$new_course->set('field_e_mail',$email);
		$new_course->set('field_contact_phone',$phone);
		$new_course->set('field_contact_title', $contact_title);
		
		$new_course->set('field_is_this_school_listed_in_u', $is_it_us_school);
		$new_course->enforceIsNew();
		$new_course->save();
		//ksm($my_article);
		$new_nid = $new_course->id();
        $this->sendSubmitConfirmEmail($email,$course_title,$first_name, $new_nid);
		$this->sendAdminSubmitConfirmEmail($course_title,$new_nid);
		if($appoval_needed == "Yes"){
			$this->sendApprovalRequiredEmail($course_title,$new_nid);
		}
		//if($appoval_needed == ''){
			$this->dataAction($new_nid);
		//}
		
		
    }
	
	public function sendSubmitConfirmEmail($email,$course_title,$first_name,$nid){
		$emails_array = array();
		$domain = $_SERVER['HTTP_HOST'];
		$title = $course_title;
		$site = "https://$domain";
		$node_link = "$site/node/$nid";
		$bottom_content = "<p>Please allow two to four weeks for the course to be evaluated. You may submit up to seven courses within a two-week period.</p><p>If you have any questions, please email <a href='mailto:courseapproval@asu.edu'>courseapproval@asu.edu</a>.</p><p>Arizona State University | Northern Arizona University | University of Arizona</p>";
		$email_content =   '<table border="0" cellpadding="0" cellspacing="0" class="100p" width="800" style="background: #ffffff; padding: 10px; font-size: 14px;"><tr><td><h2 style="text-align:center;">Dear '.$first_name.',</h2><p><strong>Thank you for submitting a course to <a href="https://courseapproval.asu.edu/">courseapproval.asu.edu</a>. Your course submission has been received.</p></td></tr></table>';
		$email_content .=  "<table border='0' cellpadding='0' cellspacing='0' class='100p' width='800' style='background: #f1f1f1; font-size: 18px; '><table border='0' cellpadding='0' cellspacing='0' class='100p' width='800' style='padding: 10px;'><tr><td><strong>Course name: </strong><a href='$node_link'>$course_title</a></td></tr></table>";
		$email_content .= "<table border='0' cellpadding='0' cellspacing='0' class='100p' width='800' style='padding: 10px;'><tr><td>$bottom_content</td></tr></table></td></tr></table></table></td></tr></table>";
		//ksm($email_content);
		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asu_customization';
		 $key = 'send_course_email';
		 /*$config = \Drupal::config('asu_customization.admin_settings');
		 $admin_email = $config->get('admin_email_ids');
		 $single_admin_emails = explode(',',$admin_email);
		 foreach($single_admin_emails as $ekye => $evalue){
			 $associative_email[$evalue] = $evalue;
		 }
		 $emails_array = $associative_email + array($email => $email);
		 $unique_emails = array_unique($emails_array);
		 $all_emails = implode(',',$unique_emails);*/
		 //ksm($all_emails);
		 $to_email_address = "$email";
		 $params['message'] = $email_content;
		 $params['subject'] = "$course_title has been received";
	     $params['from_address'] = "courseapproval@asu.edu";
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		 $send = true;
	  	 $result = $mailManager->mail($module, $key, $to_email_address, $langcode, $params, NULL, $send);
	     //ksm($result);
	}
	
	public function sendAdminSubmitConfirmEmail($course_title,$nid){
		//ksm($nid);
		$mailManager = \Drupal::service('plugin.manager.mail');
		$domain = $_SERVER['HTTP_HOST'];
		$title = $course_title;
		$site = "https://$domain";
		$node_link = "$site/node/$nid";
		$module = 'asu_customization';
		$key = 'send_course_email';
		$config = \Drupal::config('asu_customization.admin_settings');
		$admin_email = $config->get('admin_email_ids');
		$content = "<p>A new course has been submitted to <a href='https://courseapproval.ws.asu.edu'>courseapproval.asu.edu</a> for approval.</p>";
		$content .=  "<p>Course name: <a href='$node_link'>$title</a></p>";
		$content  .= "<p>Please review this course within two to four weeks.</p>";
		$to_email_address = "$admin_email";
		$params['message'] = $content;
		$params['subject'] = "Course competency submission received";
	    $params['from_address'] = "courseapproval@asu.edu";
		$langcode = \Drupal::currentUser()->getPreferredLangcode();
		$send = true;
	  	$result = $mailManager->mail($module, $key, $to_email_address, $langcode, $params, NULL, $send);
	}
	
	public function sendApprovalRequiredEmail($course_title,$nid){
		$mailManager = \Drupal::service('plugin.manager.mail');
		$domain = $_SERVER['HTTP_HOST'];
		$site = "https://$domain";
		$node_link = "$site/node/$nid";
		$config = \Drupal::config('asu_customization.admin_settings');
		$admin_email = $config->get('admin_email_ids');
		//$admin_email = "avannela@gmail.com";
		$content = "<p>A new course has been submitted with custom highschool/district name <a href='https://courseapproval.asu.edu'>courseapproval.asu.edu</a>, please review and make the edits and save the course.</p>";
		$content .=  "<p>Course name: <a href='$node_link'>$course_title</a></p>";
		$content  .= "<p>Please review this course within two to four weeks.</p>";
		$module = 'asu_customization';
		$key = 'send_course_email';
		$to_email_address = $admin_email;
		$params['message'] = $content;
		$params['subject'] = "$course_title has been submitted for editing";
		$params['from_address'] = "courseapproval@asu.edu";
		$langcode = \Drupal::currentUser()->getPreferredLangcode();
		$send = true;
		$result = $mailManager->mail($module, $key, $to_email_address, $langcode, $params, NULL, $send);
	
  }
	
   public function dataAction($nid){
	 
		
		$node = \Drupal\node\Entity\Node::load(intval($nid));
		$state_check = $node->get('field_school_state');
		//ksm($node);
	    $hs_name = array();
	    $temp_hs = array();
		$state = !empty($state_check) ? $node->field_school_state->value:'';
		$city_check = $node->get('field_school_city_import');
		$city = !empty($city_check) ? $node->get('field_school_city_import')->value:'';
		$hs_name_check = $node->get('field_high_school');
		$hs_name = !empty($hs_name_check) ? $node->get('field_high_school')->getValue():'';
		$district_check = $node->get('field_district');
		$district = !empty($district_check) ? $node->get('field_district')->value:'';
		$state_value = $node->field_school_state->getSetting('allowed_values')[$state]; 
		$temp_hs = $node->get('field_temporary_high_school')->getValue();
	    $hs_data = array_merge($hs_name, $temp_hs);
	    if(!empty($hs_data)){
			$connection = \Drupal::database();
			if(!empty($state)){
				$query = $connection->select('asu_webform_fields_hs', 'awfh')
				->fields('awfh',['hs_state_code','hs_state_name'])
				->condition('hs_name',$name_of_hs['value'],'=')	
				->condition('hs_state_code',$state,'=')
				->execute()
				->fetchAll();
				foreach($query as $results){
					$state_name = $results->hs_state_name;
				}
			}
			else{
				$state_name = '';
			}
			foreach($hs_data as $name_of_hs){
				$un_id = $nid.'-'.$name_of_hs['value'];
				$data = $connection->insert('asu_course_hs_data')
						  ->fields(['hs_id', 'hs_state_code', 'hs_state_name', 'hs_city', 'hs_name', 'hs_district'])
						  ->values([
							'hs_id' => $un_id,
							'hs_state_code' => $state,
							'hs_state_name' => $state_name,
							'hs_city' => $city,
							'hs_name' => $name_of_hs['value'],
							'hs_district' => $district   

						  ])
						  ->execute();
			}
		}
		
   }
}