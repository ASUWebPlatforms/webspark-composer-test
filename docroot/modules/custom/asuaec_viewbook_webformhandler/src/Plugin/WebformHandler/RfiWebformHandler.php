<?php

namespace Drupal\asuaec_viewbook_webformhandler\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Exception;


/**
 * Form submission handler
 *
 * Source ID is set at line 667.
 *
 * @WebformHandler(
 *   id = "viewbook_webformhandler",
 *   label = @Translation("Viewbook: Post to middleware"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Viewbook: Post to middleware, display conf page and send conf email"),
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
  } // END OF public function submitForm


  /**
   * {@inheritdoc}
   *
   *  Check to see if session variable exists. It could be already existed. If not existed, create one.
   *
   *  Post to middleware when "Submit" button was pressed.
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {

    // Check environment
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $env = 'prod';
    if($domain == 'https://live-asu-myfuture.ws.asu.edu' || $domain == 'https://myfuture.asu.edu') {
      $env = 'prod';
    } else {
      $env = 'dev';
    }
    
    
    //----------------------------------------------------//    
    //---- Post to middleware ----------------------------//
    
    $values = $webform_submission->getData();
    $rfipage_alias = $_SERVER['REQUEST_URI'];

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

    // If it is Online, map Ground interest bucket into Onine interest bucket
    //    Business (Online)
    //      Business (Ground)
    //      Entrepreneurship (Ground)
    //    Education
    //      Education and teaching
    //    Engineering
    //      Engineering and technology
    //    Health and nursing
    //      Health and wellness
    //    Humanities and arts
    //      Architecture and construction
    //      Arts
    //      Humanities
    //      Interdisciplinary studies
    //    Law and public service
    //      Law, justice and public service
    //    Science
    //      Computing and mathematics
    //      Science
    //      Sustainability
    //    Social and behavioral sciences
    //      Social and behavioral sciences
    //    Technology
    //      Communication and media
    
    $interest1 = isset($values['interest1']) ? $values['interest1'] : '';
    /*
    $campus = isset($values['campus']) ? $values['campus'] : ''; // GROUND/ONLNE
    if($campus == 'ONLNE') {
      switch($interest1) {
        case 'Business':
        case 'Entrepreneurship':
          $interest1 = 'Business';
          break;
        case 'Education and Teaching':
          $interest1 = 'Education';
          break;
        case 'Engineering and Technology':
          $interest1 = 'Engineering';
          break;
        case 'Health and Wellness':
          $interest1 = 'Health and nursing';
          break;
        case 'Architecture and Construction':
        case 'Arts':
        case 'Humanities':
        case 'Interdisciplinary Studies':
          $interest1 = 'Humanities and arts';
          break;
        case 'Law, Justice and Public Service':
          $interest1 = 'Law and public service';
          break;
        case 'Computing and Mathematics':
        case 'Science':
        case 'Sustainability':
          $interest1 = 'Science';
          break;
        case 'Social and Behavioral Sciences':
          $interest1 = 'Social and behavioral sciences';
          break;
        case 'Communication and Media':
          $interest1 = 'Technology';
          break;
      }
    }
//    \Drupal::logger('asuaec_viewbook_webformhandler')->notice("interest1: " . $interest1);
    */

    // Post to middleware

    // Pass from config page: /admin/config/asuaec-viewbook-webformhandler.

    // Source ID and Post URL switch depending on environment
    // Get values from config
    $config_data = \Drupal::config('asuaec_viewbook_webformhandler.customadmin_settings');
    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $env = 'prod';
    if($domain == 'https://live-asu-myfuture.ws.asu.edu' || $domain == 'https://myfuture.asu.edu') {
      $env = 'prod';
      $post_url = $config_data->get('posturl_prod');
      $sourceid = $config_data->get('ground_sourceid_prod');
    } else {
      $env = 'dev';
      $post_url = $config_data->get('posturl_dev');
      $sourceid = $config_data->get('ground_sourceid_dev');
    }
    
    
    // We are not posting to Online. When it is Online, post it as NOPREF. (9/12/2024)
    $campus = isset($values['campus']) ? $values['campus'] : '';
    if ($campus == 'ONLNE') {
      $campus = 'NOPREF';
    }

    $submission_data = array(
      'CitizenshipCountry' => isset($values['citizenshipcountry']) ? $values['citizenshipcountry'] : '',
      'Street1' => isset($values['street1']) ? $values['street1'] : '',
      'City' => isset($values['city']) ? $values['city'] : '',
      //'State' => isset($values['state']) ? $values['state'] : '',
      'Country' => isset($values['country']) ? $values['country'] : '',
      'Zip' => isset($values['zip']) ? $values['zip'] : '',
      'BirthDate' => isset($values['birthdate']) ? $values['birthdate'] : '',
      'MilitaryStatus' => isset($values['militarystatus']) ? $values['militarystatus'] : '',
      'Comments' => isset($values['comments']) ? $values['comments'] : '',
      'EmailAddress' => isset($values['emailaddress']) ? $values['emailaddress'] : '',
      'FirstName' => isset($values['firstname']) ? $values['firstname'] : '',
      'LastName' => isset($values['lastname']) ? $values['lastname'] : '',
      'Phone' => isset($values['phone']) ? $values['phone'] : '',
      'EntryTerm' => isset($values['entryterm']) ? $values['entryterm'] : '',
      'GdprConsent' => isset($values['gdprconsent']) ? $values['gdprconsent'] : '',
      //'Campus' => isset($values['campus']) ? $values['campus'] : '',
      'Campus' => $campus,
      //'Interest1' => isset($values['interest1']) ? $values['interest1'] : '',
      'Interest1' => $interest1,
      'Interest2' => isset($values['interest2']) ? $values['interest2'] : '',
      //'Career' => isset($values['career']) ? $values['career'] : '',
      'Career' => 'UGRAD', // Hard coded it on 9/10/2024.
      'StudentType' => isset($values['studenttype']) ? $values['studenttype'] : '',
      'Source' => $sourceid,
      'URL' => $domain . $rfipage_alias . $utm,
      'datetime' => $webform_submission->getCreatedTime(),
      'enterpriseclientid' => isset($values['enterpriseclientid']) ? $values['enterpriseclientid'] : '',
      'ga_clientid' => isset($values['ga_clientid']) ? $values['ga_clientid'] : '',
    );
    
    

    foreach ($submission_data as $key => $value) {
      if ($value == '') {
        //if(($campus == 'ONLNE' && $key == 'Interest1') || ($campus == 'ONLNE' && $key == 'Interest2') ) {
        if(($key == 'Interest1') || ($key == 'Interest2')) {
          // Keep Interest1 with empty for Online
        } else {
          unset($submission_data[$key]);
        }
      }
    }

    $data = json_encode($submission_data);
