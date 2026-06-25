<?php

namespace Drupal\asuaec_asulocal\Plugin\WebformHandler;

use Drupal\asuaec_asulocal\Controller\WebformConfirmationPage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Form submission handler
 *
 * Source ID is set at line 667.
 *
 * @WebformHandler(
 *   id = "rfi_webform_handler_asulocalrfi",
 *   label = @Translation("Post to ASU Online - ASU Local"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to ASU Online SF - ASU Local"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
// class AsuLocalRfiWebformHandler extends WebformHandlerBase {
class AsuLocalRfiWebformHandler extends WebformHandlerBase implements ContainerFactoryPluginInterface {
  /**
   * {@inheritdoc}
   */

  // Injected HTTP client (Drupal/Guzzle)
  protected $httpClient;

  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
    ];
  }


 public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // Inject only extra service
    $instance->httpClient = $container->get('http_client');

    return $instance;
  }


  /**
   * @return string
   */
  public function getEnv() {
    $config_data = \Drupal::config('asuaec_asulocal.customadmin_settings');
    $proddomain = $config_data->get('proddomain'); // https://students.asu.edu
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
   * Post to middleware -- For ASU Local, Post to Online SF.
   */
  public function postToMiddleware($webform_submission) {
      $env = $this->getEnv();

      $values = $webform_submission->getData();

      // Phone
      $phone = isset($values['phone']) ? $values['phone'] : '';

      // Remove "+" and "-"
      $phone_formatted = preg_replace('[\D]', '', $phone);
      $webform_submission->setElementData('phone', $phone_formatted);
      \Drupal::logger('cstest')->notice('phone_formatted: ' . $phone_formatted);


      // Campus and Student type
      $campus_options = 'ONLNE'; // Only Online for ASU Local site
      $student_type_options_default = isset($values['student_type_options_default']) ? $values['student_type_options_default'] : '';
      $plan = isset($values['program_of_interest']) ? $values['program_of_interest'] : '';


      // EntryTerm: '2251:2025 Spring'
      $entry_term = isset($values['entry_term']) ? $values['entry_term'] : ''; // <--- Default entry term
      $EntryTerm_formatted = $entry_term . ':' . $this->getEntryTerm_label($entry_term);

      $webform_submission->setElementData('entryterm', $EntryTerm_formatted);

      // GDPR consent
      $consent = '';
      if ($campus_options == 'ONLNE') {
        $consent = isset($values['gdpr_consent_online'][0]) ? $values['gdpr_consent_online'][0] : '';
      }

      // Area of interest
      $interest1 = '';      
      // Online
      if (($campus_options == 'ONLNE' && $student_type_options_default == 'First Time Freshman') ||
          ($campus_options == 'ONLNE' && $student_type_options_default == 'Transfer')
      ) {
        $area_of_interest_ugrad_online = isset($values['area_of_interest_ugrad_online']) ? $values['area_of_interest_ugrad_online'] : '';
        $interest1 = $area_of_interest_ugrad_online;
      }
      if ($campus_options == 'ONLNE' && $student_type_options_default == 'Readmission') {
        $area_of_interest_grad_online = isset($values['area_of_interest_grad_online']) ? $values['area_of_interest_grad_online'] : '';
        $interest1 = $area_of_interest_grad_online;
      }
      $webform_submission->setElementData('interest1', $interest1);



      //-------------------------------------------------
      $rfipage_alias = ''; // Home page

      $config2 = \Drupal::config('asuaec_asulocal.customadmin_settings');
      // Subclass
      $sub_class = isset($values['asu_local_site']) ? $values['asu_local_site'] : '';
      \Drupal::logger('cstest')->notice('sub_class: ' . $sub_class);

      // Source ID and Post URL switch depending on environment
      if($env === 'prod') {
        // ASU Online post
        $post_url_online = $config2->get('online_posturl_prod');
        $sourceid_online = $config2->get('online_sourceid_prod');
        $lead_class = $config2->get('online_leadclass_prod'); // $lead_class - 'CORP'
        
      } else { // dev
        // ASU Online post
        $post_url_online = $config2->get('online_posturl_dev');
        $sourceid_online = $config2->get('online_sourceid_dev');
        $lead_class = $config2->get('online_leadclass_dev'); // $lead_class - 'CORP'
      }


      // Enterpriseclientid -- We don't know how we get 'false', but if it is 'false', post empty string. Also, to match with what we post to middleware, changing value to empty string in Webform submission data. Changed on 4/27/2022.
      // \Drupal::logger('asuaec_asulocal')->notice("asuonline_enterpriseclientid:<pre>" . $values['asuonline_enterpriseclientid'] . "</pre>");
      $asuonline_enterpriseclientid = isset($values['asuonline_enterpriseclientid']) ? trim($values['asuonline_enterpriseclientid']) : '';
      if($asuonline_enterpriseclientid == 'false' || $asuonline_enterpriseclientid == 'FALSE') {
        $asuonline_enterpriseclientid = '';
        // Set the Webform field value also to be empty to match with what we are posting.
        $webform_submission->setElementData('asuonline_enterpriseclientid', $asuonline_enterpriseclientid);
      }

      $domain = 'https://' . $_SERVER['HTTP_HOST'];


      //-----------------------------------------------------------------------------------------------
      // ASU Online post

      if($campus_options == 'ONLNE') {

        $submission_data_online = array(
          // Online fields:
          'area_of_interest' => $interest1,
          'first_name' => isset($values['first_name']) ? $values['first_name'] : '',
          'last_name' => isset($values['last_name']) ? $values['last_name'] : '',
          'email_address' => isset($values['email_address']) ? $values['email_address'] : '',
          'phone' => $phone_formatted,
          'program_key' => isset($values['program_of_interest']) ? $values['program_of_interest'] : '',
          'origin_uri' => $domain . '/' . $rfipage_alias,
          'lead_class' => $lead_class,
          'sub_class' => $sub_class,
          'enterpriseclientid' => $asuonline_enterpriseclientid,
          'sourceid' => $sourceid_online,
          'sms_permission' => 'Y',
        );

        foreach ($submission_data_online as $key => $value) {
          if($value == '') {
            unset($submission_data_online[$key]);
          }
        }
        $data_online = json_encode($submission_data_online);

        $curl_online = curl_init($post_url_online);
        curl_setopt($curl_online, CURLOPT_RETURNTRANSFER, TRUE); //If you don't want to use any of the return information, set to false
        curl_setopt($curl_online, CURLOPT_HEADER, TRUE); //Set this to false to remove informational headers
        curl_setopt($curl_online, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_online, CURLOPT_POSTFIELDS, $data_online); //data mapping
        curl_setopt($curl_online, CURLOPT_SSLVERSION, 1); //This will set the security protocol to TLSv1
        curl_setopt($curl_online, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_online, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Lead-Origin-Token: tC4JACQEfk7roXAP0wgYMyNJgoTK2qYWB9npPChq8yIDHswI'
          )
        );
        $response_online = curl_exec($curl_online);
        $info_online = curl_getinfo($curl_online);

        // curl_close($curl_online);

        \Drupal::logger('asuaec_asulocal')->notice('ASU Online post info_online: <pre>' . print_r($info_online, true) . '</pre>');

        // Error occured
        if (($info_online['http_code'] < 200) || ($info_online['http_code'] >= 300)) {
          \Drupal::logger('asuaec_asulocal')->notice('ASU Online post failed.<br /><pre>ASU Online post: ' . htmlspecialchars(print_r($response_online, true)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</pre>');
          if($env == 'prod') {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', array());
          } else {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<br /> <pre>ASU Online post: ' . htmlspecialchars(print_r($response_online, true)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</pre>', array());
          }
          $this->messenger()->addError($the_error);
        }
        // Success
        else {
          \Drupal::logger('asuaec_asulocal')->notice('Success - <pre><code>ASU Online post: ' . htmlspecialchars(print_r($submission_data_online, TRUE)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</code></pre>');
          if($env == 'dev') {
            $the_message = new TranslatableMarkup('Success - <pre>ASU Online post: ' . htmlspecialchars(print_r($response_online, true)) . '<br />ASU Online posted data:' . htmlspecialchars(print_r($submission_data_online, true)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</pre>', array());
            $this->messenger()->addMessage($the_message);
          }

        } // END OF else


      } // END OF online


  } // END OF public function postToMiddleware($webform_submission)



  /**
   * {@inheritdoc}
   *
   *  Post to middleware
   *
   *  Check to see if session variable exists. It could be already existed. If not existed, create one.
   *
   *  Send confirmation email.
   *  **Online - We don't send confirmation email.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // $env = $this->getEnv();

    // Posting
    $this->postToMiddleware($webform_submission);

  } // END OF public function postSave()



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


} // END OF class AsuLocalRfiWebformHandler