<?php

namespace Drupal\asuaec_rfi_custompost\Plugin\WebformHandler;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Form submission handler
 * Form is at /high-school-visit-form in Visit site.
 * Webform: high_school_visit
 *
 * @WebformHandler(
 *   id = "hsvisit_webform_handler",
 *   label = @Translation("Post to middleware - High School Visit RFI"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to Middleware - High School Visit RFI"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class HsVisitRfiWebformHandler extends WebformHandlerBase {
  public static $x = 0; // Prevent posting twice.

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
      return [];
  }


  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    
    $values = $webform_submission->getData();
    $the_submit_handler = $form_state->getSubmitHandlers();

    //-------------
    // BirthDate
    $date_of_birth = isset($values['birthdate']) ? $values['birthdate'] : ''; // 2000-01-02
    // Validate birthdate
    if(!$this->validateBirthdate($date_of_birth)) {
        // Throw error if birthdate is older than 1900.
        $the_error = new TranslatableMarkup('Please check birthdate.', array());
        $this->messenger()->addError($the_error);
        $form_state->setRebuild();
        return;
    }

    $date_of_birth_formatted = '';
    if($date_of_birth != '') {
        $date_of_birth_formatted = $date_of_birth . 'T07:00:00.000Z';
        $form_state->setValue('birthdate', $date_of_birth_formatted);
    }
  }


  /**
   * Post to middleware
   */
  public function postToMiddleware($webform_submission, $env, $update) {

    // Prevent from duplicate.
    if(($update == true && self::$x == 0) || ($update == false && self::$x == 0)) {
      $values = $webform_submission->getData();
      
      //-----
      // URL
      $domain = 'https://' . $_SERVER['HTTP_HOST'];
      $rfipage_alias = strtok($_SERVER["REQUEST_URI"],'?');

      //-----
      // UTM
      if($values['utm_source'] == '[current-page:query:utm_source]') {
        $values['utm_source'] = '';
      }
      if($values['utm_medium'] == '[current-page:query:utm_medium]') {
        $values['utm_medium'] = '';
      }
      if($values['utm_campaign'] == '[current-page:query:utm_campaign]') {
        $values['utm_campaign'] = '';
      }
      if($values['utm_term'] == '[current-page:query:utm_term]') {
        $values['utm_term'] = '';
      }
      if($values['utm_content'] == '[current-page:query:utm_content]') {
        $values['utm_content'] = '';
      }
      $utm = '?utm_source=' . $values['utm_source'] . '&utm_medium=' . $values['utm_medium'] . '&utm_campaign=' . $values['utm_campaign'] . '&utm_term=' . $values['utm_term'] . '&utm_content=' .  $values['utm_content'];


      //--------------------
      // Enterpriseclientid
      // We don't know how we get 'false', but if it is 'false', post empty string. Also, to match with what we post to middleware, changing value to empty string in Webform submission data. Changed on 4/27/2022.
      $asuonline_enterpriseclientid = isset($values['asuonline_enterpriseclientid']) ? trim($values['asuonline_enterpriseclientid']) : '';
      if($asuonline_enterpriseclientid == 'false' || $asuonline_enterpriseclientid == 'FALSE') {
        $asuonline_enterpriseclientid = '';
        // Set the Webform field value also to be empty to match with what we are posting.
        $webform_submission->setElementData('asuonline_enterpriseclientid', $asuonline_enterpriseclientid);
      }

      //--------
      // Phone
      $phone = isset($values['phone']) ? $values['phone'] : '';
      // Remove "+" and "-"
      $phone_formatted = preg_replace('[\D]', '', $phone);
      $webform_submission->setElementData('phone', $phone_formatted);

      //----------------
      // Student type/status : First Time Freshman, Transfer or Masters
      $student_type = isset($values['student_type_options_default']) ? $values['student_type_options_default'] : '';

      if($student_type == 'Masters') {
        $career = 'GRAD';
      }  else {
        $career = 'UGRAD';
      }

      //------------
      // Source id
      // Get it from hidden field in the Webform
      if($env === 'prod') {
        $source_id = isset($values['source_prod']) ? $values['source_prod'] : '';
      } else {
        $source_id = isset($values['source_dev']) ? $values['source_dev'] : '';
      }



      $submission_data = array(
        'FirstName' => trim($values['first_name'] ?? ''),
        'LastName' => trim($values['last_name'] ?? ''),
        'EmailAddress' => $values['email_address'] ?? '',
        'Phone' => $phone_formatted,
        'GdprConsent' => $values['gdpr_consent'][0] ?? '',
        'BirthDate' => $values['birthdate'] ?? '', // Already formatted in validateForm function. [BirthDate] => 2000-02-18T07:00:00.000Z
        'Zip' => $values['postal_code'] ?? '',
        'CitizenshipCountry' => $values['citizenship_country'] ?? '',
        'Career' => $career,
        'StudentType' => $student_type,
        'EntryTerm' => $values['entry_term'] ?? '',
        'schoolInfo' => trim($values['hsname'] ?? ''),
        'Campus' => 'NOPREF',
        'Source' => $source_id,
        'URL' => $domain . $rfipage_alias . $utm,
        'datetime' => $webform_submission->getCreatedTime(),
        'enterpriseclientid' => $asuonline_enterpriseclientid,
        'ga_clientid' => $asuonline_enterpriseclientid,
      );

      foreach ($submission_data as $key => $value) {
        if($value == '') {
          unset($submission_data[$key]);
        }
      }

      $data = json_encode($submission_data);
      


      $config_data = \Drupal::config('asuaec_rfi_custompost.customadmin_settings');
      $env = $this->getEnv();
      if($env == 'prod') {
        $post_url = $config_data->get('ground_posturl_prod');
      } else { // 'dev'
        $post_url = $config_data->get('ground_posturl_dev');
      }

      // Prevent from duplicate. Added on 5/20/2022
      if(self::$x == 0) {

        $curl = curl_init($post_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // We need return information.
        curl_setopt($curl, CURLOPT_HEADER, TRUE); // Set this to false to remove informational headers
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //data mapping
        curl_setopt($curl, CURLOPT_SSLVERSION, 1); // Set the security protocol to TLSv1
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);


        // \Drupal::logger('cstest')->notice('webform_submission:<pre><code>' . print_r($webform_submission, TRUE) . '</code></pre>');
        \Drupal::logger('cstest')->notice('Submission data: @data', [
          '@data' => print_r($submission_data, TRUE),
        ]);
        \Drupal::logger('cstest')->notice('response: @response', [
          '@response' => print_r($response, TRUE),
        ]);
        \Drupal::logger('cstest')->notice('info: @info', [
          '@info' => print_r($info, TRUE),
        ]);
        \Drupal::logger('cstest')->notice("post url:" . htmlspecialchars($post_url));



        if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
          \Drupal::logger('asuaec_rfi_custompost')
            ->notice('Post failed.<pre>' . print_r($response, TRUE) . '</pre>');
          if ($env == 'prod') {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
          }
          else {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<pre>' . print_r($response, TRUE) . '</pre>', []);
          }
          $this->messenger()->addError($the_error);

        }
        else {
          \Drupal::logger('asuaec_rfi_custompost')
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
        \Drupal::logger('asuaec_rfi_custompost')
          ->warning('Did not post because static variable x was not 0 - Post data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
        if ($env == 'dev') {
          $the_message = new TranslatableMarkup('Did not post because static variable x was not 0 - Posted data:<pre>'  . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
          $this->messenger()->addMessage($the_message);
        }
      } // END OF else

      self::$x++;
    } // END OF if(($update == true && self::$x == 0) || ($update == false && self::$x == 0))
  } // END OF function postToMiddleware



  /**
   * Normalize a Webform value into a trimmed string.
   * - string => trimmed string
   * - array  => comma-separated list of non-empty values
   * - null/other => ''
   */
  protected function webformValueToString($value): string {
    if (is_string($value)) {
      return trim($value);
    }

    if (is_array($value)) {
      // For checkboxes you usually get [key => key] or [key => 1] depending on config.
      $flat = [];

      array_walk_recursive($value, function ($v, $k) use (&$flat) {
        // If value is scalar, keep it. If it's 1/true, keep the key (common for checkboxes).
        if (is_scalar($v) && $v !== '' && $v !== NULL) {
          if ($v === 1 || $v === '1' || $v === TRUE) {
            $flat[] = (string) $k;
          }
          else {
            $flat[] = (string) $v;
          }
        }
      });

      $flat = array_values(array_unique(array_filter(array_map('trim', $flat))));
      return implode(', ', $flat);
    }

    return '';
  }


  /**
   * {@inheritdoc}
   *
   *  Post to middleware.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
      // Check environment
      $env = $this->getEnv();

      // Posting
      $this->postToMiddleware($webform_submission, $env, $update);

  } // END OF public function postSave()




  /**
   * @return string
   */
  public function getEnv() {
    $config_data = \Drupal::config('asuaec_rfi_custompost.customadmin_settings');
    $proddomain = $config_data->get('proddomain'); // https://admission.asu.edu
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $env = 'prod';
    if($domain == $proddomain) {
      $env = 'prod';
    } else {
      $env = 'dev';
    }

    return $env;
  }

  
  /**
   * Helping function
   *
   * Validate birthdate
   */
  public function validateBirthdate ($birthdate_text) {
      $birthdate=date('Y-m-d', strtotime($birthdate_text));
      $birthdate_oldest = date('Y-m-d', strtotime("1900-01-01"));
      $retVal = true;
      if ($birthdate >= $birthdate_oldest) {
          $retVal = true;
      } else {
          $retVal = false;
      }
      return $retVal;
  }

} // END OF class HsVisitRfiWebformHandler
