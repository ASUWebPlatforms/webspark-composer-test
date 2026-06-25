<?php

namespace Drupal\asuaec_rfi_custompost\Plugin\WebformHandler;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;



/**
 * Form submission handler
 * Form is at /admitted/merit-review in Admission site.
 * Webform: merit_appeal
 *
 * @WebformHandler(
 *   id = "meritreview_webform_handler",
 *   label = @Translation("Post to middleware - Merit Review RFI"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to Middleware - Merit Review RFI"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class MeritReviewRfiWebformHandler extends WebformHandlerBase {
  public static $x = 0; // Prevent posting twice.

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
      return [];
  }

  /**
   * Post to middleware
   */
  public function postToMiddleware($webform_submission, $env, $update) {

    // Prevent from duplicate.
    if(($update == true && self::$x == 0) || ($update == false && self::$x == 0)) {
      $values = $webform_submission->getData();
      // \Drupal::logger('asuaec_rfi_custompost')->notice("submission data:<pre>" . print_r($values, true) . "</pre>");

      //-----------------------------------------------------
      // Comments - Concatinate all responses into [Comments]
      $comments = '';

      $have_you_been_awarded_new_american = $this->webformValueToString($values['have_you_been_awarded_a_new_american_university_scholarship'] ?? '');
      $which_new_american = $this->webformValueToString($values['which_new_american_university_scholarship_have_you_been_awarded'] ?? '');
      $have_you_been_awarded_university = $this->webformValueToString($values['have_you_been_awarded_any_of_these_university_scholarships'] ?? '');
      $updated_new_info = $this->webformValueToString($values['please_indicate_what_updated_or_new_information_you_are_able_to'] ?? '');
      $asu_commitment = $this->webformValueToString($values['the_asu_commitment_scholarship_is_designed_to_support_out_of_sta'] ?? '');
      $describe_the_level = $this->webformValueToString($values['please_describe_the_level_of_additional_scholarship_support_that'] ?? '');

      $comments = 'Have you been awarded a New American University Scholarship?: ' . $have_you_been_awarded_new_american . '<br />';
      $comments .= 'Which New American University Scholarship have you been awarded?: ' . $which_new_american . '<br />';
      $comments .= 'Have you been awarded any of these university scholarships?: ' . $have_you_been_awarded_university . '<br />';
      $comments .= 'Indicate what updated or new information you are able to provide to support your request.: ' . $updated_new_info . '<br />';
      $comments .= 'Do you believe you should have been awarded ASU Commitment scholarship based on your family\'s income?: ' . $asu_commitment . '<br />';
      $comments .= 'Describe the level of additional scholarship support: ' . $describe_the_level . '<br />';
      $comments .= isset($values['10_digit_id_number']) ? trim($values['10_digit_id_number']) : '';


      //-----
      // URL
      $domain = 'https://' . $_SERVER['HTTP_HOST'];
      $rfipage_alias = strtok($_SERVER["REQUEST_URI"],'?');
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

      //------------
      // Source id
      // Get it from hidden field in the Webform
      if($env === 'prod') {
        $source_id = isset($values['source_prod']) ? $values['source_prod'] : '';
      } else {
        $source_id = isset($values['source_dev']) ? $values['source_dev'] : '';
      }


      $submission_data = array(
        'FirstName' => isset($values['first_name']) ? trim($values['first_name']) : '', 
        'LastName' => isset($values['last_name']) ? trim($values['last_name']) : '',
        'EmailAddress' => isset($values['email']) ? $values['email'] : '',
        'asurite' => isset($values['asurite']) ? trim($values['asurite']) : '',
        'Career' => 'UGRAD',
        'StudentType' => 'First Time Freshman',
        'EntryTerm' => $this->get_next_fall_entry_term(), // Entry term: Fall Current year +1
        'Campus' => 'NOPREF',
        'Comments' => $comments,
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


        // \Drupal::logger('asuaec_rfi_custompost')->notice('webform_submission:<pre><code>' . print_r($webform_submission, TRUE) . '</code></pre>');
        // \Drupal::logger('asuaec_rfi_custompost')->notice("submission data:<pre>" . print_r($submission_data, true) . "</pre>");
        // \Drupal::logger('asuaec_rfi_custompost')->notice("response:<pre>" . print_r($response, true) . "</pre>");
        // \Drupal::logger('asuaec_rfi_custompost')->notice("info:<pre>" . print_r($info, true) . "</pre>");

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
   * Get Entry Term for Fall of (current year + 1).
   * 
   * Returns 2277 for Fall 2027, for example.
   */
  function get_next_fall_entry_term() {
    $entry_year = date('Y') + 1;
    $short_year = $entry_year - 1800; // 2027 → 227
    return $short_year . '7'; // Fall semester = 7
  }

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

  


} // END OF class RfiWebformHandler
