<?php

namespace Drupal\asuaec_rfib2_directpost\Plugin\WebformHandler;

use Drupal\asuaec_rfib2_directpost\Controller\WebformConfirmationPage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;


/**
 * Form submission handler
 *
 * Source ID is set at line 667.
 *
 * @WebformHandler(
 *   id = "rfib2_directpost_webform_handler",
 *   label = @Translation("Post to middleware B2 direct post"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to Middleware B2 direct post"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class RfiWebformHandler extends WebformHandlerBase {
    public static $x = 0; // Prevent posting twice. Added on 5/27/2022.

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     *  Check to see if session variable exists. It could be already existed. If not existed, create one.
     *
     *  Send confirmation email.
     *  **Online - We don't send confirmation email.
     */
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {

        // Check environment
        $domain = 'https://' . $_SERVER['HTTP_HOST'];
        $env = 'prod';
        if($domain == 'https://live-admission-asu.ws.asu.edu' || $domain == 'https://admission.asu.edu' || $domain == 'https://admissions.asu.edu') {
            $env = 'prod';
        } else {
            $env = 'dev';
        }

        // Check to see if session variable exists. It could be already existed. If not existed, create one.

        $sid = $webform_submission->id();
        $grad_ugrad = $webform_submission->getData()['grad_ugrad'];
        $plancode = $webform_submission->getData()['program_of_interest_text'];
        $interest = $webform_submission->getData()['interest1'];

        // Make sure if Session has the new $degree_data_array info.
        $session = \Drupal::request()->getSession();
        $degree_data_array = $session->get('asuaec_rfi.degree_data_array');
        if(is_null($degree_data_array) || is_null($degree_data_array['sid']) || ($degree_data_array['sid'] == '') || ($degree_data_array['sid'] != $sid)) {
            // Refresh session variable
            $request = \Drupal::request();
            $session = $request->getSession();
            $session->remove('asuaec_rfi.degree_data_array');

            $theWebformConfirmationPage = new WebformConfirmationPage;
            if($plancode != null) {
                $degree_data_array = $theWebformConfirmationPage->getDegreeData($plancode, $grad_ugrad, $sid);
            } else if ($interest != null) {
                $degree_data_array = $theWebformConfirmationPage->getInterestData($interest, $grad_ugrad, $sid);
            }
        }
        // Session variable $degree_data_array is ready to use.


        // Send confirmation email.

        $campus_option = $webform_submission->getData()['campus_options'];
        if($campus_option != 'ONLNE') {

            $mail_parts = $this->buildConfirmationEmailBody($webform_submission);

            $mailManager = \Drupal::service('plugin.manager.mail');
            $module = 'asuaec_rfib2_directpost';
            $key = 'rfi_conf_email';
            $to = $webform_submission->getData()['email_address'];

            $params['message'] = $mail_parts['body'];
            $params['subject'] = $mail_parts['subject'];

            $langcode = \Drupal::currentUser()->getPreferredLangcode();
            $send = true;
            $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

            if ($result['result'] !== true) {
                $the_message = new TranslatableMarkup('There was a problem sending your message and it was not sent.', array());
                $this->messenger()->addMessage($the_message);
                \Drupal::logger('asuaec_rfib2_directpost')->notice('Main RFI - Email error: Confirmation email was not sent. - Email address:' . $to);
            } else {
                if($env == "dev") {
                    $the_message = new TranslatableMarkup('Your confirmation email has been sent.', array());
                    $this->messenger()->addMessage($the_message);
                }
            }
        }

        // Clear session variable: 'asuaec_rfi.degree_data_array
        $request = \Drupal::request();
        $session = $request->getSession();
        $session->remove('asuaec_rfi.degree_data_array');

    } // END OF public function postSave()

    /**
     * Build confirmation email content.
     */
    public function buildConfirmationEmailBody($webform_submission) {
        // Get Ground or online
        $student_type = $webform_submission->getData()['student_type_options_default']; // First Time Freshman, Transfer or Readmission

        // Get Ugrad or Grad
        $grad_ugrad = $webform_submission->getData()['grad_ugrad'];

        // Get Ground or Online
        $campus_option = $webform_submission->getData()['campus_options'];

        // Came from Degree search -- It user came from Degree search, it will be always GROUND.
        $came_from_degree_search = $webform_submission->getData()['came_from_degree_search'];
        if($came_from_degree_search == 'true') {
            $campus_option = "GROUND";
        }
        $session = \Drupal::request()->getSession();
        $degree_data_array = $session->get('asuaec_rfi.degree_data_array');
//        \Drupal::logger('asuaec_rfi')->notice("postSave - details array: " . print_r($degree_data_array, true));
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager');
        $mail_parts = [];
        $body = '';
        $new_output = '';
        $domain = 'https://' . $_SERVER['HTTP_HOST'];
        $headerimg_href = 'https://admission.asu.edu/';
        $fname = $webform_submission->getData()['first_name'];
        if($domain == 'https://live-admission-asu.ws.asu.edu' || $domain == 'https://admission.asu.edu' || $domain == 'https://admissions.asu.edu') {
            $env = 'prod';
        } else {
            $env = 'dev';
        }

        $email_banner_image = '';
        $mail_parts['subject'] = '';
        $preheader_text = '';

        // On Campus Grad
        if(($campus_option == 'GROUND' && $grad_ugrad == 'GRAD') || ($campus_option == 'NOPREF' && $grad_ugrad == 'GRAD')) {
            $uri = 'public://2024-06/grad_confirmation_600px.jpg';
            if ($stream_wrapper_manager->isValidUri($uri)) {
              $email_banner_image = $stream_wrapper_manager->getViaUri($uri)->getExternalUrl();
            } else {
              $email_banner_image = t( $domain . '/sites/g/files/' . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] . '/files/2024-06/grad_confirmation_600px.jpg');
            }
            $mail_parts['subject'] = t('Thanks for your interest in ASU');
            $preheader_text = t('Learn more about ASU’s graduate programs.');
            $headerimg_href = 'https://admission.asu.edu/';

//            $military_veteran = isset($webform_submission->getData()['veteran_status_options']) ? $webform_submission->getData()['veteran_status_options'] : '';

//            $degree_level = $submitted_data[98]; // 15, 17, 21, cert or nd
//            $nd_college_url = '';
            $plan_url = '';
//            $degree_descr_short = '';
//            $campuses_text = '';
//            $online = '';
//            if($degree_level == 'nd') { //<----There is no 'nd' option.
//                $college_name = isset($degree_data_array['programDesrc100']) ? $degree_data_array['programDesrc100'] : '';
//                $nd_college_url = isset($degree_data_array['nd_college_url']) ? $degree_data_array['nd_college_url'] : '';
//            } else {
                $plan_url = isset($degree_data_array['plan_url']) ? $degree_data_array['plan_url'] : '';
//                $degree_descr_short = isset($degree_data_array['degree_descr_short']) ? $degree_data_array['degree_descr_short'] : '';
//                $campuses_text = isset($degree_data_array['campuses_text']) ? $degree_data_array['campuses_text'] : '';
//                $online = isset($degree_data_array['online']) ? $degree_data_array['online'] : '';
                //if($degree_level == 'cert') {
                $degree_descr100 = isset($degree_data_array['descr100']) ? $degree_data_array['descr100'] : '';
                //}
//            } // END OF else


            //////////////////////////////////////////////////////
            // Email body
            /////////////////////////////////////////////////////

            $new_output .= '<p style="margin-bottom:10px">Thank you for your interest in pursuing an advanced degree at <a style="color:#8C1D40;" href="https://www.asu.edu">Arizona State University</a>. ASU consistently <a style="color:#8C1D40;" href="https://www.asu.edu/rankings">ranks high</a> on multiple college rankings lists and is repeatedly named No. 1 in rankings that matter, such as sustainability, innovation and global impact. From engaging in meaningful, innovative research and entrepreneurial projects to collaborating with classmates and faculty, you will be elevating your career when you enroll in graduate studies at ASU.</p>';
            $new_output .= '<p style="margin-bottom:10px">With <a style="color:#8C1D40;" href="https://webapp4.asu.edu/programs/t5/graduate/false">more than 450 excellent graduate degree programs and certificates</a>, including new programs in emerging fields, there is a degree path here to fit your career goals.</p>';
            $new_output .= '<p style="margin-bottom:10px">Explore your area of interest: ';
//            if($degree_level == 'nd') {
//                $new_output .= '<a style="color:#8C1D40;" href="' . $nd_college_url . '" >' . $college_name . '</a>';
//            } else if($degree_level == 'cert') {
//                $new_output .= '<a style="color:#8C1D40;" href="' . $plan_url . '" >' . $degree_descr100 . ', ' . $degree_descr_short . '</a>';
//                //} else if($degree_level == '15' || $degree_level == '17' || $degree_level == '21' ) {
//            } else {
                //$new_output .= '<a style="color:#8C1D40;" href="' . $plan_url . '" >' . $degree_descr_formal . ', ' . $degree_descr_short . '</a>';
                $new_output .= '<a style="color:#8C1D40;" href="' . $plan_url . '" >' . $degree_descr100 . '</a>';
//            }
            $new_output .= '. Take note of each degree’s requirements and continue to plan your next steps to ASU.</p>';
            $new_output .= '<p style="margin-bottom:10px">If you have any questions about a specific degree program, please <a style="color:#8C1D40;" href="mailto:gograd@asu.edu">contact us</a> or the academic department of the program you’re interested in. We are all happy to help you take the next step toward your graduate degree.</p>';
            $new_output .= '<p style="margin-bottom:10px">And when you’re ready, apply to ASU.</p>';
            $new_output .= '<span style="margin-bottom:10px;display:inline-block;background-color:#FFC627;border-radius:24px;font-weight:bold;padding:15px;"><a style="color:#000000;text-decoration:none;" href="https://webapp4.asu.edu/dgsadmissions/?_ga=2.168399073.1511473274.1613495072-1116271701.1613495072">Apply to ASU</a></span>';
            $new_output .= '<p style="margin-bottom:10px">Gold is going to look good on you. I hope to see you here soon.</p>';
            $new_output .= '<p style="margin-bottom:10px">Sincerely,</p>';
            $new_output .= '<p style="margin-bottom:10px">Matthew López<br />Associate Vice President, Academic Enterprise Enrollment<br />Executive Director, Admission Services<br />Arizona State University<br /><a style="color:#8C1D40;" href="https://admission.asu.edu/graduate">admission.asu.edu/graduate</a></p>';


        } // END OF if($campus_option == 'GROUND' && $grad_ugrad == 'GRAD') {


        // On Campus Ugrad
        if(($campus_option == 'GROUND' && $grad_ugrad == 'UGRAD') || ($campus_option == 'NOPREF' && $grad_ugrad == 'UGRAD')) {
            $headerimg_href = 'https://admission.asu.edu/';
            $mail_parts['subject'] = t('Gold is going to look good on you');
            $preheader_text = t('Gold is going to look good on you.');
            $uri = 'public://2024-06/email_txtphb_goldisgoingtolookgoodonyou.jpg';
            if ($stream_wrapper_manager->isValidUri($uri)) {
              $email_banner_image = $stream_wrapper_manager->getViaUri($uri)->getExternalUrl();
            } else {
              $email_banner_image = t( $domain . '/sites/g/files/' . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] . '/files/2024-06/email_txtphb_goldisgoingtolookgoodonyou.jpg');
            }

            // International
            $country_of_citizenship = $country_of_citizenship = $webform_submission->getData()['citizenship_country'];
            if($country_of_citizenship != 'US') {
                $international = 'INT';
            } else {
                $international = 'USA';
            }

            /////////////////////////////////////
            // If it came from the Degree Search --> If there is plan code or not.
            /////////////////////////////////////
            $plan_code = isset($webform_submission->getData()['program_of_interest_text']) ? $webform_submission->getData()['program_of_interest_text'] : '';
            if($plan_code != '') {
                $plan_name = $degree_data_array['descr100'];
                $url = $degree_data_array['plan_url'];
                $degree = isset($degree_data_array['degree_descr_short']) ? $degree_data_array['degree_descr_short'] : ''; // BS
                $plan_link = '<a style="color:#8C1D40;" href="' . $url . '">' . $plan_name . ', ' . $degree . '</a>';
                $link = $plan_link;
            }

            ///////////////////////////////////////////////////
            // When the form doesn't have any URL parameters
            // (When user didn't come from the Degree Search)
            ///////////////////////////////////////////////////
            else {
                $link = isset($degree_data_array['interest_linked']) ? $degree_data_array['interest_linked'] : '';

            } // END of else: When the form doesn't have any URL parameters

            //////////////////////////////////////////////////////
            // Change Ending signature depending on student type (Transfer or Freshman)
            /////////////////////////////////////////////////////

            $new_output = '';
            $new_output .= '<p style="margin-bottom:10px">Thank you for your interest in pursuing a bachelor\'s degree at <a style="color:#8C1D40;" href="https://www.asu.edu">Arizona State University</a>. ASU consistently <a style="color:#8C1D40;" href="https://www.asu.edu/rankings">ranks high</a> on multiple college rankings lists and is repeatedly named No. 1 in rankings that matter, such as sustainability, innovation and global impact. With one of the largest populations of students in the U.S., ASU is an institution that measures itself by whom it includes, not by whom it excludes. ASU does its best to understand the needs of our students, and offers the support, resources, tools and opportunities necessary for success in academics, career development and life.</p>';
            $new_output .= '<p style="margin-bottom:10px;">ASU is often recognized for its outstanding value: a high-quality education, strong student support services and helpful career resources, all at an affordable tuition rate with competitive financial aid packages. And with <a style="color:#8C1D40;" href="https://webapp4.asu.edu/programs/t5">more than 400</a> excellent undergraduate degree programs and certificates offered — including new programs in emerging fields — you&#39;ll find a degree path to fit your career goals.</p>';
            $new_output .= '<p style="margin-bottom:10px;"><strong style="background-color:#FFC627;">Learn to thrive</strong><br />';
            $new_output .= 'Discover how students at ASU are learning to thrive.</p>';
            $new_output .= '<a href="https://yourfuture.asu.edu/thriving"><img src="https://admission.asu.edu/sites/default/files/learn_to_thrive_at_asu.png" alt="Learn to thrive at ASU"></a>';
            $new_output .= '<p style="margin-bottom:10px;font-size:1em;"><strong>Next steps</strong></p>';
            $new_output .= '<table><tr><td><img src="https://admission.asu.edu/sites/default/files/explore.png" alt="explore icon" alt="explore icon"></td><td>Take the time to explore your area of interest: ' . $link . '. Explore the requirements of the degrees offered and continue to plan your transition to ASU.</td></tr></table>';
            $new_output .= '<table><tr><td>Not sure what bachelor\'s degree matches your interests and passions? Take one of ASU\'s <a style="color:#8C1D40;" href="https://webapp4.asu.edu/programs/t5">quizzes</a> to see what degree could be the best fit for you.</td><td><img src="https://admission.asu.edu/sites/default/files/quizzes.png" alt="quizzes icon"></td></tr></table>';
            $new_output .= '<table><tr><td><img src="https://admission.asu.edu/sites/default/files/campuses.png" alt="campuses icon"></td><td>Each of ASU\'s campuses has a distinct identity, and together they comprise a university ranked highly for undergraduate education. <a style="color:#8C1D40;" href="https://tours.asu.edu">Explore ASU\'s campuses</a> through virtual tours, and get a taste of what it\'s like to live like a Sun Devil.</td></tr></table>';
            $new_output .= '<table><tr><td><a style="color:#8C1D40;" href="https://admission.asu.edu/contact">Contact an ASU admission representative</a> who can guide you through each step of the admission process.</td><td><img src="https://admission.asu.edu/sites/default/files/contact.png" alt="contact icon"></td></tr></table>';
            $new_output .= '<p style="margin-bottom:10px;">And when you’re ready, apply to ASU.</p>';
