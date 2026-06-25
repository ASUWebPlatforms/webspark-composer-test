<?php

namespace Drupal\uoeee_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Public Outcomes Resource
 *
 * @RestResource(
 *   id = "public_outcomes",
 *   label = @Translation("Public Outcomes Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/publicoutcomes"
 *   }
 * )
 */

class PublicOutcomes extends ResourceBase {
  /**
   * Responds to entity GET requests.
   */
  public function get() {

    $query = "SELECT ID, acadplan, element, outcome, `description` FROM pa_assessmentplans_public WHERE user NOT LIKE 'replaced'" ;
    $result =  \Drupal::database()->query($query);
    $outcomes = $result->fetchAll(\PDO::FETCH_ASSOC) ;
    return new ResourceResponse($outcomes);
  }

}
