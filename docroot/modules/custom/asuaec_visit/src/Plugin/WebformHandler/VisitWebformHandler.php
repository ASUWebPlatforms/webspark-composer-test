<?php

namespace Drupal\asuaec_visit\Plugin\WebformHandler;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
// Use Drupal\rest\Plugin\ResourceBase;
// use Drupal\rest\ResourceResponse;.
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
// Use Throwable;
// use Drupal\Component\Render\FormattableMarkup;
// use Drupal\Core\Entity\ContentEntityInterface;
// use Drupal\field\Entity\FieldStorageConfig;
// use Drupal\views\ViewExecutable;.
use Drupal\media\Entity\Media;
// Use Drupal\webform\Entity\Webform;.
use Symfony\Component\HttpFoundation\RedirectResponse;

// phpcs:disable
/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "visit_webform_handler",
 *   label = @Translation("Posts to middleware. Creates Student Reg node. Also, sends confirmation email."),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Posts the submission to Middleware. Creates Student Reg node. Also, sends confirmation email."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class VisitWebformHandler extends WebformHandlerBase {
    public static $x = 0; // Prevent posting twice.

    public static $submission_data = [];
    public static $sid = '';
    public static $update = false;
    public static $env = '';
    public static $post_url = '';
    public static $ready_to_post = []; // Added on 1/19/2024. Set in validateForm().
    public static $event = []; // Added on 1/19/2024. Set in validateForm().
    private static $phone_info = [];

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @param webformSubmissionInterface $webform_submission
     *
     * Prevent from spam submissions. Added on 1/19/2024.
     */
    public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission){
        // Prevent from getting submission with "opportunityType": "[current-page:query:ptype]"
        // Get visitor_type
        $values = $webform_submission->getData();
        $visitor_type = isset($values['visitor_type']) ? $values['visitor_type'] : '';
        $valid_values = ['High school senior', 'High school junior', 'High school sophomore', 'High school freshman', 'College transfer', 'Graduate student', 'Other' ];
//        if($visitor_type == '[current-page:query:ptype]' || $visitor_type == '') {
        if (!in_array($visitor_type, $valid_values)) {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', array());
            $this->messenger()->addError($the_error);
            $form_state->setRebuild(); // Display message and stop submitting the form.
            (new RedirectResponse('/schedule'))->send(); // Redirect
            return;
        }

        // Add validation for Event values - "Don't trust client"

        // Part 1: Event related values -- DB query based on Event ID that was passed from Webform submission. Then, compare with submitted values.

        // eventId
        $entity_id = isset($webform_submission->getData()['event_id']) ? trim($webform_submission->getData()['event_id']) : ''; // Such as "1-1687280400"
        self::$ready_to_post['eventId'] = $entity_id;
        $temp_array = explode('-', $entity_id);
        $entity_id_eventseries = $temp_array[0];
//        ksm($entity_id_eventseries, "entity_id_eventseries"); // 176
        self::$event['entity_id_eventseries'] = $entity_id_eventseries;
        // Get date
        $date_obj_utc = DateTimePlus::createFromTimestamp($temp_array[1], new \DateTimeZone('UTC'));
        $event_date_formatted = $date_obj_utc->format('Y-m-d'); // In 24-hour format in UTC time
//        ksm($event_date_formatted, "event_date_formatted");

        // Get values from DB

        $database = \Drupal::database();
        $query = $database->select('eventinstance_field_data', 'eventinstance_field_data');

//        $query->addJoin('inner','eventseries_field_data','eventseries_field_data','eventinstance_field_data.eventseries_id = eventseries_field_data.id');
        $query->addJoin('inner','eventseries__field_evtype','eventseries__field_evtype','eventinstance_field_data.eventseries_id = eventseries__field_evtype.entity_id');
        $query->addJoin('inner','eventseries__field_campus','eventseries__field_campus','eventinstance_field_data.eventseries_id = eventseries__field_campus.entity_id');
//        $query->addJoin('inner','eventseries__field_capacity','eventseries__field_capacity','eventinstance_field_data.eventseries_id = eventseries__field_capacity.entity_id');

        $query->fields('eventinstance_field_data', ['id', 'eventseries_id', 'date__value', 'date__end_value']);
        $query->fields('eventseries__field_evtype', ['field_evtype_value']);
        $query->fields('eventseries__field_campus', ['field_campus_value']);
//        $query->fields('eventseries__field_capacity', ['field_capacity_value']);

        $query->condition('eventinstance_field_data.eventseries_id', $entity_id_eventseries,'=');
        $query->condition('eventinstance_field_data.date__value', $event_date_formatted . '%','LIKE');

        $result = $query->execute();
        $theevent = [];
        foreach($result as $data){
      // Keep the code just in case. For now just checking campus to compare data between from Webform and from database.
//            $theevent['instance_id'] = $data->id;
//            $theevent['eventseries_id'] = $data->eventseries_id;
//            $theevent['event_type'] = $data->field_evtype_value;
//            $theevent['start_datetime'] = $data->date__value;
//            $theevent['end_datetime'] = $data->date__end_value;

            $theevent['campus'] = $data->field_campus_value;
//            $event['capacity_series'] = $data->field_capacity_value; // This is parent series capacity.
        }

        // Capacity - Not being passed from Webform. Already looking up(getting from DB) in postToMiddleware() at line 1072;

        // Compare with values submitted through Webform
        $pass = true;


        // Campus
        $campus = isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '';
        if ($campus == 'ASU California Center in downtown L.A.') {
          $webform_submission->setElementData('campus', 'ASU California Center in downtown LA');
		  $campus = 'ASU California Center in downtown LA';
        }
//		ksm($webform_submission->getData()['campus'], "campus from Webform");
//		ksm($theevent['campus'], "from event in DB");
      // Comment it out because it is not working for Barrett Only (5/6/2025)
//        if($campus != $theevent['campus']) {
//            $pass = false;
//        } else {
//            self::$ready_to_post['eventLocation'] = $campus;
//            self::$ready_to_post['campusName'] = $campus;
//        }

//        ksm(self::$ready_to_post, "self ready_to_post");

        if ($pass == false) {
            $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', array());
            $this->messenger()->addError($the_error);
            $form_state->setRebuild(); // Display message and stop submitting the form.
            (new RedirectResponse('/schedule'))->send(); // Redirect
            return;
        }

        // END OF Part 1: Event related values -- DB query and compare with submitted values.


        //-----------------------
        // BriteVerify validation

        // Initialize variables.
        $ret_value = true;
        $error_message = '';

        // Define fields to validate.
        // Parent cell phones are not included because they are not posted to middleware and they are just text fields.
        $fields_to_validate = [
    'email_address' => ['type' => 'email', 'error_message' => 'Please check Student e-mail.'],
    'phone' => ['type' => 'phone', 'error_message' => 'Please check phone number.'],
    'parent1_email' => ['type' => 'email', 'error_message' => 'Please check the first parent e-mail.'],
    //      'parent1_cell_phone' => ['type' => 'phone', 'error_message' => 'Please check the first parent cell phone number.'],
    'parent2_email' => ['type' => 'email', 'error_message' => 'Please check the second parent e-mail.'],
    //      'parent2_cell_phone' => ['type' => 'phone', 'error_message' => 'Please check the second parent cell phone number.'],
    'email_address_additional' => ['type' => 'email', 'error_message' => 'Please check the additional e-mail address(es).'],
    ];

        // Loop through the fields and validate.
        foreach ($fields_to_validate as $field_name => $options) {
    //      \Drupal::logger('asuaec_visit')->notice('field_name:' . $field_name);
          $field_value = $form_state->getValue($field_name, '');
          if (!empty($field_value)) {
            $is_valid = $this->validateField($field_value, $options['type']);
            if (!$is_valid) {
              $error_message .= $options['error_message'] . '<br />';
              $form_state->setErrorByName($field_name, $this->t($options['error_message']));
              $form[$field_name]['#attributes']['class'][] = 'is-invalid';
              $form[$field_name]['#parents'] = []; // To prevent "Undefined array key" warning.
              $ret_value = false;
            }
          }
        }

        // Display the error message if validation fails.
        if (!$ret_value) {
          $the_error = new TranslatableMarkup($error_message, []);
          $this->messenger()->addError($the_error);
          $form_state->setRebuild();
        }

    } // END OF public function validateForm()


    /**
     * Validate a field based on its type (email or phone) - BriteVerify
     */
    private function validateField($value, $type) {
      if ($type === 'email') {
        $is_valid = true;      
        // $value can be multiple email addresses.
        // Split the email field value if there are multiple emails separated by a comma.
        if ($value) {
          $emailList = array_map('trim', explode(',', $value)); // Split by comma and trim spaces.

          foreach ($emailList as $email) {
            // Validate each individual email.
            if ($this->validateEmail($email) === 'INVALID') {
              //$form_state->setErrorByName($field, $this->t('The email %email is invalid.', ['%email' => $email]));
              $is_valid = false;
            }
          }
          return $is_valid;
        }
      }
      if ($type === 'phone') {
        $formatted_phone = preg_replace('/\D/', '', $value);
        return $this->validatePhone($formatted_phone) !== 'INVALID';
      }
      return false; // Unknown type.
    }


    /**
     * @return string
     */
    public function getEnv() {
      //return $_ENV['AH_SITE_ENVIRONMENT']; // Changed on 9/19/2025

      $env = 'dev';
      if (!empty($_ENV['AH_SITE_ENVIRONMENT'])) {
        // 01live and 01update are prod.
        if($_ENV['AH_SITE_ENVIRONMENT'] === '01live' || $_ENV['AH_SITE_ENVIRONMENT'] === '01update') {
          $env = 'prod';
        }
      }
      return $env;
    }

    /**
     * @return string
     *
     * Used for posting switch for ddev local environment
     */
    public function isDdev() {
      $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
      if(strpos($host, '.ddev.site') !== false) {
        return 'ddev';
      }
      return 'not_ddev';
    }

    /**
     * BriteVerify
     * Check email address
     */
    public function validateEmail($pEmailAddress) {
      try {
        // Load environment configuration
        $env = $this->getEnv(); // prod/dev
        \Drupal::logger('asuaec_visit')->notice('env:' . htmlspecialchars($env));

        // For non-PROD environments, allow test emails
        $atPos  = strpos($pEmailAddress, '@');
        $emailDomain = ($atPos !== false) ? strtolower(substr($pEmailAddress, $atPos + 1)) : '';

        // If it's DEV and the domain is exactly test.asu.edu, short-circuit as VALID.
        if ($env === 'dev' && $emailDomain === 'test.asu.edu') {
          return 'VALID';
        }

        // Otherwise (PROD or DEV with other email domains), call BriteVerify

        // BriteVerify API details
        $briteVerifyURL = 'https://bpi.briteverify.com/api/v1/fullverify';
        // Get key from settings.php
        //$settings = \Drupal::service('settings');
        // Get key from config
        //if (str_contains($env, "live") !== false) { // Changed on 9/19/2025
        if ($env === 'prod') {
          // $briteVerifyAPIKey = $settings->get('briteverify_key_prod');
          $briteVerifyAPIKey = \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_prod');
        } else {
          // $briteVerifyAPIKey = $settings->get('briteverify_key_dev');
          $briteVerifyAPIKey = \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_dev');
        }

        $jsonData = json_encode(['email' => $pEmailAddress]);
        $headers = [
      'Accept: application/json',
      'Content-Type: application/json',
      'Authorization: ApiKey: ' . $briteVerifyAPIKey
      ];

        // Initialize curl for the HTTP POST request
        $ch = curl_init($briteVerifyURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Drupal::logger('asuaec_visit')->notice('Response (BriteVerify email): <pre>' . htmlspecialchars(print_r($response, true)) . '</pre>');
        // Response (BriteVerify):{"errors":@"user":"Unauthorized. Credentials invalid or missing."}
        // Response (BriteVerify):{"errors":@"user":"Not authorized"}
//        \Drupal::logger('asuaec_visit')->notice('httpStatusCode (BriteVerify):' . print_r($httpStatusCode, true));
        // httpStatusCode (BriteVerify):401
        if ($httpStatusCode !== 200) {
          \Drupal::logger('asuaec_visit')->notice('httpStatusCode is not 200s (BriteVerify)');
          return 'INVALID';
        }

        $respObj = json_decode($response);
//          \Drupal::logger('asuaec_visit')->notice('respObj:' . print_r($respObj, true));

        // Assuming the response contains an "email" object with "status"
        if (isset($respObj->email)) {
          $status = $respObj->email->status;

//          if (strcasecmp($status, 'VALID') === 0 || strcasecmp($status, 'accept_all') === 0 || strcasecmp($status, 'unknown') === 0) {
          if (strcasecmp($status, 'INVALID') !== 0) {
            \Drupal::logger('asuaec_visit')->notice('Returning VALID for email (BriteVerify) - email: ' . $pEmailAddress);
            return 'VALID';
          } else {
            \Drupal::logger('asuaec_visit')->notice('Returning INVALID for email (BriteVerify) - email: ' . $pEmailAddress);
            return 'INVALID';
          }
        }
      } catch (Exception $ex) {
        //error_log('Error validating email: ' . $ex->getMessage());
        \Drupal::logger('asuaec_visit')->notice('Error validating email (BriteVerify): <pre>' . $ex->getMessage() . '</pre>');
      }

      // Return 'VALID' if there is an error
      return 'VALID';
    }

    /**
     * BriteVerify
     * Check phone
     * Also, need to return if it is mobile or not.
     */
    public function validatePhone($pPhone) {
      // Check if it starts with 1 or not. There are no two-digit country codes starting with ‘1’ such as ‘11’ or ‘12’ under the NANP.
      if (strpos(trim($pPhone), '1') !== 0) { // Not US/Canada: phone number does not starts with 1.
  //      \Drupal::logger('asuaec_visit')->notice('Does not starts with 1');
        return 'VALID';

      } else { // US/Canada
        try {
          // Load environment configuration
          $env = $this->getEnv(); // prod/dev

          // BriteVerify API details
          $briteVerifyURL = 'https://bpi.briteverify.com/api/v1/fullverify';
          //$settings = \Drupal::service('settings');
          // Get key from config
          //if (str_contains($env, "live") !== false) { // Changed on 9/19/2025
          if ($env === 'prod') {
            //$briteVerifyAPIKey = $settings->get('briteverify_key_prod');
            $briteVerifyAPIKey = \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_prod');
          } else {
            //$briteVerifyAPIKey = $settings->get('briteverify_key_dev');
            $briteVerifyAPIKey = \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_dev');
          }

          // \Drupal::logger('asuaec_visit')->notice('test:' . $briteVerifyAPIKey);
          $jsonData = json_encode(['phone' => $pPhone]);
          $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: ApiKey: ' . $briteVerifyAPIKey
        ];

          // Initialize curl for the HTTP POST request
          $ch = curl_init($briteVerifyURL);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

          $response = curl_exec($ch);
          $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);

          \Drupal::logger('asuaec_visit')->notice('Response (BriteVerify phone):' . htmlspecialchars(print_r($response, true)));
          // Response (BriteVerify):{"errors":@"user":"Unauthorized. Credentials invalid or missing."}
          // Response (BriteVerify):{"errors":@"user":"Not authorized"}
  //        \Drupal::logger('asuaec_visit')->notice('httpStatusCode (BriteVerify):' . print_r($httpStatusCode, true));
          // httpStatusCode (BriteVerify):401

          if ($httpStatusCode !== 200) {
            \Drupal::logger('asuaec_visit')->notice('httpStatusCode is not 200s (BriteVerify)');
            return 'INVALID';
          }

          $respObj = json_decode($response);
//          \Drupal::logger('asuaec_visit')->notice('respObj:' . print_r($respObj, true));
          // For non-PROD environments, allow test emails
          if (isset($respObj->phone)) {
            $status = $respObj->phone->status;
            // if it is not "INVALID", return VALID
            //if (strcasecmp($status, 'VALID') === 0 || strcasecmp($status, 'accept_all') === 0 || strcasecmp($status, 'unknown') === 0) {
            if (strcasecmp($status, 'INVALID') !== 0) {
              $service_type = $respObj->phone->service_type;
              // Save number and service type in Class variable.
              self::$phone_info[] = [
            'number' => $pPhone,
            'service_type' => $service_type,
            ];  
              \Drupal::logger('asuaec_visit')->notice('Returning VALID for phone (BriteVerify) - Phone: ' . $pPhone . ', Sevice type: ' . $service_type);
              return 'VALID';
            } else { // unknown
              \Drupal::logger('asuaec_visit')->notice('Returning INVALID for phone (BriteVerify) - Phone: ' . $pPhone);
              return 'INVALID';
            }
          }

        } catch (Exception $ex) {
          //error_log('Error validating email: ' . $ex->getMessage());
          \Drupal::logger('asuaec_visit')->notice('Error validating phone (BriteVerify): <pre>' . $ex->getMessage() . '</pre>');
        }

      }

      // Return 'VALID' if there is an error
      return 'VALID';
    }


    /**
     * {@inheritdoc}
     *
     * Grab json_string and insert the following into Webform submission
     * - additional tours and Barrett tours under Experience ASU
     * - campus
     *
     * Depending on Grad/Ugrad, grab appropriate interest and insert into interest_name field in Webform submission.
     *
     * Build SF Event name by concatenating Event type, campus and date.
     */
    public function preSave(WebformSubmissionInterface $webform_submission) {
        $webform_bundle = $webform_submission->bundle(); // "registration_form" for "Other" form

        //-------- Grab json_string ----------//
        $json_string = isset($webform_submission->getData()['json_string']) ? $webform_submission->getData()['json_string'] : '';
        $json_data_array = json_decode($json_string, true);

        //-------- Additional tours and Barrett tours under Experience ASU ------------------//
        // Grab additional tours and Barrett tours under Experience ASU from json_string and insert them into Webform submission

        // Additional tour(s)
        // Get Additional tour(s) from json string and insert it in Webform submission field (extra_tour_id_0, extra_tour_id_1, etc)
        $i = 0;
		if((isset($json_data_array[0]['addtour'])) && (!is_null($json_data_array[0]['addtour']))) {
			foreach($json_data_array[0]['addtour'] as $thetour) {
				$thetour_exploded = explode('|', $thetour);
				$webform_submission->setElementData('extra_tour_id_' . $i, $thetour_exploded[0]); // Save value to Webform field.
				$i++;
			}
		}

        // Barrett tour(s) under Experience ASU
        // Get Barrett tour(s) under Experience ASU from json string and insert it in Webform submission field (barrett_tour_id_0, barrett_tour_id_1, etc)
        $j = 0;
		if((isset($json_data_array[0]['addtour_barrett'])) && (!is_null($json_data_array[0]['addtour_barrett']))) {
			foreach($json_data_array[0]['addtour_barrett'] as $thetour) {
				$thetour_exploded = explode('|', $thetour);
				$webform_submission->setElementData('barrett_tour_id_' . $j, $thetour_exploded[0]); // Save value to Webform field.
				$j++;
			}
		}

//        //-------- Campus -------// <-- Get it from URL.
//        $campus = $json_data_array[0]['campus'];
//        $webform_submission->setElementData('campus', $campus);


        //-------- Interest -------//
        // Get Visitor type
        $visitor_type = isset($webform_submission->getData()['visitor_type']) ? $webform_submission->getData()['visitor_type'] : '';
        if($webform_bundle == 'visit_form') {
            if ($visitor_type == 'Graduate student') {
                $interest_name = isset($webform_submission->getData()['interest']) ? $webform_submission->getData()['interest'] : '';
            } else {
//                $interest_tid = isset($webform_submission->getData()['interest']) ? $webform_submission->getData()['interest'] : '';
//                if((isset($webform_submission->getData()['interest']) && !empty($webform_submission->getData()['interest'])) ||
//                    (isset($webform_submission->getData()['interest']) && !is_null($webform_submission->getData()['interest']))){

                if((isset($webform_submission->getData()['interest'])) &&
        (!empty($webform_submission->getData()['interest'])) &&
        (!is_null($webform_submission->getData()['interest']))
    ){
                    $interest_tid = $webform_submission->getData()['interest'];
                } else {
                    $interest_tid = '';
                }
                if(($interest_tid != '0') && ($interest_tid != '') && ($interest_tid != '[current-page:query:intid]')){
                    $interest_name = \Drupal\taxonomy\Entity\Term::load($interest_tid)->get('name')->value;
                } else {
                    $interest_name = '';
                }
            }
            $webform_submission->setElementData('interest_name', $interest_name);
        }

        // -------Campus------------//
        // Change "West" to "West Valley"
        $campus = isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '';
        if ($campus == 'West') {
          $webform_submission->setElementData('campus', 'West Valley');
        }
		
        // Change "ASU California Center in downtown L.A." to "ASU California Center"
        $campus = isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '';
        if ($campus == 'ASU California Center in downtown L.A.') {
          $webform_submission->setElementData('campus', 'ASU California Center in downtown LA');
        }

        //-------- SF event name -------//
        $event_type = isset($webform_submission->getData()['event_type']) ? $webform_submission->getData()['event_type'] : '';
        $event_date = isset($webform_submission->getData()['date']) ? $webform_submission->getData()['date'] : '';
        $campus = isset($webform_submission->getData()['campus']) ? $webform_submission->getData()['campus'] : '';
        $sf_event_name = $event_type . '_' . $campus . '_' . $event_date;
        $webform_submission->setElementData('sf_event_name', $sf_event_name);

//        //-------- Event instance ID -------// <-- Get it from URL.
//        foreach($json_data_array[0]['eventinstanceid'] as $theinstanceid) {
//            $webform_submission->setElementData('event_instance_id', $theinstanceid); // Save value to Webform field.
//        }

    } // END OF public function preSave(WebformSubmissionInterface $webform_submission)



    /**
     * {@inheritdoc}
     *
     * Post to middleware
     * Send confirmation email.
     * Create a Student Registered node.
     */
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
        // Check environment
        $domain = 'https://' . $_SERVER['HTTP_HOST'];
        $env = $this->getEnv();

        if($update == false && self::$x == 0) { // Webform submitted

            //------- Create Student registered node --------------//
            $is_barrett_tour_under_exp_asu = false;
            // createStudentRegisteredNode() also creates Additional tour registrant node of customer registers for Additional tour.
            $email_parts = $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu);

            //------- Send confirmation email for Exp ASU and the top-level Barrett tour --------------//
            \Drupal::logger('cstest')->notice('email_parts from postSave:<pre>' . htmlspecialchars(print_r($email_parts, true)) . '</pre>');
            $this->sendConfEmail($email_parts['student_reg'], $env, $webform_submission);

			//----call cancel registartion function if c_aid variable exists in the url and values exists in cancel_attendee_id webform submission field --- Added by Archana
