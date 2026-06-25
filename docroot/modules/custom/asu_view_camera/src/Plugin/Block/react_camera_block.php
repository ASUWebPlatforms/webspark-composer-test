<?php

namespace Drupal\asu_view_camera\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


/**
 @file
 * Contains \Drupal\asu_mypath_signup\Plugin\Block\react_camera_block
 */






/**
 * Provides a Camera block.
 *
 * @Block(
 *   id = "react_camera_block",
 *   admin_label = @Translation("React ASU Cameras form block"),
 *   category = @Translation("React ASU Cameras form block"),
 * )
 */
class react_camera_block extends BlockBase {

   /**
   * {@inheritdoc}
   */
  public function build() {
   $build = [];
   $build['react_mapp_block'] = [
     '#markup' => '<div id="camera-div"></div>',
     '#attached' => [
       'library' => 'asu_view_camera/my-app',
     ],
   ];
   return $build;
 }
}