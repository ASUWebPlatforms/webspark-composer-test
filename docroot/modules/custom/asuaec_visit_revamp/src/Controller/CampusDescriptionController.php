<?php

namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns processed HTML from field_description on a campus_description_evolution_ node
 * matched by campus term (field_campus_tax) and visit bucket term (field_visit_bucket).
 *
 * Route: /visit-revamp-api/campus-description/{campus}/{interest}
 *   https://visit-asu-csdev60.ddev.site/visit-revamp-api/campus-description/20/28
 *   - campus: TID (taxonomy term ID for campus)
 *   - interest: TID (taxonomy term ID for visit bucket)
 *
 * Response: { "html": "<p>…</p>" }
 */
class CampusDescriptionController extends ControllerBase {

  private const NODE_TYPE = 'campus_description_evolution_';

  // Field machine names on the campus_description_evolution_ nodes:
  private const FIELD_CAMPUS_TAX  = 'field_campus_tax';     // term reference (campus)
  private const FIELD_BUCKET_TAX  = 'field_visit_bucket';   // term reference (visit bucket)
  private const FIELD_DESCRIPTION = 'field_description';    // text_long with processed

  /**
   * Controller callback.
   */
  public function getCampusDescription(string $campus, string $interest, Request $request): CacheableJsonResponse {
    $response = new CacheableJsonResponse(['html' => ''], 200);

    // Proper response cache controls.
    $response->setMaxAge(3600); // 1 hour
    $response->getCacheableMetadata()->setCacheContexts(['url.path', 'route']);

    $campus_tid   = (int) $campus;
    $interest_tid = (int) $interest;

    // Basic validation
    if ($campus_tid <= 0 || $interest_tid <= 0) {
      $response->setMaxAge(60);
      return $response;
    }

    // Load the terms to attach as cache dependencies (optional but recommended).
    $term_storage = $this->entityTypeManager()->getStorage('taxonomy_term');
    if ($campus_term = $term_storage->load($campus_tid)) {
      $response->addCacheableDependency($campus_term);
    }
    if ($interest_term = $term_storage->load($interest_tid)) {
      $response->addCacheableDependency($interest_term);
    }

    // Find a published Campus Description (Evolution) node that matches both terms.
    $node_storage = $this->entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', self::NODE_TYPE)
      ->condition('status', 1)
      ->condition(self::FIELD_CAMPUS_TAX . '.target_id', $campus_tid)
      ->condition(self::FIELD_BUCKET_TAX . '.target_id', $interest_tid)
      ->range(0, 1);

    $nids = $query->execute();

    if (!empty($nids)) {
      $node = $node_storage->load(reset($nids));
      $response->addCacheableDependency($node);

      $processed = $node->get(self::FIELD_DESCRIPTION)?->processed ?? '';
      $response->setData(['html' => $processed]);

    }

    // (Optional) basic debug aid: append ?debug=1 to see what matched.
    if ($request->query->get('debug') === '1') {
      $response->setData($response->getData() + [
          '_debug' => [
            'campus_tid' => $campus_tid,
            'interest_tid' => $interest_tid,
            'found' => !empty($nids),
            'node_id' => !empty($nids) ? reset($nids) : null,
          ],
        ]);
      // Avoid long cache with debug data.
      $response->setMaxAge(0);
    }

    return $response;
  }

}