//			if($webform_submission->getData()['cancel_attendee_id'] != "null"){ // Changed on 3/20/2025 by Chizuko to prevent from getting Php warnings.
//				$this->cancelRegistration($webform_submission);
//			}
            if(!empty($webform_submission->getData()['cancel_attendee_id']) && $webform_submission->getData()['cancel_attendee_id'] != "null"){
              $this->cancelRegistration($webform_submission);
            }

            //------- Create Student registered node for Barrett under Exp ASU --------------//
            // If Barrett under Exp ASU
            $barrett_tours_under_exp_asu = [];
            $barrett_tours_under_exp_asu = $this->getBarrettToursUnderExpAsu($webform_submission);
            foreach($barrett_tours_under_exp_asu as $barrett_tour_id) {
                $is_barrett_tour_under_exp_asu = true;
                $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu, $barrett_tour_id);
            }

            //------- Posting --------------//
            $this->postToMiddleware($webform_submission, $env, $update, 'registration-form');
//            \Drupal::logger('asuaec_visit')->notice('Testing: after function returned.');


        } else if($update == true && self::$x == 0) { // "Save" button was clicked from Webform submission

            //------- Update Student registered node --------------//
            $is_barrett_tour_under_exp_asu = false;
            $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu);

            // If Barrett under Exp ASU
            $barrett_tours_under_exp_asu = [];
            $barrett_tours_under_exp_asu = $this->getBarrettToursUnderExpAsu($webform_submission);
            foreach($barrett_tours_under_exp_asu as $barrett_tour_id) {
                $is_barrett_tour_under_exp_asu = true;
                $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu, $barrett_tour_id);
            }

            //------- Posting --------------//
            $this->postToMiddleware($webform_submission, $env, $update, 'registration-form');
        }

    } // END OF public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)


    /**
     * @param $email_parts
     * @param $env
     *
     * Send confirmation email.
     * For email to work, we also need hook_mail. See in the .module file.
     */
    public function sendConfEmail($email_parts, $env, $webform_submission) {

        if(($email_parts['email_address'] == '') || ($email_parts['subject'] == '')) {
            return;
        }

        //------------ Additional email addresses -------------//
        // NOTE: Reusing code from line 455. Result from the change: We send conf email to parent's email addresses for Ugrad.

        // Get Student's email
        $student_email = $email_parts['email_address'];
        $visitor_type = isset($webform_submission->getData()['visitor_type']) ? trim($webform_submission->getData()['visitor_type']) : '';
//        $student_email = isset($webform_submission->getData()['email_address']) ? trim($webform_submission->getData()['email_address']) : '';
        $rsvp_emails = '';
        // For Ugrad, grab email addresses under parent info section
        if($visitor_type == 'Graduate student') {
            $email_address_additional = $email_parts['email_address_additional'];
//            $email_address_additional = isset($webform_submission->getData()['email_address_additional']) ? trim($webform_submission->getData()['email_address_additional']) : '';
            if($email_address_additional != ''){
                $addittional_emails_array =explode(',', $email_address_additional);
                $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
            }
        } else if ($visitor_type == 'Other') {
            // There is no additional email addresses for "Other".
        } else { // Ugrad
            $parent1_email = isset($webform_submission->getData()['parent1_email']) ? trim($webform_submission->getData()['parent1_email']) : '';
            $parent2_email = isset($webform_submission->getData()['parent2_email']) ? trim($webform_submission->getData()['parent2_email']) : '';
            // Make sure parent1 and parent2 email are different from Student's email.
            $addittional_emails_array = [$parent1_email, $parent2_email] ;
            $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
        }
        //------------ END OF Additional email addresses -------------//

//        $email_address_additional = $email_parts['email_address_additional'];
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'asuaec_visit';
        $key = 'visit_conf_email';
        if($rsvp_emails == '') {
            $to = $email_parts['email_address'];
        } else {
            $to = $email_parts['email_address'] . ', ' . $rsvp_emails;
        }
        // Remove duplicate email address
        // Step 1: Convert to array, trimming each email
        $emails = array_map('trim', explode(',', $to));
        // Step 2: Remove duplicates
        $unique_emails = array_unique($emails);
        // Step 3: Convert back to comma-separated string
        $to_cleaned = implode(', ', $unique_emails);
//      ksm($to_cleaned, "to_cleaned");
      
        $params['subject'] = $email_parts['subject'];
        $params['message'] = $email_parts['email_body'];
        $params['reply-to'] = 'visitasu@asu.edu'; // Added on 8/30/2024
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to_cleaned, $langcode, $params, NULL, $send);

        if ($result['result'] !== true) {
            $the_message = new TranslatableMarkup('There was a problem sending your message and it was not sent.', array());
            $this->messenger()->addMessage($the_message);
            \Drupal::logger('asuaec_visit')->notice('Visit form - Email error: Confirmation email was not sent. - Email address:' . $to_cleaned);
            // Send an email to Chizuko also.
            $this->sendFailureNotificationEmail('visit_conf_email_failure', 'Visit form conf email failed', 'Visit form - Email error: Confirmation email was not sent. - Email address:' . $to_cleaned);

        } else {
            if($env == "dev") {
                $the_message = new TranslatableMarkup('Your confirmation email has been sent.', array());
                $this->messenger()->addMessage($the_message);
            }
        }

    } // END OF public function sendConfEmail($nid)

	
	/*** helper function to cancel registration of student trying to reschedule a new registration. Added by Archana 
	* @param $webform_submission
    **/
	public function cancelRegistration($webform_submission){
		$values =  $webform_submission->getData();
		//ksm($values);
		$previous_attend_id = $values['cancel_attendee_id'];
		$previous_event_id = $values['cancel_event_id'] ;
		if($previous_attend_id != null){
			$sid_explode = explode('-',$previous_attend_id);
			$sid = $sid_explode[1];
			$database = \Drupal::database();
			$nquery = $database->select('node__field_web_sub_id', 'wsi');
			$nquery->fields('wsi', ['entity_id']);
			$nquery->condition('wsi.field_web_sub_id_value', $sid,'=');
			$nresult = $nquery->execute();
			foreach($nresult as $ndata){
				$nid[$ndata->entity_id] = $ndata->entity_id;
			}
			//check if the student registered node has cancelled regitration field checked' If it's not checked, then delete those nodes
			if(!empty($nid)){
				//ksm($nid);
				if(sizeof($nid) > 0){
					$all_nids = implode(',',$nid);
					foreach($nid as $nodeid){
						$nid_to_delete = intval($nodeid);
						$node_data = Node::load($nid_to_delete);
						$node_data->delete();
					}

				}
				\Drupal::logger('Registration cancelled')->notice("$all_nids are deleted after rescheduling");
				$reason = "Wrong event/session";
				$data = array(
        'attendeeId' => $previous_attend_id,
        'eventId' => $previous_event_id,
        'reason' => $reason,
        );
				$payload = array();
				$payload = array($data);
				$domain = 'https://' . $_SERVER['HTTP_HOST'];
        $env = $this->getEnv();
				// Post URL switch depending on environment
        //if (str_contains($env, "live") !== false) { //Changed on 9/19/2025
        if ($env === 'prod') {
					  $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/cancel';
            $settings = \Drupal::service('settings'); // Changed on 10/2/2025
            $auth_value = $settings->get('auth');

				 } else {
					  $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
            if($this->isDdev() === 'ddev') { // Changed on 10/2/2025
              $auth_value = \Drupal::config('secrets.api')->get('auth');
            } else {
              $settings = \Drupal::service('settings');
              $auth_value = $settings->get('auth_qa');
            }

				 }
				//$username = \Drupal::config('secrets.api')->get('username');
				//$pass = \Drupal::config('secrets.api')->get('password');
        //$settings = \Drupal::service('settings');
        //$auth_value = $settings->get('auth'); // Changed on 10/2/2025
				$client = \Drupal::httpClient();
				try {
					//$auth = 'Basic '. base64_encode ($username . ':' . $pass);
					$auth = 'Basic '. base64_encode ($auth_value);
					$request = $client->post($post_url, [
          'json' => $payload,
          'method' => 'POST',
          'headers' => [
           'Authorization' => $auth,
           'Content-Type' => 'application/json',
          ]
          ]);
					$response = json_decode($request->getBody());

				}
				catch (RequestException $e){
					return FALSE;
				}
        \Drupal::logger('asuaec_visit')->notice('Cancel success:<pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($payload, true) . '<br />Post URL: ' . $post_url . '</pre>');

			}
		
	   }
	}
	/***** end of cancelRegistration function ***/


    /**
     * @param $webform_submission
     * @param $env
     * @param $update
     * @param $formpage_alias
     */
    public function postToMiddleware($webform_submission, $env, $update, $formpage_alias) {

        // Prevent from duplicate.
        if(($update == true && self::$x == 0) || ($update == false && self::$x == 0)) {

            // Get submission ID
            $sid = $webform_submission->id();
            // Get Event Series ID
            $event_series_id = isset($webform_submission->getData()['event_series_entity_id']) ? $webform_submission->getData()['event_series_entity_id'] : '';
            // Attendee ID
            $attendee_id = $event_series_id . "-" . $sid;
            // Interest
            $interest_name = isset($webform_submission->getData()['interest_name']) ? $webform_submission->getData()['interest_name'] : '';

            //-------Birth date---------//
            $date_of_birth = isset($webform_submission->getData()['birthdate']) ? $webform_submission->getData()['birthdate'] : ''; // 2000-01-02
            if($date_of_birth != '') {
                if (!$this->validateBirthdate($date_of_birth)) {
                    // If birthdate is older than 1900, post ''. TODO: This shouldn't happen because validation checks it.
                    $date_of_birth = '';
                }
            }

            //-------Phone--------//
            $phone = isset($webform_submission->getData()['phone']) ? $webform_submission->getData()['phone'] : '';
            // Remove "+" and "-"
            $phone_formatted = preg_replace('[\D]', '', $phone);

            //------- eventStartTime ---------//
            $event_start_timestamp = isset($webform_submission->getData()['start_timestamp']) ? $webform_submission->getData()['start_timestamp'] : '';
            // For $event_start_time_formatted, do it for America/Phoenix
            $date_obj_phx = DateTimePlus::createFromTimestamp($event_start_timestamp, new \DateTimeZone('UTC'));
            $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
            $event_start_time_formatted = $date_obj_phx->format('H:i:s'); // In 24-hour format in AZ time

            //------- eventEndTime ---------//
            $event_end_timestamp = isset($webform_submission->getData()['end_timestamp']) ? $webform_submission->getData()['end_timestamp'] : '';
            // For $event_end_time_formatted, do it for America/Phoenix
            $date_obj_phx2 = DateTimePlus::createFromTimestamp($event_end_timestamp, new \DateTimeZone('UTC'));
            $date_obj_phx2->setTimeZone(new \DateTimeZone('America/Phoenix'));
            $event_end_time_formatted = $date_obj_phx2->format('H:i:s'); // In 24-hour format in AZ time

            //----------- Visitor type (opportunityType) ------------//
            $visitor_type = isset($webform_submission->getData()['visitor_type']) ? trim($webform_submission->getData()['visitor_type']) : ''; // such as "High School Senior". For "Other" form, it is "Other"
            $visitor_type_titlecase = '';
            foreach (explode(' ', $visitor_type) as $word) {
                $visitor_type_titlecase .= ucfirst($word) . ' ';
            }

            //-------Parent --------//

            // Load the parent entity
            $entity_type_eventseries = 'eventseries';
            $entity_id_eventseries = $event_series_id;
            $entity_eventseries  = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries)->load($entity_id_eventseries);

            // Load the instance entity
            $eventinstance_id = isset($webform_submission->getData()['event_instance_entity_id']) ? $webform_submission->getData()['event_instance_entity_id'] : '';
            $entity_type_eventinstance = 'eventinstance';
            $entity_id_eventinstance = $eventinstance_id;
            $entity_eventinstance = \Drupal::entityTypeManager()->getStorage($entity_type_eventinstance)->load($entity_id_eventinstance);

            // Capacity
            // Check if Overwrite is checked or not in Event instance
            $overwrite_capacity = isset($entity_eventinstance->get('field_overwrite_capacity')->getValue()[0]['value']) ? $entity_eventinstance->get('field_overwrite_capacity')->getValue()[0]['value'] : '';
            $capacity = '';
            if($overwrite_capacity == '1') { // Overwrite conf letter
                $capacity = isset($entity_eventinstance->get('field_capacity_event_instance')->getValue()[0]['value']) ? $entity_eventinstance->get('field_capacity_event_instance')->getValue()[0]['value'] : '';
            } else {

                $capacity = isset($entity_eventseries->get('field_capacity')->getValue()[0]['value']) ? $entity_eventseries->get('field_capacity')->getValue()[0]['value'] :'';
            }

            //-------Children: Additional tours and Barrett under Exp ASU --------//

            // Grab Additional tour id(s) from Webform submission (extra_tour_0, extra_tour_1, etc)
            $children_addtour = $this->getAdditionalToursUnderExpAsuForPosting($webform_submission);

            // Grab Barrettt tour id(s) from Webform submission (barrett_tour_0, barrett_tour_1, etc)
            $children_barrett_under_expasu = $this->getBarrettToursUnderExpAsuForPosting($webform_submission);

            $children = array_merge($children_addtour, $children_barrett_under_expasu);


            //------------ Additional email addresses -------------//
            // Get Student's email
            $student_email = isset($webform_submission->getData()['email_address']) ? trim($webform_submission->getData()['email_address']) : '';
            $rsvp_emails = '';
            // For Ugrad, grab email addresses under parent info section
            if($visitor_type == 'Graduate student') {
                $email_address_additional = isset($webform_submission->getData()['email_address_additional']) ? trim($webform_submission->getData()['email_address_additional']) : '';
                if($email_address_additional != ''){
                    $addittional_emails_array =explode(',', $email_address_additional);
                    $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
                }
            } else if ($visitor_type == 'Other') {
                // There is no additional email addresses for "Other".
            } else { // Ugrad
                $parent1_email = isset($webform_submission->getData()['parent1_email']) ? trim($webform_submission->getData()['parent1_email']) : '';
                $parent2_email = isset($webform_submission->getData()['parent2_email']) ? trim($webform_submission->getData()['parent2_email']) : '';
                // Make sure parent1 and parent2 email are different from Student's email.
                $addittional_emails_array = [$parent1_email, $parent2_email] ;
                $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
            }

            //------------ Get Campus code for campusCode -------------//
            $campus = isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '';
            // dpm($campus, "campus - cstest");
            if ($campus !== '') {
              switch ($campus) {
                case 'Tempe':
                  $campus_code = 'TEMPE';
                  break;
                case 'Downtown Phoenix':
                  $campus_code = 'DTPHX';
                  break;
                case 'Polytechnic':
                  $campus_code = 'POLY';
                  break;
                case 'West Valley':
                  $campus_code = 'WEST';
                  break;
                case 'ASU California Center in downtown LA':
                  $campus_code = 'LOSAN';
                  break;
                default:
                  $campus_code = '';
              }
            }

            // schoolInfo
            $school_info = '';
            $hs_info = trim($webform_submission->getData()['hsname'] ?? '');
            $inst_info = trim($webform_submission->getData()['iname'] ?? '');
            if($hs_info ==='' && $inst_info ==='') {
              $school_info = '';
            }
            else if($hs_info !=='') {
              $school_info = $hs_info;
            }
            else if($inst_info !=='') {
              $school_info = $inst_info;
            }

            $submission_data = array(
    "events" => array(
    '0' => array(
    "campaignType" => "Event",
//                        "career" => "UGRD", // --> I don't have to worry about
    "campusCode" => $campus_code, // Added on 11/10/2025
    // Parent info
    "category" => isset($webform_submission->getData()['event_type']) ? trim($webform_submission->getData()['event_type']) : '', // Experience ASU, Burrett tour, etc.
    "eventId" => isset($webform_submission->getData()['event_id']) ? trim($webform_submission->getData()['event_id']) : '', // Such as "1-1687280400"
    "eventCapacity" => intval($capacity), // Overwritten value if it is overwritten
    "eventEndDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
    "eventEndTime" => $event_end_time_formatted, // Actual time.
    "eventLocation" => isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '', // Removed on 11/10/2025 and put it back on 11/12/2025
    "eventName" => isset($webform_submission->getData()['sf_event_name']) ? trim($webform_submission->getData()['sf_event_name']) : '', // Such as "Experience ASU_Tempe_2023/10/01 10:00:00". ***For Master form, this will be provided by Event team. Actual time.
    "eventStartDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
    "eventStartTime" => $event_start_time_formatted, // Actual time.
    "children" => $children,
    ),
    ),
    "academicInterest" => $interest_name,
    'attendeeId' => $attendee_id, // $event_series_id + $sid
    "birthdate"=> $date_of_birth,
    // 'campusName' => isset($webform_submission->getData()['campus']) ? trim($webform_submission->getData()['campus']) : '', // Tempe // Removed on 11/10/2025
    'citizenshipCountryCode' => isset($webform_submission->getData()['country_of_citizenship']) ? $webform_submission->getData()['country_of_citizenship'] : '', // US
    "city" => isset($webform_submission->getData()['city']) ? trim($webform_submission->getData()['city']) : '',
    "countryCode" => isset($webform_submission->getData()['country']) ? $webform_submission->getData()['country'] : '', // QUESTION: We need both citizenshipCountryCode and countryCode? YES
//                "dietaryRestrictions" => "Vegetarian", // ***This is for Master form.
    'email' => isset($webform_submission->getData()['email_address']) ? trim($webform_submission->getData()['email_address']) : '',
    'firstName' => isset($webform_submission->getData()['first_name']) ? trim($webform_submission->getData()['first_name']) : '',
    "rsvpEmails" => $rsvp_emails,
    'lastName' => isset($webform_submission->getData()['last_name']) ? trim($webform_submission->getData()['last_name']) : '',
    "middleName" => isset($webform_submission->getData()['middle_name']) ? trim($webform_submission->getData()['middle_name']) : '',
    "numberOfGuests" => isset($webform_submission->getData()['guests']) ? intval(trim($webform_submission->getData()['guests'])) : '',
    "opportunityType" => trim($visitor_type_titlecase), // such as "High School Senior". For "Other" form, it is "Other"
    "planCode" => isset($webform_submission->getData()['major']) ? trim($webform_submission->getData()['major']) : '', // HIARTMFA
    "phone" => $phone_formatted, // 14807663728 -- The same as RFI
    "mobilePhone" => isset(self::$phone_info[0]) && self::$phone_info[0]['service_type'] == 'mobile' ? self::$phone_info[0]['number'] : '',
    "postalCode" => isset($webform_submission->getData()['postal_code']) ? trim($webform_submission->getData()['postal_code']) : '',
    "programCode"=> isset($webform_submission->getData()['college']) ? trim($webform_submission->getData()['college']) : '', // GRHI
  //                "specialNeeds" => "Wheelchair", // ***This is for Master form.
    "stateCode" => isset($webform_submission->getData()['state']) ? trim($webform_submission->getData()['state']) : '',
    "street" => isset($webform_submission->getData()['address']) ? trim($webform_submission->getData()['address']) : '', // QUESTION: Shall I remove Adress 2 field? YES
    "term" => isset($webform_submission->getData()['entry_term']) ? trim($webform_submission->getData()['entry_term']) : '',
    "smsOptIn" => true,
    "formSource" => isset($webform_submission->getData()['event_id']) ? trim($webform_submission->getData()['event_id']) : '', // Added for testing at csdev49 on 3/5/2025
    "leadSource" => 'Event', // Added for testing at csdev49 on 3/5/2025
    "leadSourceSubtype" => "Visit", // Added for testing at csdev49 on 3/5/2025
    // Added on 11/7/2025
    "campusCode" => $campus_code,
    "asurite" => isset($webform_submission->getData()['asu_rite_id']) ? trim($webform_submission->getData()['asu_rite_id']) : '',
    "schoolInfo" => $school_info,
    );

            foreach ($submission_data as $key => $value) {
                if ($value == '') {
                    unset($submission_data[$key]);
                }
            }
//        ksm($submission_data, "submission_data");


//            $data = json_encode($submission_data); // ***In Json format. For Promise, use array, not Json. For Drupal way, use array, not Json.
//            ksm($data, "Json data");



            //------------ Posting ------------//

            //$settings = \Drupal::service('settings');
            // Post URL switch depending on environment
            //if (str_contains($env, "live") !== false) {
            if ($env === 'prod') { // Changed on 9/19/2025
//                $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
//                $post_url = 'https://crm-recruitment-event-router-dev.apps.asu.edu/v1/event/visit/register'; // Post to DEV
//                $post_url = 'https://crm-recruitment-event-router-qa.apps.asu.edu/v1/event/visit/register'; // Post to QA
              $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/register'; // Post to Prod SF
              $settings = \Drupal::service('settings');
              $auth_value = $settings->get('auth');

            } else {
//                $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
//                $post_url = 'https://crm-recruitment-event-router-dev.apps.asu.edu/v1/event/visit/register'; // Post to DEV
//                $post_url = 'https://crm-recruitment-event-router-qa.apps.asu.edu/v1/event/visit/register'; // Post to QA
              $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register'; // NEW Post to QA on 9/6/2023
              //$auth_value = $settings->get('auth_qa'); // Added a separate Secret key for Dev posting on 8/26/2025.
              if($this->isDdev() === 'ddev') { // Changed on 10/2/2025
                $auth_value = \Drupal::config('secrets.api')->get('auth');
              } else {
                $settings = \Drupal::service('settings');
                $auth_value = $settings->get('auth_qa');
              }

            }

            $domain = 'https://' . $_SERVER['HTTP_HOST'];
//            $username = \Drupal::config('secrets.api')->get('username');
//            $pass = \Drupal::config('secrets.api')->get('password');

            $payload = array();
            // Get payload as an associate array.
            $payload = $submission_data;
            $submit_handler_url = $post_url;

            // POST to API using Guzzle httpClient.
            $client = \Drupal::httpClient();
            try {
//                $auth = 'Basic '. base64_encode ($username . ':' . $pass);
                $auth = 'Basic '. base64_encode ($auth_value);
                $request = $client->post($submit_handler_url, [
        'json' => $payload,
        'method' => 'POST',
        'headers' => [
      'Authorization' => $auth,
      'Content-Type' => 'application/json',
        ]
    ]);
                $response = json_decode($request->getBody());
//                ksm($response, "response");
//                ksm($request, "request");

            } catch (RequestException $e) {
                // Log the exception/fail with copy of payload.
                $fail_msg = "Failed Visit form posting: <pre>"
                    . "\nDomain: " .  $domain
                    . "\nPost URL: " .  $post_url
//                    . "\nPAYLOAD " . var_export($payload, 1)
                    . "\nPosted data: " .  print_r($payload, TRUE)
                    . "\nEXCEPTION $e "
                    . "</pre>";
                \Drupal::logger('Visit form posting failure')->debug($fail_msg);
                // Email Chizuko
                $this->sendFailureNotificationEmail('visit_post_failure', 'Visit form posting failed', $fail_msg);
                if ($env === 'dev') {
                //if (str_contains($env, "live") === false) { // Changed on 9/19/2025
                    $the_message = new TranslatableMarkup('<pre>' . $fail_msg . '<br />Post URL: ' . $post_url . '</pre>', []);
                    $this->messenger()->addMessage($the_message);
                }
            }

            if(!is_null($request)) {

                if (($request->getStatusCode() < 200) || ($request->getStatusCode() >= 300)) {
                    // Error handling is in catch{}.
                }
                else { // Success
                    // Save submitted values back to Webform field for record
                    $webform_submission->setElementData('posted_data' , print_r($payload, TRUE));

                    if($update == true) { // If re-posted manually by clicking "Save", insert date/time in repost Webform field.
                        $webform_submission->setElementData('repost', date('Y-m-d H:i:s'));
                    }
                    $webform_submission->resave();

                    \Drupal::logger('asuaec_visit')->notice('Success - Posted data:<pre><code>' . print_r($payload, TRUE) . '<br />Post URL: ' . $post_url . '</code></pre>');

                    if ($env === 'dev') {
                    //if (str_contains($env, "live") === false) { // Changed on 9/19/2025
                        $the_message = new TranslatableMarkup('Success: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($payload, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
                        $this->messenger()->addMessage($the_message);
                    }

                } // END OF else
            }
            self::$x++;
        } // END OF if(self::$x == 0)
    } // END OF public function postToMiddleware($submission_data)


    /**
     * @param $addittional_emails_array
     * @param $student_email
     * @return string
     *
     * Remove duplicate email addresses and build comma separatted email string.
     * Also remove empty string.
     */
    public function removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email) {
        $rsvp_emails = '';
        // Clean $addittional_emails_array. Remove duplicate email addresses if there are any.
        $addittional_emails_array_cleaned = array_unique($addittional_emails_array); // Duplicate removed.
        $i = 0;
        foreach($addittional_emails_array_cleaned as $additional_email) {
            if($additional_email == $student_email) {
                unset($addittional_emails_array_cleaned[$i]);
            }
            if($additional_email == '') {
                unset($addittional_emails_array_cleaned[$i]);
            }
            $i++;
        }
        // Build comma-separated string
        $i = 0;
        foreach($addittional_emails_array_cleaned as $addittional_email) {
            if($i == 0){
                $rsvp_emails .= $addittional_email;
            } else {
                $rsvp_emails .= ',' . $addittional_email;
            }
            $i++;
        }
        return $rsvp_emails;
    }


    /**
     * @param $webform_submission
     * @return array[]
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     *
     * Returns children array for posting for Additional tour.
     */
    public function getAdditionalToursUnderExpAsuForPosting($webform_submission) {
        // Here let's get the following also:
        // - Actual date/time for Additional tour
        // - Actual timestamp for Additional tour
        // - Time only field that was used for building Additional tour ID
        $return_array = [];

        $visit_date = isset($webform_submission->getData()['visit_date']) ? $webform_submission->getData()['visit_date'] : '';
        $visit_date_formatted = str_replace('-', '', $visit_date); // yyyymmdd
        $event_series_entity_id = isset($webform_submission->getData()['event_series_entity_id']) ? $webform_submission->getData()['event_series_entity_id'] : '';
        $campus = isset($webform_submission->getData()['campus']) ? $webform_submission->getData()['campus'] : '';
        $add_tours = [];
        // Get value of Additional tour fields
        for ($i = 0; $i <= 10; $i++) {
            $add_tour_id = isset($webform_submission->getData()['extra_tour_id_' . $i]) ? $webform_submission->getData()['extra_tour_id_' . $i] : '';
            if($add_tour_id != '') {
                // Get para id
                $temp_array = explode('-', $add_tour_id);
                $addtour_paragraph_entity_id = $temp_array[1];

                // Load paragraph
                $entity_type = 'paragraph';
                $entity_id = $addtour_paragraph_entity_id;
                $entity_paragraph = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);

                //----- Addittional tour title -----//
                $addtour_title = isset($entity_paragraph->get('field_addtour_name')->getValue()[0]['value']) ? $entity_paragraph->get('field_addtour_name')->getValue()[0]['value'] : '';

                //----- Category: Additional tour type such as College session/Housing tour -----//
                $addtour_type = isset($entity_paragraph->get('field_addtour_type')->getValue()[0]['value']) ? $entity_paragraph->get('field_addtour_type')->getValue()[0]['value'] : '';

                //----- Capacity: Actual capacity for the date -----//
                $capacity_to_use = _get_capacity_for_addtour($entity_paragraph, $visit_date_formatted, $event_series_entity_id, $addtour_paragraph_entity_id);

                //----- Start time and end time (Actual time) -----//
                $timestamp_array = _get_timestamps_for_addtour($entity_paragraph, $visit_date_formatted, $event_series_entity_id, $addtour_paragraph_entity_id);
//                ksm($timestamp_array, "timestamp_array");

                // Actual start time timestamp
                $timestamp_datetime_start = $timestamp_array['start'];
                // Timestamp for Add tour ID
                $timestamp_for_addtourid = $timestamp_array['timestamp_for_addtourid'];

                // Create Date obj from timestamp
                // For America/Phoenix
                $date_obj_phx = DateTimePlus::createFromTimestamp($timestamp_datetime_start, new \DateTimeZone('UTC'));
                $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $time_start_formatted = $date_obj_phx->format('H:i:s');

                // Do the same for end time
                $timestamp_datetime_end = $timestamp_array['end'];

                // Create Date obj from timestamp
                // For America/Phoenix
                $date_obj_phx2 = DateTimePlus::createFromTimestamp($timestamp_datetime_end, new \DateTimeZone('UTC'));
                $date_obj_phx2->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $time_end_formatted = $date_obj_phx2->format('H:i:s');

				// If it is College tour
                // Get colleges for the additional tour - Added on 1/23/2024
				$department_college = '';
				if($addtour_type == 'College session') {
					$department_college_4char_code =  $entity_paragraph->get('field_college')->getValue()[0]['value'];
					// Changed on 12/27/2023. Allow multiple colleges associated per additional tour
					if(!is_null($department_college_4char_code)) {
						$department_college = $this->getCollegeNameFrom4charCollegeCode($department_college_4char_code);
					}
				}

                $return_array[$i] = array(
        "campaignType" => "Event",
        "category" => $addtour_type,
        "departmentCollege" => $department_college, // Added on 1/23/2024.
        "eventId" => $add_tour_id, // 1-331-1687880400
        "eventCapacity" => intval($capacity_to_use), // Overwritten value
        "eventEndDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
        "eventEndTime" => $time_end_formatted, // Acttual time
        "eventLocation" => $campus,
        "eventName" => $addtour_title,
        "eventStartDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
        "eventStartTime" => $time_start_formatted, // Actual time
    );

            } // END OF if($add_tour_id != '')
        }  // END OF foreach
        return $return_array;
    } // END OF public function getAdditionalToursUnderExpAsuForPosting($webform_submission)

    /**
     * @param $four_char_college_code
     * @return string
     *
     * Helper function to get Taxonomy name from description value.
     * Get college name from 4 character college code. ex. "UGES".
     * Added on 1/29/2024.
     */
    public function getCollegeNameFrom4charCollegeCode($four_char_college_code) {
        $college_name = '';
        // Get Taxonomy name from description value
        // Returns empty string if description value is not set.
        $database = \Drupal::database();
        $query = $database->select('taxonomy_term_field_data', 't');
        $query->fields('t', ['name']);
        $query->condition('t.description__value', $four_char_college_code,'=');
        $result = $query->execute();
        $records = $result->fetchAll();
        $num_results = count($records);
        if($num_results > 0 ) {
//            foreach($result as $data){
            foreach($records as $data){
                $college_name = isset($data->name) ? $data->name : '';
            }
        }
        return $college_name;
    }


    public function getBarrettToursUnderExpAsuForPosting($webform_submission) {
        $return_array = [];

        //------ Get date of the Visit in UTC in yyyymmdd format ------//

//        $visit_date = isset($webform_submission->getData()['visit_date']) ? $webform_submission->getData()['visit_date'] : '';
//        $visit_date_formatted = str_replace('-', '', $visit_date); // yyyymmdd
        // Let's use timestamp instead of $visit_date.
        $barrett_under_expasu_jsonlist = isset($webform_submission->getData()['barrett_under_expasu_jsonlist']) ? $webform_submission->getData()['barrett_under_expasu_jsonlist'] : '';
        $barrett_under_expasu_array = [];
        $barrett_under_expasu_array = json_decode($barrett_under_expasu_jsonlist);
//        ksm($barrett_under_expasu_array, "barrett_under_expasu_array");
        if(!is_null($barrett_under_expasu_array) && sizeof($barrett_under_expasu_array) > 0) {
            foreach($barrett_under_expasu_array as $barrett_under_expasu) {
                $barrett_under_expasu_exploded = explode('|', $barrett_under_expasu);
                $start_timestamp = $barrett_under_expasu_exploded[1];
            }
            $date_obj = DateTimePlus::createFromTimestamp(intval($start_timestamp), new \DateTimeZone('UTC'));
            $visit_date_formatted_utc = $date_obj->format('Ymd');
    //        ksm($visit_date_formatted_utc, "visit_date_formatted in UTC");
        }

        //------ END OF Get date of the Visit ------//

        $campus = isset($webform_submission->getData()['campus']) ? $webform_submission->getData()['campus'] : '';

        $barrett_tours = [];
        // Get value of Barrett fields
        for ($i = 0; $i <= 10; $i++) {
            $barrett_tour_id = isset($webform_submission->getData()['barrett_tour_id_' . $i]) ? $webform_submission->getData()['barrett_tour_id_' . $i] : '';
            if($barrett_tour_id != '') {
                array_push($barrett_tours, $barrett_tour_id);

                // Get Barrett top-level entity id
                $temp_array = explode('-', $barrett_tour_id);
                $entity_id_eventseries = $temp_array[0]; // <--- Barrett top-level entity id (Event series entity id)
//                ksm($entity_id_eventseries, "entity_id_eventseries");

                // Load the Barrett top-level entity
                $entity_type_eventseries = 'eventseries';
                $entity_eventseries = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries)->load($entity_id_eventseries);

                // Load the instance entity
                // NOTE: We need to use UTC date for $visit_date_formatted_utc.
                $entity_id_eventinstance = _get_barrett_eventinstanceid_from_date_and_eventseriesid($visit_date_formatted_utc, $entity_id_eventseries);
                //                ksm($entity_id_eventinstance, "entity_id_eventinstance");

                // Get Event type such as "Barrett tour"
                $event_type = $entity_eventseries->hasField('field_evtype') ? $entity_eventseries->get('field_evtype')->getValue()[0]['value'] : '';

                $capacity_to_use = '';
                $date_formatted2 = '';
                $datetime_foreventname = '';
                $date_formatted = '';
                if (!is_null($entity_id_eventinstance)) { // Added on 9/6/2023. So, the website doesn't get white screen with error message.
                    $entity_type_eventinstance = 'eventinstance';
                    $entity_eventinstance = \Drupal::entityTypeManager()->getStorage($entity_type_eventinstance)->load($entity_id_eventinstance);
    //                ksm($entity_eventinstance, "entity_eventinstance");

                    // Capacity
                    // Check if Overwrite is checked or not in Event instance
                    $overwrite_capacity = $entity_eventinstance->hasField('field_overwrite_capacity') && isset($entity_eventinstance->get('field_overwrite_capacity')->getValue()[0]['value']) ? $entity_eventinstance->get('field_overwrite_capacity')->getValue()[0]['value'] : '';
                    if ($overwrite_capacity == '1') { // Overwrite conf letter
                        $capacity_to_use = isset($entity_eventinstance->get('field_capacity_event_instance')->getValue()[0]['value']) ? $entity_eventinstance->get('field_capacity_event_instance')->getValue()[0]['value'] : '';
                    } else {
                        $capacity_to_use = isset($entity_eventseries->get('field_capacity')->getValue()[0]['value']) ? $entity_eventseries->get('field_capacity')->getValue()[0]['value'] : '';
                    }
    //                ksm($capacity_to_use, "capacity");

                    // Actual Start time and End time
                    // Look at Event instance
                    // Start time
                    $start_time_string = $entity_eventinstance->get('date')->getValue()[0]['value']; // 2023-07-19T14:00:00
                    $date_obj = \DateTime::createFromFormat("Y-m-d\TH:i:s", $start_time_string, new \DateTimeZone('UTC'));
                    $date_obj->setTimeZone(new \DateTimeZone('America/Phoenix'));
                    $date_formatted = $date_obj->format('H:i:s');
                    // Date/time for Event name such as "Barrett tour_Tempe_2023/10/01 10:00:00"
                    $datetime_foreventname = $date_obj->format('Y/m/d H:i:s');

                    // End time
                    $end_time_string = $entity_eventinstance->get('date')->getValue()[0]['end_value']; // 2023-07-19T15:30:00
                    $date_obj2 = \DateTime::createFromFormat("Y-m-d\TH:i:s", $end_time_string, new \DateTimeZone('UTC'));
                    $date_obj2->setTimeZone(new \DateTimeZone('America/Phoenix'));
                    $date_formatted2 = $date_obj2->format('H:i:s');

                } // END OF if (!is_null($entity_id_eventinstance))

                $return_array[$i] = array(
        "campaignType" => "Event",
        "category" => $event_type,
        "eventId" => $barrett_tour_id, // 6-1689778800
        "eventCapacity" => intval($capacity_to_use), // Overwritten value
        "eventEndDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
        "eventEndTime" => $date_formatted2, //$time_end_formatted, // Acttual time
        "eventLocation" => $campus,
        "eventName" => $event_type. "_" . $campus . "_" . $datetime_foreventname, // Such as "Experience ASU_Tempe_2023/10/01 10:00:00". Actual time.
        "eventStartDate" => isset($webform_submission->getData()['visit_date']) ? trim($webform_submission->getData()['visit_date']) : '', // YYYY-MM-DD
        "eventStartTime" => $date_formatted, // $time_start_formatted, // Actual time
    );

            } // END OF if($barrett_tour_id != '')
        } // END OF foreach
        return $return_array;
    }



    /**
     * @param $key -- "visit_post_failure" or "visit_conf_email_failure"
     * @param $subject
     * @param $message
     *
     * Helper function for sending failure email.
     */
    public function sendFailureNotificationEmail($key, $subject, $message) {

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'asuaec_visit';
//        $key = 'posting_failure'; <--- Passed in param.
        $to = 'chizuko.swanson@asu.edu';
        $params['subject'] = $subject;
        $params['message'] = $message;
        $params['reply-to'] = 'visitasu@asu.edu'; // Added on 8/30/2024
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
            switch($key) {
                case 'visit_post_failure':
                    $the_message = new TranslatableMarkup('Visit form posting: There was a problem sending a failur notification email and it was not sent.', array());
                    $this->messenger()->addMessage($the_message);
                    \Drupal::logger('asuaec_visit')->notice('Visit form posting - Email error: a failur notification email was not sent. - Email address:' . $to);
                    break;

                case 'visit_conf_email_failure':
                    $the_message = new TranslatableMarkup('Visit form conf email: There was a problem sending a failur notification email and it was not sent.', array());
                    $this->messenger()->addMessage($the_message);
                    \Drupal::logger('asuaec_visit')->notice('Visit form conf email - Email error: a failur notification email was not sent. - Email address:' . $to);
                    break;
            }
        }
    }

    /**
     * Helping function
     *
     * Returns true if birthdate is newer than 1900-01-01.
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

    /**
     * Create Student Registratered node.
     * Or, update Student Registratered node if already exsisted.
     * Visit event and Barrett Solo will share Student Registered Node.
     *
     * We will use  $barrett_tour_id when it is Barrett tour under Exp ASU ( when $is_barrett_tour_under_exp_asu is true )
     *
     * Also, creates Additional tour registrant node if customer registers for Additional tour.
     *
     * @param $webform_submission
     * @param $env
     * @param $update
     * @param false $is_barrett_tour_under_exp_asu
     * @param null $barrett_tour_id
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu = false, $barrett_tour_id = null) {

        $email_parts = []; // Returns nid of Student Reg node and Additional tour Reg node
        $ts_created = (int) $webform_submission->getCreatedTime(); // Unix seconds

        if($is_barrett_tour_under_exp_asu == true) {
            // $title = $webform_submission->id() . '|' . $barrett_tour_id . '|Barrett under Exp ASU'; // Changed on 10/14/2025.
            $title = $webform_submission->id() . '-' . $ts_created . '|' . $barrett_tour_id . '|Barrett under Exp ASU';
            $event_type = 'Barrett under Exp ASU';

            $event_id = $barrett_tour_id;
            $temp_array = explode('-', $event_id);
            $event_series_id = $temp_array[0];

            //------- Prepare date string ---------//
            $barrett_starttimestamp = '';
            $barrett_starttimestamp = $temp_array[1];
            if($barrett_starttimestamp != '') {
                // For $date_of_event, use UTC
                date_default_timezone_set('UTC'); //<--'UTC' is by default, but this line needed.
                $date_obj = DateTimePlus::createFromTimestamp($barrett_starttimestamp); // In DB, date is stored in UTC.
                $date_formatted_barrett = $date_obj->format('Y-m-d\TH:i:s'); // In UTC

                // For $evdate, do it for America/Phoenix
                date_default_timezone_set('UTC'); //<--'UTC' is by default, but this line needed.
                $date_obj_phx = DateTimePlus::createFromTimestamp($barrett_starttimestamp);
                $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $evdate_prep_phx = $date_obj_phx->format('D, m/d/y h:i a'); // Tue, 06/06/23 03:00 pm in AZ time
            }
            $date_of_event =isset($date_formatted_barrett) ? $date_formatted_barrett : '';
            $evdate = isset($evdate_prep_phx) ? $evdate_prep_phx : ''; // Tue, 06/06/23 03:00 pm
            //------- END OF Prepare date string ---------//

            $timestamp_for_eventid = ''; // <--- Empty for Barrett under Exp ASU for Student Reg node.
            $email_subject_line = ''; // <--- Empty for Barrett under Exp ASU for Student Reg node.

        }
        else { // Anything other than Barrett tour under Exp ASU: Top level Barrett tour and Experience ASU
            // title of the Student Registered node
            // $title = $webform_submission->id(); // Changed on 10/14/2025 because in Visit Dev site, Webform submissions are not copied over to Dev site. Therefore, conf email doesn't get sent from Visit Dev site because Webform submission ID already existed in the past.
            $title = $webform_submission->id() . '-' . $ts_created;
            $event_type = isset($webform_submission->getData()['event_type']) ? $webform_submission->getData()['event_type'] : '';
            $event_id = isset($webform_submission->getData()['event_id']) ? $webform_submission->getData()['event_id'] : '';
            $event_series_id = isset($webform_submission->getData()['event_series_entity_id']) ? $webform_submission->getData()['event_series_entity_id'] : '';
            $event_instance_id = isset($webform_submission->getData()['event_instance_entity_id']) ? $webform_submission->getData()['event_instance_entity_id'] : '';
            $temp_array = explode('-', $event_id);
            $timestamp_for_eventid = $temp_array[1];

            //------- Prepare date string ---------//
            $datetime_string = isset($webform_submission->getData()['date']) ? $webform_submission->getData()['date'] : ''; // AZ time
            if($datetime_string != '') {
                // For $date_of_event, use UTC
                date_default_timezone_set('America/Phoenix'); //<--'UTC' is by default. So, change it.
                $date_obj = \DateTime::createFromFormat("Y/m/d H:i:s", $datetime_string);
                $date_obj->setTimeZone(new \DateTimeZone('UTC')); // In DB, date is stored in UTC.
                $date_formatted = $date_obj->format('Y-m-d\TH:i:s'); // UTC time
                $evdate_prep = $datetime_string;
            }
            $date_of_event = isset($date_formatted) ? $date_formatted : ''; // Date
            $evdate = $evdate_prep; // Wed, 05/03/23 03:00 pm
            //------- END OF Prepare date string ---------//

        } // END OF else { // Anything other then Barrett tour under Exp ASU: Top level Barrett tour and Experience ASU

        $attendee_id = $event_series_id . '-' . $webform_submission->id();


        //------- Additional tour ---------//
        // NOTES:
        // Additional tour is only for Exp ASU. Not for Barrett tour.
        // Use $webform_submission to find out if it is Barrett tour or not.
        // if $event_type contains "Barrett tour", it is Barrett tour.
        //
        // Also, get the list of Barrett tours. This is also only for Exp ASU.
        $additional_tours_array = [];
        $list_of_barrett_tours_array = [];
        if($event_type == "Barrett tour") {
        } elseif($is_barrett_tour_under_exp_asu != true) { // It is Exp ASU. Not Barrett tour.
            // Grab Additional tour id(s) from Webform submission (extra_tour_0, extra_tour_1, etc)
            $additional_tours_array = $this->getAdditionalToursUnderExpAsu($webform_submission);
            // List of Barrett tours
            $list_of_barrett_tours_array = $this->getBarrettToursUnderExpAsu($webform_submission);
        }
        //------- END OF Additional tour ---------//


        //---------------- Confirmation email body ---------------------//
        // NOTES: Only for Exp ASU and Top-level Barrett solo submission.
        if($is_barrett_tour_under_exp_asu == true) {
            $conf_email_body = '';
        } else {
            $visit_date = isset($webform_submission->getData()['visit_date']) ? $webform_submission->getData()['visit_date'] : ''; // Y-m-d

            // NOTES: $expasu_actual_start_timestamp in parameter is used for getting start timestamp of the Exp ASU in Conf email to determine the position of Barrett paragraph.
            $expasu_actual_start_timestamp = isset($webform_submission->getData()['start_timestamp']) ? $webform_submission->getData()['start_timestamp'] : '';

            // Get Barrett under Exp ASU actual start time timestamp
            $barrett_under_expasu_jsonlist = ($webform_submission->getData()['barrett_under_expasu_jsonlist'] != '[]') ? $webform_submission->getData()['barrett_under_expasu_jsonlist'] : '';
            $earliest_actual_barrett_underexpasu_start_timestamp = $barrett_under_expasu_jsonlist != '' ? $this->getEarliestActualBarrettUnderExpAsuStartTimestamp($barrett_under_expasu_jsonlist) : '';

            $barrett_under_expasu_actual_start_timestamp = '';
            $conf_email_body = $this->buildConfEmailBody($event_series_id, $event_instance_id, $event_type, $visit_date, $webform_submission, $event_id, $attendee_id, $expasu_actual_start_timestamp, $additional_tours_array, $list_of_barrett_tours_array,  $earliest_actual_barrett_underexpasu_start_timestamp);
			
			// Pass Display title also for subject line
			// Get eventdisplaytitle
			//-------- Grab json_string ----------//
			$json_string = isset($webform_submission->getData()['json_string']) ? $webform_submission->getData()['json_string'] : '';
			$json_data_array = json_decode($json_string, true);
			$displaytitle = $json_data_array[0]['eventdisplaytitle'];
			
            $email_subject_line = $this->getEmailSubjectLine($event_type, $displaytitle);

        }
        //---------------- END OF Confirmation email body ---------------------//


        // Check if there is Student Registered node existed or not.
        // When updating existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions, the Student Registered node already exists. If that is the case, don't create another Student Registered node and update the existing node.
        $nids = $this->checkNodeAlreadyExists($title, 'student_registered_visits');
        $newnode_nid = '';
//        \Drupal::logger('cstest')->notice('additional_tours_array_EVOLUTION:<pre>' . print_r($additional_tours_array, true) . '</pre>');

//        if($already_exists == false ) { // This will run when someone submit the form.
        if (sizeof($nids) == 0) { // This will run when someone submit the form.

            // Grab values from $webform_submission and create node object of Student Registered Visit.
            $node = Node::create([
    'type'        => 'student_registered_visits',
    'title'       => $title,
    'field_event_type' => $event_type, // <-- If it is "Barrett tour", then it is Barrett Solo or Barrett under Exp ASU. The others will be "Experience ASU", "Sun Devil Day", etc.
    'field_barrett_under_exp_asu' => $is_barrett_tour_under_exp_asu,
    'field_student_event_id' => $event_id,
    'field_event_series_id' => $event_series_id,
    'field_student_date_of_event' => $date_of_event, // Date
    'field_student_evdate' => $evdate, // Text field: Tue, 06/06/23 03:00 pm
    'field_attendee_id' => $attendee_id,

    'field_timestamp_for_actual_tour' => isset($webform_submission->getData()['start_timestamp']) ? $webform_submission->getData()['start_timestamp'] : '',
    'field_timestamp_for_eventid' => $timestamp_for_eventid,

    'field_student_guests' => isset($webform_submission->getData()['guests']) ? $webform_submission->getData()['guests'] : '',
    'field_student_campus' => isset($webform_submission->getData()['campus']) ? $webform_submission->getData()['campus'] : '',
    'field_student_college' => isset($webform_submission->getData()['college']) ? $webform_submission->getData()['college'] : '',
    'field_student_major' => isset($webform_submission->getData()['major']) ? $webform_submission->getData()['major'] : '',
    'field_student_first_name' => isset($webform_submission->getData()['first_name']) ? $webform_submission->getData()['first_name'] : '',
    'field_student_last_name' => isset($webform_submission->getData()['last_name']) ? $webform_submission->getData()['last_name'] : '',
    'field_student_email' => isset($webform_submission->getData()['email_address']) ? $webform_submission->getData()['email_address'] : '',
    'field_web_sub_id' => $webform_submission->id(),
    'field_student_type' => isset($webform_submission->getData()['visitor_type']) ? $webform_submission->getData()['visitor_type'] : '',

    // Entry term
    'field_student_entry_term' => isset($webform_submission->getData()['entry_term']) ? $webform_submission->getData()['entry_term'] : '' ,


    'field_student_add1' => isset($webform_submission->getData()['address']) ? $webform_submission->getData()['address'] : '',
    'field_student_add2' => isset($webform_submission->getData()['address2']) ? $webform_submission->getData()['address2'] : '',
    'field_student_city' => isset($webform_submission->getData()['city']) ? $webform_submission->getData()['city'] : '',
    'field_student_state' => isset($webform_submission->getData()['state']) ? $webform_submission->getData()['state'] : '',
    'field_student_country' => isset($webform_submission->getData()['country']) ? $webform_submission->getData()['country'] : '',
    'field_student_zip' => isset($webform_submission->getData()['postal_code']) ? $webform_submission->getData()['postal_code'] : '',
    'field_stu_event_to_time' => isset($webform_submission->getData()['to_time']) ? $webform_submission->getData()['to_time'] : '',
    'field_student_add_email' => isset($webform_submission->getData()['email_address_additional']) ? $webform_submission->getData()['email_address_additional'] : '',
    'field_student_phone' => isset($webform_submission->getData()['phone']) ? $webform_submission->getData()['phone'] : '',

    // Email body
    'field_student_full_agenda' => $conf_email_body,
    'field_email_subject_line' => $email_subject_line,

    'field_parent1_fname' => isset($webform_submission->getData()['parent1_fname']) ? $webform_submission->getData()['parent1_fname'] : '',
    'field_parent1_lname' => isset($webform_submission->getData()['parent1_lname']) ? $webform_submission->getData()['parent1_lname'] : '',
    'field_parent1_email' => isset($webform_submission->getData()['parent1_email']) ? $webform_submission->getData()['parent1_email'] : '',
    'field_parent1_cell_phone' => isset($webform_submission->getData()['parent1_cell_phone']) ? $webform_submission->getData()['parent1_cell_phone'] : '',
    'field_parent1_relation' => isset($webform_submission->getData()['parent1_relation']) ? $webform_submission->getData()['parent1_relation'] : '',
    'field_parent2_fname' => isset($webform_submission->getData()['parent2_fname']) ? $webform_submission->getData()['parent2_fname'] : '',
    'field_parent2_lname' => isset($webform_submission->getData()['parent2_lname']) ? $webform_submission->getData()['parent2_lname'] : '',
    'field_parent2_email' => isset($webform_submission->getData()['parent2_email']) ? $webform_submission->getData()['parent2_email'] : '',
    'field_parent2_cell_phone' => isset($webform_submission->getData()['parent2_cell_phone']) ? $webform_submission->getData()['parent2_cell_phone'] : '',
    'field_parent2_relation' => isset($webform_submission->getData()['parent2_relation']) ? $webform_submission->getData()['parent2_relation'] : '',

    // TODO: PDF

    // Additional tours - Only for top level Exp ASU submission
    'field_student_extra_tours' => array_keys($additional_tours_array),
    // Barrett tours under Exp ASU - Only for top level Exp ASU submission
    'field_list_of_barrett_tours' => $list_of_barrett_tours_array,
    ]);

            $node->save();
            $nid = $node->nid->getValue()[0]['value'];
            $email_parts['student_reg']['nid'] = $nid ;
            $email_parts['student_reg']['email_body'] = $conf_email_body ;
            $email_parts['student_reg']['email_address'] = isset($webform_submission->getData()['email_address']) ? $webform_submission->getData()['email_address'] : '';
            $email_parts['student_reg']['email_address_additional'] = isset($webform_submission->getData()['email_address_additional']) ? $webform_submission->getData()['email_address_additional'] : '';
            $email_parts['student_reg']['subject'] = $email_subject_line;

        } // END OF if (sizeof($nids) == 0)
        else {
            // The Student Registered node with the title of the Webform submission id already exists.
            // This happens when click on Save on existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions to re-post to middleware. At the same time, it will update the Student Registered node with changed value if there is anything changed.


            foreach ($nids as $thenid) { // $nids array should contain just one element.
                $node = \Drupal\node\Entity\Node::load($thenid);

                $node->set('field_event_type', $event_type); // <-- If it is "Barrett tour", then it is Barrett Solo or Barrett under Exp ASU. The others will be "Experience ASU", "Sun Devil Day", etc.
                $node->set('field_barrett_under_exp_asu', $is_barrett_tour_under_exp_asu);
                $node->set('field_student_event_id', $event_id);
                $node->set('field_event_series_id', $event_series_id);
                $node->set('field_student_date_of_event', $date_of_event); // Date
                $node->set('field_student_evdate', $evdate); // Date
                $node->set('field_attendee_id', $event_series_id . '-' . $webform_submission->id());

                $node->set('field_student_guests', $webform_submission->getData()['guests']);
                $node->set('field_student_campus', $webform_submission->getData()['campus']);
                $node->set('field_student_college', $webform_submission->getData()['college']);
                $node->set('field_student_major', $webform_submission->getData()['major']);
                $node->set('field_student_first_name', $webform_submission->getData()['first_name']);
                $node->set('field_student_last_name', $webform_submission->getData()['last_name']);
                $node->set('field_student_email', $webform_submission->getData()['email_address']);
                $node->set('field_student_type', $webform_submission->getData()['visitor_type']);
                $node->set('field_student_semester', $webform_submission->getData()['entry_term']);

                $node->set('field_student_add1', $webform_submission->getData()['address']);
                $node->set('field_student_add2', $webform_submission->getData()['address2']);
                $node->set('field_student_city', $webform_submission->getData()['city']);
                $node->set('field_student_state', $webform_submission->getData()['state']);
                $node->set('field_student_country', $webform_submission->getData()['country']);
                $node->set('field_student_zip', $webform_submission->getData()['postal_code']);
                $node->set('field_stu_event_to_time', $webform_submission->getData()['to_time']);
                $node->set('field_student_add_email', $webform_submission->getData()['email_address_additional']);
                $node->set('field_student_phone', $webform_submission->getData()['phone']);

                // Email body
                $node->set('field_student_full_agenda', $conf_email_body);
                $node->set('field_email_subject_line', $email_subject_line);

                $node->set('field_parent1_fname', $webform_submission->getData()['parent1_fname']);
                $node->set('field_parent1_lname', $webform_submission->getData()['parent1_lname']);
                $node->set('field_parent1_email', $webform_submission->getData()['parent1_email']);
                $node->set('field_parent1_cell_phone', $webform_submission->getData()['parent1_cell_phone']);
                $node->set('field_parent1_relation', $webform_submission->getData()['parent1_relation']);
                $node->set('field_parent2_fname', $webform_submission->getData()['parent2_fname']);
                $node->set('field_parent2_lname', $webform_submission->getData()['parent2_lname']);
                $node->set('field_parent2_email', $webform_submission->getData()['parent2_email']);
                $node->set('field_parent2_cell_phone', $webform_submission->getData()['parent2_cell_phone']);
                $node->set('field_parent2_relation', $webform_submission->getData()['parent2_relation']);

                // TODO: PDF


                // Additional tours
                $node->set('field_student_extra_tours', array_keys($additional_tours_array));
                // Barrett tours under Exp ASU - Only for top level Exp ASU submission
                $node->set('field_list_of_barrett_tours', $list_of_barrett_tours_array);

                $node->save();
            }
        } // END OF else


        //---------------------------------------------------
        // Additional tour registrant node

        // Create Additioanl tour registrant node if there is any entry for additional tour
        foreach($additional_tours_array as $additional_tour_id => $additional_tour) {

            // Check if additional tour registrant node already exists or not using node title.
            // When updating existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions, the Additional tour registrant node already exists. If that is the case, don't create another Additional tour registrant node and update the existing node.

            $title = $webform_submission->id() . '|' . $additional_tour_id;
            $addtourregistrant_nids = $this->checkNodeAlreadyExists($title, 'additional_tour_registrant');
            $datetime_actual_addtour = $additional_tour['time_start_formatted'];
            $timestamp_actual_addtour = $additional_tour['timestamp_datetime_start'];
            $timeonly_for_addtourid = $additional_tour['time_for_addtourid_formatted'];
            $addtour_title = $additional_tour['addtour_title'];

            //------- Prepare date string ---------//
            $datetime_string = $datetime_actual_addtour; // 2023-08-14 1:30 pm in AZ time
            if($datetime_string != '') {
                // For $date_of_event, use UTC
                $date_obj = \DateTime::createFromFormat("Y-m-d g:i a", $datetime_string, new \DateTimeZone('America/Phoenix'));
                $date_obj->setTimeZone(new \DateTimeZone('UTC')); // In DB, date is stored in UTC.
                $date_formatted = $date_obj->format('Y-m-d\TH:i:s'); // UTC time
            }
            $date_of_event = isset($date_formatted) ? $date_formatted : ''; // Date


            if (sizeof($addtourregistrant_nids) == 0) {
                // Create nodes

                // Grab values from $webform_submission and create node object of Additional tour registrant.
                $node_addtourregistrant = Node::create([
        'type'        => 'additional_tour_registrant',
        'title'       => $title,
        'field_additional_tour_id' => $additional_tour_id,
        'field_attendee_id' => $event_series_id . '-' . $webform_submission->id(),
        'field_student_guests' => isset($webform_submission->getData()['guests']) ? $webform_submission->getData()['guests'] : '',
        'field_student_reg_nid' => $newnode_nid,
        'field_web_sub_id' => $webform_submission->id(),
        'field_parent_event_series_id' => $event_series_id,

        'field_datetime_actual_addtour' => $datetime_actual_addtour, // String field
        'field_actual_tour_date_time' => $date_of_event, // Date type field
        'field_timestamp_actual_addtour' => $timestamp_actual_addtour,
        'field_timeonly_for_addtourid' => $timeonly_for_addtourid,

        'field_student_first_name' => isset($webform_submission->getData()['first_name']) ? $webform_submission->getData()['first_name'] : '',
        'field_student_last_name' => isset($webform_submission->getData()['last_name']) ? $webform_submission->getData()['last_name'] : '',
        'field_student_email' => isset($webform_submission->getData()['email_address']) ? $webform_submission->getData()['email_address'] : '',
        'field_addtour_title' => $addtour_title,
    ]);
                $node_addtourregistrant->save();

            } else {
                // Update nodes

                // The Additional tour registrant node with the title (additional_tour_id + webform_submission_id) already exists.
                // This happens when click on Save on existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions to re-post to middleware. At the same time, it will update the Student Registered node with changed value if there is anything changed.

                foreach ($addtourregistrant_nids as $thenid) { // $addtourregistrant_nids array should contain just one element.
                    $node = \Drupal\node\Entity\Node::load($thenid);

                    $node->set('field_additional_tour_id', $additional_tour_id);
                    $node->set('field_attendee_id', $event_series_id . '-' . $webform_submission->id());
                    $node->set('field_student_guests', $webform_submission->getData()['guests']);
                    $node->set('field_student_reg_nid', $newnode_nid);
                    $node->set('field_web_sub_id', $webform_submission->id());
                    $node->set('field_parent_event_series_id', $event_series_id);

                    $node->set('field_datetime_actual_addtour', $datetime_actual_addtour);
                    $node->set('field_actual_tour_date_time', $date_of_event);
                    $node->set('field_timestamp_actual_addtour', $timestamp_actual_addtour);
                    $node->set('field_timeonly_for_addtourid', $timeonly_for_addtourid);

                    $node->set('field_student_first_name', $webform_submission->getData()['first_name']);
                    $node->set('field_student_last_name', $webform_submission->getData()['last_name']);
                    $node->set('field_student_email', $webform_submission->getData()['email_address']);

                    $node->set('field_addtour_title', $addtour_title);

                    $node->save();
                }
            } // END OF else

        } // END OF foreach($additional_tours_array as $additional_tour_id)


        //--------------------------------------------------
        // Return email parts
        \Drupal::logger('cstest')->notice('email_parts before return in reateStudentRegisteredNode():<pre>' . htmlspecialchars(print_r($email_parts, true)) . '</pre>');
        return $email_parts;

    } // END OF public function createStudentRegisteredNode()


    /**
     * @param $barrett_under_expasu_jsonlist
     * @return mixed
     *
     * Helping function
     *
     * Returns the smallest timestamp which is the earliest actual Barrett under Exp ASU start timestamp.
     */
    public function getEarliestActualBarrettUnderExpAsuStartTimestamp($barrett_under_expasu_jsonlist) {
        $json_data_array = json_decode($barrett_under_expasu_jsonlist, true);
        $timestamp_array = [];
        foreach($json_data_array as $barrett_tour) {
            $temp_array = explode('|', $barrett_tour);
            array_push($timestamp_array,$temp_array[1]);
        }
        sort($timestamp_array);
        return $timestamp_array[0];
    }


    public function getEmailSubjectLine($event_type, $displaytitle) {
        $subject = 'Your ASU visit';
//        if($event_type == 'Barrett tour') {
//            $subject = "Barrett visit confirmation";
//        }
//        else {
//            $subject = "Your " . $event_type . " visit";
//        }
		$subject = 'Your ASU visit - ' . $displaytitle; // Changed on 7/4/2024.
        return $subject;
    }


    /**
     * @param $eventseries_id
     * @param $eventinstance_id
     * @param $event_type
     * @param $visit_date
     * @param $webform_submission
     * @param null $additional_tours_array
     * @param null $list_of_barrett_tours_array
     * @param $exp_asu_start_timestamp - Used for getting start timestamp of the Exp ASU in Conf email to determine the position of Barrett paragraph.
     * @return string
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     *
     * Addes a link to cancel form: /cancel-registration?aid={attendee_id}&eventid={event_id}&etype=exp&source=conf-email&sid=65315&ptype=High%20school%20junior
     */
    public function buildConfEmailBody($eventseries_id, $eventinstance_id, $event_type, $visit_date, $webform_submission, $event_id, $attendee_id, $expasu_actual_start_timestamp, $additional_tours_array = null, $list_of_barrett_tours_array = null, $earliest_actual_barrett_underexpasu_start_timestamp = '') {
        // TODO: "Add to calendar"
        // TODO: PDF
        $ret_value = '';

        // Get Event type
//        ksm($event_type, "event_type"); // Experience ASU, Barrett under Exp ASU

        //------ Step 1: Get Confirmation letter nid ------//

        // Load the parent entity
        $entity_type_eventseries = 'eventseries';
        $entity_id_eventseries = $eventseries_id;
        $entity_eventseries  = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries)->load($entity_id_eventseries);

        // Load the instance entity
        $entity_type_eventinstance = 'eventinstance';
        $entity_id_eventinstance = $eventinstance_id;
        $entity_eventinstance = \Drupal::entityTypeManager()->getStorage($entity_type_eventinstance)->load($entity_id_eventinstance);

        // Check if Overwrite is checked or not in Event instance
        $overwrite_conf_letter = isset($entity_eventinstance->get('field_overwrite_conf_letter')->getValue()[0]['value']) ? $entity_eventinstance->get('field_overwrite_conf_letter')->getValue()[0]['value'] : '';
