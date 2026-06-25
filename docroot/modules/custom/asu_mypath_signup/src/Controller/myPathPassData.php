<?php

namespace Drupal\asu_mypath_signup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class myPathPassData extends ControllerBase {

  /**
   * Handles form data encryption and constructs redirect URL.
   */
  public function FormRedirectController(Request $request) {

    // Get the POSTed data (from React form)
    $data = json_encode(json_decode($request->getContent(), TRUE));
    if (!$data) {
      return new JsonResponse(['error' => 'No data received'], 400);
    }

    // Check if global variable for api key is set. If set, use that key instead of admin config form page.
    if (isset($GLOBALS['gardens_site_settings']['flags']['mypath_api_key'])) {
      $secret_key = $GLOBALS['gardens_site_settings']['flags']['mypath_api_key'];
    }
    else {
      // Get the secret key from config (set in a custom config form)
      $secret_key_base64 = \Drupal::config('asu_mypath_signup.settings')->get('mypath_api_key');
      $secret_key = $secret_key_base64 ? base64_decode($secret_key_base64) : '';
    }

    if (empty($secret_key)) {
      \Drupal::logger('asu_mypath')->error('MyPath encryption key is not configured. Check asu_mypath_signup settings or the mypath_api_key flag.');
      return new JsonResponse(['error' => 'Encryption key not configured'], 500);
    }

    $encrypted_payload = $this->encryptFormData($data, $secret_key);
    $domain = 'https://' . $_SERVER['HTTP_HOST'];

    if ($domain == 'https://admissionasu-asufactory1.acquia.asu.edu' || $domain == 'https://admission.asu.edu') {
      $env = 'prod';
      $redirect_url = 'https://transferguide.apps.asu.edu/app/rfi?data=';
    }
    else {
      $env = 'dev';
      $redirect_url = 'https://transferguide-qa.apps.asu.edu/app/rfi?data=';
    }

    // Construct external redirect URL.
    $redirect_external_url = $redirect_url . urlencode($encrypted_payload);
    //dpm($redirect_external_url, 'Redirecting to external URL:');
    return new JsonResponse(['redirect' => $redirect_external_url]);

  }

  /**
   * Encrypts form data.
   *
   * @param array $formData
   *   The data to encrypt.
   *
   * @return string
   *   The encrypted string.
   */
  private function encryptFormData($formData, $secretKey) {
    // Convert the key and IV to binary from UTF-8.
    // Use first 16 bytes.
    $key = mb_substr($secretKey, 0, 16, '8bit');
    $iv = $key;

    // Ensure $formData is a string.
    if (!is_string($formData)) {
      $formData = json_encode($formData);
    }

    // Encrypt using AES-128-CBC (16-byte key)
    $encrypted = openssl_encrypt(
        $formData,
        'AES-128-CBC',
        $key,
    // To get raw binary output.
        OPENSSL_RAW_DATA,
        $iv
    );

    // Return base64 encoded encrypted string.
    return base64_encode($encrypted);
  }

  // Not used currently.
  /* private function decryptFormData($encryptedData, $secretKey) {
  $key = mb_substr($secretKey, 0, 16, '8bit');
  $iv = $key;

  // Decode base64
  $ciphertext = base64_decode($encryptedData, true);
  if ($ciphertext === false) {
  return null;
  }

  // Decrypt
  $decrypted = openssl_decrypt(
  $ciphertext,
  'AES-128-CBC',
  $key,
  OPENSSL_RAW_DATA,
  $iv
  );

  // If the original data was JSON, decode it back to an array
  $jsonDecoded = json_decode($decrypted, true);
  if (json_last_error() === JSON_ERROR_NONE) {
  return $jsonDecoded;
  }

  // Otherwise, return as plain string
  return $decrypted;
  } */

}
