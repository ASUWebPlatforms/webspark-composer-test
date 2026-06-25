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
 * Contains \Drupal\asu_campus_fit\Plugin\Block\campusfit_multiple_campuses_results_block.
 */

/**
 * Provides a campus results block.
 *
 * @Block(
 *   id = "campusfit_multiple_campuses_results_block",
 *   admin_label = @Translation("Campusfit multiple campuses block on the results from the module"),
 *
 * )
 */
class campusfit_multiple_campuses_results_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if (AccessResult::allowedIfHasPermission($account, 'access content')) {
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // $sid_val = 11;
    $sid_val = !empty(\Drupal::request()->query->get('sid')) ? \Drupal::request()->query->get('sid') : '';
    // ksm($sid_val);
    $webform = Webform::load('campus_fit');
    if ($webform->hasSubmissions()) {
      $query = \Drupal::entityQuery('webform_submission')
        ->condition('webform_id', 'campus_fit')
        ->condition('sid', $sid_val)
        ->accessCheck(FALSE);
      $result = $query->execute();
      $submission_data = [];
      foreach ($result as $item) {
        $submission = WebformSubmission::load($item);
        $submission_data = $submission->getData();

      }
      // ksm($submission_data);
    }

    $get_nid_data = \Drupal::service('getJsSettings')->getJsSettings($submission_data);
    $multiple_campuses = $get_nid_data['multiple_campuses'];
    $config_data = \Drupal::config('asu_campus_fit.admin_settings');

    if ($multiple_campuses == "yes") {
      $nids = $get_nid_data['multiple_campus_nids'];
      $campuses = htmlspecialchars($get_nid_data['multiple_campus_names']);
      $campus_data = "<div class='container'><h1 class='btn-dark'>ASU has several options for you</h1><p>According to the answers you’ve provided, we think you’d love the $campuses[0] campus or $campuses[1] campus. <strong>For more information, select the campus you’d like to view</strong>.</p>";
      $campus_data .= "<div class='uds-grid-links two-columns'>";
      foreach ($campuses as $key => $campus_info) {
        $campus_intro_var = strtolower($campus_info) . '_intro_content';
        $campus_content = $config_data->get($campus_intro_var);

        $more_link = "<span class='$nids[$campus_info]'><span class='button button--primary js-form-submit form-submit btn-maroon btn btn-primary duplicate_campus_link'>View more</span></span>";
        $campus_data .= "<div class='$campus_info dynamic_campus'><p>$campus_content</p><p>$more_link</p></div>";
      }
      $campus_data .= "</div></div>";
      $rendering_in_block = $campus_data;
    }
    else {
      $rendering_in_block = '';
    }
    // Return $rendering_in_block;.
    return [
      '#markup' => Markup::create($rendering_in_block),
      '#cache' => [
        'max-age' => 0,
      ],

    ];
  }

  /*public function getCacheMaxAge() {
  return 0;
  }*/
}
