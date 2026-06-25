<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_visit\Controller;

use DateTimeZone;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\Request;

class WebformConfirmationPage extends ControllerBase
{
    /**
     * Called from Block plugin (visitConfirmationBlock.php).
     * Build content of the confirmation block.
     */
    public function process(?string $sid = null, ?string $guests = null) {
        // Guard against missing/invalid submission id
        if (empty($sid)) {
            return [];
        }

        $webform_submission = WebformSubmission::load($sid);
        if (!$webform_submission) {
            // sid is present but not a valid webform_submission entity id.
            return [];
        }

        $submission_data = $webform_submission->getData();

        // Load submission using sid.
        // $webform_submission = WebformSubmission::load($sid);
        // $submission_data = $webform_submission->getData();
//        ksm($submission_data, "submission_data");

        // Get json string from submision id
        $json_string = $submission_data['json_string'];
//        ksm($json_string, "json_string");
        $the_json = json_decode($json_string);
//        ksm($the_json);

        // Get event info from json
        foreach($the_json as $key => $obj) {
            // Clear values
            $tourtype = '';
            unset($add_tour_array);
            $add_tour_array = array();
            $add_tour_barrett_array = array();
            $eventid = '';
            $eventnid = '';
            $eventname = '';
            $eventtype = '';
            //$visit_date = '';
            $displaydate = '';
            $from_time = '';
            $to_time = '';
            $start_timestamp = '';
            $end_timestamp = '';
            $campus = '';
            $interest = '';
            $barrett_custom_title = '';
            $event_display_title = '';

            foreach($obj as $key2 => $value2){

                if($key2 == 'addtour'){
                    foreach($value2 as $addtour){
                        if($addtour != '' && $addtour != null) {
                            array_push($add_tour_array, $addtour);
                        }
                    }
                } // END OF if($key2 == 'addtour')

                if($key2 == 'addtour_barrett'){
                    foreach($value2 as $addtour_barrett){
                        if($addtour_barrett != '' && $addtour_barrett != null) {
                            array_push($add_tour_barrett_array, $addtour_barrett);
                        }
                    }
                } // END OF if($key2 == 'addtour_barrett')

                if($key2 == 'eventid'){
                    $eventid = $value2;
                }
                if($key2 == 'eventnid'){
                    $eventnid = $value2;
                }
                if($key2 == 'eventname'){
                    $eventname = $value2;
                    $eventname = str_replace('%22', '', $eventname);
                    $eventname = str_replace('+', ' ', $eventname);
                }
                if($key2 == 'eventtype'){
                    $eventtype = $value2;
                    $eventtype = str_replace('+', ' ', $eventtype);
                }
                if($key2 == 'timestamp'){
                    $start_timestamp = $value2;
                    //$displaydate = date('l, F j, Y \a\t g:i a' , $start_timestamp);
                    // Date
                    $displaydate = date('l, F j, Y' , $start_timestamp);
                    // Time
                    $displaytime = date('g:i a' , $start_timestamp);
                    $displaytime = str_replace('12 p.m.', 'noon', str_replace(':00', '', str_replace('pm', 'p.m.', str_replace('am', 'a.m.', $displaytime))));
                }
                if($key2 == 'from'){
                    $from_time = $value2;
                }
                if($key2 == 'to'){
                    $to_time = $value2;
                }
                if($key2 == 'timestamp2'){
                    $end_timestamp = $value2;
                }
                if($key2 == 'campus'){
                    $campus = $value2;
                }
                if($key2 == 'interest'){
                    $interest = $value2;
                }
                if($key2 == 'tourtype'){
                    $tourtype = $value2;
                }
                if($key2 == 'custom_title'){
                    $barrett_custom_title = $value2;
                }
                if($key2 == 'eventdisplaytitle'){
                    $event_display_title = $value2;
                }

            } // END OF foreach($obj as $key2 => $value2)

        } // END OF foreach($theJson as $key => $value)
//        ksm($event_display_title, "event_display_title");

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
      
        // Only California doesn't have campus at the end. - 8/6/2025
        if($campus != '') {
          if($campus == 'West') {
            $campus = 'West Valley campus'; // Change for West Valley campus - 10/23/2023
          }
          else if($campus == 'ASU California Center in downtown L.A.') {
            // Don't add 'campus'
          }
          else {
            $campus = $campus . ' campus';
          }
        }

        if (!function_exists('format_time_timestamp_confirmation_page')) {

            // Parameter contains unixtimestamp.
            function format_time_timestamp_confirmation_page($timestamp_from, $timestamp_to) {
                // $time_from contains 9:00 am. $time_to contains 11:00 am.

                // If start time and end time are the same, make end time empty string.
                if($timestamp_from === $timestamp_to) {
                    $timestamp_to = '';
                }

                $time_from = date('g:ia', $timestamp_from);
                if ($timestamp_to != ''){
                    $time_to = date('g:ia', $timestamp_to);
                } else {
                    $time_to = '';
                }

                // Change 12pm to Noon and noon.
                $time_from = str_replace('12:00pm', 'Noon', $time_from);
                $time_to = str_replace('12:00pm', 'noon', $time_to);

                if($time_to === '') {
                    $time_string_formatted = $time_from;
                } else {
                    $time_string_formatted = $time_from . ' &mdash; ' . $time_to;
                }

                // Change pm to p.m. and am to a.m.
                $time_string_formatted = str_replace('pm', ' p.m.', str_replace('am', ' a.m.', $time_string_formatted));

                // Remove first am/pm if it is the same as the ending time's am/pm.
                if(substr_count($time_string_formatted, 'a.m.') == 2 ) {
                    $original_string = '/'.preg_quote(' a.m.', '/').'/';
                    $time_string_formatted = preg_replace($original_string, '', $time_string_formatted, 1);
                }
                if(substr_count($time_string_formatted, 'p.m.') == 2 ) {
                    $original_string = '/'.preg_quote(' p.m.', '/').'/';
                    $time_string_formatted = preg_replace($original_string, '', $time_string_formatted, 1);
                }
                $time_string_formatted = str_replace(':00', '', $time_string_formatted);

                return $time_string_formatted;
            }

        } // END OF if (!function_exists('format_time_timestamp_confirmation_page'))


        //--------- Section 1 - Intro ------------//
        $sec1 = "";
//        if($eventtype == "Self-guided campus Tour" ||
//            $eventtype == "Sun Devil Day"
//        ) {
//            $sec1 .= "<div class='col-12 col-md-9'>";
//            $sec1 .= "<p>Thank you for registering for a {$event_display_title} at ASU’s {$campus} campus.</p>";
//            $sec1 .= "<p>You will receive a confirmation email shortly with more information for check-in and parking.</p>";
//            $sec1 .= "<p>Please review <a target='_blank' href='/what-to-expect'><strong>what to expect for your visit</strong></a> to ensure you are able to follow all health and safety guidelines and attend at your scheduled date and time.</p>";
//            $sec1 .= "<p>Here are your details for you and {$guests} guest(s):</p>";
//            $sec1 .= "</div>";
//
//        } else {
//            $sec1 .= "<div class='col-12 col-md-9'>";
//            $sec1 .= "<p>Thank you for registering for {$event_display_title} at Arizona State University. <strong>You will receive a confirmation email</strong> shortly with more information for check-in and parking. If you do not receive your confirmation email within the next 24 hours, please email us at <a href='mailto:visitASU@asu.edu'>visitASU@asu.edu</a>.</p>";
//            $sec1 .= "<p>Your campus visit details:</p>";
//            $sec1 .= "</div>";
//        }

    // article 'a' or 'an'
    $first_letter = strtolower($event_display_title[0]);
    $article = in_array($first_letter, ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';

		// Now all events will see the following in the conf page. (7/12/2024)
		$sec1 .= "<div class='col-12 col-md-9'>";
    $sec1 .= "<p>Thank you for registering for {$article} {$event_display_title} at Arizona State University. <strong>You will receive a confirmation email</strong> shortly with more information about check-in and parking. If you do not receive your confirmation email within the next 24 hours, please email us at <a href='mailto:visitASU@asu.edu'>visitASU@asu.edu</a>.</p>";
		$sec1 .= "<p>Your campus visit details:</p>";
		$sec1 .= "</div>";		


        //--------- Section 2 - Print out top-level tours ---------------//
        $sec2 = '';
        $sec2 .= "<div class='event-wrap col-12 col-md-9'>";
        $sec2 .= "<div class='toplevel-event mb-2'>";

//        ksm($eventtype, "eventtype");
        if($eventtype != "Self-guided campus Tour") { // For Self-guided tour, don't print date/time
            // Date
            $sec2 .= "<strong>Date:</strong> " . $displaydate . "<br />";
            $sec2 .= "<strong>Time:</strong> " . $displaytime . " (Check-in begins 30 minutes before)<br />";
        }
        // Campus
        // If Event type is Academic Facility Tour, don't display campus. (8/21/2025)
        if($eventtype != "Academic Facility Tour") {
          if($eventtype === "Graduate Student Event") {
            $sec2 .= "<strong>Location:</strong> " . $campus . ", Memorial Union, 2nd floor<br />";
          } else {
            $sec2 .= "<strong>Location:</strong> " . $campus . "<br />";
          }
        }

        $sec2 .= "</div>"; // END OF .toplevel-event


        //------ Section 3 - Additional tour -------//
        $sec3 = '';
        $addtour_jsonlist = isset($submission_data['addtour_jsonlist']) ? $submission_data['addtour_jsonlist'] : '';
//        ksm($addtour_jsonlist, "addtour_jsonlist");
        $the_addtour_jsonlist = [];
        if($addtour_jsonlist != ''){
            $the_addtour_jsonlist = json_decode($addtour_jsonlist);
        }


        //------ Section 4 - Barrett under Exp ASU -------//
        // Get barrett_under_expasu_jsonlist from submission
        $sec4 = '';
        $barrett_under_expasu_jsonlist = isset($submission_data['barrett_under_expasu_jsonlist']) ? $submission_data['barrett_under_expasu_jsonlist'] : '';
//        ksm($barrett_under_expasu_jsonlist, "barrett_under_expasu_jsonlist");
        $the_barrett_under_expasu_jsonlist = [];
        if($barrett_under_expasu_jsonlist != ''){
//            ksm($barrett_under_expasu_jsonlist, "barrett_under_expasu_jsonlist");
            $the_barrett_under_expasu_jsonlist = json_decode($barrett_under_expasu_jsonlist);
        }


        // In order to sort chronologically,
        // combine $sec3 and $sec4 and put it in $sec3_4
        $sec3_4 = '';
        $merged_addtour_barrett_array = [];
        $merged_addtour_barrett_array = array_merge($the_addtour_jsonlist, $the_barrett_under_expasu_jsonlist);
        if(sizeof($merged_addtour_barrett_array) > 0) {
//            ksm($merged_addtour_barrett_array, "merged_addtour_barrett_array");
            // Sort chronologically
            usort($merged_addtour_barrett_array, 'compare_timestamp');
//            ksm($merged_addtour_barrett_array, "merged_addtour_barrett_array after sort");

            foreach ($merged_addtour_barrett_array as $the_tour) {
                $temp_array = explode('|', $the_tour);
                $start_timestamp = $temp_array[1];
                $end_timestamp = $temp_array[2];
                $display_title = $temp_array[3];
                $time = format_time_timestamp_confirmation_page($start_timestamp, $end_timestamp);
                $sec3_4 .= "<p>Optional session &mdash; " . $display_title . " - " . $time . "</p>";
            }
        }

        $sec3_4 .= "</div>"; // END OF .event-wrap


        // Section 5 - Add to Calendar
        $sec5 = '';

        // If Barrett under Exp ASU starts earlier than Exp ASU, don't display Add to Calendar.
//        ksm($start_timestamp_barrett_underexpasu, "start_timestamp_barrett_underexpasu");
//        ksm($start_timestamp, "start_timestamp");
        if((!isset($start_timestamp_barrett_underexpasu)) || (is_null($start_timestamp_barrett_underexpasu)) || (intval($start_timestamp) < intval($start_timestamp_barrett_underexpasu))) {

            // Get Add to cal description
            // Get Event Series ID
            $event_series_entity_id = isset($submission_data['event_series_entity_id']) ? $submission_data['event_series_entity_id'] : '';
            $event_instance_entity_id = isset($submission_data['event_instance_entity_id']) ? $submission_data['event_instance_entity_id'] : '';
            $addtocal_descr = $this->getAddToCalDescr($event_series_entity_id, $event_instance_entity_id);
//            ksm($addtocal_descr, "addtocal_descr");

            $output_addtocalendar = "";
            $output_addtocalendar .= "<span class='addtocalendar atc-style-text col-12'>";
            $output_addtocalendar .= "<var class='atc_event'>";
            $output_addtocalendar .= "<var class='atc_date_start'>" . date('m/d/y h:i a', $start_timestamp) . "</var>";
            $output_addtocalendar .= "<var class='atc_date_end'>" . date('m/d/y h:i a', $end_timestamp) . "</var>";
            $output_addtocalendar .= "<var class='atc_timezone'>America/Phoenix</var>";
            $output_addtocalendar .= "<var class='atc_title'>{$event_display_title}</var>";
            if ($addtocal_descr != '') {
                $output_addtocalendar .= "<var class='atc_description'>{$addtocal_descr}</var>";
            }
            if($eventtype == "Academic Facility Tour") { // If Event type is Academic Facility Tour, don't display campus. (8/21/2025)
              $output_addtocalendar .= "<var class='atc_location'>Arizona State University</var>";
            } else {
              $output_addtocalendar .= "<var class='atc_location'>Arizona State University - {$campus}</var>";
            }
            $output_addtocalendar .= "<var class='atc_organizer'>Arizona State University</var>";
            $output_addtocalendar .= "<var class='atc_organizer_email'>VisitASU@asu.edu</var>";
            $output_addtocalendar .= "</var>";
            $output_addtocalendar .= "</span>";

            $sec5 .= $output_addtocalendar;

        } // ENF OF if(intval($start_timestamp) > intval($start_timestamp_barrett_underexpasu))

        // Section 6 - Closing
        $persontype = $submission_data['visitor_type'];
        $sec6 = '';
        if($eventtype != "Self-guided campus Tour") { // For Self-guided tour, don't print date/time
            $sec6 .= "<div class='col-12 col-md-9'><p style='margin-top: 1rem;'>Please review <a href='https://visit.asu.edu/what-to-expect'>what to expect</a> for your visit to make sure you're prepared for your time on campus.</p></div>";
        }
        $sec6 .= "<div class='col-12 col-md-9'>";
        if($persontype != 'Graduate student') {
            $sec6 .= '<h3>Continue your visit experience online.</h3>';
            $sec6 .= '<p>Review <a target="_blank" href="https://yourfuture.asu.edu/digital-resources">digital materials, including ASU’s viewbook</a>, where you can get a good idea of what it will be like to live and learn at ASU.</p>';
        }
//        $sec6 .= '<p>For specific questions about your application and enrollment, contact your personal admission representative at <a target="_blank" href="https://asu.edu/findmyrep">asu.edu/findmyrep</a>.</p>';
        $sec6 .= '<p>For specific questions about your application and enrollment, please contact us at <a target="_blank" href="https://admission.asu.edu/contact">admission.asu.edu/contact</a>.</p>';
        $sec6 .= '<p>PLEASE PRINT THIS FOR YOUR RECORDS</p>';
        $sec6 .= '</div>';

        // Lastly, combine sections
//        $output = $sec1 . $sec2 . $sec3 . $sec4 . $sec5 . $sec6;
        $output = $sec1 . $sec2 . $sec3_4 . $sec5 . $sec6;
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
