<?php

/**
 * @file
 * Functions to support theming in the Asu.edu theme.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements hook_preprocess_HOOK() for html.html.twig.
 * Creates a unique class for each page based on the path alias.
 */
function asu_edu_tucson_preprocess_html(&$variables) {
    /** @var \Drupal\asu_edu_tucson_components\Processor $processor */
    $processor = \Drupal::service('asu_edu_components.processor');
  
    $path_alias = $processor->getPathAlias();
  
    if (!empty($path_alias)) {
      $identifier = 'asu' . Html::cleanCssIdentifier($path_alias);
  
      $variables['attributes']['class'][] = $identifier;
    }

}

/**
 * Implements hook_page_attachments_alter().
 */
function asu_edu_tucson_page_attachments_alter(&$attachments) {
    if (\Drupal::service('path.matcher')->isFrontPage()) {
      $attachments['#attached']['library'][] = 'asu_edu_tucson/optimizely';
    }
}