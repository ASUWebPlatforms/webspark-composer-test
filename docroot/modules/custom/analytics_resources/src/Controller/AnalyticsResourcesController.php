<?php

namespace Drupal\analytics_resources\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

class AnalyticsResourcesController extends ControllerBase
{
  /**
   * Module settings page.
   *
   * @return array
   */
  public function settings(): array
  {
    return Drupal::formBuilder()->getForm('Drupal\analytics_resources\Form\AnalyticsResourcesForm');
  }
}
