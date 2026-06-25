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
 * Contains \Drupal\asu_campus_fit\Plugin\Block\campusfit_asu_local_block.
 */

/**
 * Provides a campus results block.
 *
 * @Block(
 *   id = "campusfit_asu_local_block",
 *   admin_label = @Translation("Campusfit asu local dynamic content on the results from the module"),
 *
 * )
 */
class campusfit_asu_local_block extends BlockBase {

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
      $config_data = \Drupal::config('asu_campus_fit.admin_settings');
      $asu_local_option_array = ['which_of_these_locations_are_you_interested_in_living_and_learni', 'do_you_or_will_you_live_in_any_of_these_cities_'];
      $asu_local_next = htmlspecialchars($submission_data['online_option_asu_has_several_options_that_would_fit_you_select'], ENT_QUOTES, 'UTF-8');
      foreach ($asu_local_option_array as $asu_local_option) {

        if (!empty($submission_data[$asu_local_option])) {
          $_SESSION['rfi_link'] = "https://asulocal.asu.edu/#info";

          $asLocalOption = htmlspecialchars($submission_data[$asu_local_option], ENT_QUOTES, 'UTF-8');
          // ksm($asLocalOption);
          if (($asLocalOption == "Los Angeles, CA-8q") || ($asLocalOption == "Los Angeles, CA-12q")) {
            $asuLocalContent = $config_data->get('asuLocalLA');
            $_SESSION['local_title'] = '<h2><span class="highlight-gold">ASU Local</span><br />Los Angeles</h2>';
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Los Angeles</span><br />Los Angeles</h2>';
          }
          if (($asLocalOption == "Chula Vista, CA-9q")) {
            $asuLocalContent = $config_data->get('asuChulaVista');
            $_SESSION['local_title'] = '<h2><span class="highlight-gold">ASU Local</span><br />Chula Vista</h2>';
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Chula Vista</span><br />Chula Vista</h2>';
          }
          if (($asLocalOption == "Washington, D.C.-9q") || ($asLocalOption == "Washington, D.C.-12q") || ($asLocalOption == "Washington, DC-12q")) {
            $asuLocalContent = $config_data->get('asuLocalDc');
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Washington, D.C</span><br />Washington, D.C.</h2>';
          }
          if (($asLocalOption == "Yuma, AZ-9q") || ($asLocalOption == "Yuma, AZ-12q")) {
            $asuLocalContent = $config_data->get('asuLocalYuma');
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Yuma</span><br />Yuma</h2>';
          }
          if (($asLocalOption == "Long Beach, CA-9q")) {
            $asuLocalContent = $config_data->get('asuLongBeach');
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Long Beach</span><br />Long Beach</h2>';

          }
          if (($asLocalOption == "West Hawai-9q") || ($asLocalOption == "West Hawai-12q")) {
            $asuLocalContent = $config_data->get('asuWestHawai');
            $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; West Hawai\'i </span><br />West Hawai\'i</h2>';

          }
          /*if(($asLocalOption == "Lake Havasu City-30q") || ($asLocalOption == "Lake Havasu City-12q")){
          $asuLocalContent = $config_data->get('asuWestHavasu');
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Lake Havasu </span><br />Lake Havasu</h2>';
          }*/

          /*if(($asLocalOption == "Long Beach, CA-8q") || ($asLocalOption == "Long Beach, CA-12q")){
          $asu_local_next = $submission_data['online_option_asu_has_several_options_that_would_fit_you_select'];
          if($asu_local_next == "ASU Local-9q"){
          $asuLocalContent = $config_data->get('asuLongBeach');
          $h2_title = '<h2><span class="highlight-gold">ASU Long Beach</span><br />Long Beach</h2>';
          }
          }*/
          $asuLocalHtml = '<div style="border:1px solid #f1f1f1; padding:30px;background-color:#ffffff;"><div class="uds-highlighted-heading"><h4><span class="highlight-black">Your ASU fit is</span></h4></div><div class="uds-highlighted-heading">' . $h2_title . '</div><div class="asu-local-custom-text">';
          $asuLocalHtml .= $asuLocalContent;
          $asuLocalHtml .= '</div></div>';
        }

      }