//            ksm($overwrite_conf_letter, "overwrite_conf_letter");

        $confletter_nid = '';
        if($overwrite_conf_letter == '1') { // Overwrite conf letter
            $confletter_nid = isset($entity_eventinstance->get('field_conf_letter_event_instance')->getValue()[0]['target_id']) ? $entity_eventinstance->get('field_conf_letter_event_instance')->getValue()[0]['target_id'] : '';
//                ksm($confletter_nid, "confletter_nid");
        } else {

            $confletter_nid = isset($entity_eventseries->get('field_confirmation_letter')->getValue()[0]['target_id']) ? $entity_eventseries->get('field_confirmation_letter')->getValue()[0]['target_id'] :'';
        }
//            ksm($confletter_nid, "confletter_nid");


        //------ Step 2: Load the Confirmation letter node ------//
        if($confletter_nid != '') {
            $confletter_node = \Drupal\node\Entity\Node::load($confletter_nid);
        } else {
            return $ret_value; // If Confirmation Letter node is not associated with Event series entity, return ''.
        }

        //------ Step 3:  Gather content from the Confirmation letter node. ------//

        $content = [];
        $domain = 'https://' . $_SERVER['HTTP_HOST'];


        // Get values of text fields:
        //  Section 1, 2, 3, 4, Experience ASU section title and Barrett paragraph
        $content['sec1'] = isset($confletter_node->get('field_section_1')->getValue()[0]['value']) ? $confletter_node->get('field_section_1')->getValue()[0]['value'] : '';
        $content['sec2'] = isset($confletter_node->get('field_section_2')->getValue()[0]['value']) ? $confletter_node->get('field_section_2')->getValue()[0]['value'] : ''; // "<h3>What to expect</h3> "
        $content['sec3'] = isset($confletter_node->get('field_section_3')->getValue()[0]['value']) ? $confletter_node->get('field_section_3')->getValue()[0]['value'] : ''; // "Late attendance policy"
        $content['sec4'] = isset($confletter_node->get('field_section_4')->getValue()[0]['value']) ? $confletter_node->get('field_section_4')->getValue()[0]['value'] : ''; // "Experience ASU check-in and parking details"
        $content['sec5'] = isset($confletter_node->get('field_section_5')->getValue()[0]['value']) ? $confletter_node->get('field_section_5')->getValue()[0]['value'] : ''; // Check-in and Parking
        $content['sec6'] = isset($confletter_node->get('field_section_6')->getValue()[0]['value']) ? $confletter_node->get('field_section_6')->getValue()[0]['value'] : ''; // Closing
        $content['barrett_paragraph'] = isset($confletter_node->get('field_barrett_paragraph')->getValue()[0]['value']) ? $confletter_node->get('field_barrett_paragraph')->getValue()[0]['value'] : '';
