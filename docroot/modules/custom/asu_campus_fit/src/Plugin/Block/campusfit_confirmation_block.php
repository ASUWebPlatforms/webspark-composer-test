<?php

namespace Drupal\asu_campus_fit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a Campusfit confirmation block.
 *
 * @Block(
 *   id = "campusfit_confirmation_block",
 *   admin_label = @Translation("Campusfit confirmation block from the module"),
 * )
 */
class campusfit_confirmation_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current request directly using \Drupal::service('request_stack')
    $request = \Drupal::service('request_stack')->getCurrentRequest();

    // Get query parameters from the URL.
    $sid = $request->query->get('sid', '11');
    $nid = $request->query->get('nid', '');

    // Access the CampusFitConfirmController service using \Drupal::service()
    $controller = \Drupal::service('asu_campus_fit.campus_fit_confirm_controller');

    // Call the method from the controller and get the content (ensure it's a renderable array)
    $rendered_content = $controller->campus_fit_confirm_page($sid, $nid);

    return $rendered_content;

    /*  // Ensure $rendered_content is a renderable array
    if (!is_array($rendered_content)) {
    $rendered_content = ['#markup' => $rendered_content];
    }

    // Use the renderer service to render the content
    $renderer = \Drupal::service('renderer');
    return $renderer->render($rendered_content);*/

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
