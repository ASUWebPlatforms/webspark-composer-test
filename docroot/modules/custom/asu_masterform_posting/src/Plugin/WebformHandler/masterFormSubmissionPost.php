<?php

namespace Drupal\asu_masterform_posting\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "Submit the master form submissions to salesforce API",
 *   label = @Translation("Submit the master form submissions to salesforce API"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Submit the master form submissions to salesforce API"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class masterFormSubmissionPost extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * @return string
   *
   * Used for posting switch for ddev local environment.
   */
  public function isDdev() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, '.ddev.site') !== FALSE) {
      return 'ddev';
    }
    return 'not_ddev';
  }

  /**
   * Get environment.
   *
   * @return string
   *   Returns 'prod' or 'dev' based on the current domain.
   */
  public function getEnv() {
    // Changed on 9/3/2025.
    $config_data = \Drupal::config('asuaec_visit_revamp.settings');
    // https://visit.asu.edu
    $proddomain = $config_data->get('proddomain');
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $env = 'prod';
    if ($domain == $proddomain) {
      $env = 'prod';
    }
    else {
      $env = 'dev';
    }
    \Drupal::logger('cstest')->notice('env: ' . $env);

    return $env;
  }

  /**
   * {@inheritdoc}
   */

  /**
   * Public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get webform submission values and current wenform id.
    $webform = $this->getWebform();
    $nodeid = $webform->id();
    $config = \Drupal::config('asu_masterform_posting.fields_admin_settings');
    $values = $webform_submission->getData();
    $children = [];

    // Declare array with all possible master form field names and API field namnes
    // $variables_array = array('academic_interest'=>'academicInterest','attendee_id'=>'attendeeId','dob'=>'birthdate','campus'=>'campusName','country'=>'citizenshipCountryCode','city'=>'city','country'=>'countryCode','email_address'=>'email','first_name'=>'firstName','parent_email'=>'guestEmail','last_name'=>'lastName','middle_name'=>'middleName','number_of_guests'=>'numberOfGuests','opportunityType'=>'opportunityType','major_program'=>'planCode','phone'=>'phone','zipcode'=>'postalCode','college'=>'programCode','special-needs'=>'specialNeeds','state'=>'stateCode','street_address'=>'street','semester_expected_to_attend_asu'=>'term','dietary_restrictions'=>'dietaryRestrictions');
    // Changed on 3/10/2025. Added 3 new fields.
    // $variables_array = array('academic_interest'=>'academicInterest','attendee_id'=>'attendeeId','dob'=>'birthdate','campus'=>'campusName','country'=>'citizenshipCountryCode','city'=>'city','country'=>'countryCode','email_address'=>'email','first_name'=>'firstName','parent_email'=>'guestEmail','last_name'=>'lastName','middle_name'=>'middleName','number_of_guests'=>'numberOfGuests','opportunityType'=>'opportunityType','major_program'=>'planCode','phone'=>'phone','zipcode'=>'postalCode','college'=>'programCode','special-needs'=>'specialNeeds','state'=>'stateCode','street_address'=>'street','semester_expected_to_attend_asu'=>'term','dietary_restrictions'=>'dietaryRestrictions','event_id'=>'formSource','leadsource'=>'leadSource','leadsourcesubtype'=>'leadSourceSubtype');
    // Changed from guestEmail to rsvpEmails on 3/17/2025.
    $variables_array = ['academic_interest' => 'academicInterest', 'attendee_id' => 'attendeeId', 'dob' => 'birthdate', 'campus' => 'campusName', 'country' => 'citizenshipCountryCode', 'city' => 'city', 'country' => 'countryCode', 'email_address' => 'email', 'first_name' => 'firstName', 'parent_email' => 'rsvpEmails', 'parent_email_2' => 'rsvpEmails', 'last_name' => 'lastName', 'middle_name' => 'middleName', 'number_of_guests' => 'numberOfGuests', 'opportunityType' => 'opportunityType', 'major_program' => 'planCode', 'phone' => 'phone', 'zipcode' => 'postalCode', 'college' => 'programCode', 'special-needs' => 'specialNeeds', 'state' => 'stateCode', 'street_address' => 'street', 'semester_expected_to_attend_asu' => 'term', 'dietary_restrictions' => 'dietaryRestrictions', 'event_id' => 'formSource', 'leadsource' => 'leadSource', 'leadsourcesubtype' => 'leadSourceSubtype'];

    $events = ['0' => ["category" => "event_type", "eventId" => "event_id", "eventCapacity" => "event_capacity", "eventEndDate" => "event_end_date", "eventEndTime" => "event_end_time", "eventLocation" => "event_location", "eventName" => "event_name", "eventStartDate" => "event_start_date", "eventStartTime" => "event_start_time"]];

    $camp_array = ["campaignType" => "Event"];
    // Iterate through submitted values matching with master form and API keys and create/format an array to post to API.
    $submission_data = [];
    foreach ($variables_array as $key => $data) {
      // $submission_data[$data] = $values[$key];
      // Changed on 1/17/2025 by Chizuko.
      $submission_data[$data] = !empty($values[$key]) ? $values[$key] : '';
    }
    foreach ($events[0] as $eve_key => $eve_value) {
      // $event_data[$eve_key] = $values[$eve_value]; // Changed on 3/24/2025
      $event_data[$eve_key] = !empty($values[$eve_value]) ? $values[$eve_value] : '';
    }
    // $event_data['eventLocation'] = $submission_data['campusName'];
    $event_data["children"] = $children;
    // dpm($submission_data);

    // array_push($event_data, $camp_array[0]);.
    $event_all_data = $camp_array + $event_data;
    $event_sub_array = ['events' => [$event_all_data]];
    $event_complete_array = [];
    $event_complete_array = $submission_data + $event_sub_array;
    foreach ($event_complete_array as $event_key => $event_value) {
      if ($event_value == '') {
        unset($event_complete_array[$event_key]);
      }
    }

    $env = $this->getEnv();

    // Post URL switch depending on environment.
    if ($env == 'prod') {
      // $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/register';
      $post_url = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_prod');
    }
    else {
      // $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register';
      $post_url = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_dev');
    }

    // $username = \Drupal::config('secrets.api')->get('username');
    // $pass = \Drupal::config('secrets.api')->get('password');

    // $settings = \Drupal::service('settings');
    // $auth_value = $settings->get('auth');

    // Fixed on 10/30/2025.
    if ($env === 'prod') {
      $settings = \Drupal::service('settings');
      $auth_value = $settings->get('auth');
      // Dev.
    }
    else {
      if ($this->isDdev() === 'ddev') {
        $auth_value = \Drupal::config('secrets.api')->get('auth');
      }
      else {
        // $settings = \Drupal::service('settings');
        // $auth_value = $settings->get('auth_dev');
        // Changed on 5/26/2026.
        $settings = \Drupal::service('settings');
        $auth_value = '';
        if ($post_url === 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register') {
          $auth_value = $settings->get('auth_qa', '');
        }
        elseif ($post_url === 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/register') {
          $auth_value = $settings->get('auth_dev', '');
        }
        if ($auth_value === '') {
          $auth_value = $settings->get('auth_dev', '');
        }
      }
    }

    if (empty($auth_value)) {
      \Drupal::logger('asu_masterform_posting_auth_value')->error('Auth value is empty');
    }
    $payload = [];
    $payload = $event_complete_array;
    if (!empty($payload['numberOfGuests'])) {
      $payload['numberOfGuests'] = intval($payload['numberOfGuests']);
    }
    else {
      $payload['numberOfGuests'] = intval(0);
    }
    $payload['events'][0]['eventCapacity'] = intval($payload['events'][0]['eventCapacity']);

    // New fields on 3/18/2025.
    $payload['smsOptIn'] = TRUE;
    $payload['mobilePhone'] = $values['service_type'] == 'mobile' ? $values['phone'] : '';
    $payload['leadSource'] = 'Event';
    $payload['leadSourceSubtype'] = 'Visit';

    // Insert parent_email and parent_email_2 in rsvpEmails. Added by Chizuko on 3/17/2025.
    // $parent_emails = array_filter([$values['parent_email'], $values['parent_email_2']]); // Changed on 3/24/2025.
    $parent_emails = array_filter([
      $values['parent_email'] ?? NULL,
      $values['parent_email_2'] ?? NULL
    ]);

    $payload['rsvpEmails'] = implode(',', $parent_emails);

    // New fields on 11/7/2025.
    $payload['asurite'] = $values['asurite'] ?? '';
    $payload['schoolInfo'] = $values['high_school'] ?? '';
    // Remove campusName.
    unset($payload['campusName']);

    $client = \Drupal::httpClient();
    try {
      // $auth = 'Basic '. base64_encode ($username . ':' . $pass);
      $auth = 'Basic ' . base64_encode($auth_value);
      $request = $client->post($post_url, [
      'json' => $payload,
      'method' => 'POST',
      'headers' => [
        'Authorization' => $auth,
        'Content-Type' => 'application/json',
      ]
     ]);
      $response = json_decode($request->getStatusCode());
      $responseBody = json_decode($request->getBody());
      \Drupal::logger('asu_masterform_posting_repsonse')->notice('Response:<pre>' . print_r($response, TRUE) . ' Payload:' . print_r($payload, TRUE) . '<br />Post URL: ' . $post_url . '</pre>');

      // Clear token->PII mapping after successful submission. Added on 12/12/2025.
      $token = \Drupal::request()->query->get('token');
      if ($token) {
        $cid = 'asu_masterform_posting_pii:' . $token;
        \Drupal::cache('data')->delete($cid);
      }

    }
    catch (RequestException $e) {
      // Log the exception/fail with copy of payload.
      $fail_msg = "Failed Visit Master form posting: <pre>"
        . "\nPosted data: " . htmlspecialchars(print_r($payload, TRUE))
        . "\nEXCEPTION $e "
        . "</pre>";
      \Drupal::logger('Visit Master form posting failure')->error($fail_msg);
      // Return FALSE;.
    }
    // \Drupal::logger('asu_masterform_posting')->notice('Response:<pre><code>' . htmlspecialchars(print_r($response, TRUE)) . ' Payload:' . htmlspecialchars(print_r($payload, TRUE)) . '</code></pre>');

    if ($env == 'dev') {
      $the_message = new TranslatableMarkup('Response: <pre>' . print_r($response, TRUE) . '<br />Payload:' . print_r($payload, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
      $this->messenger()->addMessage($the_message);
    }
  }

}
