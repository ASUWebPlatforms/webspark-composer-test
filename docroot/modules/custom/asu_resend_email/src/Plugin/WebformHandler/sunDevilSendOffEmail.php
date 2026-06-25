<?php

namespace Drupal\asu_resend_email\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use GuzzleHttp\Exception\RequestException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime;

/**
 * Form submission handler
 *
 * @WebformHandler(
 *   id = "Confirmation email for Sun Devil Send Off webform from resend email module",
 *   label = @Translation("Confirmation email for Sun Devil Send Off webform from resend email module"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Confirmation email for Sun Devil Send Off webform from resend email module"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class sunDevilSendOffEmail extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
      return [];
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
	//public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get webform submission values and current wenform id
		$webform = $this->getWebform();
    $nodeid = $webform->id();
    
    if($nodeid == "sun_devil_send_off"){
      $config = \Drupal::config('asu_masterform_posting.fields_admin_settings');
      $values =  $webform_submission->getData();
      $first_name = $values['first_name'];
      $event_type = $values['event_type']; // Added on 1/17/2025 by Chizuko
      $email = $values['email'];
      $event_id = $values['event_id'];
      $form_object = $form_state->getFormObject();
      $webform_submission->setSticky(!$webform_submission->isSticky())->save();
      $sid = $webform_submission->id();
      $nid = $values['nid'];
      $attendee_id = $nid.'-'.$sid;
      $fname = $values['first_name'];
      $event_date_string = $values['event_date'];
      $event_start_time = $values['event_date_and_time'];
      //dpm($event_start_time, "event_start_time");
      //$state = $values['field_offcampus_event_state']; //<--- Not in Webform field. Commented it out.
      $node = Node::Load($nid);

      //$checkin_time_string = $node->field_offcampus_checkin_time[LANGUAGE_NONE][0]['value']; //<---****Check-in time if we want to get it from node
      $city_val = $node->get('field_offcampus_event_city')->getValue();
      $state_val = $node->get('field_offcampus_event_state')->getValue();
      $city = $city_val[0]['value'];
      $state = $state_val[0]['value'];

      $location_val = $node->get('field_offcampus_event_location')->getValue();
      $location = $location_val[0]['value'];
      $parking = !empty($node->get('field_offcampus_event_parking')) ? $node->get('field_offcampus_event_parking')->getValue() : '';
      $parking_val = !empty($node->get('field_offcampus_event_parking')) ? $node->get('field_offcampus_event_parking')->getValue() : '';
      if(!empty($parking_val)){
       $parking = $parking_val[0]['value'];
      }
      else{
      $parking = '';
      }
      $event_start_time_timestamp = strtotime($event_start_time);
      //dpm($event_start_time_timestamp, "event_start_time_timestamp");
      $field_value_array = $node->get('field__domestic_international_vs')->getValue();
      $domestic_international = (!empty($field_value_array) && isset($field_value_array[0]['value'])) ? $field_value_array[0]['value'] : '';

      //  $checkin_time_v = date('g:i a', strtotime('-30 minutes',$event_start_time_timestamp));
      //  $start_time = date("g:i a", $event_start_time_timestamp);

      // Timezone for display (Changed on 11/19/2025)
      $tz = new \DateTimeZone('America/Phoenix');
      // Create DateTime from UNIX timestamp (the '@' form creates in UTC) and convert to desired tz
      $start = (new \DateTime('@' . $event_start_time_timestamp))->setTimezone($tz);
      // Subtract 30 minutes for check-in
      $checkin = (clone $start)->sub(new \DateInterval('PT30M'));
      // Format strings
      $start_time   = $start->format('g:i a');    // e.g. "10:00 am"
      $checkin_time_v = $checkin->format('g:i a');  // e.g. "9:30 am"


      $cancel_link = "/cancel-registration?aid=$attendee_id&eventid=$event_id&etype=rsvp&source=sundevilsendoff";
      //ksm($cancel_link);

      // Output switch
      $output = "";  
      $banner_img_path = '';
      $banner_img_alt = '';

      switch($event_type) {
        case "Sun Devil Sendoff":
          $subject_line = "Sun Devil Sendoff confirmation";
//          $banner = "<img src='https://visit.asu.edu/sites/default/files/2025-04/email_63192_SunDevilSendoff_Banner_700x394.jpg' alt='Sun Devil Sendoff' />"; // Updated to the new email template on 5/13/2025.
          $banner_img_path = "https://visit.asu.edu/sites/default/files/2025-04/email_63192_SunDevilSendoff_Banner_700x394.jpg";
          $banner_img_alt = 'Sun Devil Sendoff';
          $output .= "<p style='margin-bottom:10px'>Thank you for registering for the Sun Devil Send Off event in " . $city .", " . $state ."! We are excited to welcome you and your family to Arizona State University.  </p><p>This annual ASU tradition welcomes incoming ASU students and their families to the Sun Devil community and offers a great chance to meet other students from your home state coming to ASU in the fall. You will have the opportunity to connect with ASU Alumni. We hope you and your family have fun at this event to celebrate your next step into your lifelong ASU experience.</p>";
//          $output .= "<p style='margin-bottom:10px'><strong>" . $event_date_string ."</strong><br />";
//          $output .= "Registration begins at " . $checkin_time_v . ". "; //<---****Check-in time
//          $output .= "The formal presentation begins at " . $start_time . "<br />";
          $output .= $location;
//          if (!empty($parking)) {
//            $output .= "<p style='margin-bottom:10px'>" . $parking . "</p>";
//          }
          $output .= "<p style='margin-bottom:10px'>This promises to be an invaluable, informative and fun event that you won't want to miss! We look forward to seeing you — and don't forget to wear your maroon and gold.</p>";
          $output .= "<p style='margin-bottom:10px'>If you have any questions about this event or next steps to becoming a Sun Devil, please reach out to your <a href='https://admission.asu.edu/contact/undergraduate'>personal admission representative</a>.</p>";
          
  //$output .="<p><a href='$cancel_link'>Cancel registration</a></p>";
          
          $output .= "<p style='margin-bottom:10px'>Sincerely,</p><p style='margin-bottom:10px'>April Crabtree<br />Associate Vice President<br />Enrollment and Admission Services</p>";
          break;

        case "Yield Events":
         
          if($domestic_international == 'International') {
            $subject_line = "Road to ASU confirmation";
//            $banner = "<img src='https://visit.asu.edu/sites/default/files/2025-01/email_EVENT_74067_RoadtoASUBanner_700x394.png' alt='Road to ASU' />";
            $banner_img_path = "https://visit.asu.edu/sites/default/files/2025-01/email_EVENT_74067_RoadtoASUBanner_700x394.png";
            $banner_img_alt = 'Road to ASU';
            
            // Get country which is in "Event location for grouping" text field
            $country = $node->get('field_event_location_for_groupin')->getValue()[0]['value'];            
            $output .= "<p style='margin-bottom:10px'>Thank you for registering for a Road to ASU event with Arizona State University. We’re looking forward to meeting you in person!</p>";
            $output .= "<p style='margin-bottom:10px'>Please plan to arrive promptly at " . $checkin_time_v . " for check-in. After check-in, we will begin with a brief presentation that will provide an overview of key ASU information. Following the presentation, there will be dedicated time for networking to connect with other new students and families.</p>";
//            $output .= "<p style='margin-bottom:10px'><strong>" . $event_date_string ."</strong><br />";
//            $output .= "Registration begins at " . $checkin_time_v . ". "; //<---****Check-in time
//            $output .= "The formal presentation begins at " . $start_time . "<br />";
            $output .= $location;
            if (!empty($parking)) {
              $output .= "<p style='margin-bottom:10px'>" . $parking . "</p>";
            }
            
            if($country == "India" || $country == "Bangladesh") {
              $output .= "<p style='margin-bottom:10px'><strong>Questions?</strong></p>";
              $output .= "<p style='margin-bottom:10px'>Email <strong>Roshan Lalan</strong> at <a href='mailto:gograd@asu.edu'>gograd@asu.edu</a> or <strong>Tabita Chettri</strong> at <a href='mailto:asuinternational@asu.edu'>asuinternational@asu.edu</a> or contact +91 92059 92791 for any additional questions. Please note that seats are available on a first-come, first-serve basis and additional guests may have standing room only due to limited space at the venue.</p>";
//              $output .= "<p style='margin-bottom:10px'>Sincerely,</p><img src='/sites/default/files/2025-02/Niky Chokshi headshot picture_150px_0.png' width='150'><p style='margin-bottom:10px'>Niky Chokshi<br />Senior Executive Liaison, ASU<br /><a href='mailto:ASUnikychokshi@asu.edu'>ASUnikychokshi@asu.edu</a></p><p>&nbsp;</p>";
              $output .= "<p style='margin-bottom:10px'>Sincerely,</p><img src='/sites/default/files/2025-02/Niky%20Chokshi%20headshot%20picture_150px_0.png' width='150'><p style='margin-bottom:10px'>Niky Chokshi<br />Senior Executive Liaison, ASU<br /><a href='mailto:ASUnikychokshi@asu.edu'>ASUnikychokshi@asu.edu</a></p><p>&nbsp;</p>";
            }
            if($country == "Mexico") {
              $output .= "<p style='margin-bottom:10px'>If you have any event questions or need to cancel your registration for this event, send an email to <a href='mailto:ASUlilianamorachis@asu.edu'>ASUlilianamorachis@asu.edu</a>.</p>";
              $output .= "<p style='margin-bottom:10px'>Sincerely,</p><img src='/sites/default/files/2025-02/Liliana%20Morachis%20headshot%20picture.png' width='150'><p style='margin-bottom:10px'>Liliana Morachis<br />Associate Director, International Recruitment Initiatives<br />Enrollment and Admission Services</p><p>&nbsp;</p>";
            }
            if($country == "Vietnam") {
              $output .= '<p style="margin-bottom:10px"><strong>Questions?</strong></p>';
              $output .= "<p style='margin-bottom:10px'>Email <strong>Giao Vo</strong> at ASU <a href='mailto:giao.vo@asu.edu'>giao.vo@asu.edu</a> or contact +84 90 290 5776 for any additional questions. Please note that seats are available on a first-come, first-served basis and additional guests may have standing room only due to limited space at the venue.</p>";
              $output .= "<p style='margin-bottom:10px'>We look forward to meeting you in-person soon.</p>";
              $output .= "<p style='margin-bottom:10px'><img src='https://visit.asu.edu/sites/g/files/litvpz151/files/2026-03/GiaoVo_GoldCircle.png' width='150'></p><p style='margin-bottom:10px'><strong>Giao Vo</strong><br />Coordinator<br />Admission Services</p><p>&nbsp;</p>";
            }
            if($country == "China") {
              $output .= '<p style="margin-bottom:10px"><strong>Questions?</strong></p>';
              $output .= '<p style="margin-bottom:10px">Email <strong>me or add me on WeChat</strong> for any additional questions. Please note that seats are available on a first-come, first-served basis and additional guests may have standing room only due to limited space at the venue.</p>';
              $output .= '<p style="margin-bottom:10px">Contact me on WeChat 微信 at cc7420. Please be sure to include your ASU ID when adding WeChat. 添加好友时，请备注ASU ID:</p>';
              $output .= '<p style="margin-bottom:10px"><img src="https://visit.asu.edu/sites/g/files/litvpz151/files/2026-03/ChenChen_WeChatQR-code2_150px.jpg" alt="WeChat QR code for Chen Chen" /></p>';
              $output .= '<p style="margin-bottom:10px">Email Chen Chen (陈晨) at asuinternational@asu.edu.</p>';
              $output .= '<p style="margin-bottom:10px">We look forward to meeting you in-person soon.</p><img src="https://visit.asu.edu/sites/g/files/litvpz151/files/2026-03/INTL%20Recruiter%20Chen%20Chen.png" width="150"><p style="margin-bottom:10px"><strong>Chen Chen</strong><br />Coordinator<br />Admission Services</p><p>&nbsp;</p>';
              
//              <a class="mso-link" href="https://admission.asu.edu/findmyrep" target="_blank" style="text-decoration: none;"><span class="linkHover" style="color:#8C1D40; border-bottom: 1px dotted #8c1d40;font-weight: bold;"><span class="darkModeA">personal admission team member</span></span></a>

            }

          } else if ($domestic_international == 'college_signing_days') {
            $subject_line = "You’re registered for a Sun Devil Signing Day";
//            $banner = "<img src='https://visit.asu.edu/sites/default/files/2025-03/FutureSunDevils2024-WebBanner_700px.jpg' alt='College Signing Days' />";
            $banner_img_path = "https://visit.asu.edu/sites/default/files/2025-03/FutureSunDevils2024-WebBanner_700px.jpg";
            $banner_img_alt = 'College Signing Days';
            
            $campus = $node->get('field_event_location_for_groupin')->getValue()[0]['value'];
            
            $output .= "<p style='margin-bottom:10px'>Thank you for registering for a Sun Devil Signing Day at " . $campus . " campus. We are excited to welcome you and your family to Arizona State University.</p>";
            
            $output .= "<p style='margin-bottom:10px'>This event brings together incoming students and their families as they become part of the Sun Devil community. In addition to being a great opportunity to meet fellow students who will be living and learning with you this fall, you'll have the opportunity to tell the world you're going to Arizona State University!</p>";
            
            $output .= "<p style='margin-bottom:10px'>You’ll also have the chance to connect with university representatives, current students and ASU families to get your questions answered before moving to campus. We hope you and your family find this event helpful and memorable as you prepare for your ASU experience.</p>";

            $output .= $location;
            
            $output .= '<p style="margin-bottom:10px">If you have any event questions, need ADA accommodations, have dietary restrictions or need to cancel your registration for this event, send an email to <a class="mso-link" href="mailto:visitASU@asu.edu" target="_blank" style="text-decoration: none;"><span class="linkHover" style="color:#8C1D40; border-bottom: 1px dotted #8c1d40;font-weight: bold;"><span class="darkModeA">visitASU@asu.edu</span></span></a>. If you have any questions about your next steps to becoming a Sun Devil, please reach out to your <a class="mso-link" href="https://admission.asu.edu/findmyrep" target="_blank" style="text-decoration: none;"><span class="linkHover" style="color:#8C1D40; border-bottom: 1px dotted #8c1d40;font-weight: bold;"><span class="darkModeA">personal admission team member</span></span></a>.</p>';
            
            $output .= "<p style='margin-bottom:10px'>Sincerely,</p><!--<img src='/sites/default/files/2025-01/Kelsey_Singleton_headshot.png' width='150'>--><p style='margin-bottom:10px'>April Crabtree<br />Associate Vice President<br />Enrollment and Admission Services<br /></p>";
            $output .= "<!--<img src='/sites/default/files/2025-01/Kelsey_Singleton_headshot.png' width='150'>--><p style='margin-bottom:10px'>Jennifer Velez<br />Director, Arizona First-Year Recruitment and School Relations<br />Admission Services<br /></p><p>&nbsp;</p>";

            
          } else { // Domestic
            $subject_line = "Road to ASU confirmation";
            $banner_img_path = "https://visit.asu.edu/sites/default/files/2025-01/email_EVENT_74067_RoadtoASUBanner_700x394.png";
            $banner_img_alt = 'Road to ASU';
            
            $output .= "<p style='margin-bottom:10px'>Thank you for registering for a Road to ASU event with Arizona State University. We’re looking forward to meeting you in person!</p>";
            $output .= "<p style='margin-bottom:10px'>Please plan to arrive promptly at " . $start_time . " for check-in. After check-in, we will begin with a brief presentation that will provide an overview of key ASU information. Following the presentation, there will be dedicated time for networking to connect with other new students and families. Appetizers will be provided.</p>";
            $output .= "<p style='margin-bottom:10px'><strong>" . $event_date_string ."</strong><br />";
            $output .= "Registration begins at " . $checkin_time_v . ". "; //<---****Check-in time
            $output .= "The formal presentation begins at " . $start_time . "<br />";
            $output .= $location;
            if (!empty($parking)) {
              $output .= "<p style='margin-bottom:10px'>" . $parking . "</p>";
            }
            $output .= "<p style='margin-bottom:10px'>If you have any event questions, need ADA accommodations or need to cancel your registration for this event, send an email to <a href='mailto:visitASU@asu.edu'>visitASU@asu.edu</a>.</p>";
            $output .= "<p style='margin-bottom:10px'>Sincerely,</p><img src='/sites/default/files/2025-01/Kelsey_Singleton_headshot.png' width='150'><p style='margin-bottom:10px'>Kelsey Singleton<br />Director, Non-Resident Recruitment Initiatives<br /> Admission Services<br /> </p><p>&nbsp;</p>";
          }
          
          break;
      }
      
//      $email_body = $this->applyEmailTemplate($output, $banner, $first_name); // Updated to the new email template on 5/13/2025.
      $email_body = $this->applyEmailTemplate($output, $banner_img_path, $banner_img_alt, $first_name);
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'asu_resend_email';
      $key = 'send_confirmation_email';
      $to = $email;
      $subject = $subject_line;
      $from = \Drupal::config('system.site')->get('mail'); 
      $params['message'] = \Drupal\Core\Render\Markup::create($email_body);
      $params['subject'] = $subject;
      $params['from'] = $from;
      $params['reply-to'] = $from;
      //ksm($params);
      //ksm($to);
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      
    } // END OF if($nodeid == "sun_devil_send_off"){
	} // END OF public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission)
  
  
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
  } // END OF public function applyEmailTemplate($main_content, $banner, $first_name)  
}