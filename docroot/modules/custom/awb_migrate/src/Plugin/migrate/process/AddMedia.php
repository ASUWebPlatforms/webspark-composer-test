<?php

namespace Drupal\awb_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

use Drupal\awb_migrate\RemoteImage;

/**
 * Gets passed a image and/or a youtube video URL.
 *
 * If Youtube exists, that will be the featured media, otherwise it will be
 * image.
 *
 * @MigrateProcessPlugin(
 *   id = "add_media"
 * )
 */
class AddMedia extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $media_id = FALSE;
    $media_bundle = $this->configuration['bundle'] ?? 'image';
    $media_field = $this->configuration['field'] ?? 'field_media_image';

    if (!empty($value)) {
      $url = $value;
      $remote_image = new RemoteImage($media_bundle, $media_field);

      // Absolutify the URL if needed.
      if (strpos($url, '/') === 0) {
        $url = $this->configuration['remote_url_base'] . $url;
      }
      $media_id = $remote_image->SaveFromUrl($url, $this->configuration['file_dest_uri']);
    }

    // Return ID of created media entity.
    return $media_id;

  }

}
