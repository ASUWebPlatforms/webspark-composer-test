<?php

namespace Drupal\asuaec_formmanagertest\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use GuzzleHttp\Exception\RequestException;


/**
 * Form submission handler
 *
 * Source ID is set at line 667.
 *
 * @WebformHandler(
 *   id = "fmtest_webform_handler",
 *   label = @Translation("Form Manager Testing"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Form Manager Testing"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class FMTestWebformHandler extends WebformHandlerBase {

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     *  Post to Form Manager
     *
     */

    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

      // Check environment
      $domain = 'https://' . $_SERVER['HTTP_HOST'];
      $env = 'prod';
      if($domain == 'https://live-admission-asu.ws.asu.edu' || $domain == 'https://admission.asu.edu' || $domain == 'https://admissions.asu.edu') {
          $env = 'prod';
      } else {
          $env = 'dev';
      }

      // Check to see if session variable exists. It could be already existed. If not existed, create one.

//        $sid = $webform_submission->id();
//        $grad_ugrad = $webform_submission->getData()['grad_ugrad'];
//        $plancode = $webform_submission->getData()['program_of_interest_text'];
//        $interest = $webform_submission->getData()['interest1'];


      /** Get webform submission values **/

      $values =  $webform_submission->getData();

//      ksm($values);
//      $student_type = $values['which_of_these_apply_to_you_'];
//      $ftf_student_status = !empty($values['are_you_applying_to_asu_as_a_'])?$values['are_you_applying_to_asu_as_a_']:'';
//      $transfer_student_status = !empty($values['transfer_are_you_applying_to_asu_as_a_'])?$values['transfer_are_you_applying_to_asu_as_a_']:'';
//      $domestic_school = !empty($values['do_you_attend_an_u_s_high_school_'])?$values['do_you_attend_an_u_s_high_school_']:'';
//      $freshman_state= !empty($values['freshman_state'])?$values['freshman_state']:'';
//      $highschool = !empty($values['high_school_autocomplete'])?$values['high_school_autocomplete']:'';
//      $trn_institute = !empty($values['transfer_do_you_attend_a_u_s_institution_'])?$values['transfer_do_you_attend_a_u_s_institution_']:'';
      $country = !empty($values['select_country'])?$values['select_country']:'US';
      $rep_email = !empty($values['specialist_email'])?$values['specialist_email']:'';
      $first_name = !empty($values['first_name'])?$values['first_name']:'';
      $last_name = !empty($values['last_name'])?$values['last_name']:'';
      $student_email = !empty($values['email'])?$values['email']:'';
      $phone = !empty($values['phone'])?$values['phone']:'';
      $phone_formatted = preg_replace('[\D]', '', $phone);
      $form_state->setValue('phone', $phone_formatted);
      $question = !empty($values['what_s_your_question_'])?$values['what_s_your_question_']:'';
//      $transfer_state = !empty($values['transfer_state'])?$values['transfer_state']:'';
      $zipcode = !empty($values['zip_code'])?$values['zip_code']:'';
//      $cali_university = !empty($values['enter_california_college_or_university'])?$values['enter_california_college_or_university']:'';
//      $form_id = 19;
      $form_url = "https://admission.asu.edu/contact/undergradute";
      $transfer_term = !empty($values['planned_enrollment_term'])?$values['planned_enrollment_term']:'';

      $state = !empty($values['state'])?$values['state']:'';
      $az_hs_code_value = !empty($values['az_hs_code'])?$values['az_hs_code']:'';
      $transfer_institue = !empty($values['transfer_institute'])?$values['transfer_institute']:'';
      $int = !empty($values['international_institute'])?$values['international_institute']:'';
      $type_of_student = !empty($values['type_of_student'])?$values['type_of_student']:'';
      $post_url = !empty($values['end_point_url'])?$values['end_point_url']:'';

      $source_id = !empty($values['source_id'])?$values['source_id']:'';
//      //source ID for FTF
//      if($student_type == "FTF"){
//        if($ftf_student_status == "citizen"){
//          if(($domestic_school == "yes")){
//            $int = "";
//            $source_id = 79;
//          }
//          if($domestic_school == "no"){
//            $source_id = 120;
//            $int = "INT";
//          }
//          if($domestic_school == "homeschool"){
//            $source_id = 79;
//            $int = "";
//          }
//        }
//        elseif($ftf_student_status == "international"){
//          if(($domestic_school == "yes")){
//            $int = "";
//            $source_id = 79;
//          }
//          if($domestic_school == "no"){
//            $source_id = 93;
//            $int = "INT";
//          }
//          if($domestic_school == "homeschool"){
//            $source_id = 79;
//            $int = "";
//          }
//
//        }
//        elseif($ftf_student_status = "military"){
//          $source_id == 119;
//          $int = "";
//        }
//        elseif($ftf_student_status = "unique-status"){
//          $source_id == 79;
//          $int = "";
//        }
//        else{
//
//        }
//      }

//      //source ID for TRN
//      if($student_type == "TRN"){
//        if($transfer_student_status == "citizen"){
//          $int = "";
//          $source_id = 78;
//
//        }
//        elseif($transfer_student_status == "international"){
//          if(($trn_institute == "yes")){
//            $int = "INT";
//            $source_id = 78;
//          }
//          if($trn_institute == "no"){
//            $int = "INT";
//            $source_id = 93;
//          }
//
//
//        }
//        elseif($transfer_student_status = "military"){
//          $source_id == 119;
//          $int = "";
//        }
//        elseif($transfer_student_status = "unique-status"){
//          $source_id == 78;
//          $int = "";
//        }
//        else{
//
//        }
//      }




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
        if($post_url == '') {
          $url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
        } else {
          $url = $post_url;
        }
      }
      else{
        if($post_url == '') {
          //$url = 'https://webapp4-qa.asu.edu/formmanager/FormUserController?selection=1';
          $url = 'https://webapp4.asu.edu/formmanager/FormUserController?selection=1';
        } else {
          $url = $post_url;
        }
      }

      $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
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
        // Print response
        $the_message = new TranslatableMarkup('Response: <pre>' . 'Code: ' . $e->getCode() . '<br />Message: ' . print_r($e->getMessage(), TRUE) . '<br />Posted data:' . print_r($at, TRUE) . '<br />Post URL: ' . $full_url . '</pre>', []);
        $this->messenger()->addMessage($the_message);
        return FALSE;
      }

      $code = $response->getStatusCode();

      if ($code == 200) {
        $body = $response->getBody()->getContents();
      }

      // Print response
      $the_message = new TranslatableMarkup('Response: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($at, TRUE) . '<br />Post URL: ' . $full_url . '</pre>', []);
      $this->messenger()->addMessage($the_message);


    } // END OF public function postSave()


} // END OF class FMTextWebformHandler
