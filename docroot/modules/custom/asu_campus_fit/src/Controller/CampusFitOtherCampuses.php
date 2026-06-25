<?php

namespace Drupal\asu_campus_fit\Controller;

use Drupal\Core\Render\Markup;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides route responses for the custom module.
 */
class CampusFitOtherCampuses extends ControllerBase {

  /**
   * The Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new MyCustomController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function other_campuses_confirm_page($sid = NULL) {
    $nid = \Drupal::request()->query->get('nid');

    $config_data = \Drupal::config('asu_campus_fit.admin_settings');
    $combined_campus_intro = '';
    if (empty($nid)) {
      $webform = Webform::load('campus_fit');
      if ($webform->hasSubmissions()) {
        $query = \Drupal::entityQuery('webform_submission')
          ->condition('webform_id', 'campus_fit')
          ->condition('sid', $sid)
          ->accessCheck(FALSE);
        $result = $query->execute();
        $submission_data = [];
        foreach ($result as $item) {
          $submission = WebformSubmission::load($item);
          $submission_data = $submission->getData();

        }
        // ksm($submission_data);
      }
      $stype = \Drupal::request()->query->get('stype');
      if ($stype == "Earn an advanced degree (masters, PhD, etc.).") {
        $campus_array = ['Tempe' => 'Tempe', 'West' => 'West', 'Poly' => 'Poly', 'Downtown' => 'Downtown'];
      }
      else {
        // $campus_array = array('Tempe' => 'Tempe', 'West' => 'West', 'Poly' => 'Poly', 'Downtown' => 'Downtown', 'Havasu' => 'Havasu');
        $campus_array = ['Tempe' => 'Tempe', 'West' => 'West', 'Poly' => 'Poly', 'Downtown' => 'Downtown'];
      }
      // ksm($campus_array);
      $get_nid_data = \Drupal::service('getJsSettings')->getJsSettings($submission_data);
      $top_campus = $get_nid_data['top_campus'];
      // ksm($top_campus);
      $multiple = $get_nid_data['multiple_campuses'] ?? '';
      if ($multiple == "yes") {
        $multi_campuses = $get_nid_data['multiple_campus_names'];
        // ksm($multi_campuses);
        if (sizeof($multi_campuses) > 1) {
          $campuses_options = array_slice($multi_campuses, 0, 2);
        }
        else {
          $campuses_options = $campuses;
        }
        // ksm($campuses_options);
        foreach ($campuses_options as $single_campus) {
          unset($campus_array[$single_campus]);
        }
      }
      else {
        unset($campus_array[$top_campus]);
      }
      // ksm($campus_array);
      // unset($campus_array[$top_campus]);
      $div_top_container = '<div class="row pt-6 pbl-6 pbr-6 pb-6">';
      $config_data = \Drupal::config('asu_campus_fit.admin_settings');
      $div_container = "<div class='col-12 col-lg-4 px-space-xs px-md-space-md py-space-lg'><div class='row'><h3>Not quite what you're looking for?</h3><br />";
      foreach ($campus_array as $key => $other_campuses) {
        $campus_var = strtolower($other_campuses);
        $campus_intro_var = $campus_var . '_intro_content';
        $campus_intro[$key] = $config_data->get($campus_intro_var);
      }
      foreach ($campus_intro as $each_campus_intro) {
        $combined_campus_intro .= $each_campus_intro;
      }
      $div_container_end = "</div></div>";
      $div_right_campus_conatiner = '<div id="campus-rhs" class="right_campus_conatiner col-12 col-lg-8">&nbsp;</div></div>';
      $start_link = "<div><a class='btn btn-maroon btn-primary fit_email_result' href='/fit-quiz'>Start over</a></div><br />";
      $othr_campuses_content = $div_top_container . $div_container . $combined_campus_intro . $div_container_end . $div_right_campus_conatiner . $start_link;
      // ksm($othr_campuses_content);
      // ksm($top_campus);
      if (!empty($top_campus)) {
        // $body = \Drupal\Core\Render\RendererInterface::render($othr_campuses_content);
        // $body = $this->renderer->render($othr_campuses_content);
        $body = $othr_campuses_content;
      }
      else {
        $body = '';
      }
    }
    else {
      $body = '';
    }
    return [
      '#markup' => Markup::create($body),
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'asu_campus_fit/campusFitResults',
        ],
      ],

    ];

  }

}
