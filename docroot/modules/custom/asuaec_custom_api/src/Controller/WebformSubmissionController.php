<?php

namespace Drupal\asuaec_custom_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Core\Site\Settings;

class WebformSubmissionController {
  
  public function getSubmissionInterestOnly(Request $request, $sid) {
      // Load Webform Submission
      $submission = WebformSubmission::load($sid);
      if (!$submission) {
          return new JsonResponse(['error' => 'Not Found'], 404);
      }

      // Get submission data
      $data = $submission->getData();

      // Extract only the interest fields
      $response_data = [
          'interest' => $data['interest'] ?? null,
          'interest_name' => $data['interest_name'] ?? null,
      ];

      return new JsonResponse($response_data);
  }  
  
}