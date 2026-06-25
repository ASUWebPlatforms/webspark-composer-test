<?php

namespace Drupal\asu_survey\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\Core\Mail\MailManagerInterface;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "asu_survey_email_handler",
 *   label = @Translation("Send survey confirmation email"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send survey confirmation email to the user after form is submitted"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class surveyEmailHandler extends WebformHandlerBase {

   
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
	  	 $sid = $webform_submission->id();
		 $values =  $webform_submission->getData();
	     $to_email_address = isset($values['email_address'])?$values['email_address']:'';
	     $first_name = $values['full_name'];
		 $undecided = $values['i_feel_prepared_for_success_in_a_collegiate_setting']; 
		 $decided = $values['i_m_seeking_a_degree_with_the_mindset_that_an_earned_degree_will'];
		 if((($undecided == "Strongly disagree") || ($undecided == "Disagree") || ($undecided == "Neutral") ) && (($decided == "Strongly disagree") || ($decided == "Disagree") || ($decided == "Neutral") )){
				$_SESSION['decided'] = "undecided";
			    $colleg_decision = "undecided";
		 }
		 if((($undecided == "Strongly agree") || ($undecided == "Agree")) && (($decided == "Strongly agree") || ($decided == "Agree"))){
				$_SESSION['decided'] = "decided";
			    $colleg_decision = "decided";
		 }
		/*else{
				//$_SESSION['decided'] = "undecided";
				//$colleg_decision = "undecided";
		 }*/
		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asu_survey';
		 $key = 'send_survey_email';
	     $config_data = \Drupal::config('asu_survey.admin_settings');
	     $email_content = $config_data->get('email_content');
		 //ksm($colleg_decision);
		 if($colleg_decision == "undecided"){
			 $college_email_content = $config_data->get('undecided_email_content');
		 }
		 if($colleg_decision == "decided"){
			 $college_email_content = $config_data->get('decided_email_content');
		 }
		 
		 //ksm($college_email_content);
		// ksm($email_content); 
		 //$email_content = "<p>Hello</p>";
		 $to = $webform_submission->getData()['email_address'];
		 //$to = \Drupal::currentUser()->getEmail();
		 $full_email_content = "<div style='max-width: 100%''><p><strong>Dear ".$first_name.",</strong></p></div>".$college_email_content.$email_content;
		 //ksm($full_email_content);
		 $params['message'] = $full_email_content;
		 $params['subject'] = "Thanks for taking our survey, $first_name";
	     $params['from_address'] = "ptvc@asu.edu";
		 $params['reply-to'] = "apuliroj@asu.edu";
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		 $send = true;
	  	 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
	    
	
  }
}