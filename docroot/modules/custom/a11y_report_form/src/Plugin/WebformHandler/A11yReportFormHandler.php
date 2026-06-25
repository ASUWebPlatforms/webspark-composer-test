<?php

namespace Drupal\a11y_report_form\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation;

/**
 * Adds referrer to webform submission.
 *
 * @WebformHandler(
 *   id = "a11y_report_form_handler",
 *   label = @Translation("A11y Report Form Handler"),
 *   category = @Translation("Webform Handler"),
 *   description = @Translation("Adds Referrer During Form Submissions. Requires referring_url field to work."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

 // TODO - Need to confirm data stored is passed as needed and in right format
 // TODO - Need to add JS libraries to forms and site

class A11yReportFormHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */


  // Function to be fired after submitting the Webform.
  public function preSave(WebformSubmissionInterface $webform_submission) { 
    
    // Capture cookie session variables
    $cookie_referring =\Drupal::request()->cookies->get('referring'); 

    // Capture submitted data
    $values = $webform_submission->getData();

    // Set Referring URL
    if (isset($cookie_referring)) {
    $referring_url = $cookie_referring;
    } else {
    $referring_url = 'none';
    }

  // TODO - Need to add error management if referring url is not preset to prevent WSOD.
    $values['referring_url'] = $referring_url;
  
  // Set pre-save values from manipulated fields
    $webform_submission->setData($values);

  // Record submission to logger

  \Drupal::logger('a11y report form')->notice('A11y report submitted from: '.$referring_url);

  return true;
  }

}