//            ksm($content['barrett_paragraph'], "barrett paragraph");  //<---This is Exp ASU's confirmation letter node.


        // Banner image
        // Changed from image type to media type on 9/10/2023.
//        $banner_image_id = isset($confletter_node->get('field_email_header_image')->getValue()[0]['target_id']) ? $confletter_node->get('field_email_header_image')->getValue()[0]['target_id'] : '';
        $banner_image_id = isset($confletter_node->get('field_media_image')->getValue()[0]['target_id']) ? $confletter_node->get('field_media_image')->getValue()[0]['target_id'] : '';
//        ksm($banner_image_id, "banner_image_id -- It is Media id"); // 89

        if($banner_image_id != '') {
            $media = Media::load($banner_image_id);
            $fid = $media->field_media_image->target_id;

//            $file = File::load($banner_image_id);
            $file = File::load($fid);
            $uri = $file->getFileUri();
            $content['banner_image_path'] = $domain . '/sites/default/files/' . substr($uri,9); // Remove "public://"

            // Alt
//            $content['banner_image_alt'] = isset($confletter_node->get('field_email_header_image')->getValue()[0]['alt']) ? $confletter_node->get('field_email_header_image')->getValue()[0]['alt'] : '';
            $content['banner_image_alt'] = isset($media->get('field_media_image')->getValue()[0]['alt']) ? $media->get('field_media_image')->getValue()[0]['alt'] : '';
//            ksm($content['banner_image_alt'], "alt");
        }

        // Get signature
        $signature_nid = isset($confletter_node->get('field_email_signature')->getValue()[0]['target_id']) ? $confletter_node->get('field_email_signature')->getValue()[0]['target_id'] : '';
        if($signature_nid != '') {
            // Load signature node
            $signature_node = \Drupal\node\Entity\Node::load($signature_nid);
            $content['signature'] = isset($signature_node->get('field_signature')->getValue()[0]['value']) ? $signature_node->get('field_signature')->getValue()[0]['value'] : '';
//              ksm($signature, "signature"); // "<p>Liz Hill</p>..."
        }

        // Get Check-in location
        $checkinloc_nid = isset($confletter_node->get('field_check_in_location')->getValue()[0]['target_id']) ? $confletter_node->get('field_check_in_location')->getValue()[0]['target_id'] : '';
        if($checkinloc_nid != '') {
            // Load Check-in location node
            $checkinloc_node = \Drupal\node\Entity\Node::load($checkinloc_nid);
            $checkinloc_image_id = isset($checkinloc_node->get('field_checkin_location_image')->getValue()[0]['target_id']) ? $checkinloc_node->get('field_checkin_location_image')->getValue()[0]['target_id'] : '';
            if($checkinloc_image_id != ''){ // Added if condition on 10/18/2024. Event team will start not using the Check-in image.
              $file_checkinloc = File::load($checkinloc_image_id);
              $uri_checkinloc = $file_checkinloc->getFileUri();
              $content['checkinloc_image_path'] = $domain . '/sites/default/files/' . substr($uri_checkinloc,9); // Remove "public://"
              $content['checkinloc_image_alt'] = isset($checkinloc_node->get('field_checkin_location_image')->getValue()[0]['alt']) ? $checkinloc_node->get('field_checkin_location_image')->getValue()[0]['alt'] : '';
            }
            $content['campus_map_url'] = isset($checkinloc_node->get('field_campus_map_url')->getValue()[0]['uri']) ? $checkinloc_node->get('field_campus_map_url')->getValue()[0]['uri'] : '';
            $content['googlemap_url'] = isset($checkinloc_node->get('field_checkin_loc_googlemap_url')->getValue()[0]['uri']) ? $checkinloc_node->get('field_checkin_loc_googlemap_url')->getValue()[0]['uri'] : '';
            $content['printable_map_url'] = isset($checkinloc_node->get('field_printable_map_url')->getValue()[0]['uri']) ? $checkinloc_node->get('field_printable_map_url')->getValue()[0]['uri'] : '';
        }


        //------ Step 4: Gather more items from $webform_submission ------//

        $content['first_name'] = isset($webform_submission->getData()['first_name']) ? $webform_submission->getData()['first_name'] : '';
        $content['guests'] = isset($webform_submission->getData()['guests']) ? $webform_submission->getData()['guests'] : '';
        $content['campus'] = isset($webform_submission->getData()['campus']) ? $webform_submission->getData()['campus'] : '';

        // Get eventdisplaytitle
        //-------- Grab json_string ----------//
        $json_string = isset($webform_submission->getData()['json_string']) ? $webform_submission->getData()['json_string'] : '';
        $json_data_array = json_decode($json_string, true);
        $content['eventdisplaytitle'] = $json_data_array[0]['eventdisplaytitle'];
