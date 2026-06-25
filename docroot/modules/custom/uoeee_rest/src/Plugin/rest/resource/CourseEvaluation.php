<?php

namespace Drupal\uoeee_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


/**
 * Provides a Course Evaluation Resource
 *
 * @RestResource(
 *   id = "ce_api",
 *   label = @Translation("Course Evaluation Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/cesections/{period}"
 *   }
 * )
 * @Cache(
 *   max-age = 0
 * )
 */

class CourseEvaluation extends ResourceBase {
  /**
   * Responds to entity GET requests.
   */

  public function get($period) {

    $query = "SELECT
                s.section_id,
                s.sectioninfo,
                s.evaldepartment,
                s.period,
                l.action AS lastaction
              FROM CE_json_Sections s
              LEFT JOIN (
                  SELECT cl1.period, cl1.evaldepartment, cl1.action
                  FROM CE_Log cl1
                  INNER JOIN (
                      SELECT period, evaldepartment, MAX(id) AS max_id
                      FROM CE_Log
                      GROUP BY period, evaldepartment
                  ) cl2 ON cl1.period = cl2.period
                      AND cl1.evaldepartment = cl2.evaldepartment
                      AND cl1.id = cl2.max_id
              ) l ON s.evaldepartment = l.evaldepartment AND s.period = l.period
              WHERE s.period = '$period'";
    $result =  \Drupal::database()->query($query);
    $sections = $result->fetchAll(\PDO::FETCH_ASSOC);

    $response = new ResourceResponse($sections);
    $response->addCacheableDependency([
      '#cache' => [
        'max-age' => 0,
      ],
    ]);

    return $response;
  }

}
