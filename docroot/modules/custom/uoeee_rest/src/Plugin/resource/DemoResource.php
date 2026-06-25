<?php

namespace Drupal\uoeee_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;




/**
 * Provides a Public Outcomes Resource
 *
 * @RestResource(
 *   id = "publicoutcomes",
 *   label = @Translation("Public Outcomes Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/publicoutcomes/{acadplan}"
 *   }
 * )
 */

class PublicOutcomes extends ResourceBase {
  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get($acadplan) {
    $outcomes = \Drupal::database()->select('pa_assessmentplans_public')
    ->fields(array(
      'ID', 'acadplan' ,'element','outcome','description'
    ))
    ->condition('acadplan', $acadplan, 'LIKE')
    ->execute();
    return new ResourceResponse($outcomes);
  }
}
