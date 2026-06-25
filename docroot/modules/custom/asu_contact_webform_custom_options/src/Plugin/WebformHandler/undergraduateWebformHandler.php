<?php

namespace Drupal\asu_contact_webform_custom_options\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\RequestException;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Undergrad contactfrom handler",
 *   label = @Translation("Submit undergrad contact form SF"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to SF"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class undergraduateWebformHandler extends WebformHandlerBase {

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

       	// ksm($values);
		$student_type = $values['which_of_these_apply_to_you_'];
		$ftf_student_status = !empty($values['are_you_applying_to_asu_as_a_'])?$values['are_you_applying_to_asu_as_a_']:'';
		$transfer_student_status = !empty($values['transfer_are_you_applying_to_asu_as_a_'])?$values['transfer_are_you_applying_to_asu_as_a_']:'';
		$domestic_school = !empty($values['do_you_attend_an_u_s_high_school_'])?$values['do_you_attend_an_u_s_high_school_']:'';
		$freshman_state= !empty($values['freshman_state'])?$values['freshman_state']:'';
		$highschool = !empty($values['high_school_autocomplete'])?$values['high_school_autocomplete']:'';
		$trn_institute = !empty($values['transfer_do_you_attend_a_u_s_institution_'])?$values['transfer_do_you_attend_a_u_s_institution_']:'';
		$country = !empty($values['select_country'])?$values['select_country']:'US';
		$rep_email = !empty($values['specialist_email'])?$values['specialist_email']:'';
		$first_name = !empty($values['first_name'])?$values['first_name']:'';
		$last_name = !empty($values['last_name'])?$values['last_name']:'';
		$student_email = !empty($values['email'])?$values['email']:'';
		$phone = !empty($values['phone'])?$values['phone']:'';
		$phone_formatted = preg_replace('[\D]', '', $phone);
        $form_state->setValue('phone', $phone_formatted);
		$question = !empty($values['what_s_your_question_'])?$values['what_s_your_question_']:'';
		$transfer_state = !empty($values['transfer_state'])?$values['transfer_state']:'';
		$zipcode = !empty($values['zip_code'])?$values['zip_code']:'';
		$cali_university = !empty($values['enter_california_college_or_university'])?$values['enter_california_college_or_university']:'';
		$form_id = 19;
		$form_url = "https://admission.asu.edu/contact/undergradute";
		$transfer_term = !empty($values['planned_enrollment_term'])?$values['planned_enrollment_term']:'';
		//$state = !empty($freshman_state)?$freshman_state:$transfer_state;
		$source_id= '';

		if($student_type == "FTF"){
			$type_of_student = 1;
		}
		if($student_type == "TRN"){
			$type_of_student = 6;
		}

		//source ID for FTF
		if($student_type == "FTF"){
			if($ftf_student_status == "citizen"){
				if(($domestic_school == "yes")){
					// ksm('yes intl');
					$int = "";
					$source_id = 79;
				}
				if($domestic_school == "no"){
					$source_id = 120;
					$int = "INT";
				}
				if($domestic_school == "homeschool"){
					$source_id = 79;
					$int = "";
				}
			}
			elseif($ftf_student_status == "international"){
				if(($domestic_school == "yes")){
					$int = "";
					$source_id = 79;
				}
				if($domestic_school == "no"){
					$source_id = 93;
					$int = "INT";
				}
				if($domestic_school == "homeschool"){
					$source_id = 79;
					$int = "";
				}

			}
			elseif($ftf_student_status = "military"){
				$source_id == 119;
				$int = "";
			}
			elseif($ftf_student_status = "unique-status"){
				$source_id == 79;
				$int = "";
			}
			else{

			}
		}
		//ksm('ns',$source_id);

		//source ID for TRN
		if($student_type == "TRN"){
			if($transfer_student_status == "citizen"){
				$int = "";
				$source_id = 78;

			}
			elseif($transfer_student_status == "international"){
				if(($trn_institute == "yes")){
					$int = "INT";
					$source_id = 78;
				}
				if($trn_institute == "no"){
					$int = "INT";
					$source_id = 93;
				}


			}
			elseif($transfer_student_status = "military"){
				$source_id == 119;
				$int = "";
			}
			elseif($transfer_student_status = "unique-status"){
				$source_id == 78;
				$int = "";
			}
			else{

			}
		}


		/*if($domestic_school == "no"){
			if($ftf_student_status == "citizen"){
				$source_id = 120;
			}
			else{
				$source_id = 93;
			}
			$int = "INT";
		}

		if(($domestic_school == "yes") || empty($domestic_school)){
		       $int = "";

			   if($ftf_student_status == "unique-status"){
			      $source_id = 79;
			   }
		}

		if(($ftf_student_status == "militray") || ($transfer_student_status == "militray")){
			$source_id = 119;
		}

		if(($transfer_student_status == "citizen") || ($transfer_student_status == "unique-status")){
			$source_id = 78;
		}*/

		/*if($transfer_student_status == "militray"){
			$source_id = 119;
		}*/


		$database = \Drupal::database();

		/** get california intitution code from database **/
		if(!empty($cali_university)){
			$inst_data = $database->select('asu_webform_institution_data','awtd')
						->fields('awtd',['inst_id', 'inst_name'])
						->condition('inst_name','%'.$database->escapeLike($cali_university).'%', 'LIKE')
						->execute()
						->fetchAll();
			foreach($inst_data as $inst_code){
				$inst_code_value = $inst_code->inst_id;
			}

			$cali_inst_code_value = $inst_code_value;
		}
		else{
			$cali_inst_code_value = '';
		}
		if($cali_inst_code_value == "2147483647"){ //change Gila community college code to Eastern Arizona College institution code
		  $cali_inst_code = 1100100064;
		}
		else{
		  $cali_inst_code = $cali_inst_code_value;
		}

		/** Get arizona institute for transfer students if Arizona state selected **/
		if(!empty($az_university)){
			$az_inst_data = $database->select('asu_webform_institution_data','awaz')
						->fields('awaz',['inst_id', 'inst_name'])
						->condition('inst_name','%'.$database->escapeLike($az_university).'%', 'LIKE')
						->execute()
						->fetchAll();
			foreach($az_inst_data as $az_inst_code){
				$az_inst_code_value = $az_inst_code->inst_id;
			}

			$az_inst_code_value = $az_inst_code_value;
		}
		else{
			$az_inst_code_value = '';
		}

		/** check if AZ institute exists or Cali institute exists and pass the existing valie to field 18 **/
		$transfer_institue = isset($az_inst_code_value)?$az_inst_code_value:$cali_inst_code;

		/** Get Arizona high school code from database **/

		if(!empty($highschool)){
			$explode_hs = explode(' ',$highschool);

			$hs_data = $database->select('asu_webform_highschool_data','awhd')
					->fields('awhd',['hs_id', 'hs_name', 'hs_state_name'])
					->condition('hs_name','%'.$database->escapeLike($highschool).'%', 'LIKE')
				    ->condition('hs_state_name','Arizona')
					->execute()
					->fetchAll();
			//ksm($hs_data->rowCount());
			if(count($hs_data) == 0){
				$query2 = $database->select('asu_webform_highschool_data','awhd1')
						->fields('awhd1',['hs_id', 'hs_name', 'hs_state_name','hs_city'])
						->condition('hs_name','%'.$database->escapeLike(str_replace("High School", "HS", $highschool)).'%', 'LiKE')
						->condition('hs_state_name',$freshman_state)
						->execute()
						->fetchAll();
				foreach($query2 as $hs_code2){
					$hs_code_value2 = $hs_code2->hs_id;
				}
			    if(!empty($hs_code_value2)){
					$az_hs_code_value = $hs_code_value2;
				}
				else{
					$az_hs_code_value = '';
				}
			}
			else{
			   foreach($hs_data as $hs_code){
				$hs_code_value = $hs_code->hs_id;
			   }
				if(!empty($hs_code_value)){
					$az_hs_code_value = $hs_code_value;
				}
				else{
					$az_hs_code_value = '';
				}
			}

		}
		else{
				$az_hs_code_value = '';
		}

		//convert state name to code
	    switch($freshman_state){
		  case 'Alabama':
				$state_code = 'AL';
				break;
		  case 'Alaska':
				$state_code = 'AK';
				break;
		  case 'American Samoa':
				$state_code = 'AS';
				break;
		  case 'Arizona':
				$state_code = 'AZ';
				break;
		  case 'Arkansas':
				$state_code = 'AR';
				break;
		  case 'California':
				$state_code = 'CA';
				break;
		  case 'California – Central Coast and Central Valley':
				$state_code = 'CA';
				break;
		  case 'California – Greater Los Angeles':
				$state_code = 'CA';
				break;
		  case 'California – Greater Napa/Sonoma and North':
				$state_code = 'CA';
				break;
		  case 'California – Greater Orange County':
			  $state_code = 'CA';
				break;
		  case 'California – Central Valley':
				$state_code = 'CA';
				break;
		  case 'California – Greater Sacramento':
				$state_code = 'CA';
				break;
		  case 'California – Greater San Francisco Bay Area':
				$state_code = 'CA';
				break;
		  case 'California – Greater San Diego':
				$state_code = 'CA';
				break;
		  case 'California – Bay Area - Peninsula':
				$state_code = 'CA';
				break;
		  case 'California – Bay Area - San Francisco':
				$state_code = 'CA';
				break;
		  case 'California – Bay Area - Silicon Valley':
			$state_code = 'CA';
				break;
		  case 'California – Central Coast':
				$state_code = 'CA';
				break;
		  case 'California – Greater Napa and Sonoma':
				$state_code = 'CA';
				break;
		  case 'California – Greater Sacramento':
				$state_code = 'CA';
				break;
		  case 'California – Los Angeles - Other Areas':
				$state_code = 'CA';
				break;
		  case 'California – Los Angeles - San Fernando Valley':
				$state_code = 'CA';
				break;
		  case 'California – Los Angeles - South Bay & Long Beach':
				$state_code = 'CA';
				break;
		  case 'California – Los Angeles – Covina, West Covina':
				$state_code = 'CA';
				break;
		  case 'California – Los Angeles – Glendale, Pasadena':
			$state_code = 'CA';
				break;
		  case 'California – Los Angeles – Ventura County':
				$state_code = 'CA';
				break;
		  case 'California – Northern California Counties':
				$state_code = 'CA';
				break;
		  case 'California – Orange County':
				$state_code = 'CA';
				break;
	      case 'California – Other Areas':
				$state_code = 'CA';
				break;
		  case 'California – Palm Springs, Palm Desert, Coachella Valley':
				$state_code = 'CA';
				break;
		  case 'California – Riverside, San Bernadino, Ontario Areas':
				$state_code = 'CA';
				break;
		  case 'California – Riverside, San Bernardino, Ontario Areas':
		 		$state_code = 'CA';
				break;
		  case 'California – San Diego County & San Diego':
				$state_code = 'CA';
				break;
		 case 'Colorado':
			  	$state_code = 'CO';
				break;
		  case 'Connecticut':
			  	$state_code = 'CT';
				break;
		  case 'District of Columbia':
			  	$state_code = 'DC';
				break;
		  case 'Delaware':
			  $state_code = 'DE';
				break;
   		  case 'Florida':
				$state_code = 'FL';
				break;
  		  case 'Georgia':
				$state_code = 'GA';
				break;
  		  case 'Guam':
				$state_code = 'GU';
				break;
  		  case 'Hawaii':
			  $state_code = 'HI';
				break;
  		  case 'Idaho':
			  	$state_code = 'ID';
				break;
  		  case 'Illinois':
				$state_code = 'IL';
				break;
  		  case 'Indiana':
			  	$state_code = 'IN';
				break;
		  case 'Iowa':
				$state_code = 'IA';
				break;
		  case 'Kansas':
			    $state_code = 'KS';
				break;
		  case 'Kentucky':
			  $state_code = 'KY';
				break;
		  case 'Louisiana':
			  $state_code = 'LA';
				break;
		  case 'Maine':
			  $state_code = 'ME';
				break;
		  case 'Maryland':
				$state_code = 'MD';
				break;
		  case 'Massachusetts':
				$state_code = 'MA';
				break;
		  case 'Michigan':
				$state_code = 'MI';
				break;
		  case 'Minnesota':
				$state_code = 'MN';
				break;
		  case 'Mississippi':
				$state_code = 'MS';
				break;
		  case 'Missouri':
				$state_code = 'MO';
				break;
		  case 'Montana':
				$state_code = 'MT';
				break;
		  case 'Nebraska':
				$state_code = 'NE';
				break;
		  case 'Nevada':
				 $state_code = 'NV';
				break;
		  case 'New Hampshire':
				$state_code = 'NH';
				break;
		  case 'New Jersey':
				$state_code = 'NJ';
				break;
		  case 'New Mexico':
				$state_code = 'NM';
				break;
		  case 'New Mexico - Navajo Reservation':
				$state_code = 'NM';
				break;
		  case 'New Mexico – Zuni Reservation':
				$state_code = 'NM';
				break;
		  case 'New York':
				$state_code = 'NY';
				break;
		  case 'North Carolina':
				$state_code = 'NC';
				break;
		  case 'North Dakota':
				$state_code = 'ND';
				break;
		  case 'Northern Marianas':
				$state_code = 'MP';
				break;
		  case 'Ohio':
				$state_code = 'OH';
				break;
		  case 'Oklahoma':
				 $state_code = 'OK';
				break;
		  case 'Oregon':
				$state_code = 'OR';
				break;
		  case 'Pennsylvania':
				$state_code = 'PA';
				break;
		  case 'Puerto Rico':
				$state_code = 'PR';
				break;
		  case 'Rhode Island':
				$state_code = 'RI';
				break;
		  case 'South Carolina':
				$state_code = 'SC';
				break;
		  case 'South Dakota':
				$state_code = 'SD';
				break;
		  case 'Tennessee':
				$state_code = 'TN';
				break;
		  case 'Texas':
				$state_code = 'TX';
				break;
		  case 'Utah':
				$state_code = 'UT';
				break;
		  case 'US Virgin Islands':
				$state_code = 'VI';
				break;
		  case 'Vermont':
				$state_code = 'VT';
				break;
		  case 'Virginia':
				$state_code = 'VA';
				break;
		  case 'Washington':
				$state_code = 'WA';
				break;
		  case 'West Virginia':
				$state_code = 'WV';
				break;
		  case 'Wisconsin':
				$state_code = 'WI';
				break;
		  case 'Wyoming':
				$state_code = 'WY';
				break;
	  };

		$state = isset($state_code)?$state_code:$transfer_state;

		//// Assign parsed form values to array for passing to talisma.
		$data = array (
		  'source_id'=> $source_id,
		  'form_id' => '19',
		  'field1' => $first_name,
		  'field3' => $last_name,
		  'field8' => $state,
		  'field9' => $zipcode,
		  'field10' => $phone_formatted,
		  'field17' => $az_hs_code_value,
		  'field18'=> $transfer_institue,
		  'field11' => $student_email,
		  'field13' => $int,
		  'field53' => $question,
		  'field59' => $country,
		  'field61' => $type_of_student,
		  'field69' => $rep_email,
		  'field74' => $transfer_term,
		  'field102' => $form_url,
		);

		// ksm($data);

		 // SUBMIT TO FORM MANAGER
		 // URL + headers for drupal_http_request that sends to FormManager
		 $host = $_SERVER['HTTP_HOST'];
		 if(($host == "live-asu-admissions.ws.asu.edu") || ($host == "admission.asu.edu")){
		    $url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
		 }
		 else{
		    //$url = 'https://webapp4-qa.asu.edu/formmanager/FormUserController?selection=1';
			 $url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
		 }

		 $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
		 //$headers = array('Content-Type: application/x-www-form-urlencoded');
         // post the data
         $at = http_build_query($data, '', '&');
		 $full_url = $url.'&'.$at;
         $options = array(
              'method' => 'POST',
              'data' => $at,
              'timeout' => 15,
              'headers' => $headers,
         );

		  try {
			 $client = \Drupal::httpClient();
			 $response = $client->request('POST', $full_url, $options);
		 }
		 catch (RequestException $e){
		    return FALSE;
		 }

         $code = $response->getStatusCode();

         if ($code == 200) {
            $body = $response->getBody()->getContents();
         }
	}
}
