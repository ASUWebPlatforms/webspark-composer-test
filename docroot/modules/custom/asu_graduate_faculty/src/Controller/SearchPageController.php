<?php

namespace Drupal\asu_graduate_faculty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\ConfigFormBase;

class SearchPageController extends ControllerBase {

  public function content() {

    $config = $this->config('asu_graduate_faculty.settings');

    return [
      '#theme' => 'asu_graduate_faculty_search_page',
      '#content' => $config->get('content'),
      '#form' => \Drupal::formBuilder()->getForm('Drupal\asu_graduate_faculty\Form\GraduateFacultyForm'),
    ];
  }
}