//            ksm($content['eventdisplaytitle'], "eventdisplaytitle");


        //------ Step 5: Agenda ------//

        // Agenda step 1 - Get agendas from Conf letter node
        $agenda_para_array = !is_null($confletter_node->get('field_agenda')->getValue()) ? $confletter_node->get('field_agenda')->getValue() : []; // Array of para id
        $agenda_prepared_array = [];
        if(sizeof($agenda_para_array) > 0) {
            $agenda_prepared_array = $this->prepareAgendasArray($agenda_para_array, $visit_date, 'confletter_node', $confletter_node->id());
        }
//            ksm($agenda_prepared_array, "Agendas from Conf letter node");


        $barrett_paragraph_values_array = [];
        if($event_type != 'Barrett tour') { // It is Exp ASU.

            // Agenda step 2 - Get agendas from Additional tour if customer signed up. - Exp ASU submisson only

            // $additional_tours_array already contains agendas.
//                ksm($additional_tours_array, "additional_tours_array");

            // Combine and add agendas from Additional tours into $agenda_prepared_array
            foreach($additional_tours_array as $key => $value) {
//                    ksm($value['agendas'], "This is the agenda array within Additional tour");
                $agenda_prepared_array = array_merge($value['agendas'], $agenda_prepared_array);
            }
//                ksm($agenda_prepared_array, "Merged agendas from Conf letter node and Additional tours");


            // Agenda step 3 - Barrett under Exp ASU - Exp ASU submisson only
            // NOTES: For Barrett under Exp ASU, we look at the same tour of the top-level Barrett in order to find Conf letter nid. Agendas is included in conf letter node.
//                ksm($list_of_barrett_tours_array, "list_of_barrett_tours_array"); // "2-1687878000" - Event series id + start time

            foreach($list_of_barrett_tours_array as $barrett_tour) {
                $temp_array = explode('-', $barrett_tour);
                $barrett_eventseries_id = $temp_array[0];
                $timestamp = $temp_array[1];

                // Load Event series entity for the Barrett tour
                $entity_type_eventseries_b = 'eventseries';
                $entity_id_eventseries_b = $barrett_eventseries_id;
                $entity_eventseries_b  = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries_b)->load($entity_id_eventseries_b);
//                    ksm($entity_eventseries_b->get('field_confirmation_letter')->getValue()[0]['target_id'], "TEST: confletter nid b"); // <-- Academic facility tour conf letter node id

                // Get the Event instance id for the Barrett under Exp ASU
                $eventinstance_id_b = $this->getBarrettInstanceId($barrett_eventseries_id, $timestamp);

                // Load the instance entity
                $entity_type_eventinstance = 'eventinstance';
                $entity_id_eventinstance = $eventinstance_id_b;
                $entity_eventinstance_b = \Drupal::entityTypeManager()->getStorage($entity_type_eventinstance)->load($entity_id_eventinstance);

                // Check if Overwrite is checked or not in Event instance
                $overwrite_conf_letter_b = isset($entity_eventinstance_b->get('field_overwrite_conf_letter')->getValue()[0]['value']) ? $entity_eventinstance_b->get('field_overwrite_conf_letter')->getValue()[0]['value'] : '';

                $confletter_nid = '';
                if($overwrite_conf_letter_b == '1') { // Overwrite conf letter
                    $confletter_nid_b = isset($entity_eventinstance_b->get('field_conf_letter_event_instance')->getValue()[0]['target_id']) ? $entity_eventinstance_b->get('field_conf_letter_event_instance')->getValue()[0]['target_id'] : '';
                } else {

                  
                    $confletter_nid_b = isset($entity_eventseries_b->get('field_confirmation_letter')->getValue()[0]['target_id']) ? $entity_eventseries_b->get('field_confirmation_letter')->getValue()[0]['target_id'] :'';
                }
//                    ksm($confletter_nid_b, "confletter_nid_b"); // <-- Academic facility tour conf letter node id

                if($confletter_nid_b != '') {
                    $confletter_node_b = \Drupal\node\Entity\Node::load($confletter_nid_b);
                }

                // Also, get Barrett paragraph value here -- 8/6/2023
                $barrett_paragraph_value = $confletter_node_b->hasField('field_barrett_paragraph') ? $confletter_node_b->get('field_barrett_paragraph')->getValue()[0]['value'] : '';
                $barrett_paragraph_values_array[$confletter_node_b->id()] = [
        'barrett_paragraph_value' => $barrett_paragraph_value,
        'start_timestamp' => $timestamp,
        ];

//                    $agenda_para_array_b = !is_null($confletter_node_b->get('field_agenda')->getValue()) ? $confletter_node_b->get('field_agenda')->getValue() : []; // Array of para id
                $agenda_para_array_b = $confletter_node_b->hasField('field_agenda') ? $confletter_node_b->get('field_agenda')->getValue() : []; // Array of para id
                $agenda_prepared_array_b = [];
                if(sizeof($agenda_para_array_b) > 0) {
                    $agenda_prepared_array_b = $this->prepareAgendasArray($agenda_para_array_b, $visit_date, 'confletter_node', $confletter_node_b->id());
                }
//                    ksm($agenda_prepared_array_b, "Barrettt Agendas from Conf letter node");

                // Combine and add agendas from $agenda_prepared_array_b into $agenda_prepared_array
                $agenda_prepared_array = array_merge($agenda_prepared_array_b, $agenda_prepared_array);



            } // END OF foreach($list_of_barrett_tours_array as $barrett_tour)
