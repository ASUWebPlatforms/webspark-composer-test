<?php

namespace Drupal\sp_learningmod\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\sp_learningmod\Service\CouncilMeetingService;

class CouncilMeetingController extends ControllerBase
{

  protected $councilMeetingService;

  public function __construct(CouncilMeetingService $councilMeetingService)
  {
    $this->councilMeetingService = $councilMeetingService;
  }

  public static function create(ContainerInterface $container)
  {
    return new static($container->get('sp_learningmod.council_meeting_service'));
  }

  public function quizPage()
  {
    $form = \Drupal::formBuilder()->getForm('Drupal\sp_learningmod\Form\CouncilMeetingForm');
    return [
      '#theme' => 'council_meeting',
      '#form' => $form,
    ];
  }
}
