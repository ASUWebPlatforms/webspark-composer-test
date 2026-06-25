<?php

namespace Drupal\asu_mypath_signup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use GuzzleHttp\Exception\RequestException;

/**
 * Defines a route controller for generating JSON pages.
 */
class MyPathMaricopaPostData extends ControllerBase {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function postToMaricopaAPI(Request $request) {
    $values = json_decode($request->getContent(), TRUE);
    if (empty($values)) {
      return new JsonResponse(['message' => 'No Data received']);
    }

    $domain = 'https://' . $_SERVER['HTTP_HOST'];
    $env = ($domain === 'https://admissionasu-asufactory1.acquia.asu.edu' || $domain === 'https://admission.asu.edu') ? 'prod' : 'dev';
    $post_url = \Drupal::config('asu_mypath_signup.settings')->get('maricopa_api_url');

    $userName = 'MC_ASU_API_SRVC_ACCT';
    //$password = getenv('MID_API_PASSWORD');
    $settings = \Drupal::service('settings');
    if(strpos($_SERVER['HTTP_HOST'], '.ddev.site') !== FALSE) {
      $password = getenv('MID_API_PASSWORD');
    }
    else{
      $password = $settings->get('mid_api');
    }
    if (empty($userName) || empty($password)) {
      \Drupal::logger('asu_mypath_maricopa')->error('MID_API_USERNAME or MID_API_PASSWORD env vars are not set.');
      return new JsonResponse(['success' => FALSE, 'message' => 'API credentials missing.'], 500);
    }

    $MaricopaPassData = [
      'OPRID'        => $values['mid'] ?? NULL,
      'SIGN_UP_DT'   => date('Ymd'),
      'PROG_NAME'    => $values['programName'] ?? NULL,
      'COLLEGE_NAME' => $values['collegeName'] ?? NULL,
      'MODALITY'     => ($values['online'] ?? '') === 'Y' ? 'I' : 'O',
      'EFF_TERM'     => $values['entryTerm'] ?? NULL,
    ];

    $fullUrl = $post_url . '?' . http_build_query($MaricopaPassData);
    //dpm(['url' => $fullUrl, 'data' => $MaricopaPassData], 'Maricopa API Request');

    $effectiveUrl = '';
    try{
      $response = \Drupal::httpClient()->request('POST', $post_url, [
        'auth'        => [$userName, $password],
        'query'       => $MaricopaPassData,
        'timeout'     => 15,
        'http_errors' => FALSE,
        //'verify'      => FALSE,
        'on_stats'    => function (\GuzzleHttp\TransferStats $stats) use (&$effectiveUrl) {
            $effectiveUrl = (string) $stats->getEffectiveUri();
          },
      ]);

      $statusCode = $response->getStatusCode();
      $body = (string) $response->getBody();
     // $fullUrl = $post_url . '?' . http_build_query($MaricopaPassData);
      //dpm(['status' => $statusCode, 'body' => $body, 'url' => $fullUrl], 'Maricopa API Response');

      if ($statusCode >= 200 && $statusCode < 300) {
        \Drupal::logger('asu_mypath_maricopa')
          ->notice('Success - HTTP @code. Full URL: @url. Posted data:<pre><code>@payload</code></pre>', [
            '@code'    => $statusCode,
            //'@url'     => $fullUrl,
            '@payload' => print_r($MaricopaPassData, TRUE),
          ]);

        if ($env === 'dev') {
          $this->messenger()->addMessage(new TranslatableMarkup(
            'Success maricopa: <pre>@body<br />Posted data: @data<br />Post URL: @url</pre>',
            ['@body' => $body, '@data' => print_r($MaricopaPassData, TRUE), '@url' => $post_url]
          ));
        }

        return new JsonResponse(['success' => TRUE, 'message' => 'Data posted successfully']);
      }
      else {
        \Drupal::logger('asu_mypath_maricopa')->error('Post failed. HTTP @code. Full URL: @url. Response: @response. Payload: <pre>@payload</pre>', [
          '@code'     => $statusCode,
          '@url'      => $fullUrl,
          '@response' => $body,
          '@payload'  => print_r($MaricopaPassData, TRUE),
        ]);

        $errorMsg = $env === 'prod'
          ? 'We are very sorry, an error occurred while posting data. Please try again later.'
          : 'Post failed. HTTP ' . $statusCode . ': ' . $body;

        return new JsonResponse(['success' => FALSE, 'message' => $errorMsg], 500);
      }
    }
    catch (RequestException $e) {
      $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
      $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();

      \Drupal::logger('asu_mypath_maricopa')->error('Connection failed. HTTP @code. Error: @error. Payload: <pre>@payload</pre>', [
        '@code'    => $code,
        '@error'   => $e->getMessage(),
        '@payload' => print_r($MaricopaPassData, TRUE),
      ]);

      $errorMsg = $env === 'prod'
        ? 'We are very sorry, an error occurred while posting data. Please try again later.'
        : 'Connection failed. HTTP ' . $code . ': ' . $body;

      return new JsonResponse(['success' => FALSE, 'message' => $errorMsg], 500);
    }
  }

}
