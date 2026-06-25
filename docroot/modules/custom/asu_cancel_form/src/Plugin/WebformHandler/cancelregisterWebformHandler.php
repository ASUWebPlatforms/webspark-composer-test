<?php

namespace Drupal\asu_cancel_form\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Link;
use Drupal\node\NodeViewBuilder;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "Submit the form submissions to Cancel registration API",
 *   label = @Translation("Submit the form submissions to Cancel registration API"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Submit the form submissions to master form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class CancelRegisterWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    // Function asu_masterform_posting_webform_handler_invoke_post_save_alter(\Drupal\webform\Plugin\WebformHandlerInterface $handler, array &$args) {.

    // Get webform submission values and current wenform id.
    $webform = $this->getWebform();

    $values = $webform_submission->getData();
    $form_object = $form_state->getFormObject();
    $web_id = $webform->id();
    $event_type = $values['event_type'];
    $eventid = $values['event_id'];
    $reason = $values['please_specify_the_reason_for_cancellation_'];
    // Get event date.
    $date_explode = explode('-', $eventid);
    $unix_date = $date_explode[1];

    $event_date = date('M d, Y', intval($unix_date));

    // Set event date field value.
    $webform_submission->setElementData('event_date', $event_date);
    $webform_submission->save();

    if ($event_type == "exp") {
      $aid = $values['attendee_id'];
      $attendi_id = $aid;
      // Attendee id (9/10/2025)
      // Evolution version: Event series id + submission id
      // Revamp version: Submission id.
      $visit_version = 'evolution';
      // Evolution.
      if (strpos($aid, '-') !== FALSE) {
      }
      // Revamp.
      else {
        // Revamp.
        $visit_version = 'revamp';
      }

      // Evolution.
      if ($visit_version == 'evolution') {
        $sid_explode = explode('-', $aid);
        $sid = $sid_explode[1];
        // Revamp (9/10/2025)
      }
      else {
        $sid = $aid;
      }
      $database = \Drupal::database();

      // Get node ids of student registered node based on web submission id field.

      if ($visit_version == 'evolution') {
        $nquery = $database->select('node__field_web_sub_id', 'wsi');
        $nquery->fields('wsi', ['entity_id']);
        $nquery->condition('wsi.field_web_sub_id_value', $sid, '=');
        // Revamp (9/10/2025)
      }
      else {
        $title = $sid . '|' . $eventid;
        $nquery = $database->select('node_field_data', 'n');
        $nquery->addField('n', 'nid', 'entity_id');
        $nquery->condition('n.title', $title, '=');
        $nquery->condition('n.type', 'student_registered_visits');
      }

      $nresult = $nquery->execute();
      foreach ($nresult as $ndata) {
        $nid[$ndata->entity_id] = $ndata->entity_id;
      }

      // Check if the student registered node has cancelled regitration field checked' If it's not checked, then delete those nodes.
      if (!empty($nid)) {
        if (count($nid) > 0) {
          $all_nids = implode(',', $nid);
          foreach ($nid as $nodeid) {
            $node_data = Node::load($nodeid);
            $node_data->delete();

            /*$cancel_val = $node_data->get('field_canceled_registration')->getValue();
            if(empty($cancel_val)){
            $node_data->set('field_canceled_registration', 'Yes');
            $node_data->save();
            }*/
          }
          \Drupal::logger('Registration cancelled')->notice("$all_nids are deleted");
        }
      }
    }
    if ($event_type == "rsvp") {
      // $attendi_id = $web_id.'-'.$sid;
      $attendi_id = $values['attendee_id'];
      // Save cancel value results in webform field.
      $webform_submission->setElementData('cancel_submission_', 'Yes');
      $config = \Drupal::config('asu_masterform_posting.fields_admin_settings');
      $webform_id = $config->get('nid');

      $sun_webform = Webform::load($webform_id);
      $sun_webform = Webform::load($webform_id);
      if ($sun_webform->hasSubmissions()) {
        $query = \Drupal::entityQuery('webform_submission')
          ->accessCheck(TRUE)
          ->condition('webform_id', $webform_id);
        $web_result = $query->execute();
        $sun_submission_data = [];
        foreach ($web_result as $item) {
          $sun_submission = WebformSubmission::load($item);
          $sun_submission_data = $sun_submission->getData();
        }
      }

      // Get guests count.
      $guestCount = $sun_submission_data['how_many_guests_will_join_you'];
      $sub_count = 1 + intval($guestCount);

      // Get node id of the event and update remaining spots after cancellation.
      $nodedataid = intval($sun_submission_data['nid']);
      $sun_node_data = Node::load($nodedataid);
      $rem_spots = $sun_node_data->get('field_remaining_spots')->value;
      $rem_spots_diff = $rem_spots - $sub_count;
      $sun_node_data->set('field_remaining_spots', $rem_spots_diff);
      $sun_node_data->save();

    }
    $cancel_data['attendeeId'] = $attendi_id;
    $cancel_data['eventId'] = $eventid;
    $cancel_data['reason'] = $reason;
    // $data = json_encode($cancel_data);
    $data = [
      'attendeeId' => $attendi_id,
      'eventId' => $eventid,
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
      // $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
      $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/visit/cancel';

      // Changed on 10/2/2025.
      $settings = \Drupal::service('settings');
      $auth_value = $settings->get('auth');
    }
    // Else { // dev
    // // $post_url = 'https://esb-dev.asu.edu/api/v1/crm-recruiting-visits-web-exp/register';
    // $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';

    // \Drupal::logger('cstest')->notice("isDdev from cancelregisterWebformHandler: " . htmlspecialchars($this->isDdev()));
    // if($this->isDdev() === 'ddev') { // Changed on 10/2/2025
    // $auth_value = \Drupal::config('secrets.api')->get('auth');
    // } else {
    // $settings = \Drupal::service('settings');
    // $auth_value = $settings->get('auth_dev');
    // }
    // }
    // Changed on 4/28/2026.
    else {
      $post_url_from_configpage = \Drupal::config('asuaec_visit_revamp.settings')->get('post_url_dev');
      \Drupal::logger('cstest')->notice('post_url_from_configpage from cancelregisterWebformHandler.php:' . $post_url_from_configpage);

      // $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
      // Changed on 10/2/2025.
      if ($this->isDdev() === 'ddev') {
        $auth_value = \Drupal::config('secrets.api')->get('auth');
        // Post URL.
        if ($post_url_from_configpage === 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register') {
          \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - DDEV - Getting qa post url');
          $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
        }
        elseif ($post_url_from_configpage === 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/register') {
          \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - DDEV - Getting dev post url');
          $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
        }
        \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - DDEV - The post url:' . $post_url);
      }
      else {
        $settings = \Drupal::service('settings');
        $auth_value = '';
        if ($post_url_from_configpage === 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/register') {
          \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - NonDDEV - Getting qa post url');
          $auth_value = $settings->get('auth_qa', '');
          $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/visit/cancel';
        }
        elseif ($post_url_from_configpage === 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/register') {
          \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - NonDDEV - Getting dev post url');
          $auth_value = $settings->get('auth_dev', '');
          $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
        }
        if ($auth_value === '') {
          $auth_value = $settings->get('auth_dev', '');
          $post_url = 'https://crm-request-form-submission-router-dev.apps.asu.edu/v1/event/visit/cancel';
        }
        \Drupal::logger('cstest')->notice('From cancelregisterWebformHandler.php - You are in dev. Not in DDEV. The post url:' . $post_url);
      }

    }

    // $username = \Drupal::config('secrets.api')->get('username');
    // $pass = \Drupal::config('secrets.api')->get('password');
    // $auth_value = \Drupal::config('secrets.api')->get('auth'); // Changed on 10/2/2025
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
      $response = json_decode($request->getBody());
    }
    catch (RequestException $e) {
      $fail_msg = "Failed Cancel form posting: <pre>"
        . "\nDomain: " . $domain
        . "\nPost URL: " . $post_url
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

    }

    if (!is_null($request)) {

      if (($request->getStatusCode() < 200) || ($request->getStatusCode() >= 300)) {
        // Error handling is in catch{}.
      }
      // Success.
      else {
        \Drupal::logger('asu_cancel_form')->notice('Success - Posted data:<pre><code>' . print_r($payload, TRUE) . '</code></pre>');
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

  }

}
