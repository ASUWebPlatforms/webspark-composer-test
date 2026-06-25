<?php

namespace Drupal\asu_campus_fit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * @file
 * Contains \Drupal\asu_campus_fit\Plugin\Block\campusfit_other_campuses_block.
 */

/**
 * Provides a campus results block.
 *
 * @Block(
 *   id = "campusfit_other_campuses_block",
 *   admin_label = @Translation("Campusfit other campuses block on the results from the module"),
 *
 * )
 */
class campusfit_other_campuses_block extends BlockBase {

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
    /*$sid_val = !empty(\Drupal::request()->query->get('sid'))?\Drupal::request()->query->get('sid'):'';
    $controller_variable = new CampusFitOtherCampuses;
    $rendering_in_block = $controller_variable->other_campuses_confirm_page($sid_val);
    return $rendering_in_block; */
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $sid_val = $request->query->get('sid', '');
    // Access the CampusFitConfirmController service using \Drupal::service()
    $controller = \Drupal::service('asu_campus_fit.CampusFitOtherCampuses');

    // Call the method from the controller and get the content (ensure it's a renderable array)
    $rendered_content = $controller->other_campuses_confirm_page($sid_val);

    return $rendered_content;
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
