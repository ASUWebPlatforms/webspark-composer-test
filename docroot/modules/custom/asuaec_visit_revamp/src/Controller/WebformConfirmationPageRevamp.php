<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_visit_revamp\Controller;

use DateTimeZone;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\Request;
use Drupal\asuaec_visit\Controller\WebformConfirmationPage as LegacyWebformConfirmationPage;

class WebformConfirmationPageRevamp extends ControllerBase
{
  /**
   * Called from Block plugin (visitConfirmationBlock.php).
   * Build content of the confirmation block.
   */
  public function process(string $sid = null, string $guests = null) {
    // Load submission using sid.
    $webform_submission = WebformSubmission::load($sid);

    if (empty($webform_submission)) {
      return [
        '#markup' => $this->t('Submission not found.'),
        '#cache'  => ['max-age' => 0],
      ];
    }

    $webform_id = $webform_submission->getWebform()->id();
    \Drupal::logger('cstest')->notice('webform_id: ' . $webform_id );

    // If this submission came from the production webform, delegate to the legacy controller.
    if ($webform_id === 'visit_form' || $webform_id === 'registration_form') { // Original visit_form or original "other" form
      $legacy = new LegacyWebformConfirmationPage();
      return $legacy->process($sid, $guests);
    }

    // Revamp logic starts here

    $submission_data = $webform_submission->getData();
//        ksm($submission_data, "submission_data");

    // Get Event IDs and pull Event info
    $events_array =  _get_selected_events($webform_submission);
//    \Drupal::logger('cstest')->notice('events_array from WebformConfirmationPageRevamp:<pre>' . print_r($events_array, true) . '</pre>');

    //-----Display like "You have selected"-----//
    $output_selected_events = '';
    // Step 1: Group by date and campus
    $grouped = [];

    foreach ($events_array as $event) {
      $date = $event['eventStartDate'];
      $campus = $event['eventLocation'];
      $grouped[$date][$campus][] = $event;
    }

    // Step 2: Format and display
    foreach ($grouped as $date => $campuses) {
      $formatted_date = date('l, F j, Y', strtotime($date));
      $output_selected_events .= "<h4>$formatted_date</h4>" . "\n";

      foreach ($campuses as $campus => $event_list) {
        // Change for West Valley campus
        if($campus == 'West') {
          $campus = 'West Valley';
        }
        $output_selected_events .= "<h5>$campus campus</h5>";

        $output_selected_events .= "<ul>";
        foreach ($event_list as $event) {
//          $title = $event['eventTitle'] ?? $event['eventName'];
          $display_title = $event['eventDisplayTitle'] ?? $event['eventTitle'];
          // \Drupal::logger('cstest')->notice('event:<pre>' . print_r($event, true) . '</pre>');
          $eventtype = $event['category'] ?? '';
          if($eventtype === "Self-guided campus Tour") { // For Self-guided tour, don't print date/time
            $output_selected_events .= "<li>$display_title</li>";
          } else {
            $start_time = date('g:i a', strtotime($event['eventStartTime']));
            $output_selected_events .= "<li>$display_title - $start_time</li>";
          }

          // Handle children
          if (!empty($event['children'])) {
            $output_selected_events .= "<ul>";
            foreach ($event['children'] as $child) {
//              $child_title = $child['eventTitle'] ?? $child['eventName'];
              $child_display_title = $child['eventDisplayTitle'] ?? $child['eventTitle'];
              $child_start = date('g:i a', strtotime($child['eventStartTime']));
              $child_end = date('g:i a', strtotime($child['eventEndTime']));
              $output_selected_events .= "<li>Optional session — $child_display_title ($child_start - $child_end)</li>";
            }
            $output_selected_events .= "</ul>";
          }
        }
        $output_selected_events .= "</ul>";
      }

      $output_selected_events .= "\n"; // Space between dates
    }

//    \Drupal::logger('cstest')->notice('output_selected_events:<pre>' . $output_selected_events . '</pre>');


//        // Get Event type, campus, guests, start timestamp and end timestamp from submission
//        $eventtype = isset($submission_data['event_type']) ? $submission_data['event_type'] : '';
//        $campus = isset($submission_data['campus']) ? $submission_data['campus'] : '';
//        $guests = isset($submission_data['guests']) ? $submission_data['guests'] : '';
//        $start_timestamp = isset($submission_data['start_timestamp']) ? $submission_data['start_timestamp'] : '';
//        // Date
//        $displaydate = date('l, F j, Y' , $start_timestamp);
//        // Time
//        $displaytime = date('g:i a' , $start_timestamp);
//        $displaytime = str_replace('12 p.m.', 'noon', str_replace(':00', '', str_replace('pm', 'p.m.', str_replace('am', 'a.m.', $displaytime))));
//        $end_timestamp = isset($submission_data['end_timestamp']) ? $submission_data['end_timestamp'] : '';



    //--------- Section 1 - Intro ------------//
    $sec1 = "";
    // Now all events will see the following in the conf page. (7/12/2024)
    $sec1 .= "<div class='col-12 col-md-9'>";
    $sec1 .= "<p>Thank you for registering for campus visit(s) at Arizona State University. <strong>You will receive a confirmation email</strong> shortly with more information for check-in and parking. If you do not receive your confirmation email within the next 24 hours, please email us at <a href='mailto:visitASU@asu.edu'>visitASU@asu.edu</a>.</p>";
    $sec1 .= "<h3>Your campus visit details:</h3>";
    $sec1 .= "</div>";


    // Section 6 - Closing
    $persontype = isset($submission_data['visitor_type']) ? $submission_data['visitor_type'] : '';
    $sec6 = '';
//    if($eventtype != "Self-guided campus Tour") { // For Self-guided tour, don't print date/time
//        $sec6 .= "<div class='col-12 col-md-9'><p style='margin-top: 1rem;'>Please review <a href='https://visit.asu.edu/what-to-expect'>what to expect</a> for your visit to ensure you are prepared for your time on campus.</p></div>";
//    }
    $sec6 .= "<div class='col-12 col-md-9'>";
    if($persontype != 'Graduate student') {
        $sec6 .= '<h3>Continue your visit experience online.</h3>';
        $sec6 .= '<p>Review <a target="_blank" href="https://yourfuture.asu.edu/digital-resources">digital materials, including ASU’s viewbook</a>.</p>';
    }
//        $sec6 .= '<p>For specific questions about your application and enrollment, contact your personal admission representative at <a target="_blank" href="https://asu.edu/findmyrep">asu.edu/findmyrep</a>.</p>';
    $sec6 .= '<p>For specific questions about your application and enrollment, please contact us at <a target="_blank" href="https://admission.asu.edu/contact">admission.asu.edu/contact</a>.</p>';
    $sec6 .= '<p>PLEASE PRINT THIS FOR YOUR RECORDS</p>';
    $sec6 .= '</div>';

      // Lastly, combine sections
    $output = $sec1 . $output_selected_events . $sec6;


//        $output = "<h3>Confirmation page is coming soon.</h3>";
      return array(
          '#markup' => \Drupal\Core\Render\Markup::create($output),
          '#cache' => array( // Turn off cache.
              'max-age' => 0,
          ),
      );

  } // END OF public function process()

