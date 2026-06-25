<?php
/**
 * @file
 * Theme and preprocess functions for fields.
 */


/**
 * Add the Content Type title.
 * Implements hook_preprocess_field__FIELD_NAME().
 *
 * @param $variables
 *
 * @return void
 */
function analytics_preprocess_field__field_related_content(&$variables): void
{
  $element = $variables['element'];
  $variables['label'] = $element['#title'];
}

/**
 * Add the Content Type title.
 * Implements hook_preprocess_field__FIELD_NAME().
 *
 * @param $variables
 *
 * @return void
 */
function analytics_preprocess_field__field_related_resources(&$variables): void
{
  $element = $variables['element'];
  $variables['label'] = $element['#title'];
}
