<?php

namespace Drupal\asuaec_briteverify\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;

/**
 * Validates email and phone fields using BriteVerify API.
 *
 * @WebformHandler(
 *   id = "asuaec_briteverify_webform_handler",
 *   label = @Translation("BriteVerify Email & Phone Validation"),
 *   category = @Translation("Validation"),
 *   description = @Translation("Validates up to 4 email fields and 4 phone fields using BriteVerify."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class BriteVerifyWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'email_fields' => [],
      'phone_fields' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['email_fields'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email field keys'),
      '#description' => $this->t('Enter up to 4 email field keys, one per line.'),
      '#default_value' => implode("\n", $this->configuration['email_fields']),
    ];

    $form['phone_fields'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Phone field keys with service type'),
      '#description' => $this->t('Enter up to 4 phone field keys in the format "phone_key|service_type_key", one per line.'),
      '#default_value' => implode("\n", $this->configuration['phone_fields']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $email_values = array_filter(array_map('trim', explode("\n", $form_state->getValue('email_fields'))));
    $this->configuration['email_fields'] = array_slice($email_values, 0, 4);

    $phone_values = array_filter(array_map('trim', explode("\n", $form_state->getValue('phone_fields'))));
    $this->configuration['phone_fields'] = array_slice($phone_values, 0, 4);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    
    // Prevent from running this function twice when there is validateForm in another handler that is attached to the Webform
    static $validated = FALSE;
    if ($validated) {
      return;
    }
    $validated = TRUE;

    $email_fields = $this->configuration['email_fields'];
    $phone_fields = $this->configuration['phone_fields'];

    // Validate emails
    foreach ($email_fields as $field) {
      $emails = $form_state->getValue($field);

      if ($emails) {
        $email_list = array_map('trim', explode(',', $emails));

        foreach ($email_list as $email) {
//          \Drupal::logger('asuaec_briteverify')->notice('Check return value:' . $this->validateEmail($email));
          if ($this->validateEmail($email) !== 'VALID') {
            $form_state->setErrorByName($field, $this->t('The email %email is invalid.', ['%email' => $email]));
          }
        }
      }
    }

    // Validate phones
    foreach ($phone_fields as $entry) {
      [$phone_field, $service_field] = explode('|', $entry) + [null, null];

      if ($phone_field && $service_field) {
        $phone = $form_state->getValue($phone_field);

        if ($phone) {
          $phone_formatted = preg_replace('/\D/', '', $phone);
          $response = $this->validatePhone($phone_formatted);

          if ($response['status'] !== 'VALID') {
            $form_state->setErrorByName($phone_field, $this->t('The phone number %phone is invalid.', ['%phone' => $phone]));
          } else {
            // Save the service type to the submission
            // If phone number is not US number, it is Valid, but service_type is not set.
            if(!empty($response['service_type'])) {
              $form_state->setValue($service_field, $response['service_type']);
            }
          }
        }
      }
    }
  }

  /**
   * Validate an email address with BriteVerify.
   */
  private function validateEmail($email) {
    try {
      // Load environment configuration
      $env = $this->getEnv(); // prod/dev

      // For non-PROD environments, allow test emails
      if (strcasecmp($env, 'prod') !== 0 && str_ends_with($email, '@test.asu.edu')) {
        return 'VALID';
      } else {
        // BriteVerify API details
        $briteVerifyURL = 'https://bpi.briteverify.com/api/v1/fullverify';
        $briteVerifyAPIKey = $this->getBriteVerifyApiKey($env);

        $jsonData = json_encode(['email' => $email]);
        $headers = [
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: ApiKey: ' . $briteVerifyAPIKey
        ];

        // Initialize curl for the HTTP POST request
        $ch = curl_init($briteVerifyURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//        \Drupal::logger('asuaec_briteverify')->notice('Response (BriteVerify email):' . print_r($response, true));

        if ($httpStatusCode !== 200) {
          \Drupal::logger('asuaec_briteverify')->notice('httpStatusCode is not 200s (BriteVerify)');
          return 'INVALID';
        }

        $respObj = json_decode($response);
        \Drupal::logger('asuaec_briteverify')->notice('respObj:' . print_r($respObj, true));

        if (isset($respObj->email)) {
          $status = $respObj->email->status;

//          if (strcasecmp($status, 'VALID') === 0 || strcasecmp($status, 'accept_all') === 0) {
//          if (strcasecmp($status, 'VALID') === 0 || strcasecmp($status, 'accept_all') === 0 || strcasecmp($status, 'unknown') === 0 ) { // Changed on 3/27/2025
          if (strcasecmp($status, 'INVALID') !== 0) {   

            
            \Drupal::logger('asuaec_briteverify')->notice('Returning VALID for email (BriteVerify) - email: ' . $email);
            return 'VALID';
          } else {
            \Drupal::logger('asuaec_briteverify')->notice('Returning INVALID for email (BriteVerify) - email: ' . $email);
            return 'INVALID';
          }
        }
      }
    } catch (Exception $ex) {
      \Drupal::logger('asuaec_briteverify')->notice('Error validating email (BriteVerify): <pre>' . $ex->getMessage() . '</pre>');
    }

//    // Return 'VALID' if there is an error
//    return 'VALID';
  }

  /**
   * Validate a phone number with BriteVerify.
   */
  private function validatePhone($phone) {
    // Check if it starts with 1 or not. There are no two-digit country codes starting with ‘1’ such as ‘11’ or ‘12’ under the NANP.
    if (strpos(trim($phone), '1') !== 0) { // Not US/Canada: phone number does not starts with 1.
//      \Drupal::logger('asuaec_visit')->notice('Does not starts with 1');
      return ['status' => 'VALID'];

    } else { // US/Canada
    
      try {
        $env = $this->getEnv();
        $briteVerifyURL = 'https://bpi.briteverify.com/api/v1/fullverify';
        $briteVerifyAPIKey = $this->getBriteVerifyApiKey($env);

        $jsonData = json_encode(['phone' => $phone]);
        $headers = [
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: ApiKey: ' . $briteVerifyAPIKey,
        ];

        $ch = curl_init($briteVerifyURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatusCode !== 200) {
          return ['status' => 'INVALID'];
        }

        $respObj = json_decode($response);
        \Drupal::logger('asuaec_briteverify')->notice('respObj:' . print_r($respObj, true));

        if (isset($respObj->phone)) {
          $status = $respObj->phone->status;
          $service_type = $respObj->phone->service_type;

          // Check if the status is not 'INVALID'.
          $is_valid = strcasecmp($status, 'INVALID') !== 0 ? 'VALID' : 'INVALID';
          \Drupal::logger('asuaec_briteverify')->notice('Returning ' . $is_valid . ' for phone (BriteVerify) - phone: ' . $phone . ' service type: ' . $service_type);

          return [
            'status' => $is_valid,
            'service_type' => $service_type,
          ];
        }
      } catch (\Exception $e) {
        \Drupal::logger('asuaec_briteverify')->error($e->getMessage());
      }
    }
    // Return 'VALID' if there is an error
    return ['status' => 'VALID'];
  }

  /**
   * Get the BriteVerify API key.
   */
  private function getBriteVerifyApiKey($env) {
    return \Drupal::config('asuaec_briteverify.settings')->get("briteverify_key_{$env}");
  }

  /**
   * Get the current environment (prod/dev).
   */
  private function getEnv() {
    return \Drupal::config('asuaec_briteverify.settings')->get('environment') ?? 'prod';
  }
}