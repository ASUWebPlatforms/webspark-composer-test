<?php

namespace Drupal\asu_quiz\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\asu_quiz\Controller\QuizConfirmController;


/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "quiz_email_handler",
 *   label = @Translation("Send persona quiz confirmation email"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send persona quiz confirmation email to the user after form is submitted"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class quizEmailHandler extends WebformHandlerBase {


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
	  	 $sid = $webform_submission->id();
		 $values =  $webform_submission->getData();
	     $rfi_sid =  isset($values['rfi_sid'])?$values['rfi_sid']:'';
	     if(!empty($rfi_sid)){
				$rfi_data= \Drupal\webform\Entity\WebformSubmission::load($rfi_sid);
				//ksm($rfi_data);
				if(!empty($rfi_data)){
					//ksm($rfi_data);
					$rfi_submission_data = $rfi_data->getData();
					//ksm($rfi_submission_data);
					$to_email_address = isset($rfi_submission_data['email_address'])?$rfi_submission_data['email_address']:'';
					$first_name = isset($rfi_submission_data['first_name'])?$rfi_submission_data['first_name']:'';
				}
		 }
	     elseif(!empty($_SESSION['quiz_email_url_variables'])){
			 $to_email_address = isset($_SESSION['quiz_email_url_variables']['email'])?base64_decode($_SESSION['quiz_email_url_variables']['email']):'';
			 $first_name = isset($_SESSION['quiz_email_url_variables']['fname'])?base64_decode($_SESSION['quiz_email_url_variables']['fname']):'';
		 }
	  	 else{
			 $to_email_address = isset($values['email_address'])?$values['email_address']:'';
			 $first_name = $values['first_name'];
		 }
	    // ksm($to_email_address);
	    // ksm($first_name);

	  	  $persona_raw_value = isset($_SESSION['persona'])?$_SESSION['persona']:'';
		$results_link = $values['results_link'];
	  	// ksm($results_link);
		if($persona_raw_value == "deep_diver"){
            $persona = 99;
        }
        if($persona_raw_value == "trailblazer"){
            $persona = 100;
        }
        if($persona_raw_value == "focused_futurist"){
            $persona = 101;
        }
        if($persona_raw_value == "natural_networker"){
            $persona = 98;
        }
        if($persona_raw_value == "superfan"){
            $persona = 97;
        }

		if($persona_raw_value == "focused_futurist"){
			$top_image = '<img src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_FocusedFuturist.jpg" alt="Focused futurist" class="media-element file-responsive-image img-fluid" data-delta="1" data-fid="916" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_FocusedFuturist.jpg" data-mce-selected="1" width="600">';
			$persona_value = "focused futurist";
   			$persona_content = "<p>These students thrive at ASU because they are driven by their goals, which keeps them on track toward graduation.</p>";
		}

		if($persona_raw_value == "deep_diver"){
			$top_image = '<img src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_DeepDiver.jpg" alt="Deep diver" class="media-element file-responsive-image img-fluid" data-delta="2" data-fid="917" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_DeepDiver.jpg?itok=PoA7UW4v" data-mce-selected="1" width="600">';
			$persona_value = "deep diver";
   			$persona_content = "<p>These students thrive at ASU because they build great relationships with professors, leading them to becoming experts in their fields of study.</p>";
		}

		if($persona_raw_value == "trailblazer"){
			$top_image = '<img src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_Trailblazer.jpg" alt="Trailblazer" class="media-element file-responsive-image img-fluid" data-delta="3" data-fid="912" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_Trailblazer.jpg" data-mce-selected="1" width="600">';
			$persona_value = "trailblazer";
   			$persona_content = "<p>These students thrive at ASU because they customize their ASU experience, double major and even start businesses, giving them the entrepreneurial foundation needed to make their ideas happen.</p>";
		}

		if($persona_raw_value == "natural_networker"){
		   	$top_image = '<img src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_NaturalNetworker.jpg" alt="Natural networker" class="media-element file-responsive-image img-fluid" data-delta="4" data-fid="901" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_1920x512_NaturalNetworker.jpg" data-mce-selected="1" width="600">';
			$persona_value = "natural networker";
   			$persona_content = "<p>These students thrive at ASU because they get involved with clubs, organizations and take on leadership opportunities that benefit them beyond graduation.</p>";
		}

		if($persona_raw_value == "superfan"){
			$top_image = '<img src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_Color_SuperFan%402x.jpg" alt="Superfan" class="media-element file-responsive-image img-fluid" data-delta="6" data-fid="909" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/2024-09/Persona_WebBanners_Color_SuperFan%402x.jpg" data-mce-selected="1" width="600">';
			$persona_value = "superfan";
   			$persona_content = "<p>These students thrive at ASU because their self-motivated, collaborative spirit helps them build the network they will use to succeed after graduation.</p>";
		}


		$top_image_content = "<div class='row no-gutters'><div class='rfi_conf_image bg-top bg-percent-100 layout__full-width'><div class='col uds-full-width'><div class='bg-top bg-percent-100 layout__full-width'>".$top_image."</div></div></div></div>";

		$bottom_content  = "In the coming months, we will send you more information about the ways $persona_value".'s'." can thrive at ASU.";

		$content = "$top_image_content<div id='quiz_rfi_main'><div class='main_inner_quiz'><div id='quiz_rfi_conf_content'><h2 style='text-align:center;'>Dear $persona_value,</h2><h3><strong>Thanks for taking our quiz!</strong></h3><p>We designed it to help you take a first step in learning more about yourself and finding the opportunities at ASU that match your style and interest. Every student’s ASU experience is uniquely their own and we want to help you find the ASU that fits you.</p></div><div id='quiz_rfi_grey_confirmation'><div class='inner_grey_content_conf'>Your answers told us that you're most like current ASU students that we refer to as $persona_value".'s'.". See your <a href='$results_link' target='_blank'>detailed results</a>.<br /><br />$bottom_content</div><div>&nbsp;</div></div></div></div>";


		$email_content = '<table border="0" cellpadding="0" cellspacing="0" class="mobile-padding" style="border: 1px solid #fcfcfc; padding: 0; background: #fcfcfc" width="80%" ><tr><td valign="top">';
		$email_content .=  '<table border="0" cellpadding="0" cellspacing="0" class="100p" width="800"><tr><td align="center"><img src="https://admission.asu.edu/sites/default/files/asu_sunburst_rgb_maroongold.png" width="300px"></td></tr></table>';
		$email_content .=  '<table border="0" cellpadding="0" cellspacing="0" class="100p" width="800" ><tr><td>'.$top_image.'</td></tr></table>';
		$email_content .=   '<table border="0" cellpadding="0" cellspacing="0" class="100p" style="background: #ffffff; padding: 10px; font-size: 14px;"><tr><td><h2 style="text-align:center;">Dear '.$persona_value.',</h2><p><strong>Thanks for taking our quiz!</strong><br />We designed it to help you take a first step in learning more about yourself and finding the opportunities at ASU that match your style and interest. Every student\'s ASU experience is uniquely their own and we want to help you find the ASU that fits you.</p></td></tr></table>';
		$email_content .=  "<table border='0' cellpadding='0' cellspacing='0' class='100p' style='background: #f1f1f1; font-size: 18px; '><table border='0' cellpadding='0' cellspacing='0' class='100p' style='padding: 10px;'><tr><td>Your answers told us that you're most like current ASU students that we refer to as $persona_value".'s'.". See your <a href='$results_link' target='_blank'>detailed results.</a></td></tr></table>";
		$email_content .= "<table border='0' cellpadding='0' cellspacing='0' class='100p'  style='padding: 10px;'><tr><td>$bottom_content</td></tr></table></td></tr></table></table></td></tr></table>";

		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asu_quiz';
		 $key = 'send_quiz_email';
		 //$to = \Drupal::currentUser()->getEmail();
		 $params['message'] = $email_content;
		 $params['subject'] = "Thanks for taking our quiz, $first_name";
	     $params['from_address'] = "asurecruitment@asu.edu";
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		 $send = true;
	  	 $result = $mailManager->mail($module, $key, $to_email_address, $langcode, $params, NULL, $send);
	     if(isset($_SESSION["quiz_email_variables"])){
                unset($_SESSION["quiz_email_variables"]);
         }

  }
}