      $asu_online_location = htmlspecialchars($submission_data['do_you_or_will_you_live_in_any_of_these_cities_'], ENT_QUOTES, 'UTF-8');

      if (!empty($asu_online_location)) {
        // ksm($asu_online_location);
        /*if(($asu_online_location == "Lake Havasu City-12q") || ($asu_online_location == "Long Beach, CA-12q") || ($asu_online_location == "West Hawai-12q")){
        $asu_local_hav = $submission_data['online_option_asu_has_several_options_that_would_fit_you_select'];
        if($asu_local_hav == "ASU Local-9q"){
        $asuLocalContent = $config_data->get('asuLongBeach');
        $h2_title = '<h2><span class="highlight-gold">ASU Long Beach</span><br />Long Beach</h2>';
        }
        $asuLocalHtml = '<div style="border:1px solid #f1f1f1; padding:30px;background-color:#ffffff;"><div class="uds-highlighted-heading"><h4><span class="highlight-black">Your ASU fit is</span></h4></div><div class="uds-highlighted-heading">'.$h2_title.'</div><div class="asu-local-custom-text">';
        $asuLocalHtml .= $asuLocalContent;
        $asuLocalHtml .= '</div></div>';
        }*/

        if (($asu_online_location == "Chula Vista, CA-9q")) {
          $asuLocalContent = $config_data->get('asuChulaVista');
          $_SESSION['local_title'] = '<h2><span class="highlight-gold">ASU Local</span><br />Chula Vista</h2>';
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Chula Vista</span><br />Chula Vista</h2>';
        }
        if (($asu_online_location == "Los Angeles, CA-8q") || ($asu_online_location == "Los Angeles, CA-12q")) {
          $asuLocalContent = $config_data->get('asuLocalLA');
          $_SESSION['local_title'] = '<h2><span class="highlight-gold">ASU Local</span><br />Los Angeles</h2>';
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Los Angeles</span><br />Los Angeles</h2>';
        }
        if (($asu_online_location == "Washington, D.C.-9q") || ($asu_online_location == "Washington, DC-12q")) {
          $asuLocalContent = $config_data->get('asuLocalDc');
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Washington, D.C</span><br />Washington, D.C.</h2>';
        }
        if (($asu_online_location == "Yuma, AZ-9q") || ($asu_online_location == "Yuma, AZ-12q")) {
          $asuLocalContent = $config_data->get('asuLocalYuma');
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Yuma</span><br />Yuma</h2>';
        }
        if (($asu_online_location == "Long Beach, CA-12q")) {
          $asuLocalContent = $config_data->get('asuLongBeach');
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Long Beach</span><br />Long Beach</h2>';

        }
        if (($asu_online_location == "West Hawai-9q") || ($asu_online_location == "West Hawai-12q")) {
          $asuLocalContent = $config_data->get('asuWestHawai');
          $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; West Hawai\'i </span><br />West Hawai\'i</h2>';

        }
        /*if(($asu_online_location == "Lake Havasu City-30q") || ($asu_online_location == "Lake Havasu City-12q")){
        $asuLocalContent = $config_data->get('asuWestHavasu');
        $h2_title = '<h2><span class="highlight-gold">ASU Local &mdash; Lake Havasu </span><br />Lake Havasu</h2>';
        }*/
        $asuLocalHtml = '<div style="border:1px solid #f1f1f1; padding:30px;background-color:#ffffff;"><div class="uds-highlighted-heading"><h4><span class="highlight-black">Your ASU fit is</span></h4></div><div class="uds-highlighted-heading">' . $h2_title . '</div><div class="asu-local-custom-text">';
        $asuLocalHtml .= $asuLocalContent;
        $asuLocalHtml .= '</div></div>';
      }

    }
    else {
      $asuLocalHtml = '';

    }

    $body = $asuLocalHtml;
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