//                ksm($agenda_prepared_array, "Merged agendas after added Barrett under Exp ASU");

        } // END OF if($event_type != 'Barrett tour')


        // Agenda step 4: Sort $agenda_prepared_array in chronological order

        if(isset($agenda_prepared_array) && $agenda_prepared_array != null && sizeof($agenda_prepared_array)) {
            usort($agenda_prepared_array, function($a, $b) {
    return $a['start_timestamp'] <=> $b['start_timestamp'];
        });
//            ksm($agenda_prepared_array, "Agendas sorted");
        }

        if(isset($barrett_paragraph_values_array) && $barrett_paragraph_values_array != null && sizeof($barrett_paragraph_values_array) > 0) {
            usort($barrett_paragraph_values_array, function($a, $b) {
    return $a['start_timestamp'] <=> $b['start_timestamp'];
        });
//            ksm($barrett_paragraph_values_array, "barrett_paragraph_values_array - after sort");
        }


        //------ Step 6: Call prepareHtmlEmailParts() and applyEmailTemplate(). ------//
        // NEW CHANGE: Need submission_id and ptype to create Cancel Url in prepareHtmlEmailParts function. (4/17/2025)
        $submission_id = $webform_submission->id();
//        ksm($submission_id, "submission_id");
        $ptype = isset($webform_submission->getData()['visitor_type']) ? $webform_submission->getData()['visitor_type'] : '';
