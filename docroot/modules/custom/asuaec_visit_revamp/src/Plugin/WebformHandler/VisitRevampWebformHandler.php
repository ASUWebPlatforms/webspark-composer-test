<?php

namespace Drupal\asuaec_visit_revamp\Plugin\WebformHandler;

use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\media\Entity\Media;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "visitrevamp_webform_handler",
 *   label = @Translation("Visit Revamp - Posts to middleware. Creates Student Reg node. Also, sends confirmation email."),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Visit Revamp - Posts the submission to Middleware. Creates Student Reg node. Also, sends confirmation email."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class VisitRevampWebformHandler extends WebformHandlerBase {
  /**
   * Prevent posting twice.
   *
   * @var int
   */
  public static $x = 0;

  /**
   * Get environment.
   *
   * @return string
   *   The current environment (e.g., 'dev' or 'prod').
   */
  public function getEnv() {
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    \Drupal::logger('cstest')->notice('domain:<pre>' . htmlspecialchars($domain) . '</pre>');
    $env = 'prod';
    if ($domain === 'https://visit.asu.edu') {
      $env = 'prod';
    }
    else {
      $env = 'dev';
    }
    \Drupal::logger('cstest')->notice('env before return inside getEnv():<pre>' . htmlspecialchars($env) . '</pre>');
    return $env;
  }

  /**
   * Used for posting switch for ddev local environment.
   *
   * @return string
   *   Return 'ddev' or 'not_ddev'.
   */
  public function isDdev() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, '.ddev.site') !== FALSE) {
      return 'ddev';
    }
    return 'not_ddev';
  }

  /**
   * Insert interest_name value into Webform submission.
   *
   * @param \Drupal\webform\webformSubmissionInterface $webform_submission
   *   The webform submission object.
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {
    // -------- Interest -------//
    // Get Visitor type
    $visitor_type = $webform_submission->getData()['visitor_type'] ?? '';
    \Drupal::logger('cstest')->notice('visitor_type:<pre>' . $visitor_type . '</pre>');

    if ($visitor_type == 'Graduate student') {
      $interest_name = $webform_submission->getData()['interest'] ?? '';
    }
    else {
      if ((isset($webform_submission->getData()['interest'])) &&
        (!empty($webform_submission->getData()['interest'])) &&
        (!is_null($webform_submission->getData()['interest']))
      ) {
        $interest_tid = $webform_submission->getData()['interest'];
      }
      else {
        $interest_tid = '';
      }
      if (($interest_tid != '0') && ($interest_tid != '') && ($interest_tid != '[current-page:query:intid]')) {
        $interest_name = Term::load($interest_tid)->get('name')->value;
      }
      else {
        $interest_name = '';
      }
    }
    $webform_submission->setElementData('interest_name', $interest_name);

  } // END OF public function preSave(WebformSubmissionInterface $webform_submission)

  /**
   * {@inheritdoc}
   *
   * Post to middleware
   * Send confirmation email.
   * Create a Student Registered node.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Check environment.
    $env = $this->getEnv();
    // \Drupal::logger('cstest')->notice('env inside postSave:<pre>' . print_r($env, true) . '</pre>');

    // Webform submitted.
    if ($update == FALSE && self::$x == 0) {

      // ------- Create Student registered node --------------//
      $is_barrett_tour_under_exp_asu = FALSE;
      // createStudentRegisteredNode() also creates Additional tour registrant node of customer registers for Additional tour.
      $email_parts = $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu);

      // ------- Send confirmation email for Exp ASU and the top-level Barrett tour --------------//
      // $this->sendConfEmail($email_parts['student_reg'], $env, $webform_submission);

      // ----call cancel registartion function if c_aid variable exists in the url and values exists in cancel_attendee_id webform submission field --- Copied from asuaec_visit module.
      if (!empty($webform_submission->getData()['cancel_attendee_id']) && $webform_submission->getData()['cancel_attendee_id'] != "null") {
        $this->cancelRegistration($webform_submission);
      }

      // ------- Create Student registered node for Barrett under Exp ASU --------------//
      // If Barrett under Exp ASU
      // $barrett_tours_under_exp_asu = [];
      // $barrett_tours_under_exp_asu = $this->getBarrettToursUnderExpAsu($webform_submission);
      // foreach($barrett_tours_under_exp_asu as $barrett_tour_id) {
      // $is_barrett_tour_under_exp_asu = true;
      // $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu, $barrett_tour_id);
      // }

      // ------- Posting --------------//
      $this->postToMiddleware($webform_submission, $env, $update, 'registration-form');

    }
    // "Save" button was clicked from Webform submission
    elseif ($update == TRUE && self::$x == 0) {

      // ------- Update Student registered node --------------//
      $is_barrett_tour_under_exp_asu = FALSE;
      // $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu);

      // If Barrett under Exp ASU
      // $barrett_tours_under_exp_asu = [];
      // $barrett_tours_under_exp_asu = $this->getBarrettToursUnderExpAsu($webform_submission);
      // foreach($barrett_tours_under_exp_asu as $barrett_tour_id) {
      // $is_barrett_tour_under_exp_asu = true;
      // $this->createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu, $barrett_tour_id);
      // }.

      // ------- Posting --------------//
      $this->postToMiddleware($webform_submission, $env, $update, 'registration-form-v2');
    }

  } // END OF public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)

  /**
   * Post to middleware.
   */
  public function postToMiddleware($webform_submission, $env, $update, $formpage_alias) {

    // Prevent from duplicate.
    if (($update == TRUE && self::$x == 0) || ($update == FALSE && self::$x == 0)) {

      // Interest.
      $interest_name = $webform_submission->getData()['interest_name'] ?? '';

      // -------Birth date---------//
      // 2000-01-02
      $date_of_birth = $webform_submission->getData()['birthdate'] ?? '';
      if ($date_of_birth != '') {
        if (!$this->validateBirthdate($date_of_birth)) {
          // If birthdate is older than 1900, post ''. TODO: This shouldn't happen because validation checks it.
          $date_of_birth = '';
        }
      }

      // -------Phone--------//
      $phone = $webform_submission->getData()['phone'] ?? '';
      // Remove "+" and "-".
      $phone_formatted = preg_replace('[\D]', '', $phone);

      // ----------- Visitor type (opportunityType) ------------//
      // Such as "High School Senior". For "Other" form, it is "Other"
      $visitor_type = isset($webform_submission->getData()['visitor_type']) ? trim($webform_submission->getData()['visitor_type']) : '';
      $visitor_type_titlecase = '';
      foreach (explode(' ', $visitor_type) as $word) {
        $visitor_type_titlecase .= ucfirst($word) . ' ';
      }

      // ------------ Additional email addresses -------------//
      // Get Student's email
      $student_email = isset($webform_submission->getData()['email_address']) ? trim($webform_submission->getData()['email_address']) : '';
      $rsvp_emails = '';
      // For Ugrad, grab email addresses under parent info section.
      if ($visitor_type == 'Graduate student') {
        $email_address_additional = isset($webform_submission->getData()['email_address_additional']) ? trim($webform_submission->getData()['email_address_additional']) : '';
        if ($email_address_additional != '') {
          $addittional_emails_array = explode(',', $email_address_additional);
          $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
        }
      }
      elseif ($visitor_type == 'Other') {
        // There is no additional email addresses for "Other".
        // Ugrad.
      }
      else {
        $parent1_email = isset($webform_submission->getData()['parent1_email']) ? trim($webform_submission->getData()['parent1_email']) : '';
        $parent2_email = isset($webform_submission->getData()['parent2_email']) ? trim($webform_submission->getData()['parent2_email']) : '';
        // Make sure parent1 and parent2 email are different from Student's email.
        $addittional_emails_array = [$parent1_email, $parent2_email];
        $rsvp_emails = $this->removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email);
      }

      // Get submission ID.
      $sid = $webform_submission->id();
      // \Drupal::logger('cstest')->notice('webform_submission:<pre>' . print_r($webform_submission, true) . '</pre>');
      // \Drupal::logger('cstest')->notice('sid:<pre>' . print_r($sid, true) . '</pre>');

      // ---EVENT INFO---//

      $events_array = [];
      $events_array = _get_selected_events($webform_submission);
      // \Drupal::logger('cstest')->notice('events_array:<pre>' . print_r($events_array, true) . '</pre>');

      // Remove unnecessary fields for posting:
      // Title, Display title, confLetterNid, confEmailText and confEmailAgenda.
      $events_array_for_posting = [];
      foreach ($events_array as $event) {
        // Remove unwanted keys from top-level event.
        unset($event['eventTitle'], $event['eventDisplayTitle'], $event['confLetterNid'], $event['confEmailText'], $event['confEmailAgenda']);

        // If it has children, process each child.
        if (!empty($event['children']) && is_array($event['children'])) {
          foreach ($event['children'] as $index => $child) {
            unset($child['eventTitle'], $child['eventDisplayTitle'], $child['confLetterNid'], $child['confEmailText'], $child['confEmailAgenda']);
            $event['children'][$index] = $child;
          }
        }
        // Add cleaned event to new array.
        $events_array_for_posting[] = $event;
      }

      // ? TODO: NEED TO ASK LISA
      $attendee_id = $sid;

      // ? For now, inserted the first parent event id's campus - TODO: NEED TO ASK LISA
      $campus = $events_array[0]['eventLocation'];

      // Get Phone service type from submission.
      $service_type = $webform_submission->getData()['service_type'] ?? '';
      $phone_info[] = [
        'number' => $phone_formatted,
        'service_type' => $service_type,
      ];

      // --- Get Campus code for campusCode ---//
      // dpm($campus, "campus - cstest");
      if ($campus !== '') {
        $campus_name = $campus;
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

          case 'West':
            $campus_code = 'WEST';
            $campus_name = 'West Valley';
            break;

          case 'ASU California Center in downtown LA':
            $campus_code = 'LOSAN';
            break;

          default:
            $campus_code = '';
        }
      }

      // --- schoolInfo ---//
      $school_info = '';
      $hs_info = trim($webform_submission->getData()['hsname'] ?? '');
      $inst_info = trim($webform_submission->getData()['iname'] ?? '');
      if ($hs_info === '' && $inst_info === '') {
        $school_info = '';
      }
      elseif ($hs_info !== '') {
        $school_info = $hs_info;
      }
      elseif ($inst_info !== '') {
        $school_info = $inst_info;
      }

      $submission_data = [
        "events" => $events_array_for_posting,
        "academicInterest" => $interest_name,
      // $event_series_id + $sid // TODO: NEED TO ASK LISA
        'attendeeId' => $attendee_id,
        "birthdate" => $date_of_birth,
      // Tempe  // TODO: NEED TO ASK LISA.
        'campusName' => $campus_name,
      // US.
        'citizenshipCountryCode' => $webform_submission->getData()['country_of_citizenship'] ?? '',
        "city" => isset($webform_submission->getData()['city']) ? trim($webform_submission->getData()['city']) : '',
      // QUESTION: We need both citizenshipCountryCode and countryCode? YES.
        "countryCode" => $webform_submission->getData()['country'] ?? '',
      // "dietaryRestrictions" => "Vegetarian", // ***This is for Master form.
        'email' => isset($webform_submission->getData()['email_address']) ? trim($webform_submission->getData()['email_address']) : '',
        'firstName' => isset($webform_submission->getData()['first_name']) ? trim($webform_submission->getData()['first_name']) : '',
        "rsvpEmails" => $rsvp_emails,
        'lastName' => isset($webform_submission->getData()['last_name']) ? trim($webform_submission->getData()['last_name']) : '',
        "middleName" => isset($webform_submission->getData()['middle_name']) ? trim($webform_submission->getData()['middle_name']) : '',
        "numberOfGuests" => isset($webform_submission->getData()['guests']) ? intval(trim($webform_submission->getData()['guests'])) : '',
      // Such as "High School Senior". For "Other" form, it is "Other".
        "opportunityType" => trim($visitor_type_titlecase),
      // HIARTMFA.
        "planCode" => isset($webform_submission->getData()['major']) ? trim($webform_submission->getData()['major']) : '',
      // 14807663728 -- The same as RFI
        "phone" => $phone_formatted,
        "mobilePhone" => isset($phone_info[0]) && $phone_info[0]['service_type'] == 'mobile' ? $phone_info[0]['number'] : '',
        "postalCode" => isset($webform_submission->getData()['postal_code']) ? trim($webform_submission->getData()['postal_code']) : '',
      // GRHI.
        "programCode" => isset($webform_submission->getData()['college']) ? trim($webform_submission->getData()['college']) : '',
      // "specialNeeds" => "Wheelchair", // ***This is for Master form.
        "stateCode" => isset($webform_submission->getData()['state']) ? trim($webform_submission->getData()['state']) : '',
      // QUESTION: Shall I remove Adress 2 field? YES.
        "street" => isset($webform_submission->getData()['address']) ? trim($webform_submission->getData()['address']) : '',
        "term" => isset($webform_submission->getData()['entry_term']) ? trim($webform_submission->getData()['entry_term']) : '',
        "smsOptIn" => TRUE,
      // TODO: NEED TO ASK LISA.
        "formSource" => isset($webform_submission->getData()['event_id_0']) ? trim($webform_submission->getData()['event_id_0']) : '',
      // Added for testing at csdev49 on 3/5/2025.
        "leadSource" => 'Event',
      // Added for testing at csdev49 on 3/5/2025.
        "leadSourceSubtype" => "Visit",
        // Added on 11/7/2025.
        "campusCode" => $campus_code,
        "asurite" => isset($webform_submission->getData()['asu_rite_id']) ? trim($webform_submission->getData()['asu_rite_id']) : '',
        "schoolInfo" => $school_info,
      ];

      foreach ($submission_data as $key => $value) {
        if ($value == '') {
          unset($submission_data[$key]);
        }
      }
      // \Drupal::logger('cstest')->notice('submission_data:<pre>' . print_r($submission_data, true) . '</pre>');

      // $data = json_encode($submission_data); // ***In Json format. For Promise, use array, not Json. For Drupal way, use array, not Json.

      // ------------ Posting ------------//

      // Post URL switch depending on environment
      // if ($env == 'prod') {
      // $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
      // $post_url = 'https://crm-recruitment-event-router-dev.apps.asu.edu/v1/event/visit/register'; // Post to DEV
      // $post_url = 'https://crm-recruitment-event-router-qa.apps.asu.edu/v1/event/visit/register'; // Post to QA
      // $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/register'; // Post to Prod SF
      // } else {
      // $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
      // $post_url = 'https://crm-recruitment-event-router-dev.apps.asu.edu/v1/event/visit/register'; // Post to DEV
      // $post_url = 'https://crm-recruitment-event-router-qa.apps.asu.edu/v1/event/visit/register'; // Post to QA
      // $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register'; // NEW Post to QA on 9/6/2023
      // }
      // Use config settings
      // $config = \Drupal::config('asuaec_visit_revamp.settings');
      // $post_url = ($env === 'prod') ? $config->get('post_url_prod') : $config->get('post_url_dev');.

      // $domain = 'https://' . $_SERVER['HTTP_HOST'];
      // \Drupal::logger('cstest')->notice("isDdev: " . htmlspecialchars($this->isDdev()));
      // Fixed on 10/30/2025.
      if ($env === 'prod') {
        $post_url = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_prod');
        $settings = \Drupal::service('settings');
        $auth_value = $settings->get('auth');
      }
      // DEV.
      else {
        $post_url = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_dev');
        if ($this->isDdev() === 'ddev') {
          $auth_value = \Drupal::config('secrets.api')->get('auth');
        }
        else {
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
      \Drupal::logger('cstest')->notice("post_url: " . htmlspecialchars($post_url));

      $payload = [];
      // Get payload as an associate array.
      $payload = $submission_data;
      $submit_handler_url = $post_url;

      // POST to API using Guzzle httpClient.
      $client = \Drupal::httpClient();
      try {
        // $auth = 'Basic '. base64_encode ($username . ':' . $pass);
        $auth = 'Basic ' . base64_encode($auth_value);
        $request = $client->post($submit_handler_url, [
          'json' => $payload,
          'method' => 'POST',
          'headers' => [
            'Authorization' => $auth,
            'Content-Type' => 'application/json',
          ]
        ]);
        // $response = json_decode($request->getBody());
        $response = json_decode((string) $request->getBody(), TRUE);
      }
      catch (RequestException $e) {
        // Log the exception/fail with copy of payload.
        $fail_msg = "Failed Visit form posting: <pre>"
        // . "\nPAYLOAD " . var_export($payload, 1)
          . "\nPosted data: " . print_r($payload, TRUE)
          . "\nEXCEPTION $e "
          . "</pre>";
        \Drupal::logger('Visit form posting failure')->debug($fail_msg);
        // Email Chizuko.
        $this->sendFailureNotificationEmail('visit_post_failure', 'Visit form posting failed', $fail_msg);
        if ($env == 'dev') {
          $the_message = new TranslatableMarkup(
            '<pre>@fail_msg<br />Post URL: @post_url</pre>',
            [
              '@fail_msg' => $fail_msg,
              '@post_url' => $post_url,
            ]
          );
          $this->messenger()->addMessage($the_message);
        }
      }

      if (!is_null($request)) {

        if (($request->getStatusCode() < 200) || ($request->getStatusCode() >= 300)) {
          // Error handling is in catch{}.
        }
        // Success.
        else {
          // Save submitted values back to Webform field for record.
          $webform_submission->setElementData('posted_data', print_r($payload, TRUE));

          // If re-posted manually by clicking "Save", insert date/time in repost Webform field.
          if ($update == TRUE) {
            $webform_submission->setElementData('repost', date('Y-m-d H:i:s'));
          }
          $webform_submission->resave();

          \Drupal::logger('asuaec_visit_revamp')->notice('Success - Posted data:<pre><code>' . print_r($payload, TRUE) . '</code></pre><br />Post URL:' . $post_url);
          // \Drupal::logger('cstest')->notice('env:<pre>' . print_r($env, true) . "</pre>");
          if ($env == 'dev') {
            $the_message = new TranslatableMarkup(
              'Success: <pre>@response<br />Posted data: @payload<br />Post URL: @post_url</pre>',
              [
                '@response' => print_r($response, TRUE),
                '@payload' => print_r($payload, TRUE),
                '@post_url' => $post_url,
              ]
            );
            $this->messenger()->addMessage($the_message);
          }

        } // END OF else
      }
      self::$x++;
    } // END OF if(self::$x == 0)
  } // END OF public function postToMiddleware($submission_data)

  /**
   * Helper function for sending failure email.
   *
   * @param string $key
   *   "Visit_post_failure" or "visit_conf_email_failure".
   * @param string $subject
   *   Subject.
   * @param string $message
   *   Message.
   */
  public function sendFailureNotificationEmail($key, $subject, $message) {

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'asuaec_visit';
    // $key = 'posting_failure'; <--- Passed in param.
    $to = 'chizuko.swanson@asu.edu';
    $params['subject'] = $subject;
    $params['message'] = $message;
    // Added on 8/30/2024.
    $params['reply-to'] = 'visitasu@asu.edu';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      switch ($key) {
        case 'visit_post_failure':
          $the_message = new TranslatableMarkup('Visit form posting: There was a problem sending a failur notification email and it was not sent.', []);
          $this->messenger()->addMessage($the_message);
          \Drupal::logger('asuaec_visit_revamp')->notice('Visit form posting - Email error: a failur notification email was not sent. - Email address:' . $to);
          break;

        case 'visit_conf_email_failure':
          $the_message = new TranslatableMarkup('Visit form conf email: There was a problem sending a failur notification email and it was not sent.', []);
          $this->messenger()->addMessage($the_message);
          \Drupal::logger('asuaec_visit_revamp')->notice('Visit form conf email - Email error: a failur notification email was not sent. - Email address:' . $to);
          break;
      }
    }
  }

  /**
   * Helping function.
   *
   * Returns true if birthdate is newer than 1900-01-01.
   */
  public function validateBirthdate($birthdate_text) {
    $birthdate = date('Y-m-d', strtotime($birthdate_text));
    $birthdate_oldest = date('Y-m-d', strtotime("1900-01-01"));
    $retVal = TRUE;
    if ($birthdate >= $birthdate_oldest) {
      $retVal = TRUE;
    }
    else {
      $retVal = FALSE;
    }
    return $retVal;
  }

  /**
   * Get college name from 4 character college code.
   */
  public function getCollegeNameFrom4charCollegeCode($four_char_college_code) {
    $college_name = '';
    // Get Taxonomy name from description value
    // Returns empty string if description value is not set.
    $database = \Drupal::database();
    $query = $database->select('taxonomy_term_field_data', 't');
    $query->fields('t', ['name']);
    $query->condition('t.description__value', $four_char_college_code, '=');
    $result = $query->execute();
    $records = $result->fetchAll();
    $num_results = count($records);
    if ($num_results > 0) {
      // foreach($result as $data){.
      foreach ($records as $data) {
        $college_name = $data->name ?? '';
      }
    }
    return $college_name;
  }

  /**
   * Remove duplicate email addresses and build comma separatted email string.
   *
   * Also remove empty string.
   */
  public function removeDuplicateEmailAddressAndBuildCommaSeparatedEmailString($addittional_emails_array, $student_email) {
    $rsvp_emails = '';
    // Clean $addittional_emails_array. Remove duplicate email addresses if there are any.
    // Duplicate removed.
    $addittional_emails_array_cleaned = array_unique($addittional_emails_array);
    $i = 0;
    foreach ($addittional_emails_array_cleaned as $additional_email) {
      if ($additional_email == $student_email) {
        unset($addittional_emails_array_cleaned[$i]);
      }
      if ($additional_email == '') {
        unset($addittional_emails_array_cleaned[$i]);
      }
      $i++;
    }
    // Build comma-separated string.
    $i = 0;
    foreach ($addittional_emails_array_cleaned as $addittional_email) {
      if ($i == 0) {
        $rsvp_emails .= $addittional_email;
      }
      else {
        $rsvp_emails .= ',' . $addittional_email;
      }
      $i++;
    }
    return $rsvp_emails;
  }

  /**
   * Create Student Registratered node.
   *
   * Or, update Student Registratered node if already exsisted.
   *
   * Visit event and Barrett Solo will share Student Registered Node.
   *
   * We will use  $barrett_tour_id when it is Barrett tour under Exp ASU ( when $is_barrett_tour_under_exp_asu is true )
   *
   * Also, creates Additional tour registrant node if customer registers for Additional tour.
   */
  public function createStudentRegisteredNode($webform_submission, $env, $update, $is_barrett_tour_under_exp_asu = FALSE, $barrett_tour_id = NULL) {

    $events_array = _get_selected_events($webform_submission);
    // \Drupal::logger('cstest')->notice('events_array in createStudentRegisteredNode:<pre>' . print_r($events_array, true) . '</pre>');

    // Returns nid of Student Reg node and Additional tour Reg node.
    $email_parts = [];

    // $attendee_id = $event_series_id . '-' . $webform_submission->id();
    // Submission id.
    $attendee_id = $webform_submission->id();

    foreach ($events_array as $event) {

      // ------- Prepare $additional_tours_array and $list_of_barrett_tours_array ---------//
      // NOTES:
      // Additional tour is only for Exp ASU. Not for Barrett tour.
      // if $event_type contains "Barrett tour", it is Barrett tour.
      //
      // Also, get the list of Barrett tours. This is also only for Exp ASU.
      $additional_tours_array = [];
      $list_of_barrett_tours_array = [];
      // \Drupal::logger('cstest')->notice('children:<pre>' . print_r($event['children'], true) . '</pre>');

      if (isset($event['children']) && count($event['children']) > 0) {
        foreach ($event['children'] as $child) {
          if ($child['category'] == "Barrett tour" || $child['category'] == "Academic Facility Tour") {
            $list_of_barrett_tours_array[$child['eventId']] = $child;

            $barrett_tour_id = $child['eventId'];

            // Prepare to create Student Reg node.
            $title = $webform_submission->id() . '|' . $barrett_tour_id . '|Barrett under Exp ASU';
            $event_type = 'Barrett under Exp ASU';
            $event_id = $barrett_tour_id;
            $temp_array = explode('-', $event_id);
            $event_series_id = $temp_array[0];
            $timestamp_for_eventid = $temp_array[1];

            // $evdate - Actual event date/time
            // 2025-06-16
            $eventStartDate = $child['eventStartDate'];
            // 09:00:00
            $eventStartTime = $child['eventStartTime'];
            // e.g., "2025-06-16 09:00:00".
            $datetimeString = "$eventStartDate $eventStartTime";
            $dt = new \DateTime($datetimeString, new \DateTimeZone('America/Phoenix'));
            // e.g., "Mon, 06/16/25 09:00 am".
            $evdate = $dt->format('D, m/d/y h:i a');

            // $date_of_event
            // Set to UTC
            $dt->setTimezone(new \DateTimeZone('UTC'));
            // Format: 2025-07-23T22:00:00.
            $date_of_event = $dt->format('Y-m-d\TH:i:s');

            // <--- Empty for Barrett under Exp ASU for Student Reg node.
            $email_subject_line = '';
            $is_barrett_tour_under_exp_asu = TRUE;

            // Create a Student Reg node for Barrett under Exp ASU.
            $this->createOneStudentRegisteredNode($webform_submission, $title, $event_type, $is_barrett_tour_under_exp_asu, $event_id, $event_series_id, $date_of_event, $evdate, $attendee_id, $timestamp_for_eventid, [], []);

          }
          else {
            // array_push($additional_tours_array,$child['eventId'] );.
            $additional_tours_array[$child['eventId']] = $child;
          }
        } // END OF foreach ($event['children'] as $child)
      } // END OF if(isset($event['children']) && sizeof($event['children']) > 0)
      // \Drupal::logger('cstest')->notice('additional_tours_array:<pre>' . print_r($additional_tours_array, true) . '</pre>');

      // ------- END OF Prepare $additional_tours_array and $list_of_barrett_tours_array ---------//

      // Top level event - Anything other than Barrett tour under Exp ASU: Top level Barrett tour and Experience ASU.
      $event_id = $event['eventId'];
      // \Drupal::logger('cstest')->notice('event_id in foreach:<pre>' . $event_id . '</pre>');

      // Title of the Student Registered node -- Sid|Event ID.
      $title = $webform_submission->id() . '|' . $event['eventId'];
      $event_type = $event['category'];
      // Top level event series id
      // Get it from $event_id (356-1750690800)
      $parts = explode('-', $event_id);
      $event_series_id = $parts[0];
      $timestamp_for_eventid = $parts[1];

      // Maybe we don't need instance id.
      // $event_instance_id = isset($webform_submission->getData()['event_instance_entity_id']) ? $webform_submission->getData()['event_instance_entity_id'] : '';.

      // $evdate - Actual event date/time
      // 2025-06-16
      $eventStartDate = $event['eventStartDate'];
      // 09:00:00
      $eventStartTime = $event['eventStartTime'];
      // e.g., "2025-06-16 09:00:00".
      $datetimeString = "$eventStartDate $eventStartTime";
      $dt = new \DateTime($datetimeString, new \DateTimeZone('America/Phoenix'));
      // e.g., "Mon, 06/16/25 09:00 am".
      $evdate = $dt->format('D, m/d/y h:i a');

      // $date_of_event
      // Set to UTC
      $dt->setTimezone(new \DateTimeZone('UTC'));
      // Format: 2025-07-23T22:00:00.
      $date_of_event = $dt->format('Y-m-d\TH:i:s');

      // Create a Student Reg node.
      $this->createOneStudentRegisteredNode($webform_submission, $title, $event_type, $is_barrett_tour_under_exp_asu, $event_id, $event_series_id, $date_of_event, $evdate, $attendee_id, $timestamp_for_eventid, $additional_tours_array, $list_of_barrett_tours_array);

      // ---------------------------------------------------
      // Additional tour registrant node
      // Create Additioanl tour registrant node if there is any entry for additional tour
      if ($additional_tours_array) {
        foreach ($additional_tours_array as $additional_tour_id => $additional_tour) {

          // Check if additional tour registrant node already exists or not using node title.
          // When updating existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions, the Additional tour registrant node already exists. If that is the case, don't create another Additional tour registrant node and update the existing node.
          $this->createOneAdditionalTourRegistrantNode(webform_submission: $webform_submission, additional_tour_id: $additional_tour_id, additional_tour: $additional_tour, event_series_id: $event_series_id, parent_event_id: $event_id);

        } // END OF foreach($additional_tours_array as $additional_tour_id)
      } // END OF if ($additional_tours_array)

    } // END OF foreach ($events_array as $event)

    // ---------------------------------------------------
    // Attendee conf email node
    $this->createAttendeeConfEmailNode($webform_submission, $events_array);

    // --------------------------------------------------
    // Return email parts
    return $email_parts;

  } // END OF public function createStudentRegisteredNode()

  /**
   * Create one student registered node.
   */
  private function createOneStudentRegisteredNode($webform_submission, $title, $event_type, $is_barrett_tour_under_exp_asu, $event_id, $event_series_id, $date_of_event, $evdate, $attendee_id, $timestamp_for_eventid, $additional_tours_array = [], $list_of_barrett_tours_array = []) {
    // Check if there is Student Registered node existed or not.
    // When updating existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions, the Student Registered node already exists. If that is the case, don't create another Student Registered node and update the existing node.
    $nids = $this->checkNodeAlreadyExists($title, 'student_registered_visits');
    // This will run when someone submit the form.
    if (count($nids) == 0) {

      // Grab values from $webform_submission and create node object of Student Registered Visit.
      $node = Node::create([
        'type'        => 'student_registered_visits',
        'title'       => $title,
      // <-- If it is "Barrett tour", then it is Barrett Solo or Barrett under Exp ASU. The others will be "Experience ASU", "Sun Devil Day", etc.
        'field_event_type' => $event_type,
        'field_barrett_under_exp_asu' => $is_barrett_tour_under_exp_asu,
        'field_student_event_id' => $event_id,
        'field_event_series_id' => $event_series_id,
      // Date.
        'field_student_date_of_event' => $date_of_event,
      // Text field: Tue, 06/06/23 03:00 pm.
        'field_student_evdate' => $evdate,
        'field_attendee_id' => $attendee_id,

        'field_timestamp_for_actual_tour' => $webform_submission->getData()['start_timestamp'] ?? '',
        'field_timestamp_for_eventid' => $timestamp_for_eventid,

        'field_student_guests' => $webform_submission->getData()['guests'] ?? '',
        'field_student_campus' => $webform_submission->getData()['campus'] ?? '',
        'field_student_college' => $webform_submission->getData()['college'] ?? '',
        'field_student_major' => $webform_submission->getData()['major'] ?? '',
        'field_student_first_name' => $webform_submission->getData()['first_name'] ?? '',
        'field_student_last_name' => $webform_submission->getData()['last_name'] ?? '',
        'field_student_email' => $webform_submission->getData()['email_address'] ?? '',
        'field_web_sub_id' => $webform_submission->id(),
        'field_student_type' => $webform_submission->getData()['visitor_type'] ?? '',

        // Entry term.
        'field_student_entry_term' => $webform_submission->getData()['entry_term'] ?? '' ,

        'field_student_add1' => $webform_submission->getData()['address'] ?? '',
        'field_student_add2' => $webform_submission->getData()['address2'] ?? '',
        'field_student_city' => $webform_submission->getData()['city'] ?? '',
        'field_student_state' => $webform_submission->getData()['state'] ?? '',
        'field_student_country' => $webform_submission->getData()['country'] ?? '',
        'field_student_zip' => $webform_submission->getData()['postal_code'] ?? '',
        'field_stu_event_to_time' => $webform_submission->getData()['to_time'] ?? '',
        'field_student_add_email' => $webform_submission->getData()['email_address_additional'] ?? '',
        'field_student_phone' => $webform_submission->getData()['phone'] ?? '',

        // Email body
        // 'field_student_full_agenda' => $conf_email_body,
        // 'field_email_subject_line' => $email_subject_line,.

        'field_parent1_fname' => $webform_submission->getData()['parent1_fname'] ?? '',
        'field_parent1_lname' => $webform_submission->getData()['parent1_lname'] ?? '',
        'field_parent1_email' => $webform_submission->getData()['parent1_email'] ?? '',
        'field_parent1_cell_phone' => $webform_submission->getData()['parent1_cell_phone'] ?? '',
        'field_parent1_relation' => $webform_submission->getData()['parent1_relation'] ?? '',
        'field_parent2_fname' => $webform_submission->getData()['parent2_fname'] ?? '',
        'field_parent2_lname' => $webform_submission->getData()['parent2_lname'] ?? '',
        'field_parent2_email' => $webform_submission->getData()['parent2_email'] ?? '',
        'field_parent2_cell_phone' => $webform_submission->getData()['parent2_cell_phone'] ?? '',
        'field_parent2_relation' => $webform_submission->getData()['parent2_relation'] ?? '',

        // Additional tours - Only for top level Exp ASU submission.
        'field_student_extra_tours' => array_keys($additional_tours_array),
        // Barrett tours under Exp ASU - Only for top level Exp ASU submission.
        'field_list_of_barrett_tours' => array_keys($list_of_barrett_tours_array),

      // Added on 9/29/2025.
        'field_revamp' => 'Revamp',
        // Capture interest (1/7/2026)
        'field_student_interest' => $webform_submission->getData()['interest'] ?? '',
        'field_student_interest_name' => $webform_submission->getData()['interest_name'] ?? '',
      ]);

      $node->save();
      $nid = $node->nid->getValue()[0]['value'];
      $email_parts['student_reg']['nid'] = $nid;
      // $email_parts['student_reg']['email_body'] = $conf_email_body ;
      $email_parts['student_reg']['email_address'] = $webform_submission->getData()['email_address'] ?? '';
      $email_parts['student_reg']['email_address_additional'] = $webform_submission->getData()['email_address_additional'] ?? '';
      // $email_parts['student_reg']['subject'] = $email_subject_line;

    }
    // END OF if (sizeof($nids) == 0)
    else {
      // The Student Registered node with the title of the Webform submission id already exists.
      // This happens when click on Save on existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions to re-post to middleware. At the same time, it will update the Student Registered node with changed value if there is anything changed.

      // $nids array should contain just one element.
      foreach ($nids as $thenid) {
        $node = Node::load($thenid);

        // <-- If it is "Barrett tour", then it is Barrett Solo or Barrett under Exp ASU. The others will be "Experience ASU", "Sun Devil Day", etc.
        $node->set('field_event_type', $event_type);
        $node->set('field_barrett_under_exp_asu', $is_barrett_tour_under_exp_asu);
        $node->set('field_student_event_id', $event_id);
        $node->set('field_event_series_id', $event_series_id);
        // Date.
        $node->set('field_student_date_of_event', $date_of_event);
        // Date.
        $node->set('field_student_evdate', $evdate);
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
        // Added on 1/7/2026.
        $node->set('field_student_interest', $webform_submission->getData()['interest']);
        $node->set('field_student_interest_name', $webform_submission->getData()['interest_name']);

        // Email body
        // $node->set('field_student_full_agenda', $conf_email_body);
        // $node->set('field_email_subject_line', $email_subject_line);.

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

        // TODO: PDF.

        // Additional tours.
        $node->set('field_student_extra_tours', array_keys($additional_tours_array));
        // Barrett tours under Exp ASU - Only for top level Exp ASU submission.
        $node->set('field_list_of_barrett_tours', array_keys($list_of_barrett_tours_array));

        $node->save();
      }
    } // END OF else

  } // END OF private function createOneStudentRegisteredNode()

  /**
   * Create one additional tour registration node.
   */
  private function createOneAdditionalTourRegistrantNode($webform_submission, $additional_tour_id, $additional_tour, $event_series_id, $parent_event_id) {

    // Check if additional tour registrant node already exists or not using node title.
    // When updating existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions, the Additional tour registrant node already exists. If that is the case, don't create another Additional tour registrant node and update the existing node.

    $title = $webform_submission->id() . '|' . $parent_event_id . '|' . $additional_tour_id;
    $addtourregistrant_nids = $this->checkNodeAlreadyExists($title, 'additional_tour_registrant');

    // $timestamp_actual_addtour = $additional_tour['timestamp_datetime_start'];
    // $timeonly_for_addtourid = $additional_tour['time_for_addtourid_formatted'];
    $addtour_title = $additional_tour['eventName'];

    // $timeonly_for_addtourid
    // 356-2142-1750104000
    $parts2 = explode('-', $additional_tour_id);
    $timestamp_for_eventid = $parts2[2];
    // The "@" tells DateTime to treat it as a Unix timestamp.
    $dt2 = new \DateTime("@$timestamp_for_eventid");
    // Set timezone to Arizona.
    $dt2->setTimezone(new \DateTimeZone('America/Phoenix'));
    // Result: "2025-06-16 09:00:00".
    $timeonly_for_addtourid = $dt2->format('Y-m-d H:i:s');

    // $datetime_actual_addtour - Actual event date/time
    // 2025-06-16
    $eventStartDate = $additional_tour['eventStartDate'];
    // 09:00:00
    $eventStartTime = $additional_tour['eventStartTime'];
    // e.g., "2025-06-16 09:00:00".
    $date_time_string = "$eventStartDate $eventStartTime";
    $dt = new \DateTime($date_time_string, new \DateTimeZone('America/Phoenix'));
    // e.g., "06/16/25 09:00 am".
    $datetime_actual_addtour = $dt->format('m/d/y h:i a');

    // $date_of_event
    // Set to UTC
    $dt->setTimezone(new \DateTimeZone('UTC'));
    // Format: 2025-07-23T22:00:00.
    $date_of_event = $dt->format('Y-m-d\TH:i:s');

    if (count($addtourregistrant_nids) == 0) {
      // Create nodes.

      // Grab values from $webform_submission and create node object of Additional tour registrant.
      $node_addtourregistrant = Node::create([
        'type'        => 'additional_tour_registrant',
        'title'       => $title,
        'field_additional_tour_id' => $additional_tour_id,
        'field_attendee_id' => $webform_submission->id(),
        'field_student_guests' => $webform_submission->getData()['guests'] ?? '',
        // 'field_student_reg_nid' => $newnode_nid,
        'field_web_sub_id' => $webform_submission->id(),
        'field_parent_event_series_id' => $event_series_id,
        'field_parent_event_id' => $parent_event_id,

        // String field.
        'field_datetime_actual_addtour' => $datetime_actual_addtour,
        // Date type field.
        'field_actual_tour_date_time' => $date_of_event,
        // 'field_timestamp_actual_addtour' => $timestamp_actual_addtour,.
        'field_timeonly_for_addtourid' => $timeonly_for_addtourid,

        'field_student_first_name' => $webform_submission->getData()['first_name'] ?? '',
        'field_student_last_name' => $webform_submission->getData()['last_name'] ?? '',
        'field_student_email' => $webform_submission->getData()['email_address'] ?? '',
        'field_addtour_title' => $addtour_title,
      ]);
      $node_addtourregistrant->save();

    }
    else {
      // Update nodes.

      // The Additional tour registrant node with the title (additional_tour_id + webform_submission_id) already exists.
      // This happens when click on Save on existing Webform submission in https://visit-asu-csdev4.ddev.site/admin/structure/webform/manage/visit_form/results/submissions to re-post to middleware. At the same time, it will update the Student Registered node with changed value if there is anything changed.

      // $addtourregistrant_nids array should contain just one element.
      foreach ($addtourregistrant_nids as $thenid) {
        $node = Node::load($thenid);

        $node->set('field_additional_tour_id', $additional_tour_id);
        $node->set('field_attendee_id', $webform_submission->id());
        $node->set('field_student_guests', $webform_submission->getData()['guests']);
        // $node->set('field_student_reg_nid', $newnode_nid);
        $node->set('field_web_sub_id', $webform_submission->id());
        $node->set('field_parent_event_series_id', $event_series_id);
        $node->set('field_parent_event_id', $parent_event_id);

        $node->set('field_datetime_actual_addtour', $datetime_actual_addtour);
        $node->set('field_actual_tour_date_time', $date_of_event);
        // $node->set('field_timestamp_actual_addtour', $timestamp_actual_addtour);
        // $node->set('field_timeonly_for_addtourid', $timeonly_for_addtourid);

        $node->set('field_student_first_name', $webform_submission->getData()['first_name']);
        $node->set('field_student_last_name', $webform_submission->getData()['last_name']);
        $node->set('field_student_email', $webform_submission->getData()['email_address']);

        $node->set('field_addtour_title', $addtour_title);

        $node->save();
      }
    } // END OF else

  }

  /**
   * Create attendee conf email node.
   */
  private function createAttendeeConfEmailNode($webform_submission, $events_array) {

    $email_content_json = '';
    $events_json = json_encode($events_array);

    // Grab values from $webform_submission and create node object of Additional tour registrant.
    $node_attendeeConfEmail = Node::create([
      'type'        => 'attendee_conf_email',
      'title'       => $webform_submission->id(),
      'field_student_email' => $webform_submission->getData()['email_address'] ?? '',
    // Email content from Conf Letter node such as Event ID.
      'field_email_content_json' => $email_content_json,
      'field_events_json' => $events_json,
      'field_student_first_name' => $webform_submission->getData()['first_name'] ?? '',
      'field_student_last_name' => $webform_submission->getData()['last_name'] ?? '',
      'field_parent1_email' => $webform_submission->getData()['parent1_email'] ?? '',
      'field_parent2_email' => $webform_submission->getData()['parent2_email'] ?? '',
      'field_student_guests' => $webform_submission->getData()['guests'] ?? '',
      'field_student_add_email' => $webform_submission->getData()['email_address_additional'] ?? '',
      'field_student_type' => $webform_submission->getData()['visitor_type'] ?? '',
      'field_sid' => $webform_submission->id(),
      'field_student_interest' => $webform_submission->getData()['interest'] ?? '',
      'field_student_interest_name' => $webform_submission->getData()['interest_name'] ?? '',
      // Added on 6/1/2026.
      'field_conf_email_nid' => $webform_submission->getData()['conf_email_nid'] ?? '',
    ]);
    $node_attendeeConfEmail->save();

  }

  /**
   * Helping function.
   *
   * Returns multi-dimensional array of Additional tour ids with:
   * - Start timestamp
   * - Start date/time
   * - End date/time
   * - Agendas array.
   *
   * Includes code to check if aganda is overwritten.
   */
  public function getAdditionalToursUnderExpAsu($webform_submission) {
    // Here let's get the following also:
    // - Actual date/time for Additional tour
    // - Actual timestamp for Additional tour
    // - Time only field that was used for building Additional tour ID
    // - Agendas in multi-dimensional array.

    $visit_date = $webform_submission->getData()['visit_date'] ?? '';
    $add_tours = [];
    // Get value of Additional tour fields.
    for ($i = 0; $i <= 10; $i++) {
      $add_tour_id = $webform_submission->getData()['extra_tour_id_' . $i] ?? '';

      if ($add_tour_id != '') {
        // Get para id.
        $temp_array0 = explode('|', $add_tour_id);
        $temp_array = explode('-', $temp_array0[0]);
        $addtour_paragraph_entity_id = $temp_array[1];

        // Load paragraph.
        $entity_type = 'paragraph';
        $entity_id = $addtour_paragraph_entity_id;
        $entity_paragraph = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
        // ksm($entity_paragraph, "entity_para");.

        // ---------------------- Get Addtour name ---------------------//
        $addtour_title = $entity_paragraph->get('field_addtour_name')->getValue()[0]['value'] ?? '';
        // ---------------------- END OF Get Addtour name ---------------------//

        // ----- Get timestamp for actual Additional tour -----//
        $time_range_from = $entity_paragraph->get('field_time_range')->getValue()[0]['from'];
        // Format time.
        $datenew = \DateTime::createFromFormat("Y-m-d g:i a", $visit_date . " 00:00 am");
        // Timestamp of the date at 0:00am.
        $timestamp_ofthedateat0am = $datenew->getTimestamp();
        // ksm($datenew->format("Y/m/d g:i a"), "test");
        // ksm($timestamp_ofthedateat0am, "timestamp_ofthedateat0am");.

        // Actual start time timestamp.
        // Add timestamp of the date at 0:00am and timestamp for the event time.
        $timestamp_datetime_start = $timestamp_ofthedateat0am + $time_range_from;
        // ----- END OF Get timestamp for actual Additional tour -----//.

        // ----- Get human-readable date/time for actual Additional tour -----//
        // Create Date obj from timestamp
        // For America/Phoenix
        $date_obj_phx = DateTimePlus::createFromTimestamp($timestamp_datetime_start);
        $date_obj_phx->setTimeZone(new \DateTimeZone('America/Phoenix'));
        $time_start_formatted = $date_obj_phx->format('Y-m-d g:i a');
        // ----- END OF Get human-readable date/time for actual Additional tour -----//

        // ----- Get Time only field that was used for building Additional tour ID -----//
        // The timestamp is the last part of Additional tour ID
        $timestamp_for_addtourid = $temp_array[2];

        // Make it human-readable
        // Create Date obj from timestamp
        // For America/Phoenix.
        $date_obj_phx2 = DateTimePlus::createFromTimestamp($timestamp_for_addtourid);
        $date_obj_phx2->setTimeZone(new \DateTimeZone('America/Phoenix'));
        $time_for_addtourid_formatted = $date_obj_phx2->format('Y-m-d g:i a');
        // ----- END OF Get Time only field that was used for building Additional tour ID -----//

        // ---------------------- Agendas -----------------------//
        // NOTES: Include code for overwrite
        // Check if the overwrite checkbox (field_overwrite_agenda_addtour) is checked.
        // If checked, iterate Agenda set (Date and agenda) and use the one with the correct date.

        // Original agenda para ids.
        // Agenda para ids array.
        $agenda_para_array = $entity_paragraph->get('field_agenda_para')->getValue();

        // Check if field_overwrite_agenda_addtour is checked or not.
        $overwrite_agenda_addtour = $entity_paragraph->get('field_overwrite_agenda_addtour')->getValue()[0]['value'] ?? '';
        $agenda_para_array_to_use = $agenda_para_array;
        // Overwritten.
        if ($overwrite_agenda_addtour == '1') {
          // Iterate and find agenda to use.

          // 329
          $agenda_overwrite_para_ids = $entity_paragraph->get('field_agenda_overwrite_para')->getValue() != NULL ? $entity_paragraph->get('field_agenda_overwrite_para')->getValue() : '';

          foreach ($agenda_overwrite_para_ids as $overwrite_para) {

            // 351
            $overwrite_para_id = $overwrite_para['target_id'];

            // Load agenda overwrite paragraph using Paragraph ID.
            $entity_type_overwrite = 'paragraph';
            $entity_id_overwrite = $overwrite_para_id;
            $entity_paragraph_overwrite = \Drupal::entityTypeManager()->getStorage($entity_type_overwrite)->load($entity_id_overwrite);

            // Get Agenda overwrite -> Date (Overwritten date)
            // 2023-06-13.
            $date_overwrite = $entity_paragraph_overwrite->get('field_date_only')->getValue() != NULL ? $entity_paragraph_overwrite->get('field_date_only')->getValue()[0]['value'] : '';

            // Get date clicked
            // ksm($visit_date, "visit_date"); // 20230613.
            // If date matches.
            if ($date_overwrite == $visit_date) {
              // Get agenda overwrite -> Agenda (Overwritten agenda).
              // 349.
              $agenda_para_overwrite = $entity_paragraph_overwrite->get('field_agenda_para')->getValue() != NULL ? $entity_paragraph_overwrite->get('field_agenda_para')->getValue() : [];
              $agenda_para_array_to_use = $agenda_para_overwrite;
            }

          } // END OF foreach

        } // END OF if($overwrite_agenda_addtour == '1')

        $agenda_prepared_array = [];
        if (count($agenda_para_array) > 0) {
          // $agenda_prepared_array = $this->prepareAgendasArray($agenda_para_array, $visit_date, 'addtour', $entity_id);
          $agenda_prepared_array = $this->prepareAgendasArray($agenda_para_array_to_use, $visit_date, 'addtour', $entity_id);
        }
        // ksm($agenda_prepared_array, "agenda_prepared_array after prepareAgendasArray() in getAdditionalToursUnderExpAsu()");

        // ---------------------- END OF Agendas -----------------------//

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
  }  // END OF public function getAdditionalToursUnderExpAsu($webform_submission)

  /**
   * Helping function.
   *
   * Returns array of Barrett tour ids.
   */
  public function getBarrettToursUnderExpAsu($webform_submission) {

    $visit_date = $webform_submission->getData()['visit_date'] ?? '';
    $barrett_tours = [];
    // Get value of Barrett fields.
    for ($i = 0; $i <= 10; $i++) {
      $barrett_tour_id = $webform_submission->getData()['barrett_tour_id_' . $i] ?? '';
      if ($barrett_tour_id != '') {
        array_push($barrett_tours, $barrett_tour_id);
      }
    }
    return $barrett_tours;
  }

  /**
   * Helping function.
   *
   * Returns the smallest timestamp which is the earliest actual Barrett under Exp ASU start timestamp.
   */
  public function getEarliestActualBarrettUnderExpAsuStartTimestamp($barrett_under_expasu_jsonlist) {
    $json_data_array = json_decode($barrett_under_expasu_jsonlist, TRUE);
    $timestamp_array = [];
    foreach ($json_data_array as $barrett_tour) {
      $temp_array = explode('|', $barrett_tour);
      array_push($timestamp_array, $temp_array[1]);
    }
    sort($timestamp_array);
    return $timestamp_array[0];
  }

  /**
   * Check if the Student Registered node with the title of the Webform submission id already exists.
   *
   * Or, if the Additional tour registrant node with the title of the Additional tour id + Webform submission id already exists.
   *
   * Returns empty array if node doesn't exist.
   *
   * Returns nids if node exists. It should be just 1 nid, but still it is in array.
   */
  public function checkNodeAlreadyExists($title, $content_type) {
    $entity_type = 'node';
    $query = \Drupal::entityTypeManager()->getStorage($entity_type)->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('type', $content_type);
    $query->condition('title', $title);
    $nids = $query->execute();

    // If (!(empty($nids))) {
    // // The Student Registered node with the title of the Webform submission id already exists.
    // // OR, The Additional tour registrant node with the title of the Additional tour id + Webform submission id already exists.
    // $already_exists = true;
    // }.
    return $nids;
  }

  /**
   * Helper function to cancel registration of student trying to reschedule a new registration. Copied from asuaec_visit module.
   */
  public function cancelRegistration($webform_submission) {
    $values = $webform_submission->getData();
    $previous_attend_id = $values['cancel_attendee_id'];
    $previous_event_id = $values['cancel_event_id'];
    // \Drupal::logger('csstest')->notice("previous_attend_id (revamp):" . $previous_attend_id);
    // \Drupal::logger('csstest')->notice("previous_event_id (revamp):" . $previous_event_id);

    if ($previous_attend_id != NULL) {
      // If there is "-" in $previous_attend_id, it is legacy.
      // Legacy.
      if (str_contains($previous_attend_id, '-')) {
        $sid_explode = explode('-', $previous_attend_id);
        $sid = $sid_explode[1];

        $database = \Drupal::database();
        $nquery = $database->select('node__field_web_sub_id', 'wsi');
        $nquery->fields('wsi', ['entity_id']);
        $nquery->condition('wsi.field_web_sub_id_value', $sid, '=');
        $nresult = $nquery->execute();
        foreach ($nresult as $ndata) {
          // $nid[$ndata->entity_id] = $ndata->entity_id;
          $nid = $ndata->entity_id;
        }

      }
      else {
        $sid = $previous_attend_id;

        // Gather node ids.

        // Build the expected node title: "attendeeId|eventId".
        $title_pattern = $previous_attend_id . '|' . $previous_event_id;

        // Initialize $nid as empty array.
        $nid = [];

        // Only run query if we have both pieces.
        if (!empty($previous_attend_id) && !empty($previous_event_id)) {
          // Use entityQuery to find student_registered_visits nodes with that title.
          $nids = \Drupal::entityQuery('node')
          // REQUIRED in Drupal 10+.
            ->accessCheck(FALSE)
            ->condition('type', 'student_registered_visits')
            ->condition('title', $title_pattern)
            ->execute();

          // $nids is an array keyed by nid: [nid => nid, ...].
          if (!empty($nids)) {
            $nid = $nids;
          }
        }
      }

      // Delete those nodes. When the nodes are deleted, asuaec_visit_revamp_node_delete will run. (Revamp)
      if (!empty($nid)) {
        // \Drupal::logger('cstest revamp')->notice('nid:<pre>' . $nid . '</pre>');

        // Normalize to array.
        if (!is_array($nid)) {
          $nid = [$nid];
        }

        if (count($nid) > 0) {
          $all_nids = implode(',', $nid);
          foreach ($nid as $nodeid) {
            $nid_to_delete = intval($nodeid);
            $node_data = Node::load($nid_to_delete);
            $node_data->delete();
          }
        }
        \Drupal::logger('Registration cancelled')->notice("$all_nids are deleted after rescheduling");

        // Then, posting cancelation.
        $reason = "Wrong event/session";
        $data = [
          'attendeeId' => $previous_attend_id,
          'eventId' => $previous_event_id,
          'reason' => $reason,
        ];
        $payload = [];
        $payload = [$data];
        $domain = 'https://' . $_SERVER['HTTP_HOST'];
        if ($domain == 'https://visit.asu.edu') {
          $env = 'prod';
        }
        else {
          $env = 'dev';
        }
        // Post URL switch depending on environment.
        if ($env == 'prod') {
          $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/cancel';
          // Changed on 10/2/2025.
          $settings = \Drupal::service('settings');
          $auth_value = $settings->get('auth');
        }
        // DEV.
        else {
          $post_url_from_configpage = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_dev');
          \Drupal::logger('cstest')->notice('post_url_from_configpage:' . $post_url_from_configpage);

          // $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
          // Changed on 10/2/2025.
          if ($this->isDdev() === 'ddev') {
            $auth_value = \Drupal::config('secrets.api')->get('auth');
            // Post URL.
            if ($post_url_from_configpage === 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register') {
              \Drupal::logger('cstest')->notice('DDEV - Getting qa post url');
              $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
            }
            elseif ($post_url_from_configpage === 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/register') {
              \Drupal::logger('cstest')->notice('DDEV - Getting dev post url');
              $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
            }
            \Drupal::logger('cstest')->notice('DDEV - The post url:' . $post_url);
          }
          else {
            $settings = \Drupal::service('settings');
            $auth_value = '';
            if ($post_url_from_configpage === 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register') {
              \Drupal::logger('cstest')->notice('NonDDEV - Getting qa post url');
              $auth_value = $settings->get('auth_qa', '');
              $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
            }
            elseif ($post_url_from_configpage === 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/register') {
              \Drupal::logger('cstest')->notice('NonDDEV - Getting dev post url');
              $auth_value = $settings->get('auth_dev', '');
              $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
            }
            if ($auth_value === '') {
              $auth_value = $settings->get('auth_dev', '');
              $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
            }
            \Drupal::logger('cstest')->notice('You are in dev. Not in DDEV. The post url:' . $post_url);
          }

        }

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
          // $response = json_decode($request->getBody());
          $response = json_decode((string) $request->getBody(), TRUE);
        }
        catch (RequestException $e) {
          // Log the exception/fail with copy of payload.
          $fail_msg = "Failed Cancel form posting: <pre>"
            . "\nPosted data: " . print_r($payload, TRUE)
            . "\nEXCEPTION $e "
            . "</pre>";
          \Drupal::logger('Cancel form posting failure')->debug($fail_msg);
          if ($env == 'dev') {
            $the_message = new TranslatableMarkup(
              '<pre>@fail_msg<br />Post URL: @post_url</pre>',
              [
                '@fail_msg' => $fail_msg,
                '@post_url' => $post_url,
              ]
            );
            $this->messenger()->addMessage($the_message);
          }
          return FALSE;
        }

        if (!is_null($request)) {

          if (($request->getStatusCode() < 200) || ($request->getStatusCode() >= 300)) {
            // Error handling is in catch{}.
          }
          // Success.
          else {
            \Drupal::logger('asuaec_visit_revamp')->notice('Cancel success - Posted data:<pre><code>' . print_r($payload, TRUE) . '</code></pre><br />Post URL:' . $post_url);
            if ($env == 'dev') {
              $the_message = new TranslatableMarkup(
                'Cancel success: <pre>@response<br />Posted data: @payload<br />Post URL: @post_url</pre>',
                [
                  '@response' => print_r($response, TRUE),
                  '@payload' => print_r($payload, TRUE),
                  '@post_url' => $post_url,
                ]
              );
              $this->messenger()->addMessage($the_message);
            }

          } // END OF else
        }

      }

    }
  } // END OF cancelRegistration function

}
