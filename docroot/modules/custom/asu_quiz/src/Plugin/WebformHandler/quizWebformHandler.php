<?php

namespace Drupal\asu_quiz\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "quiz_webform_handler",
 *   label = @Translation("Submit to SF"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to SF"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class quizWebformHandler extends WebformHandlerBase {
	public static $x = 0; 
    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
	public function preSave(WebformSubmissionInterface $webform_submission) {
    	$rfi_val_sub =  $webform_submission->getData();
		//ksm($val_sub);
		/*$rfi_sid =  isset($rfi_val_sub['rfi_sid'])?$rfi_val_sub['rfi_sid']:'';
		$rfi_nid = "rfi";
        $persona_original = isset($_SESSION['persona'])?$_SESSION['persona']:'';
		ksm($rfi_val_sub['rfi_sid']);
		
		ksm($_SESSION["quiz_email_variables"]);
		if(!empty($rfi_nid) && !empty($rfi_sid)){
			$rfi_data= \Drupal\webform\Entity\WebformSubmission::load($rfi_sid);
			ksm($rfi_data);
			if(!empty($rfi_data)){
				//ksm($rfi_data);
				$rfi_submission_data = $rfi_data->getData();
				ksm($rfi_submission_data);
				$data['first_name'] = isset($rfi_submission_data['first_name'])?$rfi_submission_data['first_name']:'';
				$data['last_name'] = isset($rfi_submission_data['last_name'])?$rfi_submission_data['last_name']:'';
				$data['email'] = isset($rfi_submission_data['email_address'])?$rfi_submission_data['email_address']:'';
				$data['zipcode'] = isset($rfi_submission_data['postal_code'])?$rfi_submission_data['postal_code']:'';
				$data['student_type'] = isset($rfi_submission_data['student_type_options_default'])?$rfi_submission_data['student_type_options_default']:'';
				$data['start_term'] = isset($rfi_submission_data['entryterm'])?$rfi_submission_data['entryterm']:'';
				$data['country'] = isset($rfi_submission_data['country'])?$rfi_submission_data['country']:'';
				$webform_submission->setData($data);
				$webform_submission->save();
				$quiz_rfi_data = array('fname' => $data['first_name'], 'lname' => $data['last_name'], 'email' => $data['email'], 'stype' => $data['student_type'], 'sterm' => $data['start_term'], 'zipcode' => $data['zipcode'], 'country' => $data['country']);
				$_SESSION["quiz_rfi_data"] = $quiz_rfi_data;
			}
			ksm($_SESSION["quiz_rfi_data"]);
			ksm($webform_submission);
		}
	
	
		//If student is coming to the form via email with url parameters then save the them in the form and in session variables
		if(empty($rfi_sid)){
			if(isset($_SESSION["quiz_email_variables"])){
				if(!empty($_SESSION["quiz_email_variables"])){
					$data_from_email = $_SESSION['quiz_email_variables'];
					$efirst_name = isset($data_from_email['fname'])?base64_decode($data_from_email['fname']):'';
					$elast_name = isset($data_from_email['lname'])?base64_decode($data_from_email['lname']):'';
					$eemail_address = isset($data_from_email['email'])?base64_decode($data_from_email['email']):'';
					$ecountry = isset($data_from_email['country'])?base64_decode($data_from_email['country']):'';
					$estudent_type = isset($data_from_email['stype'])?base64_decode($data_from_email['stype']):'';
					$ezip_code = isset($data_from_email['zipcode'])?base64_decode($data_from_email['zipcode']):'';
					$estart_term = isset($data_from_email['sterm'])?base64_decode($data_from_email['sterm']):'';
					$submission_data['first_name'] = $efirst_name;
					$submission_data['last_name'] = $elast_name;
					$submission_data['email'] = $eemail_address;
					$submission_data['zipcode'] = $ezip_code;
					$submission_data['student_type'] = $estudent_type;
					$submission_data['start_term'] = $estart_term;
					$submission_data['country'] = $ecountry;
				}
					unset($_SESSION["quiz_email_variables"]);
			}
		}
		
		*/
			
		
    }

    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

        /** @var Node $node */
       $values =  $webform_submission->getData();
		
       //$websid = $values['sid'];
       // \Drupal::logger('webform-sid')->notice('<pre><code>' . print_r($values, TRUE) . '</code></pre>');
		//ksm($_SESSION['quiz_email_url_variables']);
		//ksm($_SESSION['persona']);
		$rfi_sid =  isset($values['rfi_sid'])?$values['rfi_sid']:'';
        $persona_agree = isset($values['do_the_above_results_sound_like_you_'])?$values['do_the_above_results_sound_like_you_']:'';
        $first_name_from_url = isset($values['first_name_from_url'])?$values['first_name_from_url']:'';
        $request_info_sid =  isset($values['rfi_sid'])?$values['rfi_sid']:'';
        $persona_original = isset($_SESSION['persona'])?$_SESSION['persona']:'';
		//ksm($persona_original);
        if($persona_original == "deep_diver"){
            $persona = 99;
        }
        if($persona_original == "trailblazer"){
            $persona = 100;
        }
        if($persona_original == "focused_futurist"){
            $persona = 101;
        }
        if($persona_original == "natural_networker"){
            $persona = 98;
        }
        if($persona_original == "superfan"){
            $persona = 97;
        }

        if($persona_agree != "No"){
			if(!empty($rfi_sid)){
				$rfi_data= \Drupal\webform\Entity\WebformSubmission::load($rfi_sid);
				//ksm($rfi_data);
				if(!empty($rfi_data)){
					//ksm($rfi_data);
					$rfi_submission_data = $rfi_data->getData();
					//ksm($rfi_submission_data);
					$data['first_name'] = isset($rfi_submission_data['first_name'])?$rfi_submission_data['first_name']:'';
					$data['last_name'] = isset($rfi_submission_data['last_name'])?$rfi_submission_data['last_name']:'';
					$data['email_address'] = isset($rfi_submission_data['email_address'])?$rfi_submission_data['email_address']:'';
					$data['zipcode'] = isset($rfi_submission_data['postal_code'])?$rfi_submission_data['postal_code']:'';
					$data['student_type'] = isset($rfi_submission_data['student_type_options_default'])?$rfi_submission_data['student_type_options_default']:'';
					$data['start_term'] = isset($rfi_submission_data['entryterm'])?$rfi_submission_data['entryterm']:'';
					$data['country'] = isset($rfi_submission_data['country'])?$rfi_submission_data['country']:'';
					$data['first_name_from_url'] = isset($rfi_submission_data['first_name'])?$rfi_submission_data['first_name']:'';
					$webform_submission->setData($data);
					$webform_submission->save();
					$quiz_rfi_data = array('fname' => $data['first_name'], 'lname' => $data['last_name'], 'email' => $data['email_address'], 'stype' => $data['student_type'], 'sterm' => $data['start_term'], 'zipcode' => $data['zipcode'], 'country' => $data['country']);
					$_SESSION["quiz_rfi_data"] = $quiz_rfi_data;
					//ksm($_SESSION["quiz_rfi_data"]);
					//ksm($data['student_type']);
					$first_name = $data['first_name'];
					$last_name = $data['last_name'];
					$email_address = $data['email_address'];
					$zip_code =  $data['zipcode'];
					$student_type = $data['student_type'];
					$country = $data['country'];
				}
				
			}
			elseif(!empty($_SESSION['quiz_email_url_variables'])){
					$data_from_email = $_SESSION['quiz_email_url_variables'];
					//ksm($data_from_email);
					if(sizeof($data_from_email) > 0){
						$email_data['first_name'] = isset($data_from_email['fname'])?base64_decode($data_from_email['fname']):'';
						$email_data['last_name'] = isset($data_from_email['lname'])?base64_decode($data_from_email['lname']):'';
						$email_data['email_address'] = isset($data_from_email['email'])?base64_decode($data_from_email['email']):'';
						$email_data['country'] = isset($data_from_email['country'])?base64_decode($data_from_email['country']):'';
						$email_data['student_type'] = isset($data_from_email['stype'])?base64_decode($data_from_email['stype']):'';
						$email_data['zip_code'] = isset($data_from_email['zipcode'])?base64_decode($data_from_email['zipcode']):'';
						$email_data['start_term'] = isset($data_from_email['sterm'])?base64_decode($data_from_email['sterm']):'';
						$email_data['i_consent'] = isset($values['i_consent'])?$values['i_consent']:'';
						$email_data['do_the_above_results_sound_like_you_'] = isset($values['do_the_above_results_sound_like_you_'])?$values['do_the_above_results_sound_like_you_']:'';
						$webform_submission->setData($email_data);
						$webform_submission->save();
						$first_name = $email_data['first_name'];
						$last_name = $email_data['last_name'];
						$email_address = $email_data['email_address'];
						$zip_code = $email_data['zip_code'];
						$student_type = $email_data['student_type'];
						$start_term = $email_data['start_term'];
						$country = $email_data['country'];
					}
			}
			else{
		  		$first_name = isset($values['first_name'])?$values['first_name']:'';
				$last_name = isset($values['last_name'])?$values['last_name']:'';
				$email_address = isset($values['email_address'])?$values['email_address']:'';
				$zip_code = isset($values['zip_code'])?$values['zip_code']:'';
				$student_type = isset($values['student_type'])?$values['student_type']:'';
				$start_term = isset($values['start_term'])?$values['start_term']:'';
				$country = isset($values['country'])?$values['country']:'';

				$EntryTerm_formatted = $start_term . ':' . $this->getEntryTerm_label($start_term);
			//ksm($EntryTerm_formatted);
			}
			//ksm($student_type);
            $host = $_SERVER['HTTP_HOST'];

            
            $http = 'https://';
            $url = $http.$host."/quiz";
            /*if(($host == "live-asu-admissions.ws.asu.edu") || ($host == "admission.asu.edu")){
                $posting_url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
                $grad_url =  'https://requestinfo.asu.edu/prospect_form_post';
            }
            else{
                $posting_url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
                $grad_url =  'https://requestinfo.asu.edu/prospect_form_post';
            }*/
			//ksm($student_type);
			
            ///undergrad submissions
            //if(empty($request_info_sid)){
                if(($student_type != 'grad') || ($student_type != 7)){
                    $degree_level = 'UGRAD';

                   /* $posting_data = array (
                        'form_id'=> 19,
                        'source_id'=> 99,
                        'field1'=> $first_name,
                        'field3'=> $last_name,
                        'field9'=> $zip_code,
                        'field11'=> $email_address,
                        'field13' => $country,
                        'field61'=> $student_type,
                        'field74'=> $start_term,
                        'field102'=> $url,
                        'field128' =>  $persona
                    );

                    //if submitting data from dev or test site, submit to testing environment

                    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
                    // post the data
                    $at = http_build_query($posting_data, '', '&');
                    $options = array(
                        'method' => 'POST',
                        'data' => $at,
                        'timeout' => 15,
                        'headers' => $headers,
                    );
                    //dpm($at);
					$full_url = $posting_url.'&'.$at;
                    $client = \Drupal::httpClient();
                    $response = $client->request('POST', $posting_url, $options);
                    $code = $response->getStatusCode();
                    if ($code == 200) {
                        $body = $response->getBody()->getContents();
						
                     }
					ksm($response);*/
                    
                }

                ///Grad submissions
                if(($student_type == 'grad') || ($student_type == 7)){
                    $degree_level = 'GRAD';
                    /*$source_id = '';
                    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
                    // build data array to post
                    $submission_data = array (
                        'source'=> '',
                        'firstName'=> $first_name ,
                        'lastName'=> $last_name,
                        'emailAddress'=> $email_address,
                        'projectedEnrollment'=>$start_term,
                        'countryOfCitizenship' => $country,
                        'zip' => $zip_code,

                    );

                    //url to post data to requestinfo
                    $curl = curl_init($grad_url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //If you don't want to use any of the return information, set to false
                    curl_setopt($curl, CURLOPT_HEADER, FALSE); //Set this to false to remove informational headers
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $submission_data); //data mapping
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1); //This will set the security protocol to TLSv1
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                    $response = curl_exec($curl);

                    $info = curl_getinfo($curl);

                    curl_close($curl);*/
                }
				$domain = 'https://' . $_SERVER['HTTP_HOST'];
            
				if($domain == 'https://live-admission-asu.ws.asu.edu' || $domain == 'https://admission.asu.edu') {
					$env = 'prod';
					$sourceid = '7010W000002eyCSQAY';
					$post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/rfi';
					//$post_url = 'https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
				} else {
					$env = 'dev';
					$sourceid = '7016T000002c8qMQAQ';
					//$post_url = 'https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
					$post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/rfi'; //<--- New posting URL
				}
				$interest2 = '';
			
				$submission_data = array(
						'CitizenshipCountry' => isset($country) ? $country : '',
						'Country' => isset($country) ? $country : '',
						'Zip' => isset($zip_code) ? $zip_code : '',
						'EmailAddress' => isset($email_address) ? $email_address : '',
						'FirstName' => isset($first_name) ? trim($first_name) : '', // Added trim() on 8/23/2022
						'LastName' => isset($last_name) ? trim($last_name) : '', // Added trim() on 8/23/2022
						'EntryTerm' => $EntryTerm_formatted,
						'GdprConsent' => 1,
						'Campus' => 'NOPREF',
						'Interest1' => strval($persona),
						//'Interest2' => $interest2,
						'Career' => $degree_level,
						'StudentType' => isset($student_type) ? $student_type : '',
						'Source' => $sourceid,
						'URL' => $domain . '/quiz-landing-page',
						'datetime' => $webform_submission->getCreatedTime(),
						
            	);
				
			
				
				

			foreach ($submission_data as $key => $value) {
					if($value == '') {
						unset($submission_data[$key]);
					}
			}
			
			
            $data = json_encode($submission_data);
		

          	// Prevent from duplicate. Added on 5/20/2022
          	if(self::$x == 0) {

				$curl = curl_init($post_url);
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
				if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
					  \Drupal::logger('asu_quiz')
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
				  \Drupal::logger('asu_quiz')
					->notice('Success - Posted data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
				  if ($env == 'dev') {
					$the_message = new TranslatableMarkup('Success: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
					$this->messenger()->addMessage($the_message);
				  }

				} // END OF else

            	self::$x++;

          	} // END OF if(self::$x == 0)
          	else {
				// This shouldn't happen, but this happens when post already happened right before this. So, $this->x has value of 1 from line 778. Added on 5/27/2022.
				\Drupal::logger('asu_quiz')
				  ->warning('Did not post because static variable x was not 0 - Post data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
				if ($env == 'dev') {
				  $the_message = new TranslatableMarkup('Did not post because static variable x was not 0 - Posted data:<pre>'  . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
				  $this->messenger()->addMessage($the_message);
				}
			  } // END OF else
            //}
			//End of post data
			
			/*if(!empty($request_info_sid)){
				$rfi_sid = $request_info_sid;
				$result_agree = $values['do_the_above_results_sound_like_you_'];
				if( ($result_agree == 'Yes') || ($result_agree == "100")){
					$webform_rfi_submission = WebformSubmission::load($rfi_sid);
					$rfi_val_sub = $webform_rfi_submission->getData();
					//ksm($rfi_val_sub);
					$webform_submission->setElementData('first_name', $rfi_val_sub['first_name']); //save quiz results in webform field
					$webform_submission->setElementData('last_name', $rfi_val_sub['last_name']);
					$webform_submission->setElementData('email_address', $rfi_val_sub['email_address']);
					$webform_submission->setElementData('zip_code', $rfi_val_sub['zip_code']);
					$webform_submission->setElementData('student_type', $rfi_val_sub['grad_ugrad']);
					$webform_submission->setElementData('start_term', $rfi_val_sub['entry_term']);
					$webform_submission->setElementData('country', $rfi_val_sub['country']);
					$webform_submission->setElementData('rfi_sid', $rfi_sid);
					$webform_submission->setElementData('first_name_from_url', $rfi_val_sub['first_name']);
					$webform_submission->setElementData('persona',$persona_original);
					// Save submission.
					$webform_submission->save();
					//ksm($webform_submission);
				}
			}*/
			if(!empty($persona_original)){
				$webform_submission->setElementData('persona',$persona_original);
				$webform_submission->save();
			}
			
		}

     }
	
	 /**
     * Helping function
     *
     * Format Entry term to be such as "2020 Fall"
     */
    public function getEntryTerm_label ($entryterm_code) {
        // Start term
        $start_term = $entryterm_code; // 2197
        $semester = '';
        switch (substr($start_term, 3)) {
            case '1':
                $semester = 'Spring';
                break;
            case '4':
                $semester = 'Summer';
                break;
            case '7':
                $semester = 'Fall';
                break;
        }
        return substr($start_term, 0, 1) . '0' . substr($start_term, 1, 2) . ' ' . $semester; // 2020 Fall
    }
}