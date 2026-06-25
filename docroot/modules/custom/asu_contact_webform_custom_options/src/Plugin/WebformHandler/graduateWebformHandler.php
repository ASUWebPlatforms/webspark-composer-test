<?php

namespace Drupal\asu_contact_webform_custom_options\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Graduate contactfrom handler",
 *   label = @Translation("Submit Graduate contact form submissions to SF"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the grad form submissions to SF"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class graduateWebformHandler extends WebformHandlerBase {

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

        /** Get webform submission values **/
		
       	$values =  $webform_submission->getData();
       	
       	
		$question_to_whom = isset($values['who_do_you_have_questions_for_'])?$values['who_do_you_have_questions_for_']:'';
		$question = isset($values['what_s_your_question_'])?$values['what_s_your_question_']:'';
		$country = isset($values['country_of_citizenship'])?$values['country_of_citizenship']:'';
		$first_name = isset($values['first_name'])?$values['first_name']:'';
		$last_name = isset($values['last_name'])?$values['last_name']:'';
		$student_email = isset($values['email'])?$values['email']:'';
		$phone = isset($values['phone_number'])?$values['phone_number']:'';
		$phone_formatted = preg_replace('[\D]', '', $phone);
		$degree_level = isset($values['degree_level'])?$values['degree_level']:'';
		$term = isset($values['start_term'])?$values['start_term']:'';
		$program_of_interest = isset($values['select_program_of_interest'])?$values['select_program_of_interest']:'';
		$which_of_these_apply = $values['which_one_of_these_apply_to_you_'];
		$area_of_interest = isset($values['select_area_of_interest']) && $values['select_area_of_interest'] != 0 ? $values['select_area_of_interest'] : '';

		if($which_of_these_apply == "Military"){
			//$source_id = 119;
			$source_id = '7016T0000020Oe3QAE';
			$military_status = "Veteran";
		}
		else{
			
			$military_status = '';
		}
		
		if($which_of_these_apply == "Online"){
			$campus = "ONLNE";
		}
		else{
			$campus = "GROUND";
		}
		
		if(isset($question_to_whom)){
				if(($question_to_whom == "admissionservices") || ($question_to_whom == "unsure")){
					//$source_id = 'CONTACTUS';
					$source_id = "7016T0000020OdtQAE";
					//$source_id = '7016T000002c8qMQAQ';
				}
				if($question_to_whom == "academicprogram"){
					//$source_id = 'CONTACTUS-COLLEGE';
					$source_id = "7016T0000020OdyQAE";
					//$source_id = '7016T000002c8qMQAQ';
				}
		}
		$domain = 'https://' . $_SERVER['HTTP_HOST'];
		$form_url = "https://admission.asu.edu/contact/gradform";
		if($degree_level == '17') {
			$degree_level = 'Masters';
		} else if ($degree_level == '21') { // $degree_level == '21' actually includes case of gradtype of 15. Therefore, decided to change to text string of "Doctoral". So both become 'Doctoral'.
			$degree_level = 'Doctoral';
		} else if ($degree_level == 'cert') {
			$degree_level = 'Certificate';
		} else if ($degree_level == 'nd') {
			$degree_level = 'Non-Degree';
		}  
		//ksm($source_id);
		//// Assign parsed form values to array for passing to talisma. --- oLd format
		/*$submission_data = array (
			'source'=> $source_id,
			'firstName'=> $first_name,
			'lastName'=> $last_name,
			'emailAddress'=> $student_email,
			'phoneNumber'=> $phone,
			'projectedEnrollment'=> $term,
			'veteranStatus' => $military_status,
			'questions' => $question,
			'poiCode' => $program_of_interest,
			'degreeLevel' => $degree_level,
			'submissionType' => $source_id,
		 );*/
		$ip_address = $_SERVER['REMOTE_ADDR'];
		
		$submission_data = array(
			'Source'=> $source_id,
			'FirstName'=> $first_name,
			'LastName'=> $last_name,
			'EmailAddress'=> $student_email,
			'Phone'=> $phone_formatted,
			'EntryTerm'=> $term,
			'MilitaryStatus' => $military_status,
			'Comments' => $question,
			'Interest1' => $area_of_interest,
			'Interest2' => $program_of_interest,
			'StudentType' => $degree_level,
			'Career' => 'GRAD',
			'GdprConsent' => 1,
			'CitizenshipCountry' => $country,
			'Campus' => $campus,
			'URL' => $domain . '/contact/graduate',
			'datetime' => $webform_submission->getCreatedTime(),
			//'enterpriseclientid' => '12345',
            //'ga_clientid' => '12345',
            'ip_address' => $ip_address,
			//'Zip' => '85286'
		 );
		
		foreach ($submission_data as $key => $value) {
            if($value == '') {
                unset($submission_data[$key]);
            }
        }

        $data = json_encode($submission_data);
		
		
		$host = $_SERVER['HTTP_HOST'];
	    if(($host == "live-asu-admissions.ws.asu.edu") || ($host == "admission.asu.edu")){
		   $url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/rfi/';
			$env = 'prod';
		}
	    else{
		 // $url = 'https://requestinfo-qa.asu.edu/prospect_form_post';
			$url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/rfi/';
			$env = 'dev';
		}

	   
	 //url to post data to requestinfo
		 $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //If you don't want to use any of the return information, set to false
          curl_setopt($curl, CURLOPT_HEADER, TRUE); //Set this to false to remove informational headers
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //data mapping
          curl_setopt($curl, CURLOPT_SSLVERSION, 1); //This will set the security protocol to TLSv1
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
          ]);
          $response = curl_exec($curl);
          $info = curl_getinfo($curl);

          curl_close($curl);

		
		
		
       
		//ksm($info);
		//ksm($response);
		
		
		if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
					  \Drupal::logger('asu_grad')
						->notice('Post failed.<pre>' . print_r($response, TRUE) . '</pre>');
					  if ($env == 'prod') {
						$the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
					  }
					  else {
						$the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<pre>' . print_r($response, TRUE) . '</pre>', []);
					  }
					  $this->messenger()->addError($the_error);
					 // $form_state->setRebuild();

            	}
				else {
				  \Drupal::logger('asu_grad')
					->notice('Success - Posted data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
				  if ($env == 'dev') {
					$the_message = new TranslatableMarkup('Success: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $url . '</pre>', []);
					$this->messenger()->addMessage($the_message);
				  }

				} // END OF else
	    //Comment out this part to work on confirmation page. Rmemeber to uncomment this before going live. (7/19/2019 Chizuko)
	    if($code != '200' ) {
			//ksm($response);

			if($code == '-111' ) {
			  $reason = $response->error . '. Please try again later.';
			}

			if (isset($response->data)) {

			  $reason = $response->data;
			  if($reason == '-1 : Invalid or non-verifiable e-mail submitted') {
				$reason = 'Invalid or non-verifiable e-mail was submitted. Please check email address and try again.';
			  }
			  if($reason == '-2 : Field contains invalid characters or data') {
				$reason = 'Field contains invalid characters or data. Please check all fields are not containing invalid characters and try again.';
			  }
			}

			//$form_state->setErrorByName('form', t('We are sorry, there was a problem posting your information. ' . $reason, array()));		


	  	} // END OF if($response->code != 200 )
		
		
		
		
		
	}
}