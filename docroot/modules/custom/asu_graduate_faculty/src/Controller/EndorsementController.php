<?php

namespace Drupal\asu_graduate_faculty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\asu_graduate_faculty\GraduateQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EndorsementController extends ControllerBase {

  protected $graduateQueryService;

  public function __construct(GraduateQueryService $graduateQueryService) {
    $this->graduateQueryService = $graduateQueryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asu_graduate_faculty.graduate_query_service')
    );
  }
  
  public function getInfo($eid) {
    // Perform your query based on $endorsementId
    $data = $this->graduateQueryService->getDegreeFromEid($eid);

    // Return JSON response
    return new JsonResponse($data);
  }
}
