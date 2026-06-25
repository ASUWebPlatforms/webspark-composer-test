<?php

namespace Drupal\asu_campus_fit\Plugin\Block;

use Drupal\Core\Render\Markup;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * @file
 * Contains \Drupal\asu_campus_fit\Plugin\Block\campusfit_asu_online_dynamic_block.
 */

/**
 * Provides a campus results block.
 *
 * @Block(
 *   id = "campusfit_asu_online_dynamic_block",
 *   admin_label = @Translation("Campusfit asu online dynamic content on the results page from the module"),
 *
 * )
 */
class campusfit_asu_online_dynamic_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Return $account->hasPermission('search content');.
    if (AccessResult::allowedIfHasPermission($account, 'access content')) {
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $sid_val = !empty(\Drupal::request()->query->get('sid')) ? \Drupal::request()->query->get('sid') : '';
    $nid = !empty(\Drupal::request()->query->get('nid')) ? \Drupal::request()->query->get('nid') : '';
    $degree_link = '';
    $me3_link = '';
    $online_m3link = '';
    $suo_content = '';
    if (!empty($nid)) {

      $webform = Webform::load('campus_fit');
      if ($webform->hasSubmissions()) {
        $query = \Drupal::entityQuery('webform_submission')
          ->condition('webform_id', 'campus_fit')
          ->condition('sid', intval($sid_val))
          ->accessCheck(FALSE);
        $result = $query->execute();
        $submission_data = [];
        foreach ($result as $item) {
          $submission = WebformSubmission::load($item);
          $submission_data = $submission->getData();

        }

      }
      
      // Online degrees dynamic content.
      if ((!empty($submission_data['what_s_your_top_interest_area_online']))) {
        unset($_SESSION['rfi_link']);
        $_SESSION['rfi_link'] = "https://asuonline.asu.edu/#asuo-rfi-section";
        $online_interest = htmlspecialchars($submission_data['what_s_your_top_interest_area_online'], ENT_QUOTES, 'UTF-8');
        $degree_type = htmlspecialchars($submission_data['asu_can_help_you_achieve_your_academic_goals_no_matter_what_they'], ENT_QUOTES, 'UTF-8');
        //dpm($online_interest, 'online interest');
        if ($degree_type == "bachelor degree-2q") {
          if($online_interest == "Arts, culture and society-14q"){
            $degree_link = "https://asuonline.asu.edu/study/arts-culture-degrees/";
          }
          if ($online_interest == "Business-14q") {
            $degree_link = "https://asuonline.asu.edu/study/business-degrees/";
          }
          if ($online_interest == "Education-14q") {
            $degree_link = "https://asuonline.asu.edu/study/education-degrees/";
          }
          if ($online_interest == "Engineering-14q") {
            $degree_link = "https://asuonline.asu.edu/study/engineering-degrees/";
          }
          if ($online_interest == "Health and nursing-14q") {
            $degree_link = "https://asuonline.asu.edu/study/health-and-nursing/";
          }
          if ($online_interest == "Law, compliance and public service-14q") {
            $degree_link = "https://asuonline.asu.edu/study/law-degrees-public-service/";
          }
          if ($online_interest == "Technology-14q") {
            $degree_link = "https://asuonline.asu.edu/study/technology-degrees/";
          }
          if ($online_interest == "Social sciences-14q") {
            $degree_link = "https://asuonline.asu.edu/study/social-sciences-degrees/";
          }
          if ($online_interest == "Science-14q") {
            $degree_link = "https://asuonline.asu.edu/study/science-degrees/";
          }
          if ($online_interest == "I'm not sure, exploratory-14q") {
            $degree_link = "https://asuonline.asu.edu/online-degree-programs/";
            $me3_link = "<div class='container'><p>Unsure what degree is right for you? Play the me3 game to find out.</p><p><a class='btn btn-maroon btn-primary' href='https://yourfuture.asu.edu/me3'><span class='text'>Take the me3 quiz</span></a>&nbsp;<a class='btn btn-maroon btn-primary fit_email_result' href='$degree_link'><span class='text'>Explore all degrees</span></a></p></div>";
          }
          if(empty($online_interest)){
            $degree_link = "https://asuonline.asu.edu/online-degree-programs/";
          }
          //dpm($degree_link, 'degree link inner');
        }

      }

      if ((!empty($submission_data['what_s_your_top_interest_area_online_grad_options']))) {
        unset($_SESSION['rfi_link']);
        $_SESSION['rfi_link'] = "https://asuonline.asu.edu/#asuo-rfi-section";
        $grad_online_interest = htmlspecialchars($submission_data['what_s_your_top_interest_area_online_grad_options'], ENT_QUOTES, 'UTF-8');
        $degree_type = htmlspecialchars($submission_data['asu_can_help_you_achieve_your_academic_goals_no_matter_what_they'], ENT_QUOTES, 'UTF-8');
        if ($degree_type == "advanced degree-21q") {
          if($grad_online_interest == "Art and design-onlres"){
            $degree_link = "https://asuonline.asu.edu/study/arts-culture-degrees/";
          }
          if ($grad_online_interest == "Business-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/business-degrees/";
          }
          if ($grad_online_interest == "Education-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/education-degrees/";
          }
          if ($grad_online_interest == "Engineering-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/engineering-degrees/";
          }
          if ($grad_online_interest == "Health and nursing-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/health-and-nursing/";
          }
          if ($grad_online_interest == "Humanities and arts-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/humanities-degrees/";
          }
          if ($grad_online_interest == "Technology-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/technology-degrees/";
          }
          if ($grad_online_interest == "Science-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/science-degrees/";
          }
          if ($grad_online_interest == "Social sciences-onlres") {
            $degree_link = "https://asuonline.asu.edu/study/social-sciences-degrees/";
          }
          if ($grad_online_interest == "I'm not sure-onlres") {
            $degree_link = "https://asuonline.asu.edu/online-degree-programs/";
            $me3_link = "<div class='container'><p>Unsure what degree is right for you? Play the me3 game to find out.</p><p><a class='btn btn-maroon btn-primary' href='https://yourfuture.asu.edu/me3'><span class='text'>Take the me3 quiz</span></a>&nbsp;<a class='btn btn-maroon btn-primary fit_email_result' href='$degree_link'><span class='text'>Explore all degrees</span></a></p></div>";
          }
        }
      }
      if ((empty($submission_data['what_s_your_top_interest_area_online']) && empty($submission_data['what_s_your_top_interest_area_online_grad_options']))) {
        $degree_link = "https://asuonline.asu.edu/online-degree-programs/";
      }
      if (!empty($me3_link)) {
        $online_m3link = $me3_link;
      }
      else {
        $online_m3link = "<div class='container'><p>Based on your answers, we've selected online programs to fit your needs:</p><a class='btn btn-maroon btn-primary fit_email_result' href='$degree_link'>view degrees</a></div>";
      }
     
      // Dynamic content for starbucks, uber or other partnerships content.
      $star_uber_options = 'did_you_know_asu_online_has_educational_partnerships_to_make_you';
      if (!empty($submission_data[$star_uber_options])) {
        unset($_SESSION['rfi_link']);
        $config_data = \Drupal::config('asu_campus_fit.admin_settings');
        $star_uber_sub = htmlspecialchars($submission_data[$star_uber_options], ENT_QUOTES, 'UTF-8');
        $suo_content = "<div class='container gray-1-bg' style='padding:8px;'>";
        if ($star_uber_sub == "Yes, I am an Uber/Uber Eats Driver or beneficiary.-onlres") {
          $suo_content .= $config_data->get('uber_content');
          $_SESSION['rfi_link'] = "https://uber.asu.edu/";
        }
        if ($star_uber_sub == "Yes, I am a Starbucks partner looking to participate in the Starbucks College Achievement Plan.-onlres") {
          $suo_content .= $config_data->get('starbucks_content');
          $_SESSION['rfi_link'] = "https://starbucks.asu.edu/#get-started";
        }
        if ($star_uber_sub == "Yes, I am employed by a company that has partnered with ASU Online.-onlres") {
          $suo_content .= $config_data->get('otherparterns_content');
        }
        $suo_content .= "</div>";
        if (empty($_SESSION['rfi_link'])) {
          $_SESSION['rfi_link'] = "https://asuonline.asu.edu/#asuo-rfi-section";
        }
      }
      else {
        $suo_content = '';
      }

    }

    $body = "<div style='border-bottom:none;border-top:none;padding:0px 10px;background-color:#ffffff;'>$online_m3link<br />$suo_content</div>";

    return [
      '#markup' => Markup::create($body),
      '#cache' => [
        'max-age' => 0,
      ],

    ];
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
