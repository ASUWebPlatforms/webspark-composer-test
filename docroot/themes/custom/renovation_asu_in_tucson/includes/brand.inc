<?php

use Drupal\Core\Url;

/**
 * Implements hook_preprocess_HOOK().
 */
function asu_edu_preprocess_asu_footer__footer_block(&$variables) {
    if (!empty($variables['unit_custom_logo'])) {
      $variables['unit_custom_logo'] = [
        '#type' => 'link',
        '#title' => $variables['unit_custom_logo'],
        '#url' => Url::fromUri('https://asu.edu'),
      ];
    }
  
    /* removing in favor of asu_footer module variable
    
    if (!empty($variables['src_footer_img'])) {
      $theme = \Drupal::theme()->getActiveTheme();
      $variables['src_footer_img'] = '/' . $theme->getPath(). '/img/footer-image.png';
    }
    
    */
}