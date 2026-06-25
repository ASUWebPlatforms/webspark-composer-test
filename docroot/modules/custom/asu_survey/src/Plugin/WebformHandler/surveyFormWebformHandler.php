<?php

namespace Drupal\asu_survey\Plugin\WebformHandler;

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
 *   id = "asu_veteran_survey_form_handler",
 *   label = @Translation("Submit Survey form submissions to middleware"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the webform submissions to middleware"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class surveyFormWebformHandler extends WebformHandlerBase {
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
	
	 

    //public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
        /** Get webform submission values **/
		
       $values =  $webform_submission->getData();
	   $rfi_opted = $values['i_want_to_get_more_information_about_asu'];
	   if($rfi_opted == "1"){
       	   $phone = isset($values['phone_number']) ? $values['phone_number'] : '';
            // Remove "+" and "-"
            $phone_formatted = preg_replace('[\D]', '', $phone);
		   $married = isset($values['are_you_married_']) ? $values['are_you_married_'] : '';
		   $married_question = "Are you married? - ".$married."\n";
		   $children = isset($values['have_children']) ? $values['have_children'] : '';
		   $children_question = "Have children? - ".$children."\n";
		   $spouse = isset($values['my_my_spouse_my_parent_military_service'])?$values['my_my_spouse_my_parent_military_service']:'';
		   
		   if(sizeof($spouse) > 1 ){
			   $full_spouse = implode(',',$spouse);
		   }
		   else{
			   $full_spouse = $spouse;
		   }
		   
		   $spouse_question = "My / My spouse's / My parent's military service (select all that apply) - ".$full_spouse."\n";
		   $education = isset($values['my_my_spouse_my_parent_highest_education_level'])?$values['my_my_spouse_my_parent_highest_education_level']:'';
		   $education_question = "My / My spouse's / My parent's highest education level - ".$education."\n";
		   $giBillIntent = isset($values['i_intend_to_use_gi_bill_benefits_for_my_college_degree'])?$values['i_intend_to_use_gi_bill_benefits_for_my_college_degree']:'';
		   $giBillIntentQuestion = "I intend to use GI Bill&reg; Benefits for my college degree - ".$giBillIntent."\n";
		   $degree_path = isset($values['what_degree_path_do_you_plan_to_pursue_'])?$values['what_degree_path_do_you_plan_to_pursue_']:'';
		   $degree_path_question = "What degree path do you plan to pursue? - ".$degree_path."\n";
		   $college_prepared = isset($values['i_feel_prepared_for_success_in_a_collegiate_setting'])?$values['i_feel_prepared_for_success_in_a_collegiate_setting']:'';
		   $college_prepared_question = "I feel prepared for college - ".$college_prepared."\n";
		   
		   $seeking_degree = isset($values['i_m_seeking_a_degree_with_the_mindset_that_an_earned_degree_will'])?$values['i_m_seeking_a_degree_with_the_mindset_that_an_earned_degree_will']:'';
		   
		   $seeking_degree_question = "I'm seeking a degree with the mindset that it'll help me get a job in a specific industry upon graduation. - ".$seeking_degree."\n";
		   $thinking_future = isset($values['once_you_earn_your_degree_what_kind_of_job_do_you_want_to_get_'])?$values['once_you_earn_your_degree_what_kind_of_job_do_you_want_to_get_']:'';
		   $thinking_future_question = "Thinking into the future a bit, once you earn your degree, what kind of job will you pursue? - ".$thinking_future."\n";
		   $comments = $married_question.$children_question.$spouse_question.$education_question.$giBillIntentQuestion.$degree_path_question.$college_prepared_question.$seeking_degree_question.$thinking_future_question;
		   
            //$form_state->setValue('phone_number', $phone_formatted);

            // Campus and Student type
            $student_type_options_default = isset($values['select_your_student_status']) ? $values['select_your_student_status'] : '';
		    if(($student_type_options_default == "First Time Freshman") ||($student_type_options_default == "Transfer")) {
				$grad_ugrad = "UGRAD";
			}
			if($student_type_options_default == "Readmission"){
				$grad_ugrad = "GRAD";
			}
            $plan = isset($values['program_of_interest']) ? $values['program_of_interest'] : '';


            // EntryTerm: '2251:2025 Spring'
            $EntryTerm_formatted = '';
			$entry_term_text = isset($values['when_do_you_anticipate_starting_at_asu_']) ? $values['when_do_you_anticipate_starting_at_asu_'] : '';
            $EntryTerm_formatted = $entry_term_text . ':' . $this->getEntryTerm_label($entry_term_text);
		
            
            // Area of interest
            // Ground
            $interest1 = '';
            $area_of_interest_online = isset($values['area_of_interest_online']) ? $values['area_of_interest_online'] : '';
            $interest1 = $area_of_interest_online;
            $campus_options = isset($values['which_applies_to_you_'])?$values['which_applies_to_you_']:'';
            
 
//            // Post URL
//            $post_url = ($post_url != null || $post_url != '') ? $post_url : 'https://crm-enterprise-rfi-forms-submit-handler-sandbox.sdc.uto.asu.edu/';
//            $post_url = 'https://crm-enterprise-rfi-forms-submit-handler-sandbox.sdc.uto.asu.edu/';
//            $post_url = 'https://admission-asu-csdev4.ddev.site/webform_example_remote_post/completed'; //<---For testing

            // Source ID and Post URL switch depending on environment
		    $config_data = \Drupal::config('asu_survey.admin_settings');
            $domain = 'https://' . $_SERVER['HTTP_HOST'];
            $env = 'prod';
            if($domain == 'https://live-veterans-asu.ws.asu.edu/' || $domain == 'https://veterans.asu.edu/') {
                $env = 'prod';
                //$sourceid = '7016T000002cNcPQAU';
				$sourceid = $config_data->get('survey_source_id');
				$post_url = $config_data->get('survey_middleware_url');
               //$post_url = 'https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
            } else {
                $env = 'dev';
                $sourceid = '7016T000002c8qMQAQ';
//                $post_url = 'https://crm-enterprise-rfi-forms-submit-handler-sandbox.sdc.uto.asu.edu/'; //<--- Old posting URL
                $post_url = 'https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
            }

            // Enterpriseclientid -- We don't know how we get 'false', but if it is 'false', post empty string. Also, to match with what we post to middleware, changing value to empty string in Webform submission data. Changed on 4/27/2022.
//            \Drupal::logger('asuaec_rfi')->notice("asuonline_enterpriseclientid:<pre>" . $values['asuonline_enterpriseclientid'] . "</pre>");
            $asuonline_enterpriseclientid = isset($values['asuonline_enterpriseclientid']) ? trim($values['asuonline_enterpriseclientid']) : '';
            if($asuonline_enterpriseclientid == 'false' || $asuonline_enterpriseclientid == 'FALSE') {
              $asuonline_enterpriseclientid = '';
              // Set the Webform field value also to be empty to match with what we are posting.
              $form_state->setValue('asuonline_enterpriseclientid', $asuonline_enterpriseclientid);
            }
		   $url_value = $domain . '/veteran-survey-form';
		   $time_created = $webform_submission->getCreatedTime();
            $submission_data = array(
                'CitizenshipCountry' => 'US',
                //'Street1' => isset($values['address']) ? $values['address'] : '',
                //'City' => isset($values['city']) ? $values['city'] : '',
                //'State' => isset($values['state_or_province']) ? $values['state_or_province'] : '',
                'Country' => 'US',
                'Zip' => isset($values['postal_code']) ? $values['postal_code'] : '',
               // 'BirthDate' => $date_of_birth_formatted,
                'MilitaryStatus' => isset($values['i_am']) ? $values['i_am'] : '',
                'Comments' => $comments,
                'EmailAddress' => isset($values['email_address']) ? $values['email_address'] : '',
                'FirstName' => isset($values['full_name']) ? $values['full_name'] : '', // Added trim() on 8/23/2022
                'LastName' => isset($values['last_name']) ? $values['last_name'] : '', // Added trim() on 8/23/2022
                'Phone' => $phone_formatted,
				//'Phone' => '4809650981',
                'EntryTerm' => $EntryTerm_formatted,
                'GdprConsent' => true,
                'Campus' => $campus_options,
                'Interest1' => $interest1,
                'Interest2' => $interest1,
                'Career' => $grad_ugrad,
                'StudentType' => isset($values['select_your_student_status']) ? $values['select_your_student_status'] : '',
                'Source' => $sourceid,
                'URL' => $url_value,
				'origin_uri' => $url_value,
                'datetime' => $time_created,
                'enterpriseclientid' => $asuonline_enterpriseclientid,
                'ga_clientid' => $asuonline_enterpriseclientid,
				'program_key' => $plan,
            );
		 
		  // ksm($EntryTerm_formatted);
		  
// For testing
           /* $submission_data = array(
                'CitizenshipCountry' => 'US',
                //'Street1' => '123 Main St',
                //'City' => 'Tempe',
              //  'State' => 'Arizona',
                'Country' => 'US',
                'Zip' => '12345',
               // 'BirthDate' => '2000-01-01T07:00:00.000Z',
                'MilitaryStatus' => 'None',
                'Comments' => 'test',
                'EmailAddress' => 'archana.puliroju@asu.edu',
                'FirstName' => 'Archana',
                'LastName' => 'Puliroju',
                'Phone' => '14809650821',
                'EntryTerm' => '2251:2025 Spring',
                'GdprConsent' => true,
                'Campus' => 'ONLNE',
                'Interest1' => 'Arts',
                'Interest2' => 'Arts',
               'Career' => 'UGRAD',
                'StudentType' => 'First Time Freshman',
                'Source' => '7016T000002c8qMQAQ',
                'URL' => $url_value,
               'datetime' =>  $time_created,
				'origin_uri' => $url_value,
               'enterpriseclientid' => $asuonline_enterpriseclientid,
               'ga_clientid' => $asuonline_enterpriseclientid,
				'program_key' => $plan
           );*/


            $data = json_encode($submission_data);
            //ksm($submission_data);

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
			//ksm($response);
			//ksm($info);  
            curl_close($curl);

            if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
              \Drupal::logger('veterans survey')->notice('Post failed.<pre>' . print_r($response, TRUE) . '</pre>');
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
              \Drupal::logger('veterans_survey')->notice('Success - Posted data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
              if ($env == 'dev') {
                $the_message = new TranslatableMarkup('Success: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
                //$this->messenger()->addMessage($the_message);
              }

            } // END OF else

            self::$x++;

          } // END OF if(self::$x == 0)
          else {
            // This shouldn't happen, but this happens when post already happened right before this. So, $this->x has value of 1 from line 778. Added on 5/27/2022.
            \Drupal::logger('veterans_survey')->warning('Did not post because static variable x was not 0 - Post data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
            if ($env == 'dev') {
              $the_message = new TranslatableMarkup('Did not post because static variable x was not 0 - Posted data:<pre>'  . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
              $this->messenger()->addMessage($the_message);
            }
          } // END OF else
		   
	   } //end rfi_opted if statement

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