//        ksm($ptype, "ptype");

        $html_email_parts = [];
        $html_email_parts = $this->prepareHtmlEmailParts($content, $event_type, $agenda_prepared_array, $expasu_actual_start_timestamp, $earliest_actual_barrett_underexpasu_start_timestamp, $event_id, $attendee_id, $domain, $checkinloc_nid, $barrett_paragraph_values_array, $submission_id, $ptype);
        // NOTE: Cancel form link is included in $html_email_parts['main_content'].
        $ret_value = $this->applyEmailTemplate($html_email_parts['main_content'], $html_email_parts['banner_image_path'], $html_email_parts['banner_image_alt'], $content['first_name']);

        return $ret_value;
    } // END OF public function buildConfEmailBody






    /**
     * @param $eventseries_id
     * @param $timestamp
     * @return int|string|null
     *
     * Helping function
     *
     * Returns Event instance id.
     */
    public function getBarrettInstanceId($eventseries_id, $timestamp) {

        // Barrett
        // Can we find out event instance id from event series id and date(timestamp)?

        // Create Date obj from timestamp
        // For America/Phoenix
        $date_obj_phx = DateTimePlus::createFromTimestamp($timestamp);
        $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
        $date = $date_obj_phx->format('Ymd'); // yyyymmdd format
//            ksm($date, "time_start_formatted");

        $view = \Drupal\views\Views::getView('barrett_find_event_instance_id');
        $view->setArguments([$date, $eventseries_id]); // Date in yyyymmdd format
        $view->setDisplay('default');
        $view->execute();
        // Get the results of the view. There should be only one result. ex. 505
        $view_result = $view->result;
        foreach ($view_result as $row) {
            $instance_id = $row->_entity->id();
        }
        return $instance_id;
    }



    /**
     * @param $agenda_array
     * @param $visit_date
     * @return array
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     *
     * Helping function
     *
     * Returns multi-dimensional array about agendas that will be used in conf email.
     *
     * $parent_id can be Additional tour id or Conf letter nid
     *
     * Also, added Barrett pagagraph. (8/6/2023)
     */
    public function prepareAgendasArray($agenda_para_array, $visit_date, $agenda_type, $parent_id = null) {
        $agenda_prepared_array = [];
        foreach($agenda_para_array as $agenda_para) {

            // Load paragraph - Agenda para inside the conf letter node
            $entity_type = 'paragraph';
            $entity_id = $agenda_para['target_id'];
            $entity_paragraph = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
            //        ksm($entity_paragraph, "entity_para");

            //----- Get Agenda time and text -----//

            // Agenda text
            $agenda_text = '';
            if($entity_paragraph->hasField('field_agenda_text') == true && $entity_paragraph->get('field_agenda_text')->getValue() != false) {
                $agenda_text = $entity_paragraph->get('field_agenda_text')->getValue()[0]['value'];
            }

            // Agenda time from

            $timestamp_datetime_start = '';
            $agenda_time_start_formatted_webstandard = '';
            $agenda_timestamp_from = isset($entity_paragraph->get('field_agenda_timerange')->getValue()[0]['from']) ? $entity_paragraph->get('field_agenda_timerange')->getValue()[0]['from'] : '';
            if($agenda_timestamp_from != '') {
                //----- Get timestamp for agenda -----//

                // Agenda start timestamp

                // Format time
//                ksm($visit_date, "visit date"); // Y-m-d 2023-06-27
                $datenew = \DateTime::createFromFormat("Y-m-d g:i a", $visit_date . " 00:00 am");
                $timestamp_ofthedateat0am = $datenew->getTimestamp(); // Timestamp of the date at 0:00am
//                    ksm($timestamp_ofthedateat0am, "timestamp_ofthedateat0am");

                $timestamp_datetime_start = $timestamp_ofthedateat0am + $agenda_timestamp_from; // Add timestamp of the date at 0:00am and timestamp for the event time.
//            ksm($timestamp_datetime_start, "timestamp_datetime_start");

                //----- END OF Get timestamp for agenda -----//

                //----- Get human-readable time only for agenda -----//
                // Create Date obj from timestamp
                // For America/Phoenix
                $date_obj_phx = DateTimePlus::createFromTimestamp($timestamp_datetime_start);
                $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $agenda_time_start_formatted = $date_obj_phx->format('g:i a');
//            ksm($agenda_time_start_formatted, "time_start_formatted");
                $agenda_time_start_formatted_webstandard = str_replace("am","a.m.",$agenda_time_start_formatted);
                $agenda_time_start_formatted_webstandard = str_replace("pm","p.m.",$agenda_time_start_formatted_webstandard);
                //----- END OF Get human-readable date/time for actual Additional tour -----//

            } // END OF if($agenda_timestamp_from != '')

            $agenda_prepared_array[$entity_id] = [
    'time' => $agenda_time_start_formatted_webstandard,
    'start_timestamp' => $timestamp_datetime_start,
//                'time2' => $agenda_time_end_formatted_webstandard,
    'text' => $agenda_text,
    'agenda_type' => $agenda_type,
    'parent_id' => $parent_id, // Conf letter nid or Additional tour paragraph id
    ];

        } // END OF foreach
        return $agenda_prepared_array;
    }


    /**
     * @param $main_content
     * @param $first_name
     * @param $banner
     * @return string
     *
     * Apply Email template
     */
    public function applyEmailTemplate($main_content, $banner_img_path, $banner_img_alt, $first_name) {
      
        $output = '';

        $output = <<<EOD
<!DOCTYPE html "-//w3c//dtd xhtml 1.0 transitional //en" "http://www.w3.org/tr/xhtml1/dtd/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
  <!--[if gte mso 9]><xml>      <o:OfficeDocumentSettings>       <o:AllowPNG/>       <o:PixelsPerInch>96</o:PixelsPerInch>      </o:OfficeDocumentSettings>     </xml><![endif]-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta name="x-apple-disable-message-reformatting">
    <!--target dark mode-->
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark only">
    <title>Arizona State University
    </title>
    <!-- Allow for better image rendering on Windows hi-DPI displays. -->
    <!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
    <!--to support dark mode meta tags-->
    <style type="text/css">
      :root {
        color-scheme: light dark;
        supported-color-schemes: light dark;
      }
    </style>
    <!--webfont code goes here-->
    <!--[if (gte mso 9)|(IE)]><!-->
    <!--webfont <link /> goes here-->
    <style>
      /*Web font over ride goes here
      h1, h2, h3, h4, h5, p, a, img, span, ul, ol, li { font-family: 'webfont name', Arial, Helvetica, sans-serif !important; } */
    </style>
    <!--<![endif]-->
    <!--[if gte mso 16]> 
<style> 
.keep-black {
mso-style-textfill-type:gradient;
mso-style-textfill-fill-gradientfill-stoplist:"0 \#000000 1 100000\,99000 \#000000 1 100000";
color:#fff !important;
} 
</style> 
<![endif]-->
    <style id="media-query">
      /* Client-specific Styles & Reset */
      v\:* {
        behavior: url(#default#VML);
        display: inline-block
      }
      #outlook a {
        padding: 0;
      }
      a[x-apple-data-detectors] {
        color: inherit !important;
        text-decoration: none !important;
        font-size: inherit !important;
        font-family: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
      }
      u + .body .gmail-blend-screen{
        background:#000;
        mix-blend-mode:screen;
      }
      u + .body .gmail-blend-difference {
        background:#000;
        mix-blend-mode:difference;
      }
      u + .body .gmailhack {
        background:#fff;
      }
      /* .ExternalClass applies to Outlook.com (the artist formerly known as Hotmail) */
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
        line-height: 100%;
      }
      #backgroundTable {
        margin: 0;
        padding: 0;
        width: 100% !important;
        line-height: 100% !important;
      }
      /* Buttons */
      .button a {
        display: inline-block;
        text-decoration: none;
        -webkit-text-size-adjust: none;
        text-align: center;
      }
      .button a div {
        text-align: center !important;
      }
      /* Outlook First */
      body.outlook p {
        display: inline !important;
      }
      /*  Media Queries */
      @media only screen and (max-width: 500px) {
        table[class="body"] img.fullwidth {
          max-width: 100% !important;
        }
        table[class="body"] center {
          min-width: 0 !important;
        }
        table[class="body"] .container {
          width: 95% !important;
        }
        table[class="body"] .row {
          width: 100% !important;
          display: block !important;
        }
        table[class="body"] .wrapper {
          display: block !important;
          padding-right: 0 !important;
        }
        table[class="body"] .columns, table[class="body"] .column {
          table-layout: fixed !important;
          float: none !important;
          width: 100% !important;
          padding-right: 0px !important;
          padding-left: 0px !important;
          display: block !important;
        }
        table[class="body"] .wrapper.first .columns, table[class="body"] .wrapper.first .column {
          display: table !important;
        }
        table[class="body"] table.columns td, table[class="body"] table.column td, .col {
          width: 100% !important;
        }
        table[class="body"] table.columns td.expander {
          width: 1px !important;
        }
        table[class="body"] .right-text-pad, table[class="body"] .text-pad-right {
          padding-left: 10px !important;
        }
        table[class="body"] .left-text-pad, table[class="body"] .text-pad-left {
          padding-right: 10px !important;
        }
        table[class="body"] .hide-for-small, table[class="body"] .show-for-desktop {
          display: none !important;
        }
        table[class="body"] .show-for-small, table[class="body"] .hide-for-desktop {
          display: inherit !important;
        }
        *[class=nomobile]{
          display:none !important;
        }
        *[class=mobilefullwidth]{
          width:100% !important;
          height: auto !important;
        }
      }
      @media screen and (max-width: 700px) {
        div[class="col"] {
          width: 100% !important;
        }
        .mobilefont {
          font-size:14px !important;
        }
        h1 {
          font-size:24px !important;
          line-height:32px !important;
      }
        .mobilefontheader {
          font-size:24px !important;
          line-height:32px !important;
          background-color:#ffc627 !important;
          color:#000 !important;
        }
        .mobilefonthero {
          font-size:48px !important;
          line-height: 48px !important;
          padding: 24px 0px 14px 0px !important;
        }
        .mobilebg {
          background-color: #FFFFFF !important;
          background-color: #FFFFFF !important;
          border: none !important;
        }
        .fullwidth {
          width: 100% !important;
        }
      }
      @media screen and (min-width: 701px) {
        table[class="container"] {
          width: 700px !important;
        }
      }
    </style>
    <style>
      @media (prefers-color-scheme: light) {
        .dark-img {
          display: none !important;
        }
        .light-img {
          display: block !important;
          width: auto !important;
          overflow: visible !important;
          float: none !important;
          max-height: inherit !important;
          max-width: 240px !important;
          line-height: auto !important;
          margin-top: 0px !important;
          visibility: inherit !important;
        }
        .linkHover {
          color: #8c1d40 !important;
          transition: .5s !important
        }
        .linkHover:hover {
          color: #cb2a5d !important;
          transition: .5s !important;
        }
      }
      @media (prefers-color-scheme: dark) {
        /* Shows Dark Mode-Only Content, Like Images */
        .dark-img {
          display: block !important;
          width: auto !important;
          overflow: visible !important;
          float: none !important;
          max-height: inherit !important;
          max-width: inherit !important;
          line-height: auto !important;
          margin-top: 0px !important;
          visibility: inherit !important;
        }
        /* Hides Light Mode-Only Content, Like Images */
        .light-img {
          display: none !important;
        }
        /* Custom Dark Mode Background Color */
        .darkmode {
          background-color: #000000 !important;
        }
        .darkmode2 {
          color: white !important;
          background-color: #000000 !important;
          border-right: solid 1px #191919 !important;
          border-left: solid 1px #191919 !important;
        }
        .darkmode3 {
          color:#fff !important;
        }
        .darkmode4 {
          color: #000 !important;
          background-color: #000000 !important;
          border-right: solid 1px #191919 !important;
          border-left: solid 1px #191919 !important;
        }
        .darkmodenotext {
          background-color: #000000 !important;
        }
        .darkmode5 {
          color: #000 !important;
          background-color: #000000 !important;
        }
        .darkmode6 {
          color: #fff !important;
          background-color: #000000 !important;
        }
        .darkmodecallout {
          color: #000 !important;
          background-color: #000000 !important;
          border-right: solid 1px #191919  !important;
          border-left: solid 1px #191919 !important;
          border-top: solid 1px #191919  !important;
          border-bottom: solid 1px #191919  !important;
        }
        .darkModeLink {
          color: #000 !important
        }
        .darkModeA {
          color: #ffc627 !important;
          border-bottom: 1px dotted #ffc627 !important;
        }
        .darkModeSpacer {
          Background-color: #000 !important;
        }
        .darkModedvb {
          background-color:#000 !important;
          background:linear-gradient(0deg, #808080, #808080 50%, #000, #000 50%) !important;
          border-color:#000 !important;
        }
        .header {
          color: #ffffff !important;
          background-color: #000000 !important;
        }
        .darkModeNoBorder {
          color: white !important;
          background-color: #000000 !important;
        }
        .darkmode365{
          color: white !important;
          background-color: #000000 !important;
          border-right: solid 1px #191919 !important;
          border-left: solid 1px #191919 !important;
        }
        .mso-h1 {
          Margin-bottom:0px !important;
        }
        .linkHover {
          color: #ffc627 !important;
          transition: .5s !important
        }
        .linkHover:hover {
          color: #ffd35a !important;
          transition: .5s !important;
        }
      }
      /* Copy dark mode styles for android support */
      /* Shows Dark Mode-Only Content, Like Images */
      [data-ogsc] .dark-img {
        display: block !important;
        width: auto !important;
        overflow: visible !important;
        float: none !important;
        max-height: inherit !important;
        max-width: inherit !important;
        line-height: auto !important;
        margin-top: 0px !important;
        visibility: inherit !important;
      }
      /* Hides Light Mode-Only Content, Like Images */
      [data-ogsc] .light-img {
        display: none !important;
      }
      /* Custom Dark Mode Background Color */
      [data-ogsc] .darkModeA {
        color: #ffc627 !important;
        border-bottom: 1px dotted #ffc627 !important;
      }
      [data-ogsc] .darkModeNoBorder {
        color: #fff !important;
        background-color: #000000 !important;
      }
      [data-ogsc] .darkmode {
        background-color: #000000 !important;
      }
      [data-ogsc] .darkmode2 {
        color: white !important;
        background-color: #000000 !important;
        border-right: solid 1px #191919 !important;
        border-left: solid 1px #191919 !important;
      }
      [data-ogsc] .darkmode3 {
        color:#fff !important;
      }
      [data-ogsc]   .darkmode4 {
        color: #000 !important;
        background-color: #000000 !important;
        border-right: solid 1px #191919 !important;
        border-left: solid 1px #191919 !important;
      }
      [data-ogsc]  .darkmode5 {
        color: #000 !important;
        background-color: #000000 !important;
      }
      [data-ogsc]  .darkmode6 {
        color: #fff !important;
        background-color: #000000 !important;
      }
      [data-ogsc] .darkmode365{
        background-color: #000000 !important;
        border-right: solid 1px #191919 !important;
        border-left: solid 1px #191919 !important;
      }
      /* Custom Dark Mode Font Colors */
      /* Custom Dark Mode Text Link Color */
      .buttonHover {
        transition: .05s
      }
      .buttonHover:hover {
        background: #ffd35a !important;
        transition: .05s
      }
      /* Button and link hover styles */
      .buttonHover{
        transition:.05s}
      .buttonHover:hover{
        background:#ffd35a !important;
        transition:.05s}
      .linkHover{
        color:#8c1d40 !important;
        transition:.5s !important}
      .linkHover:hover{
        color:#cb2a5d !important;
        transition:.5s !important;
      }
      .darkbackground {
        background-color:#fff !important;
      }
    </style>
    <!--[if (gte mso 9)|(IE)]> <style> .mso-link {text-decoration: underline !important; display: inline-block !important;}  </style> <![endif]-->
  </head>
  <body class="mobilebg darkmode body body-fix" style="width: 100% !important;min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100% !important;margin: 0;padding: 0; background-color: #F1F1F1;">
    <style type="text/css">
      div.preheader 
      {
        display: none !important;
      }
    </style>
    <!--[if mso]> <style type="text/css"> body, table, td, p, div, a {font-family: Arial, sans-serif !important;} .mso-link {border-bottom: 1px solid #8c1d40 !important; display: inline-block;}  </style> <![endif]-->
    <!--Begin Email-->
    <table role="presentation" class="body mobilebg" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;height: 100%;width: 100%;table-layout: fixed" cellpadding="0" cellspacing="0" width="100%" border="0">
      <div width="100%" align="center">
        <!--End Wrap-->
        <!-- Insert &zwnj;&nbsp; hack after hidden preview text -->
        <div style="display: none; max-height: 0px; overflow: hidden;">
          &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp; &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp; &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        </div>
        <table role="presentation" style="border-spacing: 0; border-collapse: collapse; vertical-align: top;" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
          <tbody>
            <!--Insert Reference block for UTM-->
            <tr>
              <td>
                <custom name="opencounter" type="tracking">
                  </td>
            </tr>
            <tr>
            </tr>
            <tr>
              <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: top" width="100%">
                <!--[if (gte mso 9)|(IE)]> <table id="outlookholder" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td> <table width="700" align="center" cellpadding="0" cellspacing="0" border="0"> <tr> <td> <![endif]-->
                <!--Header-->
                <!--Grey Wrapper-->
                <table role="presentation" style="border-spacing: 0; border-collapse: collapse; vertical-align: top; max-width: 700px; margin: 0 auto; background-color: #f1f1f1;" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
                  <tbody>
                    <tr>
                      <td style="word-break: break-word;border-collapse: collapse !important; vertical-align: top;" width="100%">
                        <!--Header-->
                        <tr>
                          <td id="asu_header" align="center" valign="top">
                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="padding: 0px;" bgcolor="#ffffff">
<tr>
            <td class="darkmode2" align="left" valign="top" style="padding:24px 24px 24px 24px; background-color: #ffffff;">
                <!--light mode logo image-->
                <a alias="ASU Top Logo" href="https://asu.edu">
        <img class="light-img" src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/3/d9f2d016-e69b-4a9f-9054-b0651026a038.png" width="230" height="auto" alt="Arizona State University" style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: Arial, sans-serif; padding: 0px; line-height: 24px; font-weight: bold; width:230px !important;">

                <!--dark mode logo image-->
                <div class="dark-img" style="display:none; overflow:hidden; width:0px; max-height:0px; max-width:0px; line-height:0px; visibility:hidden;" align="left">
                    <img src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/e0014106-a5a7-4286-9410-f5d5a1f52fc4.png" width="230" height="auto" alt="Arizona State University" style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: Arial, sans-serif; padding: 0px; line-height: 24px; font-weight: bold;">
                </div>
                    </a>
            </td>
          </tr>
</table></td></tr></table>
                          </td>
                        </tr>
                        <!--Start Content-->
                        <tr>
                          <td id="asu_header" align="center" valign="top">
                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner"><table width="100%" cellspacing="0" cellpadding="0" role="presentation"><tr><td align="center"><img data-assetid="404749" src="{$banner_img_path}" alt="{$banner_img_alt}" width="700" style="display: block; padding: 0px; text-align: center; border: 0px solid transparent; height: auto; width: 100%;"></td></tr></table></td></tr></table>
                          </td>
                        </tr>
                        <tr>
                          <td id="asu_header" align="center" valign="top">
                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="background-color: #ffffff; padding: 0px;">
                              <tr>
                                <td class="darkmode2" style="border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 24px 24px 24px 24px; mso-line-height-rule:exactly; line-height: 24px; font-weight: bold;">
                                {$first_name},
                                </td>
                              </tr>
                            </table></td></tr></table>
                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner">                        
                                <table class="darkmodenotext" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="left" style="background-color: #ffffff; padding: 0px;">    
                                  <tr>
                                    <td class="darkmode2" style="border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 0px 24px 24px 24px; line-height: 24px;">
                                      <p style="margin-top:0px;margin-bottom:0px">
                                        {$main_content}
                                      </p>
                                    </td>
                                  </tr>
                                </table>
                            </td></tr></table>
                          </td>
                        </tr>
                        <!--End Content-->
                        <!--Social Media--> 
                        <tr>
                          <td id="asu_header" align="center" valign="top">
                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="background-color: #000000;padding: 0px;">
  <tr style="vertical-align: middle">
    <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle;text-align: center; font-size: 0; padding: 16px 0px 16px 0px;">

<!--[if (gte mso 9)|(IE)]><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td valign="middle" width="355" style="line-height:0px"><![endif]-->
  
<div style="display: inline-block;vertical-align: middle;text-align: center;width: 355px;">

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding: 0px 8px 0px 0px;">
    
<tr>
<td align="center" valign="middle" style="border-collapse: collapse; text-align: center; font-size:16px; color:#000000; font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 0px 24px 4px 48px; line-height: 24px; font-weight: 300;">
<a alias="Repeataedly Ranked #1" href="https://asu.edu">
<img src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/1/46d71a63-4f9e-4daa-9266-ccb390669950.jpg" width="230" height="auto" alt="Repeataedly Ranked #1. 20+ lists in the last 3 years." style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 0px; line-height: 24px;">
</a>
</td>

</tr>
</table>

</div>

<!--[if (gte mso 9)|(IE)]><td valign="middle" width="285" style="padding: 0px 0px 0px 0px;><![endif]-->
  
<div class="col num4" style="display: inline-block;vertical-align: middle;text-align: center;width: 285px;">

<table role="presentation" width="95%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding: 0px 0px 0px 0px;">
    
<tr>
<td align="center" valign="middle" style="border-collapse: collapse; text-align: center; font-size:16px; color:#000000; font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 10px 10px 10px 10px; line-height: 24px; font-weight: 300;">
<a alias="ASU Bottom Logo" href="https://asu.edu">
<img src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/e0014106-a5a7-4286-9410-f5d5a1f52fc4.png" width="230" height="auto" alt="Arizona State University" style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 0px; line-height: 24px;">
</a>
</td>
</tr></table>

</div>

<!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
</td>
</tr>
</table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="padding: 0px;">
  
  <tr>
    <td style="word-break: break-word;border-collapse: collapse !important; vertical-align: top; text-align: center; padding-top: 16px;" align="center" width="100%">
 <center>
 <table role="presentation" style="width: 90%;">
  <tr>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="Facebook_ASU" href="https://www.facebook.com/FutureSunDevils">
        <img width="40" height="40" border="0" alt="Facebook Logo" src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/1/02bb64df-844c-4720-8d37-d4c7de8c464c.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="X_ASU" href="http://twitter.com/futuresundevils">
        <img width="40" height="40" border="0" alt="X Logo" src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/1/8a3c00b5-9dfb-4fec-b46a-7846599e563f.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="Instagram_ASU" href="https://instagram.com/FutureSunDevils">
        <img width="40" height="40" border="0" alt="Instagram Logo" src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/1/54231603-7475-4639-aa0b-6811447b0062.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
    
        <td style="font-size:0px; word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        
        <a alias="YouTube_ASU" href="https://www.youtube.com/@futuresundevils">
        <img width="40" height="40" border="0" alt="YouTube Logo" src="https://image.reply.asu.edu/lib/fe8d137272610d7c76/m/1/20f269f0-483a-4410-b178-bc36440cb68e.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
  
        </td>
        </tr>
 </table>
 </center>
    </td>
  </tr>
</table></td></tr></table>
                          </td>
                        </tr>
                        <!--Start Content/Legal-->
                        <tr>
                          <td id="asu_header" align="center" valign="top">
                            
                          </td>
                        </tr>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <!--[if mso]> </td> </tr> </table> </td> </tr> </table> <![endif]-->
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </table>
  </body>
</html>
EOD;

        return $output;
    }


    /**
     * @param $content
     * @param $event_type
     * @param $agenda_prepared_array
     * @param $expasu_actual_start_timestamp
     * @param $earliest_actual_barrett_underexpasu_start_timestamp
     * @param $event_id
     * @param $attendee_id
     * @param $domain
     * @param $checkinloc_nid
     * @param array $barrett_paragraph_values_array
     * @return array
     *
     * Helping function
     *
     * Prepare email parts(main_content and banner image code) before applying email template.
     *
     * Includes Cancel form link.
     *
     * $agenda_prepared_array also contains Barrett paragraph from various Conf Letter nodes.
     */
    public function prepareHtmlEmailParts($content, $event_type, $agenda_prepared_array, $expasu_actual_start_timestamp, $earliest_actual_barrett_underexpasu_start_timestamp, $event_id, $attendee_id, $domain, $checkinloc_nid, $barrett_paragraph_values_array = [], $sid = '', $ptype = '') {
        $output = [];
//        // Cancel form link
//        $cancelform_link_html = "<p><strong>How to reschedule or cancel your visit</strong></p><p>To cancel or reschedule your visit please use this link: <a href='{$domain}/cancel-registration?aid={$attendee_id}&eventid={$event_id}&etype=exp&source=conf-email'>Cancel your registration</a></p>"; // Changed on 4/17/2025.
        // Acquia migration 6/27/2025 -> Put back the Cancel link on 8/8/2025
        $cancelform_link_html = "<p><strong>How to reschedule or cancel your visit</strong></p><p>To cancel or reschedule your visit please use this link: <a href='{$domain}/cancel-registration?aid={$attendee_id}&eventid={$event_id}&etype=exp&source=conf-email&sid={$sid}&ptype={$ptype}'>Cancel your registration</a></p>";
//        $cancelform_link_html = "<p><strong>How to reschedule or cancel your visit</strong></p><p>Please call Campus Visits team at 480-727-4531 if you need to cancel your registration.</p>";
      


//        $output['banner'] = isset($content['banner_image_path']) ? "<img src='" . $content['banner_image_path'] . "' alt='" . $content['banner_image_alt'] . "' />" : ''; // Changed on 4/1/2025
        $output['banner_image_path'] = isset($content['banner_image_path']) ? $content['banner_image_path'] : '';
        $output['banner_image_alt'] = isset($content['banner_image_alt']) ? $content['banner_image_alt'] : '';
      
        $output['main_content'] = '';
      
        // West Valley campus change - 10/23/2023
        $campus_displayname = isset($content['campus']) ? $content['campus'] : '';
        if($campus_displayname != '') {
          if($campus_displayname == 'West') {
            $campus_displayname = 'West Valley campus';
          }
          else if($campus_displayname == 'ASU California Center in downtown LA') {
            $campus_displayname = 'ASU California Center in downtown L.A.';
          }
          else {
            $campus_displayname = $campus_displayname . " campus";
          }
        }
        // If Event type is Academic Facility Tour, don't display campus. (8/21/2025)
        if($event_type == "Academic Facility Tour") {
          $campus_displayname = '';
        }

        if($event_type == "Barrett tour") {
            $output['main_content'] .= isset($content['sec1']) ? $content['sec1'] : ''; // "If you need to cancel your upcoming visit, please email visitASU@asu.edu as soon as possible."

            //------- Gold box with date --------//
            // TODO: "Add to calendar" inside buildGoldDateBoxHtml()
            // NOTES: If $earliest_actual_barrett_underexpasu_start_timestamp is not '', compare the Exp ASU start timestamp and the earliest Barrett under Exp ASU timestamp and diaplay the earlier time.
            // If $earliest_actual_barrett_underexpasu_start_timestamp is '', just display Exp ASU time/Top-level Barrett tour time.
            $gold_box_date_array = $this->buildGoldBoxHtmlDate(
    $expasu_actual_start_timestamp,
    $earliest_actual_barrett_underexpasu_start_timestamp,
//                isset($content['campus']) ? $content['campus'] : ''
    $campus_displayname
);
            $output['main_content'] .= $gold_box_date_array['regular'];

            // Cancelation link
            $output['main_content'] .= $cancelform_link_html;

            $output['main_content'] .= isset($content['sec2']) ? $content['sec2'] : ''; // "<h3>What to expect</h3> "
            $output['main_content'] .= isset($content['sec3']) ? $content['sec3'] : ''; // Late attendance policy
            $output['main_content'] .= isset($content['sec4']) ? $content['sec4'] : '';; // "Experience ASU check-in and parking details"
            $output['main_content'] .= isset($content['checkinloc_image_path']) ? "<img src='" . $content['checkinloc_image_path'] . "' alt='" . $content['checkinloc_image_alt'] . "' />" : ''; // Check-in image
            $output['main_content'] .= isset($content['sec5']) ? $content['sec5'] : ''; // Check-in and Parking
            $output['main_content'] .= isset($content['sec6']) ? $content['sec6'] : ''; // Closing paragraph
            $output['main_content'] .= isset($content['signature']) ? $content['signature'] : '';


        } else {
//            ksm($event_type, "event_type");
            if($event_type == "Self-guided campus Tour") {
                $output['main_content'] .= "<p>Thank you for your interest in Arizona State University. You are welcome to visit and tour the {$content['campus']} campus at your convenience. Below are some resources to help you along your self-guided tour.</p>";

            } else {
                $output['main_content'] .= "<p>Thank you for your interest in Arizona State University. As requested, you and " . $content['guests'] . " guest(s) are registered to attend " . $content['eventdisplaytitle'] . ". Please keep this email accessible for your upcoming visit.</p>";
            }
            $output['main_content'] .= isset($content['sec1']) ? $content['sec1'] : ''; // "If you need to cancel your upcoming visit, please email visitASU@asu.edu as soon as possible."

            //----------- Insert Gold box with date and Cancel form link ---------------//
            // NOTE: No Gold box with date for Self guided tour. --> Changed: For Self guided tour, display Gold date box except for time.
            // Also, no Cancel form link for Self guided tour.
            if($event_type == "Self-guided campus Tour") {
                // No Cancel form link
                // No Gold box with date
            } else {

                //----- Insert Gold box with date -----//
                // TODO: "Add to calendar" inside buildGoldDateBoxHtml()
                // NOTES: Compare the Exp ASU start timestamp and the earliest Barrett under Exp ASU timestamp and diaplay the earlier time.
                $gold_box_date_array = $this->buildGoldBoxHtmlDate(
        $expasu_actual_start_timestamp,
        $earliest_actual_barrett_underexpasu_start_timestamp,
//                    isset($content['campus']) ? $content['campus'] : ''
        $campus_displayname                  
    );
                $output['main_content'] .= $gold_box_date_array['regular'];

            }

            $output['main_content'] .= isset($content['sec2']) ? $content['sec2'] : ''; // "<h3>What to expect</h3> "

            //---- Insert Agenda ----//
            // Combine agendas in:
            // - Conf letter node
            // - Additional tour
            // - Barrett tour -- Since Barrett also has Conf letter node attached, we will just use Agenda from Conf letter node.
            $output['main_content'] .= $this->buildAgendaHtml($agenda_prepared_array);

            $output['main_content'] .= isset($content['sec3']) ? $content['sec3'] : ''; // Late attendance policy

            //------ Insert Barrett paragraph (Barrett paragraph moved up.) -------//
            if($expasu_actual_start_timestamp > $earliest_actual_barrett_underexpasu_start_timestamp) {
//                $output['main_content'] .= isset($content['barrett_paragraph']) ? $content['barrett_paragraph'] : ''; // Changed on 8/7/2023.
                $barrettpara_output = '';
                foreach($barrett_paragraph_values_array as $barrett_paragraph) {
                    $barrettpara_output .= $barrett_paragraph['barrett_paragraph_value'];
                }
                $output['main_content'] .= $barrettpara_output;
            }

            $output['main_content'] .= isset($content['sec4']) ? $content['sec4'] : '';; // "Experience ASU check-in and parking details"

            // If Check-in location node is selected in Conf letter node
            if($checkinloc_nid != '') {
                $output['main_content'] .= '<p>';
                $output['main_content'] .= isset($content['checkinloc_image_path']) ? "<img src='" . $content['checkinloc_image_path'] . "' alt='" . $content['checkinloc_image_alt'] . "' width='500' />" : ''; // Check-in image

                // Gold box with Printable map
                $gold_box_map_array = $this->buildGoldBoxHtmlMap(
        isset($content['printable_map_url']) ? $content['printable_map_url'] : '',
        isset($content['campus_map_url']) ? $content['campus_map_url'] : '',
        isset($content['googlemap_url']) ? $content['googlemap_url'] : '',
        isset($event_type) ? $event_type : '',
        isset($content['campus']) ? $content['campus'] : ''
);
                $output['main_content'] .= $gold_box_map_array['regular'];
                $output['main_content'] .= '</p>';
            }

            $output['main_content'] .= isset($content['sec5']) ? $content['sec5'] : ''; // Check-in and Parking
          
            // Cancel form link
            if($event_type == "Self-guided campus Tour") {
                // No Cancel form link
            } else {
                // Cancel form link
                $output['main_content'] .= $cancelform_link_html;
            }
          

            //------ Insert Barrett paragraph. There may be more than 2 Barrettt paragraphs. -------//
            if($expasu_actual_start_timestamp <= $earliest_actual_barrett_underexpasu_start_timestamp) {
//                $output['main_content'] .= isset($content['barrett_paragraph']) ? $content['barrett_paragraph'] : ''; // Changed on 8/7/2023.
                $barrettpara_output = '';
                foreach($barrett_paragraph_values_array as $barrett_paragraph) {
                    $barrettpara_output .= $barrett_paragraph['barrett_paragraph_value'];
                }
                $output['main_content'] .= $barrettpara_output;
            }

            $output['main_content'] .= isset($content['sec6']) ? $content['sec6'] : ''; // Closing paragraph
            $output['main_content'] .= isset($content['signature']) ? $content['signature'] : '';

//            //--------- Cancel form link -----------//
//            if($event_type == "Self-guided campus Tour") {
//                // No Cancel form link
//            } else {
//                // Cancel form link
//                $output['main_content'] .= $cancelform_link_html;
//            }
        }
        return $output;
    }


    /**
     * @param $agenda_prepared_array
     * @return string
     *
     * Helping function
     * Returns HTML table code for Agenda.
     */
    public function buildAgendaHtml($agenda_prepared_array) {
        $j = 0;
        $agenda_output = "<table id='agenda' cellpadding='10'><tbody>";
        foreach($agenda_prepared_array as $the_agenda) {
            if($the_agenda['time'] == '') {
                $agenda_output .= "<tr>";
                $agenda_output .= "<td>" . ($the_agenda['text'] != '' ? $the_agenda['text'] : '') . "</td>";
                $agenda_output .= "</tr>";

            } else {
                $agenda_output .= "<tr>";
                $agenda_output .= "<td valign='top' style='min-width:20%'><p>" . $the_agenda['time'] . "</p></td>";
                $agenda_output .= "<td valign='top'>" . ($the_agenda['text'] != '' ? $the_agenda['text'] : '') . "</td>";
                $agenda_output .= "</tr>";
            }
            $j++;
        }
        $agenda_output .= "</tbody></table>";
        return $agenda_output;
    }





    /**
     * @param string $printable_map_url
     * @return array
     *
     * Helping function
     * Returns HTML table code for Gold Map box. - Printable map
     */
//    public function buildGoldBoxHtmlMap($printable_map_url = '', $campus_map_url = '', $googlemap_url = '') { // Changed on 8/8/2025.
    public function buildGoldBoxHtmlMap($printable_map_url = '', $campus_map_url = '', $googlemap_url = '', $event_type = '', $campus = '') {
        $output_line = '';
        if($printable_map_url != '') {
            $output_line = "<a style='display:block' href='{$printable_map_url}'>Printable map</a>";
        }
        if($campus_map_url != '') {
            $output_line .= "<a style='display:block' href='{$campus_map_url}'>Campus map</a>";
        }
        if($googlemap_url != '') {
          // If Exp ASU and any campus except for California, then, "Future Sun Devil Welcome Center". (8/8/2025)
          if($event_type == 'Experience ASU' && $campus != 'ASU California Center in downtown LA') {
              $output_line .= "<a style='display:block' href='{$googlemap_url}'>Future Sun Devil Welcome Center</a>";
          }
          else {
            $output_line .= "<a style='display:block' href='{$googlemap_url}'>Google map</a>";
          }
        }

        $gold_box_html = [];
        if($output_line == '') {
            $gold_box_html['regular'] = '';
            $gold_box_html['pdf'] = '';
        } else {
            $gold_box_html['regular'] = <<<EOD
<table cellspacing='10'>
  <tr>
    <td style='background-color:#FFC627; padding:20px; text-align:center;' >
      <strong>Check-in location</strong><br />
      {$output_line}
    </td>
  </tr>
</table>
EOD;

            $gold_box_html['pdf'] = <<<EOD
<table cellspacing='10'>
  <tr>
    <td style='background-color:#FFC627; padding:20px; text-align:center; font-family: Arial, Roboto, Helvetica Neue, Helvetica, sans-serif; font-size: 16px;' >
      <strong>Check-in location</strong><br />
      {$output_line}
    </td>
  </tr>
</table>
EOD;
        }
        return $gold_box_html;
    }


    /**
     * Helping function
     *
     * Build Gold date box
     * TODO: Add to calendar
     *
     * @param $date
     * @param $time
     * @param $campus
     * @return array
     */
    public function buildGoldBoxHtmlDate($expasu_actual_start_timestamp, $earliest_actual_barrett_underexpasu_start_timestamp = '', $campus = '') {
        $gold_box_html = [];

        if($earliest_actual_barrett_underexpasu_start_timestamp == '') {
            $date_obj_phx = DateTimePlus::createFromTimestamp($expasu_actual_start_timestamp);
        } else {
            if($expasu_actual_start_timestamp <= $earliest_actual_barrett_underexpasu_start_timestamp) { // Exp ASU starts first
                // For America/Phoenix
                $date_obj_phx = DateTimePlus::createFromTimestamp($expasu_actual_start_timestamp);
            } else { // Barrettt starts first
                // For America/Phoenix
                $date_obj_phx = DateTimePlus::createFromTimestamp($earliest_actual_barrett_underexpasu_start_timestamp);
            }
        }
        $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
        $date = $date_obj_phx->format('D., M j, Y');
        $time = $date_obj_phx->format('g:i a');
        $time_start_formatted_webstandard = str_replace("am","a.m.",$time);
        $time_start_formatted_webstandard = str_replace("pm","p.m.",$time_start_formatted_webstandard);


        // TODO: Insert "Add to calendar" code
        $gold_box_html['regular'] = <<<EOD
<p><table cellspacing='10'>
<tr>
<td style='background-color:#FFC627; padding:20px; text-align:center;'>
<strong>{$date}</strong><br />
<strong>{$time_start_formatted_webstandard}</strong><br />
<strong>{$campus}</strong>
</td>
</tr>
</table></p>
EOD;

        $gold_box_html['pdf'] = <<<EOD
<p><table cellspacing='10'>
<tr>
<td style='background-color:#FFC627; padding:20px; text-align:center; font-family: Arial, Roboto, Helvetica Neue, Helvetica, sans-serif; font-size: 16px;'>
<strong>{$date}</strong><br />
<strong>{$time_start_formatted_webstandard}</strong><br />
<strong>{$campus}</strong>
</td>
</tr>
</table></p>
EOD;

        return $gold_box_html;
    }

    /**
     * @param $webform_submission
     * @return array
     *
     * Helping function
     * Returns array of Barrett tour ids.
     *
     */
    public function getBarrettToursUnderExpAsu($webform_submission) {

        $visit_date = isset($webform_submission->getData()['visit_date']) ? $webform_submission->getData()['visit_date'] : '';
        $barrett_tours = [];
        // Get value of Barrett fields
        for ($i = 0; $i <= 10; $i++) {
            $barrett_tour_id = isset($webform_submission->getData()['barrett_tour_id_' . $i]) ? $webform_submission->getData()['barrett_tour_id_' . $i] : '';
            if($barrett_tour_id != '') {
                array_push($barrett_tours, $barrett_tour_id);
            }
        }
        return $barrett_tours;
    }

    /**
     * @param $webform_submission
     * @return array
     *
     * Helping function
     *
     * Returns multi-dimensional array of Additional tour ids with:
     *  - Start timestamp
     *  - Start date/time
     *  - End date/time
     *  - Agendas array
     *
     *  Includes code to check if aganda is overwritten.
     */
    public function getAdditionalToursUnderExpAsu($webform_submission) {
        // Here let's get the following also:
        // - Actual date/time for Additional tour
        // - Actual timestamp for Additional tour
        // - Time only field that was used for building Additional tour ID
        // - Agendas in multi-dimensional array

        $visit_date = isset($webform_submission->getData()['visit_date']) ? $webform_submission->getData()['visit_date'] : '';
        $add_tours = [];
        // Get value of Additional tour fields
        for ($i = 0; $i <= 10; $i++) {
            $add_tour_id = isset($webform_submission->getData()['extra_tour_id_' . $i]) ? $webform_submission->getData()['extra_tour_id_' . $i] : '';

            if($add_tour_id != '') {
                // Get para id
                $temp_array0 = explode('|', $add_tour_id);
                $temp_array = explode('-', $temp_array0[0]);
                $addtour_paragraph_entity_id = $temp_array[1];

                // Load paragraph
                $entity_type = 'paragraph';
                $entity_id = $addtour_paragraph_entity_id;
                $entity_paragraph = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
//        ksm($entity_paragraph, "entity_para");

                //---------------------- Get Addtour name ---------------------//
                $addtour_title = isset($entity_paragraph->get('field_addtour_name')->getValue()[0]['value']) ? $entity_paragraph->get('field_addtour_name')->getValue()[0]['value'] : '';
                //---------------------- END OF Get Addtour name ---------------------//


                //----- Get timestamp for actual Additional tour -----//
                $time_range_from = $entity_paragraph->get('field_time_range')->getValue()[0]['from'];
                // Format time
                $datenew = \DateTime::createFromFormat("Y-m-d g:i a", $visit_date . " 00:00 am");
                $timestamp_ofthedateat0am = $datenew->getTimestamp(); // Timestamp of the date at 0:00am
//                    ksm($datenew->format("Y/m/d g:i a"), "test");
//                    ksm($timestamp_ofthedateat0am, "timestamp_ofthedateat0am");

                // Actual start time timestamp
                $timestamp_datetime_start = $timestamp_ofthedateat0am + $time_range_from; // Add timestamp of the date at 0:00am and timestamp for the event time.
                //----- END OF Get timestamp for actual Additional tour -----//


                //----- Get human-readable date/time for actual Additional tour -----//
                // Create Date obj from timestamp
                // For America/Phoenix
                $date_obj_phx = DateTimePlus::createFromTimestamp($timestamp_datetime_start);
                $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $time_start_formatted = $date_obj_phx->format('Y-m-d g:i a');
                //----- END OF Get human-readable date/time for actual Additional tour -----//


                //----- Get Time only field that was used for building Additional tour ID -----//
                // The timestamp is the last part of Additional tour ID
                $timestamp_for_addtourid = $temp_array[2];

                // Make it human-readable
                // Create Date obj from timestamp
                // For America/Phoenix
                $date_obj_phx2 = DateTimePlus::createFromTimestamp($timestamp_for_addtourid);
                $date_obj_phx2->setTimeZone(new \DateTimeZone('America/Phoenix'));
                $time_for_addtourid_formatted = $date_obj_phx2->format('Y-m-d g:i a');
                //----- END OF Get Time only field that was used for building Additional tour ID -----//


                //---------------------- Agendas -----------------------//
                // NOTES: Include code for overwrite
                // Check if the overwrite checkbox (field_overwrite_agenda_addtour) is checked.
                // If checked, iterate Agenda set (Date and agenda) and use the one with the correct date.

                // Original agenda para ids
                $agenda_para_array = $entity_paragraph->get('field_agenda_para')->getValue(); // agenda para ids array

                // Check if field_overwrite_agenda_addtour is checked or not.
                $overwrite_agenda_addtour = isset($entity_paragraph->get('field_overwrite_agenda_addtour')->getValue()[0]['value']) ? $entity_paragraph->get('field_overwrite_agenda_addtour')->getValue()[0]['value'] : '';
                $agenda_para_array_to_use = $agenda_para_array;
                if($overwrite_agenda_addtour == '1') { // Overwritten
                    // Iterate and find agenda to use

                    $agenda_overwrite_para_ids = $entity_paragraph->get('field_agenda_overwrite_para')->getValue() != null ? $entity_paragraph->get('field_agenda_overwrite_para')->getValue() : ''; // 329

                    foreach($agenda_overwrite_para_ids as $overwrite_para) {

                        $overwrite_para_id = $overwrite_para['target_id']; // 351

                        // Load agenda overwrite paragraph using Paragraph ID
                        $entity_type_overwrite = 'paragraph';
                        $entity_id_overwrite = $overwrite_para_id;
                        $entity_paragraph_overwrite = \Drupal::entityTypeManager()->getStorage($entity_type_overwrite)->load($entity_id_overwrite);

                        // Get Agenda overwrite -> Date (Overwritten date)
                        $date_overwrite = $entity_paragraph_overwrite->get('field_date_only')->getValue() != null ? $entity_paragraph_overwrite->get('field_date_only')->getValue()[0]['value'] : ''; // 2023-06-13

                        // Get date clicked
//                        ksm($visit_date, "visit_date"); // 20230613
                        if($date_overwrite == $visit_date) { // If date matches
                            // Get agenda overwrite -> Agenda (Overwritten agenda).
                            $agenda_para_overwrite = $entity_paragraph_overwrite->get('field_agenda_para')->getValue() != null ? $entity_paragraph_overwrite->get('field_agenda_para')->getValue() : []; // 349
                            $agenda_para_array_to_use = $agenda_para_overwrite;
                        }

                    } // END OF foreach

                } // END OF if($overwrite_agenda_addtour == '1')

                $agenda_prepared_array = [];
                if(sizeof($agenda_para_array) > 0) {
//                    $agenda_prepared_array = $this->prepareAgendasArray($agenda_para_array, $visit_date, 'addtour', $entity_id);
                    $agenda_prepared_array = $this->prepareAgendasArray($agenda_para_array_to_use, $visit_date, 'addtour', $entity_id);
                }
//                ksm($agenda_prepared_array, "agenda_prepared_array after prepareAgendasArray() in getAdditionalToursUnderExpAsu()");

                //---------------------- END OF Agendas -----------------------//


                $add_tours[$add_tour_id] = [
        'timestamp_datetime_start' => $timestamp_datetime_start,
        'time_start_formatted' => $time_start_formatted,
        'time_for_addtourid_formatted' => $time_for_addtourid_formatted,
        'agendas' => $agenda_prepared_array,
        'addtour_title' => $addtour_title,
    ];
            } // END OF if($add_tour_id != '')
        }
        return $add_tours;
    }

    /**
     * @param $title
     * @param $content_type
     * @return array|int
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     *
     * Check if the Student Registered node with the title of the Webform submission id already exists.
     * Or, if the Additional tour registrant node with the title of the Additional tour id + Webform submission id already exists.
     *
     * Returns empty array if node doesn't exist.
     * Returns nids if node exists. It should be just 1 nid, but still it is in array.
     */
    public function checkNodeAlreadyExists($title, $content_type) {
        $entity_type = 'node';
        $query = \Drupal::entityTypeManager()->getStorage($entity_type)->getQuery();
        $query->accessCheck(FALSE);
        $query->condition('type', $content_type);
        $query->condition('title', $title);
        $nids = $query->execute();

//        if (!(empty($nids))) {
//            // The Student Registered node with the title of the Webform submission id already exists.
//            // OR, The Additional tour registrant node with the title of the Additional tour id + Webform submission id already exists.
//            $already_exists = true;
//        }
        return $nids;
    }

}
// phpcs:enable