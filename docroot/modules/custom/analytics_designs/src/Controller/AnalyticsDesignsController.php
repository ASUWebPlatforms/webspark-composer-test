<?php

namespace Drupal\analytics_designs\Controller;

use Drupal\Core\Controller\ControllerBase;

class AnalyticsDesignsController extends ControllerBase
{
  /**
   * Render the My Content page.
   * Add user here as the global {{ user }} passed to twig is a session proxy.
   *
   * @return array
   * @see /my-content
   */
  public function renderMyContent(): array
  {
    return [
      '#theme' => 'my_content',
    ];
  }
}
