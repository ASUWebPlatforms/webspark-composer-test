<?php

namespace Drupal\asu_campus_fit\Plugin\WebformHandler;

use Drupal\webform\entity\WebformSubmission;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "email_campus_fit_webform_handler",
 *   label = @Translation("Add first name, last name and email address to submissionsand send confirmation email. "),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Add first name, last name and email addresst to submissions"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class EmailcampusFitWebformHandler extends WebformHandlerBase {

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

    $values = $webform_submission->getData();

    $sid = $values['campus_fit_sid'];
    $fname = $values['first_name'];
    $lname = $values['last_name'];
    $email = $values['email_address'];
    $stype = $values['student_type'];
    $form_url = $values['form_url'];
    $result_nid = $values['nid'];
    $result_link = $form_url . '?sid=' . $sid . '&nid=' . $result_nid . '&stype=' . $stype;
    // $webform_exists = \Drupal::entityTypeManager()->getStorage('webform')->load('campus_fit');
    $webform_submission = WebformSubmission::load($sid);
    $webform_submission->setElementData('first_name', $fname);
    $webform_submission->setElementData('last_name', $lname);
    $webform_submission->setElementData('email_address', $email);
    $webform_submission->save();

    // Code to send confirmation email.
    /* $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'asu_campus_fit';
    $key = 'fit_quiz_results_confirmation';
    $to = $email;
    //ksm($to);
    $content = "There is no such thing as one-size-fits-all learning. While everyone should have access to an education, how that journey looks is different for each person. ASU offers multiple options to reach you in the way that works best.";
    $content .= "Here are the results for your ASU Fit Quiz:<br /><br /><button><a href='$result_link'>Quiz results</a></button><p>If you have any questions, please contact <a href='https://asu.edu/findmyrep'>asu.edu/findmyrep</a></p>";

    $params['message'] = $content;
    $params['subject'] = "Thanks for taking our quiz, $fname";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $resultData = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    ksm($resultData);*/
    /*if ($resultData['result'] !== true) {
    $message = new TranslatableMarkup('There was a problem sending your message and it was not sent.', array());
    $this->messenger()->addMessage($message);


    } else {

    $message = new TranslatableMarkup('Your confirmation email has been sent.', array());
    $this->messenger()->addMessage($message);

    }*/

  }

}
