<?php

namespace Drupal\asu_campus_fit\Controller;

use Drupal\Core\Render\Markup;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class CampusFitConfirmController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function campus_fit_confirm_page($sid = NULL, $url_node_id = NULL) {

    \Drupal::service('page_cache_kill_switch')->trigger();
    if (!empty(($sid))) {
      // Load webform submissions.
      $path = \Drupal::request()->getpathInfo();
      $all_ac_data = '';
      // $sid = \Drupal::request()->query->get('sid');
      $config_data = \Drupal::config('asu_campus_fit.admin_settings');
      $webform = Webform::load('campus_fit');
      if ($webform->hasSubmissions()) {
        $query = \Drupal::entityQuery('webform_submission')
          ->condition('webform_id', 'campus_fit')
          ->condition('sid', intval($sid))
          ->accessCheck(FALSE);
        $result = $query->execute();
        $submission_data = [];
        foreach ($result as $item) {
          $submission = WebformSubmission::load($item);
          $submission_data = $submission->getData();

        }

      }
      // ksm($submission_data);
      // code to create dynamic content bsaed on on campus and current status (2nd question) options.
      $current_status = htmlspecialchars($submission_data['what_are_you_doing_now_'], ENT_QUOTES, 'UTF-8');
      $mode_of_learning = htmlspecialchars($submission_data['how_would_you_rather_learn_'], ENT_QUOTES, 'UTF-8');
      $scholar = htmlspecialchars($submission_data['how_are_high_school_studies_going_'], ENT_QUOTES, 'UTF-8');
      if ($mode_of_learning == "on_campus-5q") {
        if ($current_status == "Studying at a community college or other university-4q") {
          $current_data = "<h3><span class='highlight-gold'>Tools for a smooth transfer to ASU</h3><p>MyPath2ASU™ offers a seamless transition from community college to ASU. Pick your ASU major and sign up for <a href='https://admission.asu.edu/transfer/MyPath2ASU'>MyPath2ASU™</a> and you'll get a customized plan that shows you exactly what courses you need to take to earn admission to your ASU major. By taking only classes that satisfy your major requirements, you'll save time and money on the path to your degree.</p>";

        }
        elseif ((($current_status == "I still have several years to go before I graduate high school-3q") || ($current_status == "Finishing up high school-3q")) && ($scholar == "Scholar, yo (3.50 or better GPA)-4q")) {
          $current_data = "<h3><span class='highlight-gold'>Opportunities for high-achieving students</h3><p>As a scholar, you'll be automatically considered for ASU's <a href='https://scholarships.asu.edu/estimator'>New American University Scholarship</a> upon admission to the university. You’re also encouraged to <a href='https://scholarships.asu.edu/scholarship-search'>browse private scholarships</a> and apply for any and all that you're eligible for. Once you apply to ASU, consider applying to <a href='https://barretthonors.asu.edu/'>Barrett, The Honors College</a>, a small living-learning community for academically outstanding students.</p>";
        }
        else {
          $current_data = '';
        }

      }
      // ksm($current_data);
      if (!empty($current_data)) {
        $current_full_data = "<div class='container'>" . $current_data . "<br /></div>";
        // $rendered_current_data =  \Drupal::service('renderer')->render($current_full_data);
        $rendered_current_data = $current_full_data;
      }
      else {
        $rendered_current_data = '';
      }

      // Code to get dynamic content for resources accordion on results page from config variables based on submission data.
      $dynamic_options = ['what_excites_you_about_the_college_experience_check_all_that_app', 'what_are_you_the_most_nervous_about_check_all_that_apply_', 'what_excites_you_about_graduate_studies_check_all_that_apply_'];

      // If node id is not present in the url, the get node id from services file and print out the content.
      if (empty($url_node_id)) {
        unset($_SESSION['rfi_link']);
        $_SESSION['rfi_link'] = "https://admission.asu.edu/future-student-request?plan=&name=&prog=&college=&GraduateType=&source=";
        $get_nid_data = \Drupal::service('getJsSettings')->getJsSettings($submission_data);
        $nid = $get_nid_data['top_campus_nid'];
        if (!empty($nid)) {
          // Print content for multiple campuses if there is a match between campuses.
          $multiple_campuses = !empty($get_nid_data['multiple_campuses']) ? $get_nid_data['multiple_campuses'] : '';
          $campuses = $get_nid_data['multiple_campus_names'] ?? '';
          if ($multiple_campuses == "yes") {
            $campus_data = "<div class='container'><h1 class='btn-dark'>ASU has several options for you</h1><p>According to the answers you’ve provided, we think you'd love the $campuses[0] campus or $campuses[1] campus. <strong>For more information, select the campus you’d like to view</strong>.</p>";
            $campus_data .= "<div class='uds-grid-links two-columns'>";
            if (sizeof($campuses) > 2) {
              $campuses_options = array_slice($campuses, 0, 2);
            }
            else {
              $campuses_options = $campuses;
            }
            // ksm($campuses_options);
            foreach ($campuses_options as $key => $campus_info) {
              $campus_intro_var = strtolower($campus_info) . '_intro_content';
              $campus_content = $config_data->get($campus_intro_var);

              $more_link = "<span class='$nid[$campus_info]'><span class='button button--primary js-form-submit form-submit btn-maroon btn btn-primary duplicate_campus_link $campus_info'>View more</span></span>";
              $campus_data .= "<div class='$campus_info dynamic_campus'><p>$campus_content</p><p>$more_link</p></div>";
            }
            $campus_data .= "</div></div>";
          }
          else {
            $campus_data = '';
          }

          // Save score value in webform submission.
          $score = $get_nid_data['score_value'];
          if (!empty($score)) {
            $webform_submission = WebformSubmission::load($sid);
            $webform_submission->setElementData('score', $score);
            $webform_submission->save();
          }

          $node_id = array_values($nid);
          // ksm(gettype($node_id[0]));.
          $int_node_id = intval($node_id[0]);
          $node = Node::load($int_node_id);

          $builder = \Drupal::entityTypeManager()->getViewBuilder('node');
          $build = $builder->view($node, 'full');
          $output = "<div id='top_campus_node'>";
          $output .= \Drupal::service('renderer')->render($build);
          $output .= "</div>";
          $body = '';
          $body .= is_array($campus_data) ? \Drupal::service('renderer')->render($campus_data) : $campus_data;
          $body .= is_array($output) ? \Drupal::service('renderer')->render($output) : $output;
        }
        else {
          $body = '';
        }

      }
      else {
        $urlnid = $url_node_id;
        $node = Node::load($urlnid);
        $builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $build = $builder->view($node, 'full');
        $output = "<div id='top_campus_node'>";
        $output .= \Drupal::service('renderer')->render($build);
        $output .= "</div>";
        // $body = $output . $scholar_content . $military_content . $rendered_residency_content . $email_button . $confirmation_email_form . $footer_content;
        $body = $output;
      }

    }
    else {
      $body = '';
    }
    // ksm($_SESSION['rfi_link']);.
    return [
      '#markup' => Markup::create($body),
      '#cache' => [
        'max-age' => 0,
      ],

    ];

  }

}
