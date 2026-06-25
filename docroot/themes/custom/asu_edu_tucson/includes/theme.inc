<?php

/**
 * @file
 * Functions to support theming in the Asu.edu theme.
 */

/**
 * Implements hook_library_info_build().
 * Automatically creates components libraries (Not visible in theme's libraries.yml file)
 * This allows us to add js files/behaviours to components within the components own folder.
 * Note that the component should have the same name as the folder.
 *  {{ attach_library('renovation/block') }}.
 */
function asu_edu_tucson_library_info_build() {
  $extensions = ['css', 'js'];
  $theme_path = \Drupal::service('theme.manager')->getActiveTheme()->getPath();
  $directory = $theme_path . '/src/components';
  $extensions = array_map('preg_quote', $extensions);
  $extensions = implode('|', $extensions);
  if (!is_dir($directory)) {
    return [];
  }
  $file_scan = \Drupal::service('file_system')->scanDirectory($directory, "/{$extensions}$/");
  $libraries = [];
  foreach ($file_scan as $file) {
    $parts = explode('.', $file->filename);
    $extension = end($parts);
    switch ($extension) {
      case 'css':
        $libraries[$file->name][$extension] = [
          'component' => [
            '/' . $file->uri => [],
          ],
        ];
        break;

      case 'js':
        $libraries[$file->name][$extension] = [
          '/' . $file->uri => [],
        ];
        break;
    }
  }
  return $libraries;
}