<?php

namespace Drupal\asu_mypath_signup\Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a route controller for generating JSON pages.
 */
class MyPathRedirectController extends ControllerBase {

  /**
   *
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function redirectToMyPathExternalSite(Request $request) {
    static $x = 0;
    // Get data from the request sent by the React app.
    $values = json_decode($request->getContent(), TRUE);

    if (!empty($values)) {
      // Construct the redirect URL with the query parameters.
      $query = http_build_query($values);

      if (($values['online'] == 'N') && ($values['local'] == 'N')) {
        $campus = "GROUND";
        $fieldName = 'Interest2';
      }
      else {
        $campus = "ONLNE";
        $fieldName = "program_key";
      }

      // Source ID and Post URL switch depending on environment.
      $domain = 'https://' . $_SERVER['HTTP_HOST'];
      $env = 'prod';
      if ($domain == 'https://admissionasu-asufactory1.acquia.asu.edu/' || $domain == 'https://admission.asu.edu') {
        $env = 'prod';
        $sourceid = '7016T0000020CzZQAU';
        // $post_url = 'https://crm-enterprise-rfi-forms-submit-handler-prod.apps.asu.edu/'; //<--- Old posting URL
        // <--- New posting URL
        $post_url = 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/rfi';
      }
      else {
        $env = 'dev';
        $sourceid = '7016T000002c8qMQAQ';
        // $post_url = 'https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/'; //<--- New posting URL
        $post_url = 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/rfi';
      }

      $currentTimestamp = time();

      // Format the timestamp as a human-readable date and time.
      $submittedTime = date('Y-m-d H:i:s', $currentTimestamp);
      $phone = $values['phone'] ?? '';
      // Remove "+" and "-".
      $phone_formatted = preg_replace('[\D]', '', $phone);
      $term = $values['entryTerm'];
      // ksm($term);
      $config = \Drupal::config('asu_mypath_signup.settings');
      $asuRite = $config->get('asurite') ? $config->get('asurite') : '';
      $emplid = $config->get('emplid') ? $config->get('emplid') : '';

      $submission_data = [
        'EmailAddress' => $values['email'] ?? '',
      // Added trim() on 8/23/2022.
        'FirstName' => isset($values['firstName']) ? trim($values['firstName']) : '',
      // Added trim() on 8/23/2022.
        'LastName' => isset($values['lastName']) ? trim($values['lastName']) : '',
        'Phone' => $phone_formatted,
        'GdprConsent' => 1,
        'Campus' => $campus,
           // 'Interest1' => isset($values['sfMajor']) ? $values['sfMajor'] : '',
        'Interest2' => $values['sfMajor'] ?? '',
        'Career' => 'UGRAD',
        'EntryTerm' => $term,
        'StudentType' => 'Transfer',
        'Source' => $sourceid,
        'URL' => $domain . '/mypath',
        'datetime' => $submittedTime,
        'Zip' => $values['zipCode'] ?? '',
        'schoolInfo' => $values['institution'] ?? '',
        'enterpriseclientid' => $values['enterpriseclientid'] ?? '',
        'ga_clientid' => $values['enterpriseclientid'] ?? '',
        'ip_address' => \Drupal::request()->getClientIp(), //Added on 6/22/26
       // 'ASURITE' => $asuRite,
       // 'EMPLID' => $emplid,
        /* 'enterpriseclientid' => $asuonline_enterpriseclientid,
        'ga_clientid' => $asuonline_enterpriseclientid, */
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
        'Content-Type: application/json',
      ]);
      $response = curl_exec($curl);
      $info = curl_getinfo($curl);

      curl_close($curl);

      if (($info['http_code'] < 200) || ($info['http_code'] >= 300)) {
        \Drupal::logger('asu_mypath')->notice('Post failed.<pre><code>' . print_r($submission_data, TRUE) . '</code>' . print_r($response, TRUE) . '</pre>');
        if ($env == 'prod') {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.', []);
        }
        else {
          $the_error = new TranslatableMarkup('We are very sorry, error occured while posting data. Please try again later.<pre>' . print_r($response, TRUE) . '</pre>', []);
        }
        $this->messenger()->addError($the_error);

      }
      else {
        \Drupal::logger('asu_mypath')
          ->notice('Success - Posted data:<pre><code>' . print_r($submission_data, TRUE) . '</code></pre>');
        if ($env == 'dev') {
          $the_message = new TranslatableMarkup('Success: <pre>' . print_r($response, TRUE) . '<br />Posted data:' . print_r($submission_data, TRUE) . '<br />Post URL: ' . $post_url . '</pre>', []);
          $this->messenger()->addMessage($the_message);
          // ksm($submission_data);
          // ksm($info);
        }

      }
      // Save data in the database.
      $connection = Database::getConnection();
      $schema = $connection->schema();

      // Define your table name.
      $tableName = 'asu_mypath_data';
     
      // Check if the table already exists.
      if ($schema->tableExists($tableName)) {
        // Data to insert if table exists.
      
        $institute = $values['institution'] ?? '';
        $local_inst = $values['local'].'-'.$institute;
        $fields = [
          'EmailAddress' => $values['email'] ?? '',
        // Added trim() on 8/23/2022.
          'FirstName' => isset($values['firstName']) ? trim($values['firstName']) : '',
        // Added trim() on 8/23/2022.
          'LastName' => isset($values['lastName']) ? trim($values['lastName']) : '',
          'Phone' => $phone_formatted,
          'Campus' => $campus,
          'Interest2' => $values['sfMajor'] ?? '',
          'Career' => 'UGRAD',
          'EntryTerm' => $term,
          'Zipcode' => $values['zipCode'] ?? '',
          'online' => $values['online'] ?? '',
          //'local' => $values['local'] ?? '',
          'local' => $local_inst,
        // Current date for created_at.
          'created_at' => $submittedTime,
        ];
        
        // Insert the data into the asu_mypath_data table.
        $connection->insert('asu_mypath_data')
          ->fields($fields)
          ->execute();
      }

      return new JsonResponse(['message' => 'Data received successfully']);
    }
    else {
      return new JsonResponse(['message' => 'No Data received']);
    }

  }

}