  /**
   * @param $eventseries_id
   * @param $eventinstance_id
   * @return mixed|string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * Helping function to get Add to Calendar description.
   */
  public function getAddToCalDescr($eventseries_id, $eventinstance_id) {
      // Load the parent entity
      $entity_type_eventseries = 'eventseries';
      $entity_id_eventseries = $eventseries_id;
      $entity_eventseries  = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries)->load($entity_id_eventseries);
      // Load the instance entity
      $entity_type_eventinstance = 'eventinstance';
      $entity_id_eventinstance = $eventinstance_id;
      $entity_eventinstance = \Drupal::entityTypeManager()->getStorage($entity_type_eventinstance)->load($entity_id_eventinstance);


      // Check if Overwrite is checked or not in Event instance
      $overwrite_addtocal_descr = '';
      $overwrite_addtocal_descr = (!is_null($entity_eventinstance)) && $entity_eventinstance->hasField('field_overwrite_addtocal_descr') && sizeof($entity_eventinstance->get('field_overwrite_addtocal_descr')->getValue()) > 0 ? $entity_eventinstance->get('field_overwrite_addtocal_descr')->getValue()[0]['value'] : '';

      $addtocal_description = '';
      if($overwrite_addtocal_descr == '1') { // Overwrite conf letter
          $addtocal_description = $entity_eventinstance->hasField('field_addtocal_descr_eventinst') && sizeof($entity_eventinstance->get('field_addtocal_descr_eventinst')->getValue()) > 0 ? $entity_eventinstance->get('field_addtocal_descr_eventinst')->getValue()[0]['value'] : '';
  //                ksm($addtocal_description, "addtocal_description");
      } else {
          $addtocal_description = (!is_null($entity_eventseries)) && $entity_eventseries->hasField('field_add_to_calendar_descr') && sizeof($entity_eventseries->get('field_add_to_calendar_descr')->getValue()) > 0 ? $entity_eventseries->get('field_add_to_calendar_descr')->getValue()[0]['value'] :'';
      }
      return $addtocal_description;
  }

} // END OF class WebformConfirmationPage
