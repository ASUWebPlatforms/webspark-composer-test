<?php

namespace Drupal\asu_mypath_signup\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MidApiController extends ControllerBase {

   /**
   *
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */

public function fetch(Request $request): Response {
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    //dpm($uriSegment_array, 'URI Segments');
    $mid = end($uriSegment_array);
    $api_url = \Drupal::config('asu_mypath_signup.settings')->get('maricopa_validate_url');
    if (empty($api_url)) {
      \Drupal::logger('asu_mypath_signup_mcid')->error('maricopa_validate_url is not configured in asu_mypath_signup.settings.');
      return new JsonResponse(['success' => FALSE, 'message' => 'API URL not configured.'], 500);
    }
    //$username = getenv('MID_API_USERNAME');
    //$password = getenv('MID_API_PASSWORD');
    $username = 'MC_ASU_API_SRVC_ACCT';
    $settings = \Drupal::service('settings');
    if(strpos($_SERVER['HTTP_HOST'], '.ddev.site') !== FALSE) {
      $password = getenv('MID_API_PASSWORD');
    }
    else{
      $password = $settings->get('mid_api');
    }
    
    try {
      $response = \Drupal::httpClient()->request('GET', $api_url, [
        'auth' => [$username, $password],
        'query' => [
          'OPRID' => $mid,
        ],
        'headers' => [
          'Accept' => 'application/json',
        ],
        'timeout' => 15,
        'http_errors' => false,
      ]);

      $body = (string) $response->getBody();
      $content_type = $response->getHeaderLine('Content-Type') ?: 'text/plain';
      $success = false;
      $data = json_decode($body, true);
      //dpm($body, 'API Response Body');
      
      if(!empty($data['OPRID'])) {
        $success = true;
      }
      //dpm($success, 'MID validation success status');
      /*return new Response($body, $response->getStatusCode(), [
        'Content-Type' => $content_type,
      ]);*/
      return new JsonResponse([
        'success' => $success,
        'mid' => $data['OPRID'] ?? null,
      ]);

    }
    catch (\Exception $e) {
      \Drupal::logger('asu_mypath_signup_mcid')->error('SOAP request failed for MID @mid: @message', [
        '@mid' => $mid,
        '@message' => $e->getMessage(),
      ]);
      return new Response(
        'API request failed: ' . $e->getMessage(),
        500,
        ['Content-Type' => 'text/plain']
      );
    }
  }  

 public function postMaricopaData(Request $request): JsonResponse {
    $data = json_decode($request->getContent(), TRUE);
    //dpm($data, 'Data received for Maricopa API POST');
    // Here you would add code to post $data to the Maricopa API.
    // For now, we'll just return a success response.
    return new JsonResponse(['success' => true]);
  }
 
}