//            $new_output .= '<p style="margin-bottom:10px;"><strong style="background-color:#FFC627; display:flex; margin-right:auto; margin-left:auto; width:104px;">Apply to ASU</strong></p>';
//            $new_output .= '<p style="margin-top:0; display:flex; margin-right:auto; margin-left:auto; width:230px;"><a style="color:#8C1D40;" href="https://admission.asu.edu/apply">https://admission.asu.edu/apply</a></p>';
            $new_output .= '<span style="margin-bottom:10px;display:inline-block;background-color:#FFC627;border-radius:24px;font-weight:bold;padding:15px;"><a style="color:#000000;text-decoration:none;" href="https://admission.asu.edu/apply">Apply to ASU</a></span>';
            $new_output .= '<p style="margin-bottom:10px"><strong>Gold is going to look good on you.</strong> I hope to see you here soon.</p>';
            $new_output .= '<p style="margin-bottom:10px">Sincerely,</p>';

            if($international == 'INT') {
                $new_output .= '<p style="margin-bottom:10px">Matthew López<br />Associate Vice President, Academic Enterprise Enrollment<br />Executive Director, Admission Services<br />Arizona State University<br /><a style="color:#8C1D40;" href="https://admission.asu.edu/international">admission.asu.edu/international</a></p>';
                $headerimg_href = 'https://admission.asu.edu/international';

            } else { // USA

                if($student_type == 'Transfer'){ //transfer student
                    $new_output .= '<p style="margin-bottom:10px">Matthew López<br />Associate Vice President, Academic Enterprise Enrollment<br />Executive Director, Admission Services<br />Arizona State University<br /><a href="https://admission.asu.edu/transfer" style="color:#8C1D40;">admission.asu.edu/transfer</a></p>';
                    $headerimg_href = 'https://admission.asu.edu/transfer';

                } else if ($student_type == 'First Time Freshman') { // Freshman
                    $new_output .= '<p style="margin-bottom:10px;">Brad Baertsch <br />Senior Director, First-Year Domestic and International Recruitment <br />Admission Services <br /><a href="https://admission.asu.edu/freshman" style="color:#8C1D40;">admission.asu.edu/freshman</a></p>';
                    $headerimg_href = 'https://admission.asu.edu/freshman';
                }
            }

        } // END OF if($submitted_data[74] == 'On Campus' && $submitted_data[70] != 'grad') {   --> In other words, On Campus Ugrad

        $uri = 'public://2024-06/asu_logo_230px.png';
        if ($stream_wrapper_manager->isValidUri($uri)) {
          $asuLogo = $stream_wrapper_manager->getViaUri($uri)->getExternalUrl();
        } else {
          $asuLogo = t( $domain . '/sites/g/files/' . $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] . '/files/2024-06/asu_logo_230px.png');
        }

        $body = <<<EOD
