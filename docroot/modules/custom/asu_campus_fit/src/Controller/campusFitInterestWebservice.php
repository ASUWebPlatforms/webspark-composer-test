<?php

namespace Drupal\asu_campus_fit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for generating JSON pages.
 */
class campusFitInterestWebservice extends ControllerBase {

  /**
   * Handler for JSON request.
   * Build JSON page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function campus_fit_degree_webservice(Request $request) {
    $requestUri = $request->getRequestUri();
    $uriVariables = [];
    $uriVariables = explode('/', $requestUri);
    $programType = $uriVariables[3];
    $interest = $uriVariables[4];
    $campus = $uriVariables[5];
    $newarray = [$programType, $interest, $campus];

    if ($campus != 'ONLINE') {
      $program_options = \Drupal::service('getOnCampusProgramList')->getOnCampusProgramList($programType, $interest, $campus);
    }
    if (($campus == "ONLINE")) {
      $program_options = \Drupal::service('getOnlineProgramList')->getOnlineProgramList($programType, $interest, $campus);

    }

    // $programJson = json_encode($program_options);
    // return $interestJson;*/
    return new JsonResponse($program_options);
    // Return $uriVariables;.
  }

}
