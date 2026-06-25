<?php
/**
 * @file
 * Theme and preprocess functions for menus.
 */

/**
 * Remove password and session IDs, since themes should not need nor see them.
 *
 * @param $variables
 *
 * @return void
 */
// function menu_template_preprocess_default_variables_alter(&$variables): void
// {
//   $user = Drupal::currentUser();
//   $variables['user'] = clone $user;

//   unset($variables['user']->pass, $variables['user']->sid, $variables['user']->ssid);
//   $variables['is_admin'] = $user->hasPermission('access administration pages');
//   $variables['logged_in'] = $user->isAuthenticated();
// }