<head>
<meta name='robots' content='noindex, nofollow'><meta name='referrer' content='no-referrer'>
<!--[if gte mso 9]><xml>      <o:OfficeDocumentSettings>       <o:AllowPNG/>       <o:PixelsPerInch>96</o:PixelsPerInch>      </o:OfficeDocumentSettings>     </xml><![endif]-->
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width'>
<meta http-equiv='X-UA-Compatible' content='IE=9; IE=8; IE=7; IE=EDGE'>
<title>Arizona State University</title>
<style id='media-query'>
v\:* { behavior: url(#default#VML); display:inline-block}
/* Client-specific Styles & Reset */
#outlook a {padding: 0;}
a[x-apple-data-detectors] {color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important;}
/* .ExternalClass applies to Outlook.com (the artist formerly known as Hotmail) */
.ExternalClass {width: 100%;}
.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
#backgroundTable {margin: 0; padding: 0; width: 100% !important; line-height: 100% !important;}
/* Buttons */
.button a {display: inline-block; text-decoration: none; -webkit-text-size-adjust: none;
text-align: center;}
.button a div {text-align: center !important;}
/* Outlook First */
body.outlook p {display: inline !important;}
/*  Media Queries */
@media only screen and (max-width: 500px) {
table[class='body'] img.fullwidth {max-width: 100% !important; }
table[class='body'] center {min-width: 0 !important;}
table[class='body'] .container {width: 95% !important;}
table[class='body'] .row {width: 100% !important; display: block !important;}
table[class='body'] .wrapper {display: block !important; padding-right: 0 !important; }
table[class='body'] .columns, table[class='body'] .column {table-layout: fixed !important; float: none !important; width: 100% !important; padding-right: 0px !important; padding-left: 0px !important; display: block !important; }
table[class='body'] .wrapper.first .columns, table[class='body'] .wrapper.first .column { display: table !important; }
table[class='body'] table.columns td, table[class='body'] table.column td, .col {width: 100% !important; }
table[class='body'] table.columns td.expander {width: 1px !important; }
table[class='body'] .right-text-pad, table[class='body'] .text-pad-right {padding-left: 10px !important; }
table[class='body'] .left-text-pad, table[class='body'] .text-pad-left {padding-right: 10px !important; }
table[class='body'] .hide-for-small, table[class='body'] .show-for-desktop {display: none !important; }
table[class='body'] .show-for-small, table[class='body'] .hide-for-desktop {display: inherit !important; }
*[class=nomobile]{display:none !important;}
*[class=mobilefullwidth]{ width:100% !important; height: auto !important; }
}
@media screen and (max-width: 700px) {
div[class='col'] {width: 100% !important;}
.mobilefont {font-size:14px !important;}
.mobilefontheader {font-size:16px !important;}
.mobilefonthero {font-size:48px !important; line-height: 48px !important; padding: 24px 0px 14px 0px !important;}
.mobilebg {background-color: #FFFFFF !important; background-color: #FFFFFF !important; border: none !important;}
  .fullwidth {width: 100% !important;}
}
@media screen and (min-width: 701px) {
table[class='container'] {width: 700px !important;}
}
/* Button and link hover styles */
.buttonHover{transition:.05s}.buttonHover:hover{background:#ffd35a !important;transition:.05s}
.linkHover{color:#8c1d40 !important;transition:.5s !important}.linkHover:hover{color:#cb2a5d !important;transition:.5s !important;}
div.preheader{ display: none !important; }
</style>
<!--[if (gte mso 9)|(IE)]> <style> .mso-link {text-decoration: underline !important; display: inline-block !important;}  </style> <![endif]-->
</head>
<body class='mobilebg' style='width: 100% !important;min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100% !important;margin: 0;padding: 0; background-color: #F1F1F1;'>
<!--[if mso]> <style type='text/css'> body, table, td, p, div, a {font-family: Arial, sans-serif !important;} .mso-link {border-bottom: 1px solid #8c1d40 !important; display: inline-block;}  </style> <![endif]-->
<!--Begin Email-->
<table role='presentation' class='body mobilebg' style='border-spacing: 0;border-collapse: collapse;vertical-align: top;height: 100%;width: 100%;table-layout: fixed' cellpadding='0' cellspacing='0' width='100%' border='0'>
<div width='100%' align='center'>
<!--End Wrap-->
<!-- Insert &zwnj;&nbsp; hack after hidden preview text -->
<div style='display: none; max-height: 0px; overflow: hidden;'>
&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
</div>

<table role='presentation' style='border-spacing: 0; border-collapse: collapse; vertical-align: top;' cellpadding='0' cellspacing='0' align='center' width='100%' border='0'>
<tbody>

  <tr><td><custom name='opencounter' type='tracking'></td></tr>
  <tr><td width='100%' height='16px'>&shy;</td></tr>

<tr>
<td style='word-break: break-word; border-collapse: collapse !important; vertical-align: top' width='100%'>
<!--[if (gte mso 9)|(IE)]> <table id='outlookholder' border='0' cellspacing='0' cellpadding='0' align='center'><tr><td> <table width='700' align='center' cellpadding='0' cellspacing='0' border='0'> <tr> <td> <![endif]-->
<!--Header-->
<!--Grey Wrapper-->

<table role='presentation' style='border-spacing: 0; border-collapse: collapse; vertical-align: top; max-width: 700px; margin: 0 auto; background-color: #f1f1f1;' cellpadding='0' cellspacing='0' align='center' width='100%' border='0'>
<tbody>

<tr>
<td style='word-break: break-word;border-collapse: collapse !important; vertical-align: top;' width='100%'>

<!--Header-->
<tr>
<td id='asu_header' align='center' valign='top'>

<table cellpadding='0' cellspacing='0' width='100%' style='min-width: 100%; ' class='stylingblock-content-wrapper'><tr><td class='stylingblock-content-wrapper camarker-inner'><table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' align='center' style='padding: 0px;' bgcolor='#ffffff'>
  <tr>
    <td class='mobile-bump' style='border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 24px 24px 24px 24px; line-height: 24px; font-weight: 300;'>
      <a name='ASU Logo Link' href='https://asu.edu' target='_blank'>
        <img src='{$asuLogo}' width='230' height='auto' alt='Arizona State University' style='border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: Arial, sans-serif; padding: 0px; line-height: 24px; font-weight: bold;'>
      </a>
    </td>
  </tr>
</table></td></tr></table>
</td>
</tr>

<!--Start Content-->
<tr>
<td id='asu_header' align='center' valign='top'>
<table cellpadding='0' cellspacing='0' width='100%' style='min-width: 100%; ' class='stylingblock-content-wrapper'><tr><td class='stylingblock-content-wrapper camarker-inner'><table width='100%' cellspacing='0' cellpadding='0'>
  <tr>
    <td align='center'><a href='{$headerimg_href}' title='' conversion='false'><img src='{$email_banner_image}' alt='{$preheader_text}' width='700' style='display: block; padding: 0px; text-align: center; height: auto; width: 100%; border: 0px;'></a>
    </td>
  </tr>
</table></td></tr></table>

</td>
</tr>

<tr>
<td id='asu_header' align='center' valign='top'>
<table cellpadding='0' cellspacing='0' width='100%' style='min-width: 100%; ' class='stylingblock-content-wrapper'><tr><td class='stylingblock-content-wrapper camarker-inner'><table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' align='center' style='background-color: #ffffff; padding: 0px;'>
  <tr>
    <td class='mobile-bump' style='border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 24px 24px 24px 24px; mso-line-height-rule:exactly; line-height: 24px; font-weight: bold;'>
    {$fname},
    </td>
  </tr>
</table></td></tr></table>
</td>
</tr>

<tr>
<td id='asu_header' align='center' valign='top'>
<table cellpadding='0' cellspacing='0' width='100%' style='min-width: 100%; ' class='stylingblock-content-wrapper'><tr><td class='stylingblock-content-wrapper camarker-inner'><table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' align='left' style='background-color: #ffffff; padding: 0px;'>
  <tr>
    <td class='mobile-bump' style='border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 0px 24px 24px 24px; line-height: 24px; font-weight: 300;'>
      {$new_output}
    </td>
  </tr>
</table></td></tr></table>
</td>
</tr>
<!--End Content-->

<!--Social Media-->
<tr>
<td id='asu_header' align='center' valign='top'>
<table cellpadding='0' cellspacing='0' width='100%' style='min-width: 100%; ' class='stylingblock-content-wrapper'><tr><td class='stylingblock-content-wrapper camarker-inner'><table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0' align='center' style='background-color: #ffffff; padding: 0px;'>
  <tr>
    <td class='mobile-bump' style='border-collapse: collapse; text-align: center; font-size:16px; color:#8c1d40; font-family: Arial, sans-serif; padding: 24px 24px 24px 24px; mso-line-height-rule:exactly; line-height: 24px; font-weight: bold;'>
    Learn to thrive. ASU &mdash; #1 in the U.S. for innovation.
    </td>
  </tr>
</table></td></tr></table>
</td>
</tr>

<!--Start Content/Legal-->
<tr>
<td id='asu_header' align='center' valign='top'>

</td>
</tr>

</td></tr>
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
EOD;

        if ($env == 'dev' && \Drupal::VERSION < 11) {
          \Drupal::logger('asuaec_rfib2_directpost')->notice('Email body: ' . $body);
        }
        $mail_parts['body'] = $body;

        return $mail_parts;
    }


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

    /**
     * Helping function
     *
     * Validate 2 of 2 page
     */
    public function validate2ndpage($campus_options, $form_state, $email_address, $first_name, $last_name, $phone_formatted, $postal_code, $country, $EntryTerm_formatted, $consent, $came_from_degree_search, $student_type_options_default, $grad_ugrad, $program_of_interest_text, $area_of_interest_ugrad, $area_of_interest_grad, $area_of_interest_ugrad_online, $area_of_interest_grad_online, &$form) {

//        \Drupal::logger('asuaec_rfib2_directpost')->notice('EntryTerm_formatted:' . $EntryTerm_formatted);

        $ret_value = true;
        $error_message = '';

        if($campus_options == '0') {
            $error_message .= 'Please select which applies to you.<br />';
            $form_state->setErrorByName('campus_options', $this->t('Please select which applies to you.'));
            //$form['campus_options']['#attributes']['class'][] = 'is-invalid';
            $ret_value = false;
        }
        if($student_type_options_default == '0') {
            $error_message .= 'Please select your student status.<br />';
            $form_state->setErrorByName('student_type_options_default', $this->t('Please select your student status.'));
            //$form['student_type_options_default']['#attributes']['class'][] = 'is-invalid';
            $ret_value = false;
        }

        //------- Area of interest - Added on 5/10/2024.

        if($campus_options == "GROUND" || $campus_options == "NOPREF") {
          if($student_type_options_default == "First Time Freshman" || $student_type_options_default == "Transfer") {
            if($area_of_interest_ugrad == '0') {
              $error_message .= 'Please select area of interest.<br />';
              $form_state->setErrorByName('area_of_interest_ugrad', $this->t('Please select area of interest.'));
              //$form['area_of_interest_ugrad']['#attributes']['class'][] = 'is-invalid';
              $ret_value = false;
            }
          }
          if($student_type_options_default == "Readmission") {
            if($area_of_interest_grad == '0') {
              $error_message .= 'Please select area of interest.<br />';
              $form_state->setErrorByName('area_of_interest_grad', $this->t('Please select area of interest.'));
              //$form['area_of_interest_grad']['#attributes']['class'][] = 'is-invalid';
              $ret_value = false;
            }
          }
        }
        else if($campus_options == "ONLNE") {
          if($student_type_options_default == "First Time Freshman" || $student_type_options_default == "Transfer") {
            if($area_of_interest_ugrad_online == '0') {
              $error_message .= 'Please select area of interest.<br />';
              $form_state->setErrorByName('area_of_interest_ugrad_online', $this->t('Please select area of interest.'));
              //$form['area_of_interest_ugrad_online']['#attributes']['class'][] = 'is-invalid';
              $ret_value = false;
            }
          }
          else if($student_type_options_default == "Readmission") {
            if($area_of_interest_grad_online == '0') {
              $error_message .= 'Please select area of interest.<br />';
              $form_state->setErrorByName('area_of_interest_grad_online', $this->t('Please select area of interest.'));
              //$form['area_of_interest_grad_online']['#attributes']['class'][] = 'is-invalid';
              $ret_value = false;
            }
          }
        }

      //--------- END OF Area of interest - Added on 5/10/2024.

        if($grad_ugrad == 'GRAD' && ($program_of_interest_text == '' || $program_of_interest_text == '0')) {
            $error_message .= 'Please select program of interest.<br />';
            $form_state->setErrorByName('program_of_interest_text', $this->t('Please select program of interest.'));
            //$form['program_of_interest_text']['#attributes']['class'][] = 'is-invalid';
            $ret_value = false;
        }
//        if ($email_address == '') {
//            $error_message .= 'Please enter email address.<br />';
//            $form_state->setErrorByName('email_address', $this->t('Please enter email address.'));
//            $form['email_address']['#attributes']['class'][] = 'is-invalid';
//            $ret_value = false;
//        }
//        if ($first_name == '') {
//            $error_message .= 'Please enter first name.<br />';
//            $form_state->setErrorByName('first_name', $this->t('Please enter first name.'));
//            $form['first_name']['#attributes']['class'][] = 'is-invalid';
//            $ret_value = false;
//        }
//        if ($last_name == '') {
//            $error_message .= 'Please enter last name.<br />';
//            $form_state->setErrorByName('last_name', $this->t('Please enter last name.'));
//            $form['last_name']['#attributes']['class'][] = 'is-invalid';
//            $ret_value = false;
//        }
//        if (preg_match("/^\d{11}/", $phone_formatted) !== 1) { // More than 10 digit
//            $error_message .= 'Please enter phone number.<br />';
//            $form_state->setErrorByName('phone', $this->t('Please enter phone number.'));
//            $form['phone']['#attributes']['class'][] = 'is-invalid';
//            $ret_value = false;
//        }
//        if($campus_options != 'ONLNE') {
//            if ($postal_code == '') {
//                $error_message .= 'Please enter postal code.<br />';
//                $form_state->setErrorByName('postal_code', $this->t('Please enter postal code.'));
//                $form['postal_code']['#attributes']['class'][] = 'is-invalid';
//                $ret_value = false;
//            }
//        }
        if($campus_options != 'ONLNE') {
            if ($country == '') {
                $error_message .= 'Please enter country of citizenship.<br />';
                $form_state->setErrorByName('country', $this->t('Please enter country of citizenship.'));
                //$form['country']['#attributes']['class'][] = 'is-invalid';
                $ret_value = false;
            }
        }
        if($campus_options != 'ONLNE') {
//            \Drupal::logger('asuaec_rfib2_directpost')->notice('EntryTerm_formatted 2:' . $EntryTerm_formatted);
            if ($EntryTerm_formatted == '' || $EntryTerm_formatted == '0:00') {
//                \Drupal::logger('asuaec_rfib2_directpost')->notice('cs test');
//                if($student_type_options_default == 'Readmission') {
//                  $error_message .= 'Please select start term.<br />';
//                  $form_state->setErrorByName('entry_term_text', $this->t('Please select start term.'));
//                } else {
//                  $error_message .= 'Please select start term.<br />';
//                  $form_state->setErrorByName('entry_term', $this->t('Please select start term.'));
//                }
                $error_message .= 'Please select start term.<br />';
                $form_state->setErrorByName('entry_term', $this->t('Please select start term.'));
                if($grad_ugrad == 'GRAD') {
                  $form['elements']['step_1']['grad_entry_term']['#attributes']['class'][] = 'is-invalid';
                }
                $ret_value = false;
            }
        }
        if ($consent == '') {
            $error_message .= 'Consent checkbox needs to be checked in order to submit this form.<br />';
            if($campus_options == 'ONLNE') {
              //$form_state->setErrorByName('gdpr_consent_online', $this->t('Consent checkbox needs to be checked in order to submit this form.'));
              $form_state->setErrorByName('gdpr_consent_online', $this->t('Consent checkbox needs to be checked in order to submit this form.'));
              //$form['gdpr_consent_online']['#attributes']['class'][] = 'is-invalid';
            } else {
              //$form_state->setErrorByName('gdpr_consent', $this->t('Consent checkbox needs to be checked in order to submit this form.'));
              $form_state->setErrorByName('gdpr_consent', $this->t('Consent checkbox needs to be checked in order to submit this form.'));
              //$form['gdpr_consent']['#attributes']['class'][] = 'is-invalid';
            }
            $ret_value = false;
        }

        if($ret_value == false) {
            $the_error = new TranslatableMarkup($error_message, []);
            $this->messenger()->addError($the_error);
            $form_state->setRebuild();
        }
        return $ret_value;
    }

    /**
     * {@inheritdoc}
     *
     *  Perform validation when "Next" button was pressed.
     *  Post to middleware when "Submit" button was pressed.
     *  ** It seems that when "Previous" button was pressed, this function will not be called.
     */
    public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

        $values = $webform_submission->getData();
        $the_submit_handler = $form_state->getSubmitHandlers();


        // Post to middleware
        if($the_submit_handler[0] == "::submit") {

            // BirthDate
            // We will post birthdate hidden field.
            $date_of_birth = isset($values['date_of_birth']) ? $values['date_of_birth'] : ''; // 2000-01-02
            // Validate birthdate - Added on 4/18/2022
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

            // Phone
            $phone = isset($values['phone']) ? $values['phone'] : '';
            // Remove "+" and "-"
            $phone_formatted = preg_replace('[\D]', '', $phone);
            $form_state->setValue('phone', $phone_formatted);

            // Campus and Student type
            $campus_options = isset($values['campus_options']) ? $values['campus_options'] : '';
            // When coming from Degree search, campus_options contain empty string. So, set it to be "GROUND" -- Added on 4/21/2023 for B and C form for ABC testing.
            $came_from_degree_search = isset($values['came_from_degree_search']) ? $values['came_from_degree_search'] : ''; // Added on 4/20/2023
            if($came_from_degree_search == 'true') {
              $campus_options = 'GROUND';
            }
            $student_type_options_default = isset($values['student_type_options_default']) ? $values['student_type_options_default'] : '';
            $grad_ugrad = isset($values['grad_ugrad']) ? $values['grad_ugrad'] : '';
            $plan = isset($values['program_of_interest_text']) ? $values['program_of_interest_text'] : '';


            // EntryTerm: '2251:2025 Spring'
            // Ground Grad
            $EntryTerm_formatted = '';
            if($campus_options != 'ONLNE') { // Skip this when Online. Added on 3/3/2023.
  //            ksm($values['entry_term_text'], "values['entry_term_text']");
  //            ksm($values['grad_ugrad'], "values['grad_ugrad']");
  //            if ($campus_options == 'GROUND' && $student_type_options_default == 'Readmission' || $campus_options == 'NOPREF' && $student_type_options_default == 'Readmission')
                if ($campus_options != 'ONLNE' && $grad_ugrad == 'GRAD' && $plan != '')
                {
                    $entry_term_text = isset($values['entry_term_text']) ? $values['entry_term_text'] : '';
                    if($entry_term_text != '') {
                        $EntryTerm_formatted = $entry_term_text . ':' . $this->getEntryTerm_label($entry_term_text);
                    }
                } else { // The rest
                    $entry_term = (isset($values['entry_term']) && $values['entry_term']) != '0' ? $values['entry_term'] : ''; // <--- Default entry term
                    if($entry_term != '') {
                        $EntryTerm_formatted = $entry_term . ':' . $this->getEntryTerm_label($entry_term);
                    }
                }
            }
            $form_state->setValue('entryterm', $EntryTerm_formatted);

            // GDPR consent
            $consent = '';
            if ($campus_options == 'GROUND' || $campus_options == 'NOPREF') {
              $consent = isset($values['gdpr_consent'][0]) ? $values['gdpr_consent'][0] : '';

            }
            if ($campus_options == 'ONLNE') {
              $consent = isset($values['gdpr_consent_online'][0]) ? $values['gdpr_consent_online'][0] : '';
            }

            // Area of interest
            // Ground
            $interest1 = '';
            if (($campus_options == 'GROUND' && $student_type_options_default == 'First Time Freshman') ||
                ($campus_options == 'GROUND' && $student_type_options_default == 'Transfer') ||
                ($campus_options == 'NOPREF' && $student_type_options_default == 'First Time Freshman') ||
                ($campus_options == 'NOPREF' && $student_type_options_default == 'Transfer')
            ) {
                $area_of_interest_ugrad = isset($values['area_of_interest_ugrad']) ? $values['area_of_interest_ugrad'] : '';
                $interest1 = $area_of_interest_ugrad;
            }
            if ($campus_options == 'GROUND' && $student_type_options_default == 'Readmission') {
                $area_of_interest_grad = isset($values['area_of_interest_grad']) ? $values['area_of_interest_grad'] : '';
                $interest1 = $area_of_interest_grad;
            }
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
            $form_state->setValue('interest1', $interest1);

            // Campus
            // When came from Degree search
            if ($campus_options == '') {
                $campus_options = 'NOPREF';
                $form_state->setValue('campus_options', 'NOPREF');
            }

            // Career
            if ($student_type_options_default == '') { // When came from Degree search, $student_type_options_default becomes empty. However, grad_ugrad webform field was already set by JS.
                $grad_ugrad = isset($values['grad_ugrad']) ? $values['grad_ugrad'] : '';

            } else { // When there was no URL params.
                if ($student_type_options_default == 'Readmission') {
                    $grad_ugrad = "GRAD";
                } else {
                    $grad_ugrad = "UGRAD";
                }
                $form_state->setValue('grad_ugrad', $grad_ugrad);
            }

            //------------------------------------------------

            // Let's validate 2nd page here
            $email_address = isset($values['email_address']) ? $values['email_address'] : '';
            $first_name = isset($values['first_name']) ? trim($values['first_name']) : '';
            $last_name = isset($values['last_name']) ? trim($values['last_name']) : '';
            $postal_code = isset($values['postal_code']) ? $values['postal_code'] : '';
            $country = isset($values['citizenship_country']) ? $values['citizenship_country'] : '';

            $area_of_interest_ugrad = isset($values['area_of_interest_ugrad']) ? $values['area_of_interest_ugrad'] : '';
            $area_of_interest_grad = isset($values['area_of_interest_grad']) ? $values['area_of_interest_grad'] : '';
            $area_of_interest_ugrad_online = isset($values['area_of_interest_ugrad_online']) ? $values['area_of_interest_ugrad_online'] : '';
            $area_of_interest_grad_online = isset($values['area_of_interest_grad_online']) ? $values['area_of_interest_grad_online'] : '';

            $validated2ndpage = false;
//            $validated2ndpage = $this->validate2ndpage($campus_options, $form_state, $email_address, $first_name, $last_name, $phone_formatted, $postal_code, $country, $EntryTerm_formatted, $consent);
//            $validated2ndpage = $this->validate2ndpage($campus_options, $form_state, $email_address, $first_name, $last_name, $phone_formatted, $postal_code, $country, $EntryTerm_formatted, $consent, $came_from_degree_search, $student_type_options_default, $grad_ugrad, $plan); // -- Added came_from_degree_search on 4/21/2023 for B and C form for ABC testing.
            $validated2ndpage = $this->validate2ndpage($campus_options, $form_state, $email_address, $first_name, $last_name, $phone_formatted, $postal_code, $country, $EntryTerm_formatted, $consent, $came_from_degree_search, $student_type_options_default, $grad_ugrad, $plan, $area_of_interest_ugrad, $area_of_interest_grad, $area_of_interest_ugrad_online, $area_of_interest_grad_online, $form ); // -- Changed for rfi b2 on 5/10/2024

            if($validated2ndpage == false) {
                $form_state->setRebuild();
                return;
            }

            //-------------------------------------------------

            //$rfipage_alias = 'future-student-request'; // Changed to be dynamic on 6/15/2024.
            $rfipage_alias = $_SERVER['REQUEST_URI'];

            // Source ID and Post URL switch depending on environment
            // Get values from config
            $config_data = \Drupal::config('asuaec_rfib2_directpost.customadmin_settings');
            $domain = 'https://' . $_SERVER['HTTP_HOST'];
            $env = 'prod';
            if($domain == 'https://live-ufcw99-d10.ws.asu.edu' || $domain == 'https://ufcw99.asu.edu') {
                $env = 'prod';

                // Ground
                //$sourceid = '7016T000002Ti32QAC';
                $sourceid = $config_data->get('ground_sourceid_prod');
                //$post_url = 'https://crm-enterprise-rfi-forms-submit-handler-prod.apps.asu.edu/'; //<--- Old posting URL
                //$post_url = 'https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
                $post_url = $config_data->get('ground_posturl_prod');

            } else {
                $env = 'dev';

                // Ground
                //$post_url = 'https://crm-enterprise-rfi-forms-submit-handler-sandbox.sdc.uto.asu.edu/'; //<--- Old posting URL
                //$post_url = 'https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
                $post_url = $config_data->get('ground_posturl_dev');
                //$sourceid = '7016T000002c8qMQAQ'; // Changed on 10/25/2023
                $sourceid = $config_data->get('ground_sourceid_dev');
            }

            // Enterpriseclientid -- We don't know how we get 'false', but if it is 'false', post empty string. Also, to match with what we post to middleware, changing value to empty string in Webform submission data. Changed on 4/27/2022.
//            \Drupal::logger('asuaec_rfi')->notice("asuonline_enterpriseclientid:<pre>" . $values['asuonline_enterpriseclientid'] . "</pre>");
            $asuonline_enterpriseclientid = isset($values['asuonline_enterpriseclientid']) ? trim($values['asuonline_enterpriseclientid']) : '';
            if($asuonline_enterpriseclientid == 'false' || $asuonline_enterpriseclientid == 'FALSE') {
              $asuonline_enterpriseclientid = '';
              // Set the Webform field value also to be empty to match with what we are posting.
              $form_state->setValue('asuonline_enterpriseclientid', $asuonline_enterpriseclientid);
            }

            // Change "Readmission" to "Masters" on 3/26/2024.
            $studentType = $values['student_type_options_default'];
            if($studentType == 'Readmission') {
              $studentType = 'Masters';
            }

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




            //-----------------------------------------------------------------------------------------------
            // ASU Online post if ASU Onlin is selected -- Added on 6/15/2024.

            if($campus_options == 'ONLNE') {
              // Pass from config page: /admin/config/asuaec-rfib2-directpost. Changed on 6/15/2024.
//              $lead_class = 'CORP';
//              $sub_class = 'AMZ';

              if($domain == 'https://live-ufcw99-d10.ws.asu.edu' || $domain == 'https://ufcw99.asu.edu') {
                // ASU Online - 6/15/2024
                //$post_url_online = 'https://api.edpl.us/v1/asuo/rfi-leads';
                $post_url_online = $config_data->get('online_posturl_prod');
                //$sourceid_online = 'UFCW99-I+sCa5YI73uLG9kA';
                $sourceid_online = $config_data->get('online_sourceid_prod');
                $lead_class = $config_data->get('online_leadclass_prod');
                $sub_class = $config_data->get('online_subclass_prod');

              } else {
                // ASU Online - 6/15/2024
                //$post_url_online = 'https://qa-api.edpl.us/v1/asuo/rfi-leads';
                $post_url_online = $config_data->get('online_posturl_dev');
                //$sourceid_online = 'UFCW99-I+sCa5YI73uLG9kA';
                $sourceid_online = $config_data->get('online_sourceid_dev');
                $lead_class = $config_data->get('online_leadclass_dev');
                $sub_class = $config_data->get('online_subclass_dev');
              }

              $submission_data_online = array(
                // Online fields:
                'first_name' => isset($values['first_name']) ? $values['first_name'] : '',
                'last_name' => isset($values['last_name']) ? $values['last_name'] : '',
                'email_address' => isset($values['email_address']) ? $values['email_address'] : '',
                'phone' => $phone_formatted,
                'program_key' => isset($values['program_of_interest_text']) ? $values['program_of_interest_text'] : '',
                //'origin_uri' => $domain . '/' . $rfipage_alias,
                'origin_uri' => $domain . $rfipage_alias,
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
  //                curl_setopt($curl_online, CURLOPT_HTTPHEADER, array( // Updated on 1/22/2024.
  //                    'Content-Type: application/json'));
              curl_setopt($curl_online, CURLOPT_HTTPHEADER, array(
                  'Content-Type: application/json',
                  'Lead-Origin-Token: tC4JACQEfk7roXAP0wgYMyNJgoTK2qYWB9npPChq8yIDHswI'
                )
              );
              $response_online = curl_exec($curl_online);
              $info_online = curl_getinfo($curl_online);
  //                    ksm($info, "info");

              curl_close($curl_online);

              // Error occured
              if (($info_online['http_code'] < 200) || ($info_online['http_code'] >= 300)) {
                \Drupal::logger('asuaec_rfi')->notice('ASU Online post failed.<br /><pre>ASU Online post: ' . print_r($response_online, true) . '<br />ASU Online post URL: ' . $post_url_online . '</pre>');
                if($env == 'prod') {
                  $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', array());
                } else {
                  $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<br /> <pre>ASU Online post: ' . print_r($response_online, true) . '<br />ASU Online post URL: ' . $post_url_online . '</pre>', array());
                }
                $this->messenger()->addError($the_error);
                $form_state->setRebuild();
              }
              // Success
              else {
                \Drupal::logger('asuaec_rfi')->notice('Success - <pre><code>ASU Online post: ' . print_r($submission_data_online, TRUE) . '<br />ASU Online post URL: ' . $post_url_online . '</code></pre>');
                if($env == 'dev') {
                  $the_message = new TranslatableMarkup('Success - <pre>ASU Online post: ' . print_r($response_online, true) . '<br />ASU Online posted data:' . print_r($submission_data_online, true) . '<br />ASU Online post URL: ' . $post_url_online . '</pre>', array());
                  $this->messenger()->addMessage($the_message);
                }

              } // END OF else

            } // END OF online

            //-----------------------------------------------------------------------------------------------
            // Middleware post if ASU Online is NOT selected -- Added on 3/29/2022
            else {

              $submission_data = array(
                'CitizenshipCountry' => $country,
//                'Street1' => isset($values['address']) ? $values['address'] : '',
//                'City' => isset($values['city']) ? $values['city'] : '',
//                'State' => isset($values['state_or_province']) ? $values['state_or_province'] : '',
//                'Country' => isset($values['country']) ? $values['country'] : '',
                'Zip' => $postal_code,
//                'BirthDate' => $date_of_birth_formatted,
//                'MilitaryStatus' => isset($values['veteran_status_options']) ? $values['veteran_status_options'] : '',
//                'Comments' => isset($values['comments']) ? $values['comments'] : '',
                'EmailAddress' => $email_address,
                'FirstName' => $first_name, // Added trim() on 8/23/2022
                'LastName' => $last_name, // Added trim() on 8/23/2022
                'Phone' => $phone_formatted,
                'EntryTerm' => $EntryTerm_formatted,
                'GdprConsent' => $consent,
                'Campus' => $campus_options,
                'Interest1' => $interest1,
                'Interest2' => isset($values['program_of_interest_text']) ? $values['program_of_interest_text'] : '',
                'Career' => $grad_ugrad,
//                'StudentType' => isset($values['student_type_options_default']) ? $values['student_type_options_default'] : '',
                'StudentType' => $studentType,
                'Source' => $sourceid,
//                'URL' => $domain . '/' . $rfipage_alias,
                //'URL' => $domain . '/' . $rfipage_alias . $utm,
                'URL' => $domain . $rfipage_alias . $utm,
                'datetime' => $webform_submission->getCreatedTime(),
                'enterpriseclientid' => $asuonline_enterpriseclientid,
                'ga_clientid' => $asuonline_enterpriseclientid,
              );

              foreach ($submission_data as $key => $value) {
                if ($value == '') {
                  unset($submission_data[$key]);
                }
              }

              $data = json_encode($submission_data);
//            ksm($submission_data, "submission data");

              // Prevent from duplicate. Added on 5/20/2022
              if (self::$x == 0) {

                $curl = curl_init($post_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //If you don't want to use any of the return information, set to false
                curl_setopt($curl, CURLOPT_HEADER, TRUE); //Set this to false to remove informational headers
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //data mapping
                curl_setopt($curl, CURLOPT_SSLVERSION, 1); //This will set the security protocol to TLSv1
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                  'Content-Type: application/json'
                ]);
                $response = curl_exec($curl);
                $info = curl_getinfo($curl);

                curl_close($curl);

                //        \Drupal::logger('asuaec_rfi')->notice('webform_submission:<pre><code>' . print_r($webform_submission, TRUE) . '</code></pre>');
                //            \Drupal::logger('asuaec_rfi')->notice("submission data:<pre>" . print_r($submission_data, true) . "</pre>");
                //            \Drupal::logger('asuaec_rfi')->notice("response:<pre>" . print_r($response, true) . "</pre>");
                //            \Drupal::logger('asuaec_rfi')->notice("info:<pre>" . print_r($info, true) . "</pre>");

                if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
                  \Drupal::logger('asuaec_rfib2_directpost')
                    ->notice('Post failed.<pre>' . print_r($response, TRUE) . '</pre>');
                  if ($env == 'prod') {
                    $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
                  } else {
                    $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<pre>' . print_r($response, TRUE) . '</pre>', []);
                  }
                  $this->messenger()->addError($the_error);
                  $form_state->setRebuild();

                } else {
                  \Drupal::logger('asuaec_rfib2_directpost')
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
                \Drupal::logger('asuaec_rfib2_directpost')
                  ->warning('Did not post because static variable x was not 0 - Post data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
                if ($env == 'dev') {
                  $the_message = new TranslatableMarkup('Did not post because static variable x was not 0 - Posted data:<pre>' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
                  $this->messenger()->addMessage($the_message);
                }
              } // END OF else


            }

        } // END OF  if($the_submit_handler[0] == "::submit")


    } // END OF public function validateForm

} // END OF class RfiWebformHandler
