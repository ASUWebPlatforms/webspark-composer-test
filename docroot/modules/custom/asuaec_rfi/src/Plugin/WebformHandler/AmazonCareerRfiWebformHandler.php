<?php

namespace Drupal\asuaec_rfi\Plugin\WebformHandler;

use Drupal\asuaec_rfi\Controller\WebformConfirmationPage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Form submission handler.
 *
 * Source ID is set at line 667.
 *
 * @WebformHandler(
 *   id = "rfi_webform_handler_amazoncareer",
 *   label = @Translation("Post to middleware - Amazon Career"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Send the submission to Middleware - Amazon Career"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class AmazonCareerRfiWebformHandler extends WebformHandlerBase {
  /**
   * Prevent posting twice. Added on 5/27/2022.
   *
   * @var int
   */
  public static $x = 0;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * Get environment.
   */
  public function getEnv() {
    $config_data = \Drupal::config('asuaec_rfi.customadmin_settings');
    // https://amazoncareerchoice.asu.edu
    $proddomain = $config_data->get('proddomain');
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    // \Drupal::logger('cstest')->notice('proddomain: <pre>' . $proddomain . '</pre>');
    // \Drupal::logger('cstest')->notice('domain: <pre>' . $domain . '</pre>');

    $env = 'prod';
    if ($domain == $proddomain) {
      $env = 'prod';
    }
    else {
      $env = 'dev';
    }
    // \Drupal::logger('cstest')->notice('env before return: <pre>' . $env . '</pre>');
    return $env;
  }

  /**
   * Post to middleware.
   */
  public function postToMiddleware($webform_submission) {
    $env = $this->getEnv();

    $values = $webform_submission->getData();
    // \Drupal::logger('cstest')->notice('values: <pre>' . print_r($values, true) . '</pre>');

    // BirthDate
    // We will post birthdate hidden field.
    // 2000-01-02.
    $date_of_birth = $values['date_of_birth'] ?? '';
    $date_of_birth_formatted = '';
    if ($date_of_birth != '') {
      $date_of_birth_formatted = $date_of_birth . 'T07:00:00.000Z';
      $webform_submission->setElementData('birthdate', $date_of_birth_formatted);
    }

    // Phone.
    $phone = $values['phone'] ?? '';
    // Remove "+" and "-".
    $phone_formatted = preg_replace('/\D/', '', $phone);
    $webform_submission->setElementData('phone', $phone_formatted);

    // Campus and Student type.
    $campus_options = $values['campus_options'] ?? '';
    $student_type_options_default = $values['student_type_options_default'] ?? '';
    $grad_ugrad = $values['grad_ugrad'] ?? '';
    $plan = $values['program_of_interest_text'] ?? '';

    // EntryTerm: '2251:2025 Spring'
    // Ground Grad.
    $entryTerm_formatted = '';
    if ($campus_options != 'ONLNE' && $grad_ugrad == 'GRAD' && $plan != '') {
      $entry_term_text = $values['entry_term_text'] ?? '';
      $entryTerm_formatted = $entry_term_text . ':' . $this->getEntryTerm_label($entry_term_text);

      // The rest.
    }
    else {
      // <--- Default entry term
      $entry_term = $values['entry_term'] ?? '';
      $entryTerm_formatted = $entry_term . ':' . $this->getEntryTerm_label($entry_term);
    }
    $webform_submission->setElementData('entryterm', $entryTerm_formatted);

    // GDPR consent.
    $consent = '';
    if ($campus_options == 'GROUND' || $campus_options == 'NOPREF') {
      $consent = $values['gdpr_consent'][0] ?? '';
    }
    if ($campus_options == 'ONLNE') {
      $consent = $values['gdpr_consent_online'][0] ?? '';
    }

    // Area of interest
    // Ground.
    $interest1 = '';
    if (($campus_options == 'GROUND' && $student_type_options_default == 'First Time Freshman') ||
        ($campus_options == 'GROUND' && $student_type_options_default == 'Transfer') ||
        ($campus_options == 'NOPREF' && $student_type_options_default == 'First Time Freshman') ||
        ($campus_options == 'NOPREF' && $student_type_options_default == 'Transfer')
    ) {
      $area_of_interest_ugrad = $values['area_of_interest_ugrad'] ?? '';
      $interest1 = $area_of_interest_ugrad;
    }
    if ($campus_options == 'GROUND' && $student_type_options_default == 'Readmission') {
      $area_of_interest_grad = $values['area_of_interest_grad'] ?? '';
      $interest1 = $area_of_interest_grad;
    }
    // Online.
    if (($campus_options == 'ONLNE' && $student_type_options_default == 'First Time Freshman') ||
        ($campus_options == 'ONLNE' && $student_type_options_default == 'Transfer')
    ) {
      $area_of_interest_ugrad_online = $values['area_of_interest_ugrad_online'] ?? '';
      $interest1 = $area_of_interest_ugrad_online;
    }
    if ($campus_options == 'ONLNE' && $student_type_options_default == 'Readmission') {
      $area_of_interest_grad_online = $values['area_of_interest_grad_online'] ?? '';
      $interest1 = $area_of_interest_grad_online;
    }
    $webform_submission->setElementData('interest1', $interest1);

    // Campus
    // When came from Degree search.
    if ($campus_options == '') {
      $campus_options = 'NOPREF';
      $webform_submission->setElementData('campus_options', $campus_options);
    }

    // Career.
    // When came from Degree search, $student_type_options_default becomes empty. However, grad_ugrad webform field was already set by JS.
    if ($student_type_options_default == '') {
      $grad_ugrad = $values['grad_ugrad'] ?? '';

      // When there was no URL params.
    }
    else {
      if ($student_type_options_default == 'Readmission') {
        $grad_ugrad = "GRAD";
      }
      else {
        $grad_ugrad = "UGRAD";
      }
      $webform_submission->setElementData('grad_ugrad', $grad_ugrad);
    }

    // -------------------------------------------------
    $config = \Drupal::config('asuaec_rfi.settings');
    $rfipage_alias = $config->get('asuaec_rfi_rfipage_alias');
    $rfipage_alias = ($rfipage_alias != NULL || $rfipage_alias != '') ? $rfipage_alias : 'arizona-public-employee-scholarship/municipalities';

    $config2 = \Drupal::config('asuaec_rfi.customadmin_settings');
    // Subclass.
    $sub_class = '';
    // $sub_class - 'AMZ'
    $sub_class = $config2->get('online_subclass_prod');
    // \Drupal::logger('cstest')->notice('sub_class: ' . $sub_class);

    // Source ID and Post URL switch depending on environment.
    if ($env === 'prod') {
      $sourceid = $config2->get('ground_sourceid_prod');
      $post_url = $config2->get('ground_posturl_prod');

      // ASU Online post.
      $post_url_online = $config2->get('online_posturl_prod');
      $sourceid_online = $config2->get('online_sourceid_prod');
      // $lead_class - 'CORP'
      $lead_class = $config2->get('online_leadclass_prod');

    }
    // Dev.
    else {
      $sourceid = $config2->get('ground_sourceid_dev');
      $post_url = $config2->get('ground_posturl_dev');

      // ASU Online post.
      $post_url_online = $config2->get('online_posturl_dev');
      $sourceid_online = $config2->get('online_sourceid_dev');
      // $lead_class - 'CORP'
      $lead_class = $config2->get('online_leadclass_dev');
    }

    // Enterpriseclientid -- We don't know how we get 'false', but if it is 'false', post empty string. Also, to match with what we post to middleware, changing value to empty string in Webform submission data. Changed on 4/27/2022.
    // \Drupal::logger('asuaec_rfi')->notice("asuonline_enterpriseclientid:<pre>" . $values['asuonline_enterpriseclientid'] . "</pre>");.
    $asuonline_enterpriseclientid = isset($values['asuonline_enterpriseclientid']) ? trim($values['asuonline_enterpriseclientid']) : '';
    if ($asuonline_enterpriseclientid == 'false' || $asuonline_enterpriseclientid == 'FALSE') {
      $asuonline_enterpriseclientid = '';
      // Set the Webform field value also to be empty to match with what we are posting.
      $webform_submission->setElementData('asuonline_enterpriseclientid', $asuonline_enterpriseclientid);
    }

    $domain = 'https://' . $_SERVER['HTTP_HOST'];

    // -----------------------------------------------------------------------------------------------
    // ASU Online post if ASU Online is selected -- Added on 3/29/2022

    if ($campus_options == 'ONLNE') {

      $submission_data_online = [
        // Online fields:
        'first_name' => $values['first_name'] ?? '',
        'last_name' => $values['last_name'] ?? '',
        'email_address' => $values['email_address'] ?? '',
        'phone' => $phone_formatted,
        'program_key' => $values['program_of_interest_text'] ?? '',
        'origin_uri' => $domain . '/' . $rfipage_alias,
        'lead_class' => $lead_class,
        'sub_class' => $sub_class,
        'enterpriseclientid' => $asuonline_enterpriseclientid,
        'sourceid' => $sourceid_online,
        'sms_permission' => 'Y',
      ];

      foreach ($submission_data_online as $key => $value) {
        if ($value == '') {
          unset($submission_data_online[$key]);
        }
      }
      $data_online = json_encode($submission_data_online);
      $settings = \Drupal::service('settings');
      $edplus_lead_origin_token = $settings->get('edplus_lead_origin_token');
      if (empty($edplus_lead_origin_token)) {
        \Drupal::logger('cstest')->error('Lead-Origin-Token is missing.');
      }

      $curl_online = curl_init($post_url_online);
      // If you don't want to use any of the return information, set to false.
      curl_setopt($curl_online, CURLOPT_RETURNTRANSFER, TRUE);
      // Set this to false to remove informational headers.
      curl_setopt($curl_online, CURLOPT_HEADER, TRUE);
      curl_setopt($curl_online, CURLOPT_CUSTOMREQUEST, 'POST');
      // Data mapping.
      curl_setopt($curl_online, CURLOPT_POSTFIELDS, $data_online);
      // This will set the security protocol to TLSv1.
      curl_setopt($curl_online, CURLOPT_SSLVERSION, 1);
      curl_setopt($curl_online, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($curl_online, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Lead-Origin-Token: ' . $edplus_lead_origin_token,
      ]
      );
      $response_online = curl_exec($curl_online);
      $info_online = curl_getinfo($curl_online);

      curl_close($curl_online);

      // \Drupal::logger('asuaec_rfi')->notice('ASU Online post info_online: <pre>' . print_r($info_online, true) . '</pre>');

      // Error occured.
      if (($info_online['http_code'] < 200) || ($info_online['http_code'] >= 300)) {
        \Drupal::logger('asuaec_rfi')->notice('ASU Online post failed.<br /><pre>ASU Online post: ' . htmlspecialchars(print_r($response_online, TRUE)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</pre>');
        if ($env == 'prod') {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
        }
        else {
          $the_error = new TranslatableMarkup(
            'We are very sorry, error occurred while posting data. Please try again later.<br /> <pre>ASU Online post: @response<br />ASU Online post URL: @url</pre>',
            [
              '@response' => htmlspecialchars(print_r($response_online, TRUE)),
              '@url' => htmlspecialchars($post_url_online),
            ]
          );
        }
        $this->messenger()->addError($the_error);
      }
      // Success.
      else {
        \Drupal::logger('asuaec_rfi')->notice('Success - <pre><code>ASU Online post: ' . htmlspecialchars(print_r($submission_data_online, TRUE)) . '<br />ASU Online post URL: ' . htmlspecialchars($post_url_online) . '</code></pre>');
        if ($env == 'dev') {
          $the_message = new TranslatableMarkup(
            'Success - <pre>ASU Online post: @response<br />ASU Online posted data: @data<br />ASU Online post URL: @url</pre>',
            [
              '@response' => htmlspecialchars(print_r($response_online, TRUE)),
              '@data' => print_r($submission_data_online, TRUE),
              '@url' => htmlspecialchars($post_url_online),
            ]
          );
          $this->messenger()->addMessage($the_message);
        }

      } // END OF else

    }
    // END OF online.

    // -----------------------------------------------------------------------------------------------
    // Middleware post if ASU Online is NOT selected -- Added on 3/29/2022
    else {

      $comments = $values['comments'] ?? '';

      // Change "Readmission" to "Masters" on 3/26/2024.
      $studentType = $values['student_type_options_default'];
      if ($studentType == 'Readmission') {
        $studentType = 'Masters';
      }

      $submission_data = [
        'CitizenshipCountry' => $values['citizenship_country'] ?? '',
        'Street1' => $values['address'] ?? '',
        'City' => $values['city'] ?? '',
        'State' => $values['state_or_province'] ?? '',
        'Country' => $values['country'] ?? '',
        'Zip' => $values['postal_code'] ?? '',
        'BirthDate' => $date_of_birth_formatted,
        'MilitaryStatus' => $values['veteran_status_options'] ?? '',
        // 'Comments' => isset($values['comments']) ? $values['comments'] : '',
        'Comments' => $comments,
        'EmailAddress' => $values['email_address'] ?? '',
        'FirstName' => $values['first_name'] ?? '',
        'LastName' => $values['last_name'] ?? '',
        'Phone' => $phone_formatted,
        'EntryTerm' => $entryTerm_formatted,
        'GdprConsent' => $values['gdpr_consent'][0] ?? '',
        'Campus' => $campus_options,
        'Interest1' => $interest1,
        'Interest2' => $values['program_of_interest_text'] ?? '',
        'Career' => $grad_ugrad,
        'StudentType' => $studentType,
        'Source' => $sourceid,
        'URL' => $domain . '/' . $rfipage_alias,
        'datetime' => $webform_submission->getCreatedTime(),
        'enterpriseclientid' => $asuonline_enterpriseclientid,
        'ga_clientid' => $asuonline_enterpriseclientid,
      ];

      foreach ($submission_data as $key => $value) {
        if ($value == '') {
          unset($submission_data[$key]);
        }
      }
      $data = json_encode($submission_data);

      $curl = curl_init($post_url);
      // If you don't want to use any of the return information, set to false.
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      // Set this to false to remove informational headers.
      curl_setopt($curl, CURLOPT_HEADER, TRUE);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
      // Data mapping.
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      // This will set the security protocol to TLSv1.
      curl_setopt($curl, CURLOPT_SSLVERSION, 1);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json'
        ]);
      $response = curl_exec($curl);
      $info = curl_getinfo($curl);

      curl_close($curl);

      if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
        \Drupal::logger('asuaec_rfi')->notice('Middleware post failed.<pre>' . htmlspecialchars(print_r($response, TRUE)) . '<br />Middleware post URL: ' . $post_url . '</pre>');
        if ($env == 'prod') {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
        }
        else {
          $the_error = new TranslatableMarkup(
            'We are very sorry, error occurred while posting data. Please try again later.<pre>@response<br />Middleware post URL: @url</pre>',
            [
              '@response' => htmlspecialchars(print_r($response, TRUE)),
              '@url' => htmlspecialchars($post_url),
            ]
          );
        }
        $this->messenger()->addError($the_error);

      }
      else {
        \Drupal::logger('asuaec_rfi')->notice('Success - Middleware posted data:<pre><code>' . htmlspecialchars(print_r($submission_data, TRUE)) . '<br />Middleware post URL: ' . $post_url . '</code></pre>');
        if ($env == 'dev') {
          $the_message = new TranslatableMarkup(
            'Success: <pre>@response<br />Middleware posted data: @data<br />Middleware post URL: @url</pre>',
            [
              '@response' => htmlspecialchars(print_r($response, TRUE)),
              '@data' => print_r($submission_data, TRUE),
              '@url' => htmlspecialchars($post_url),
            ]
          );
          $this->messenger()->addMessage($the_message);
        }

      } // END OF else

    } // END of "NOT online"

  } // END OF public function postToMiddleware($webform_submission)

  /**
   * {@inheritdoc}
   *
   *  Post to middleware.
   *
   *  Check to see if session variable exists. It could be already existed. If not existed, create one.
   *
   *  Send confirmation email.
   *  **Online - We don't send confirmation email.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $env = $this->getEnv();

    // Posting.
    $this->postToMiddleware($webform_submission);

    // Check to see if session variable exists. It could be already existed. If not existed, create one.

    $sid = $webform_submission->id();
    $grad_ugrad = $webform_submission->getData()['grad_ugrad'];
    $plancode = $webform_submission->getData()['program_of_interest_text'];
    $interest = $webform_submission->getData()['interest1'];

    // Make sure if Session has the new $degree_data_array info.
    $session = \Drupal::request()->getSession();
    $degree_data_array = $session->get('asuaec_rfi.degree_data_array');
    if (is_null($degree_data_array) || is_null($degree_data_array['sid']) || ($degree_data_array['sid'] == '') || ($degree_data_array['sid'] != $sid)) {
      // Refresh session variable.
      $request = \Drupal::request();
      $session = $request->getSession();
      $session->remove('asuaec_rfi.degree_data_array');

      $theWebformConfirmationPage = new WebformConfirmationPage();
      if ($plancode != NULL) {
        $degree_data_array = $theWebformConfirmationPage->getDegreeData($plancode, $grad_ugrad, $sid);
      }
      elseif ($interest != NULL) {
        $degree_data_array = $theWebformConfirmationPage->getInterestData($interest, $grad_ugrad, $sid);
      }
    }
    // Session variable $degree_data_array is ready to use.

    // Send confirmation email.

    $campus_option = $webform_submission->getData()['campus_options'];
    if ($campus_option != 'ONLNE') {

      $mail_parts = $this->buildConfirmationEmailBody($webform_submission);

      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'asuaec_rfi';
      $key = 'rfi_conf_email';
      $to = $webform_submission->getData()['email_address'];

      $params['message'] = $mail_parts['body'];
      $params['subject'] = $mail_parts['subject'];

      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = TRUE;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

      if ($result['result'] !== TRUE) {
        $the_message = new TranslatableMarkup('There was a problem sending your message and it was not sent.', []);
        $this->messenger()->addMessage($the_message);
        \Drupal::logger('asuaec_rfi')->notice('Main RFI - Email error: Confirmation email was not sent. - Email address:' . htmlspecialchars($to));
      }
      else {
        if ($env == "dev") {
          $the_message = new TranslatableMarkup('Your confirmation email has been sent.', []);
          $this->messenger()->addMessage($the_message);
        }
      }
    }

    // Clear session variable: 'asuaec_rfi.degree_data_array.
    $request = \Drupal::request();
    $session = $request->getSession();
    $session->remove('asuaec_rfi.degree_data_array');

  } // END OF public function postSave()

  /**
   * Build confirmation email content.
   */
  public function buildConfirmationEmailBody($webform_submission) {
    // Get Ground or online.
    // First Time Freshman, Transfer or Readmission.
    $student_type = $webform_submission->getData()['student_type_options_default'];

    // Get Ugrad or Grad.
    $grad_ugrad = $webform_submission->getData()['grad_ugrad'];

    // Get Ground or Online.
    $campus_option = $webform_submission->getData()['campus_options'];

    // Came from Degree search -- It user came from Degree search, it will be always GROUND.
    $came_from_degree_search = $webform_submission->getData()['came_from_degree_search'];
    if ($came_from_degree_search == 'true') {
      $campus_option = "GROUND";
    }

    $session = \Drupal::request()->getSession();
    $degree_data_array = $session->get('asuaec_rfi.degree_data_array');
    // \Drupal::logger('asuaec_rfi')->notice("postSave - details array: " . print_r($degree_data_array, true));

    $mail_parts = [];
    $body = '';
    $new_output = '';
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $headerimg_href = 'https://admission.asu.edu/';
    $fname = $webform_submission->getData()['first_name'];

    $email_banner_image = '';
    $mail_parts['subject'] = '';
    $preheader_text = '';
    $designation = '<p style="margin-bottom:10px;">April Crabtree<br />Associate Vice President<br />Enrollment and Admission Services<br />Arizona State University<br />';

    // On Campus Grad.
    if (($campus_option == 'GROUND' && $grad_ugrad == 'GRAD') || ($campus_option == 'NOPREF' && $grad_ugrad == 'GRAD')) {
      $email_banner_image = t('https://admission.asu.edu/sites/default/files/grad_confirmation_600px.jpg');
      $mail_parts['subject'] = t('Thanks for your interest in ASU');
      $preheader_text = t('Learn more about ASU’s graduate programs.');
      $headerimg_href = 'https://admission.asu.edu/';

      $plan_url = '';
      $plan_url = $degree_data_array['plan_url'] ?? '';
      $degree_descr100 = $degree_data_array['descr100'] ?? '';

      //
      // Email body
      // .

      $new_output .= '<p style="margin-bottom:10px">Thank you for your interest in pursuing an advanced degree at <a style="color:#8C1D40;" href="https://www.asu.edu">Arizona State University</a>. ASU consistently <a style="color:#8C1D40;" href="https://www.asu.edu/rankings">ranks high</a> on multiple college rankings lists and is repeatedly named No. 1 in rankings that matter, such as sustainability, innovation and global impact. From engaging in meaningful, innovative research and entrepreneurial projects to collaborating with classmates and faculty, you will be elevating your career when you enroll in graduate studies at ASU.</p>';
      $new_output .= '<p style="margin-bottom:10px">With <a style="color:#8C1D40;" href="https://webapp4.asu.edu/programs/t5/graduate/false">more than 450 excellent graduate degree programs and certificates</a>, including new programs in emerging fields, there is a degree path here to fit your career goals.</p>';
      $new_output .= '<p style="margin-bottom:10px">Explore your area of interest: ';
      $new_output .= '<a style="color:#8C1D40;" href="' . $plan_url . '" >' . $degree_descr100 . '</a>';
      $new_output .= '. Take note of each degree’s requirements and continue to plan your next steps to ASU.</p>';
      $new_output .= '<p style="margin-bottom:10px">If you have any questions about a specific degree program, please <a style="color:#8C1D40;" href="mailto:gograd@asu.edu">contact us</a> or the academic department of the program you’re interested in. We are all happy to help you take the next step toward your graduate degree.</p>';
      $new_output .= '<p style="margin-bottom:10px">And when you’re ready, apply to ASU.</p>';
      $new_output .= '<span style="margin-bottom:10px;display:inline-block;background-color:#FFC627;border-radius:24px;font-weight:bold;padding:15px;"><a style="color:#000000;text-decoration:none;" href="https://webapp4.asu.edu/dgsadmissions/?_ga=2.168399073.1511473274.1613495072-1116271701.1613495072">Apply to ASU</a></span>';
      $new_output .= '<p style="margin-bottom:10px">Gold is going to look good on you. I hope to see you here soon.</p>';
      $new_output .= '<p style="margin-bottom:10px">Sincerely,</p>';
      $new_output .= $designation;
      $new_output .= '<a style="color:#8C1D40;" href="https://admission.asu.edu/graduate">admission.asu.edu/graduate</a></p>';

    } // END OF if($campus_option == 'GROUND' && $grad_ugrad == 'GRAD')

    // On Campus Ugrad.
    if (($campus_option == 'GROUND' && $grad_ugrad == 'UGRAD') || ($campus_option == 'NOPREF' && $grad_ugrad == 'UGRAD')) {
      $headerimg_href = 'https://admission.asu.edu/';
      $mail_parts['subject'] = t('Gold is going to look good on you');
      $preheader_text = t('Gold is going to look good on you.');
      $email_banner_image = t('https://admission.asu.edu/sites/default/files/2022-03/email_txtphb_goldisgoingtolookgoodonyou.jpeg');

      // International.
      $country_of_citizenship = $country_of_citizenship = $webform_submission->getData()['citizenship_country'];
      if ($country_of_citizenship != 'US') {
        $international = 'INT';
      }
      else {
        $international = 'USA';
      }

      //
      // If it came from the Degree Search --> If there is plan code or not.
      //
      $plan_code = $webform_submission->getData()['program_of_interest_text'] ?? '';
      if ($plan_code != '') {
        $plan_name = $degree_data_array['descr100'];
        $url = $degree_data_array['plan_url'];
        // BS.
        $degree = $degree_data_array['degree_descr_short'] ?? '';
        $plan_link = '<a style="color:#8C1D40;" href="' . $url . '">' . $plan_name . ', ' . $degree . '</a>';
        $link = $plan_link;
      }

      //
      // When the form doesn't have any URL parameters
      // (When user didn't come from the Degree Search)
      //
      else {
        $link = $degree_data_array['interest_linked'] ?? '';

      } // END of else: When the form doesn't have any URL parameters

      //
      // Change Ending signature depending on student type (Transfer or Freshman)
      //

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
      $new_output .= '<span style="margin-bottom:10px;display:inline-block;background-color:#FFC627;border-radius:24px;font-weight:bold;padding:15px;"><a style="color:#000000;text-decoration:none;" href="https://admission.asu.edu/apply">Apply to ASU</a></span>';
      $new_output .= '<p style="margin-bottom:10px"><strong>Gold is going to look good on you.</strong> I hope to see you here soon.</p>';
      $new_output .= '<p style="margin-bottom:10px">Sincerely,</p>';

      if ($international == 'INT') {
        $new_output .= $designation;
        $new_output .= '<a style="color:#8C1D40;" href="https://admission.asu.edu/international">admission.asu.edu/international</a></p>';
        $headerimg_href = 'https://admission.asu.edu/international';

        // USA.
      }
      else {

        // Transfer student.
        if ($student_type == 'Transfer') {
          $new_output .= $designation;
          $new_output .= '<a href="https://admission.asu.edu/transfer" style="color:#8C1D40;">admission.asu.edu/transfer</a></p>';
          $headerimg_href = 'https://admission.asu.edu/transfer';

          // Freshman.
        }
        elseif ($student_type == 'First Time Freshman') {
          $new_output .= $designation;
          $new_output .= '<a href="https://admission.asu.edu/freshman" style="color:#8C1D40;">admission.asu.edu/freshman</a></p>';
          $headerimg_href = 'https://admission.asu.edu/freshman';
        }
      }

    } // END OF if($submitted_data[74] == 'On Campus' && $submitted_data[70] != 'grad') {   --> In other words, On Campus Ugrad

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
        <img src='https://admission.asu.edu/sites/default/files/asu_logo_230px.png' width='230' height='auto' alt='Arizona State University' style='border-collapse: collapse; text-align: left; font-size:22px; color:#ffc627; font-family: Arial, sans-serif; padding: 0px; line-height: 24px; font-weight: bold;'>
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

    $mail_parts['body'] = $body;
    return $mail_parts;
  }

  // phpcs:disable
  /**
   * Helping function.
   *
   * Format Entry term to be such as "2020 Fall"
   */
  public function getEntryTerm_label($entryterm_code) {
    // Start term.
    // 2197.
    $start_term = $entryterm_code;
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
    // 2020 Fall
    return substr($start_term, 0, 1) . '0' . substr($start_term, 1, 2) . ' ' . $semester;
  }
  // phpcs:enable

  /**
   * Helping function.
   *
   * Validate birthdate.
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
   * {@inheritdoc}
   *
   *  Perform validation when "Next" button was pressed.
   *  Post to middleware when "Submit" button was pressed.
   *  ** It seems that when "Previous" button was pressed, this function will not be called.
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $env = $this->getEnv();
    $values = $webform_submission->getData();
    $the_submit_handler = $form_state->getSubmitHandlers();

    if ($the_submit_handler[0] == "::submit") {

      // BirthDate
      // We will post birthdate hidden field.
      // 2000-01-02.
      $date_of_birth = $values['date_of_birth'] ?? '';
      // Validate birthdate - Added on 4/18/2022.
      if (!$this->validateBirthdate($date_of_birth)) {
        // Throw error if birthdate is older than 1900.
        $the_error = new TranslatableMarkup('Please check birthdate.', []);
        $this->messenger()->addError($the_error);
        $form_state->setRebuild();
        return;
      }

    }
    // END OF  if($the_submit_handler[0] == "::submit")

    // Perform validation.
    // When "Next" button is pressed.
    else {
      $storage = $form_state->getStorage();
      $current_page = $storage['current_page'];

      // Step 1.
      if ($current_page == 'step_1') {

        if (isset($values['campus_options'])) {
          if ($values['campus_options'] == '0') {
            $form_state->setErrorByName('campus_options', $this->t('Please select campus option.'));
          }
        }

        if (isset($values['student_type_options_default'])) {
          if ($values['student_type_options_default'] == '0') {
            $form_state->setErrorByName('student_type_options_default', $this->t('Please select student type.'));
          }
        }

        // Area of interest.
        if ($values['campus_options'] == "GROUND" || $values['campus_options'] == "NOPREF") {
          if ($values['student_type_options_default'] == "First Time Freshman" || $values['student_type_options_default'] == "Transfer") {
            if ($values['area_of_interest_ugrad'] == '0') {
              $form_state->setErrorByName('area_of_interest_ugrad', $this->t('Please select area of interest.'));
            }
          }
          if ($values['student_type_options_default'] == "Readmission") {
            if ($values['area_of_interest_grad'] == '0') {
              $form_state->setErrorByName('area_of_interest_grad', $this->t('Please select area of interest.'));
            }
          }
        }
        elseif ($values['campus_options'] == "ONLNE") {
          if ($values['student_type_options_default'] == "First Time Freshman" || $values['student_type_options_default'] == "Transfer") {
            if ($values['area_of_interest_ugrad_online'] == '0') {
              $form_state->setErrorByName('area_of_interest_ugrad_online', $this->t('Please select area of interest.'));
            }
          }
          elseif ($values['student_type_options_default'] == "Readmission") {
            if ($values['area_of_interest_grad_online'] == '0') {
              $form_state->setErrorByName('area_of_interest_grad_online', $this->t('Please select area of interest.'));
            }
          }
        }

        // Program of interest.
        if ($values['campus_options'] == "ONLNE") {
          if ($values['student_type_options_default'] == "Readmission") {
            if ($values['area_of_interest_grad_online'] != "0") {
              if ($values['program_of_interest_text'] == '' || $values['program_of_interest_text'] == '0') {
                $form_state->setErrorByName('program_of_interest_text', $this->t('Please select program of interest.'));
              }
            }
          }
          else {
            if ($values['area_of_interest_ugrad_online'] != "0") {
              if ($values['program_of_interest_text'] == '' || $values['program_of_interest_text'] == '0') {
                $form_state->setErrorByName('program_of_interest_text', $this->t('Please select program of interest.'));
              }
            }
          }
        }
        elseif ($values['campus_options'] == "GROUND" || $values['campus_options'] == "NOPREF") {
          if ($values['student_type_options_default'] == "Readmission") {
            if ($values['area_of_interest_grad'] != "0") {
              if ($values['program_of_interest_text'] == '' || $values['program_of_interest_text'] == '0') {
                $form_state->setErrorByName('program_of_interest_text', $this->t('Please select program of interest.'));
              }
            }
          }
        }

      }
      // END OF if($current_page == 'step_1')
      // Step 2.
      elseif ($current_page == 'step_2') {
        // \Drupal::logger('asuaec_rfi')->notice('Step2!!!: ' . $values['ground_online']);
        // Entry term for Ground grad
        if (isset($values['entry_term_text'])) {
          // Changed on 5/10/2022.
          if (($values['campus_options'] == "GROUND" && $values['student_type_options_default'] == "Readmission") || ($values['grad_ugrad'] == "GRAD" && $values['came_from_degree_search'] == "true")) {
            // \Drupal::logger('asuaec_rfi')->notice('entry_term_text:' . $values['entry_term_text']);.
            if ($values['entry_term_text'] == '' || $values['entry_term_text'] == '0') {
              $form_state->setErrorByName('entry_term_text', $this->t('Please select start term.'));
            }
          }
        }

      } // END OF // END OF if($current_page == 'step_2')

    } // END OF if(($the_submit_handler[0] == "::next") || ($the_submit_handler[0] == "::prev"))

  } // END OF public function validateForm

}
