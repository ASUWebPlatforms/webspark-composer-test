<?php

namespace Drupal\asu_mypath_signup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\ClientInterface;

class BrightVerifyController extends ControllerBase {

  protected $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public static function create($container) {
    return new static($container->get('http_client'));
  }

  public function verify(Request $request) {
    $email = $request->query->get('email');
    $phone = $request->query->get('phone');

    // Validate input before relaying it to the BriteVerify API.
    if ($email && !\Drupal::service('email.validator')->isValid($email)) {
      return new JsonResponse(['status' => 'INVALID']);
    }
    if ($phone && !preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
      return new JsonResponse(['status' => 'INVALID']);
    }
    if (!$email && !$phone) {
      return new JsonResponse(['status' => 'INVALID']);
    }

    //dpm($request->query->all(), 'BrightVerify Request Query Parameters:');
    $query = array_filter(['email' => $email, 'phone' => $phone]);
    $briteVerifyUrl = 'https://bpi.briteverify.com/api/v1/fullverify';
    $url = $briteVerifyUrl . '?' . http_build_query($query);
    //dpm($url, 'BrightVerify API Request URL:');
    $env = \Drupal::config('asuaec_briteverify.settings')->get('environment') ?? 'prod';
    $apiKey = $env === 'prod' ? \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_prod') : \Drupal::config('asuaec_briteverify.settings')->get('briteverify_key_dev');
    //dpm($apiKey, 'Using API Key:'); 
    try {
      if (isset($email)) {
        $body = ['email' => $email];
        $variable = 'email';
      }
      if (isset($phone)) {
        $body = ['phone' => $phone];
        $variable = 'phone';
      }

      $response = $this->httpClient->request('POST', $url, [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
          'Authorization' => 'ApiKey: ' . $apiKey,
        ],
        'json' => $body,
        'http_errors' => FALSE,
      ]);

      if ($response->getStatusCode() !== 200) {
        return new JsonResponse(['status' => 'INVALID']);
      }

      $respObj = json_decode($response->getBody()->getContents());

      if (isset($respObj->$variable)) {
        $status = $respObj->$variable->status;
        $service_type = $respObj->$variable->service_type ?? NULL;

        // Check if the status is not 'INVALID'.
        $is_valid = strcasecmp($status, 'INVALID') !== 0 ? 'VALID' : 'INVALID';

        return new JsonResponse([
          'status' => $is_valid,
          'service_type' => $service_type,
        ]);
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('asuaec_mypath_briteverify')->error($e->getMessage());
    }

    return new JsonResponse(['status' => 'INVALID']);
  }

}