<?php

namespace Drupal\asuaec_confemail\Plugin\WebformHandler;

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
 *   id = "Confirmation email for Early Start webform",
 *   label = @Translation("Confirmation email for Early Start webform"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Confirmation email for Early Start webform"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/

class EarlyStartEmail extends WebformHandlerBase {

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
        /** Get webform submission values and current wenform id**/
		$webform = $this->getWebform();
        $nodeid = $webform->id();
		$values =  $webform_submission->getData();
//        ksm($values, "values");
        $completed_timestamp = $webform_submission->getCompletedTime();
        $completed_dateTime = date('Y-m-d H:i', $completed_timestamp);

      $email = $values['email'];
//		$event_id = $values['event_id'];
//      $fname = $values['first_name'];

//		$form_object = $form_state->getFormObject();
//		$webform_submission->setSticky(!$webform_submission->isSticky())->save();
//       	$sid = $webform_submission->id();

        $banner = '';

        $output = <<<EOD
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head><meta name="robots" content="noindex, nofollow"><meta name="referrer" content="no-referrer">
    <!--[if gte mso 9]><xml>      <o:OfficeDocumentSettings>       <o:AllowPNG/>       <o:PixelsPerInch>96</o:PixelsPerInch>      </o:OfficeDocumentSettings>     </xml><![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width">
<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">
<title>Arizona State University</title>
<style id="media-query">
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

table[class="body"] img.fullwidth {max-width: 100% !important; }
table[class="body"] center {min-width: 0 !important;}
table[class="body"] .container {width: 95% !important;}
table[class="body"] .row {width: 100% !important; display: block !important;}
table[class="body"] .wrapper {display: block !important; padding-right: 0 !important; }
table[class="body"] .columns, table[class="body"] .column {table-layout: fixed !important; float: none !important; width: 100% !important; padding-right: 0px !important; padding-left: 0px !important; display: block !important; }
table[class="body"] .wrapper.first .columns, table[class="body"] .wrapper.first .column { display: table !important; }
table[class="body"] table.columns td, table[class="body"] table.column td, .col {width: 100% !important; }
table[class="body"] table.columns td.expander {width: 1px !important; }
table[class="body"] .right-text-pad, table[class="body"] .text-pad-right {padding-left: 10px !important; }
table[class="body"] .left-text-pad, table[class="body"] .text-pad-left {padding-right: 10px !important; }
table[class="body"] .hide-for-small, table[class="body"] .show-for-desktop {display: none !important; }
table[class="body"] .show-for-small, table[class="body"] .hide-for-desktop {display: inherit !important; }
*[class=nomobile]{display:none !important;}
*[class=mobilefullwidth]{ width:100% !important; height: auto !important; }
}

@media screen and (max-width: 700px) {
div[class="col"] {width: 100% !important;}
.mobilefont {font-size:14px !important;}
.mobilefontheader {font-size:16px !important;} 
.mobilefonthero {font-size:48px !important; line-height: 48px !important; padding: 24px 0px 14px 0px !important;}
.mobilebg {background-color: #FFFFFF !important; background-color: #FFFFFF !important; border: none !important;}
  .fullwidth {width: 100% !important;}
}

@media screen and (min-width: 701px) {
table[class="container"] {width: 700px !important;}
}
  
</style>

<style>
  
/* Button and link hover styles */
  
.buttonHover{transition:.05s}.buttonHover:hover{background:#ffd35a !important;transition:.05s}
.linkHover{color:#8c1d40 !important;transition:.5s !important}.linkHover:hover{color:#cb2a5d !important;transition:.5s !important;}  
  
 /* Agenda time - Make it appear in one line */
 table#agenda > tbody > tr > td:first-child {
    white-space: nowrap !important;
 }
</style>

<!--[if (gte mso 9)|(IE)]> <style> .mso-link {text-decoration: underline !important; display: inline-block !important;}  </style> <![endif]-->
  
</head>

<body class="mobilebg" style="width: 100% !important;min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100% !important;margin: 0;padding: 0; background-color: #F1F1F1;"><style type="text/css">
div.preheader 
{ display: none !important; } 
</style>

<!--[if mso]> <style type="text/css"> body, table, td, p, div, a {font-family: Arial, sans-serif !important;} .mso-link {border-bottom: 1px solid #8c1d40 !important; display: inline-block;}  </style> <![endif]-->
<style>
a {color:#8C1D40;}
h3 {background-color: #191919 !important; color: #fafafa !important; display: inline-block;}
</style>
<!--Begin Email-->

<table role="presentation" class="body mobilebg" style="border-spacing: 0;border-collapse: collapse;vertical-align: top;height: 100%;width: 100%;table-layout: fixed" cellpadding="0" cellspacing="0" width="100%" border="0">
<div width="100%" align="center">
<!--End Wrap-->
<!-- Insert &zwnj;&nbsp; hack after hidden preview text -->
<div style="display: none; max-height: 0px; overflow: hidden;">
&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
</div>

<table role="presentation" style="border-spacing: 0; border-collapse: collapse; vertical-align: top;" cellpadding="0" cellspacing="0" align="center" width="100%" border="0">
<tbody>
  <!--Insert Reference block for UTM-->
 
  <tr><td><custom name="opencounter" type="tracking"></td></tr>
<tr><td width="100%" height="16px">&shy;</td></tr>

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
    <td class="mobile-bump" style="border-collapse: collapse; text-align: left; font-size:16px; color:#000000; font-family: Arial, sans-serif; padding: 24px 24px 24px 24px; line-height: 24px; font-weight: 300;">
      <a alias="ASU Top Logo" href="https://asu.edu">
        <img src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/3/d9f2d016-e69b-4a9f-9054-b0651026a038.png" width="230" height="auto" alt="Arizona State University" style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: Arial, sans-serif; padding: 0px; line-height: 24px; font-weight: bold;">
      </a>
    </td>
  </tr>
</table></td></tr></table>

</td>
</tr>
 
<!--Start Content-->

<!--
<tr>
<td align="center" valign="top">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="left" style="background-color:#ffffff; padding:0px">
<tbody>
<tr>
<td class="x_mobile-bump" style="border-collapse:collapse; text-align:left; font-size:16px; color:#000000; font-family:Arial,sans-serif; padding:0px 24px 0px 24px; line-height:24px">
{$banner}
</td></tr></tbody></table>

</td>
</tr>
-->

<tr>
<td align="center" valign="top">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="left" style="background-color:#ffffff; padding:0px">
<tbody>
<tr>
<td class="x_mobile-bump" style="border-collapse:collapse; text-align:left; font-size:16px; color:#000000; font-family:Arial,sans-serif; padding:0px 24px 0px 24px; line-height:24px">
<!--Main content-->		
	
	<p>Hello {$values['first_name']},</p>

	<p>Thank you for applying for ASU's The College of Liberal Arts and Sciences Early Start program.  You can expect a response to your application in about a week.  In reviewing applications, we review for both eligibility and if the program for your major still has space.</p> 

	<p>If you are approved for Early Start, you will receive a confirmation email with some additional information.  More detailed logistical information will be shared with approved applicants starting in early summer.  For questions about The College Early Start program, please email <a href="mailto:thecollegeearlystart@asu.edu">thecollegeearlystart@asu.edu</a>.</p>

	<p>Best wishes,</p>

	<p>The College of Liberal Arts and Sciences<br />
	Arizona State University<br />
	<a href="mailto:thecollegeearlystart@asu.edu">thecollegeearlystart@asu.edu</a>
	</p>
	
	
	
	
<!--END OF Main content-->
</td></tr></tbody></table>

</td>
</tr>
  
 
<!--End Content-->
 
<!--Social Media--> 
<tr>
<td align="center" valign="top">

<table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="min-width: 100%; " class="stylingblock-content-wrapper"><tr><td class="stylingblock-content-wrapper camarker-inner">
	
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="background-color: #000000;padding: 0px;">
  <tr style="vertical-align: middle">
    <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle;text-align: center; font-size: 0; padding: 16px 0px 16px 0px;">

<!--[if (gte mso 9)|(IE)]><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td valign="middle" width="355" style="line-height:0px"><![endif]-->
  
<div style="display: inline-block;vertical-align: middle;text-align: center;width: 355px;">

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding: 0px 8px 0px 0px;">
    
<tr>
<td align="center" valign="middle" style="border-collapse: collapse; text-align: right; font-size:16px; color:#ffffff; font-family: Arial, sans-serif; padding: 8px; line-height: 0px;">
              
 <b style="font-size:24px;line-height:22px; color: #ffffff;">#<span style="font-size:36px;color: #ffffff;">1</span> in the U.S. for innovation</b><br>
 
 <b style="background-color: #ffc627; color: #000000; font-size:16px;line-height: 16px;">&nbsp;ASU ahead of MIT and Stanford&nbsp;</b><br>
 
 <b style="font-size:11px; color: #000000; background-color: #ffffff;padding:2px;line-height: 18px;">&nbsp;&ndash; U.S. News &amp; World Report, 7 years, 2016&ndash;2022</b>           

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
<img src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/e0014106-a5a7-4286-9410-f5d5a1f52fc4.png" width="230" height="auto" alt="Arizona State University" style="border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 0px; line-height: 24px;">
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
        <img width="40" height="40" border="0" alt="Facebook Logo" src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/3ceb1afd-6403-4283-85ad-3e2e1787d7d2.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="Twitter_ASU" href="http://twitter.com/futuresundevils">
        <img width="40" height="40" border="0" alt="Twitter Logo" src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/2a6ec595-27aa-48c8-9baf-6574a843af56.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="Instagram_ASU" href="http://instagram.com/FutureSunDevils">
        <img width="40" height="40" border="0" alt="Instagram Logo" src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/64803eb2-927c-461a-bb27-24ca29f888aa.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        <a alias="Snapchat_ASU" href="https://www.snapchat.com/add/futuresundevils">
        <img width="40" height="40" border="0" alt="Snapchat Logo" src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/7e757536-f2d9-4de0-a048-3ffe08e5475d.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>
        </td>
        <td style="font-size:0px; word-break: break-word; border-collapse: collapse !important; vertical-align: middle; padding:0px; text-align: center;">
        
        <a alias="YouTube_ASU" href="https://www.youtube.com/user/ASU">
        <img width="40" height="40" border="0" alt="YouTube Logo" src="http://image.reply.asu.edu/lib/fe8d137272610d7c76/m/7/a3d6a3ee-c40c-427d-a46f-caf9111f2ad9.png" style="vertical-align: middle; padding: 0px; text-align: center;">
        </a>

        </td>
        </tr>
 </table>
 </center>
    </td>
  </tr>
</table>
	</td></tr></table>

</td>
</tr>


<!--Start Content/Legal-->
<tr>
<td align="center" valign="top">


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
</html>
EOD;

		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asuaec_confemail';
		 $key = 'confemail';
		 $to = $email;
		 $subject = "Early Start confirmation";
		 $from = \Drupal::config('system.site')->get('mail'); 
	     $params['message'] = \Drupal\Core\Render\Markup::create($output);
		 $params['subject'] = $subject;
	     $params['from'] = $from;
		 $params['reply-to'] = $from;
		 //ksm($params);
		 //ksm($to);
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		 $send = true;
	  	 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
		
	}
}