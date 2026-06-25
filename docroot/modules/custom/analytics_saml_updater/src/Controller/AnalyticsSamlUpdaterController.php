<?php

namespace Drupal\analytics_saml_updater\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

class AnalyticsSamlUpdaterController extends ControllerBase
{
  /**
   * Module settings page.
   *
   * @return array
   */
  public function settings(): array
  {
    return Drupal::formBuilder()->getForm('Drupal\analytics_saml_updater\Form\AnalyticsSamlUpdaterForm');
  }
}
