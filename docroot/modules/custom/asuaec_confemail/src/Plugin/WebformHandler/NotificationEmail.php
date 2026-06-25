<?php

namespace Drupal\asuaec_confemail\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use GuzzleHttp\Exception\RequestException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime;

/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Notification email for webform",
 *   label = @Translation("Notification email for webform"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Notification email for webform"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class NotificationEmail extends WebformHandlerBase {

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
        /** Get webform submission values and current wenform id**/
		$webform = $this->getWebform();
        $webform_id = $webform->id();
		$values =  $webform_submission->getData();
//        ksm($values, "values");
//		ksm($webform_id, "webform_id");
        $completed_timestamp = $webform_submission->getCompletedTime();
        $completed_dateTime = date('Y-m-d H:i', $completed_timestamp);

		$email = $values['notification_email_to'];
//		$event_id = $values['event_id'];
//      $fname = $values['first_name'];

//		$form_object = $form_state->getFormObject();
//		$webform_submission->setSticky(!$webform_submission->isSticky())->save();
//       	$sid = $webform_submission->id();

        $output = "<p>Submitted on: " . $completed_dateTime . "</p>";
        $output .= "<p>Submitted values are:</p><p>";

		foreach ($values as $key => $value) {


            $label = '';
            $type = '';
            if(!array_key_exists($key, ($form['elements'])) || is_null($form['elements'][$key]['#title'])) {
                //ksm("is null");
                //$label = $form['elements'][$key]['#text'];

                // Look for $key in the array
                foreach($form['elements'] as $element_array) {
                    foreach ($element_array as $element_key => $element_value) {
                        if ($element_key == "#type" && $element_value == 'fieldset') {
                            if (array_key_exists($key, $element_array)) {
                                $label = $element_array[$key]['#title'];
                                break 2;
                            }
                        }
                    }
                }
            }
            else {
                //ksm("is not null");
                $label = $form['elements'][$key]['#title'];
                $type = $form['elements'][$key]['#type'];

            }
            if($type != "hidden") {
                if (is_array($value)) {
                    $value_output = "<ul>";
                    foreach ($value as $sub_value) {
                        $value_output .= "<li>$sub_value</li>";
                    }
                    $value_output .= "</ul>";
                } else {
                    $value_output = $value;
                }
                $output .= "<strong>" . $label . ":</strong><br />" . $value_output . "<br /><br />";
            }
		}
		$output .= "</p>";


		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asuaec_confemail';
		 $key = 'confemail';
		 $to = $email;
		 $subject = "Form submission from: " . $webform->get('title');
		 $from = \Drupal::config('system.site')->get('mail'); 
	     $params['message'] = \Drupal\Core\Render\Markup::create($output);
		 $params['subject'] = $subject;
	     $params['from'] = $from;
		 $params['reply-to'] = $from;
		 //ksm($params);
		 //ksm($to);
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		 $send = true;
	  	 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
		
	}
}