//    ksm($submission_data, "submission data");

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

      //        \Drupal::logger('asuaec_viewbook_webformhandler')->notice('webform_submission:<pre><code>' . print_r($webform_submission, TRUE) . '</code></pre>');
      //            \Drupal::logger('asuaec_viewbook_webformhandler')->notice("submission data:<pre>" . print_r($submission_data, true) . "</pre>");
      //            \Drupal::logger('asuaec_viewbook_webformhandler')->notice("response:<pre>" . print_r($response, true) . "</pre>");
      //            \Drupal::logger('asuaec_viewbook_webformhandler')->notice("info:<pre>" . print_r($info, true) . "</pre>");

      if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
        \Drupal::logger('asuaec_viewbook_webformhandler')
          ->notice('Post failed.<pre>' . print_r($response, TRUE) . '</pre>');
        if ($env == 'prod') {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
        } else {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<pre>' . print_r($response, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
        }
        $this->messenger()->addError($the_error);

      } else {
        \Drupal::logger('asuaec_viewbook_webformhandler')
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
      \Drupal::logger('asuaec_viewbook_webformhandler')
        ->warning('Did not post because static variable x was not 0 - Post data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
      if ($env == 'dev') {
        $the_message = new TranslatableMarkup('Did not post because static variable x was not 0 - Posted data:<pre>' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
        $this->messenger()->addMessage($the_message);
      }
    } // END OF else
    
    
    
    //---- END OF Post to middleware ----------------------------//
    //-----------------------------------------------------------//
    
    

  } // END OF public function postSave()

} // END OF class RfiWebformHandler
