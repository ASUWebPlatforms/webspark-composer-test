<?php

namespace Drupal\awb_migrate;

use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;

/**
 * Class to manage pulling in of remote image.
 */
class RemoteImage {

  protected $bundle;
  protected $fieldName;

  public function __construct($bundle = 'image', $field_name = 'field_media_image') {
    $this->bundle = $bundle;
    $this->fieldName = $field_name;
  }

  /**
   * Utility string comparison function.
   */
  protected function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
      return TRUE;
    }
    return (substr($haystack, -$length) === $needle);
  }

  /**
   * Check for 200 HTTP code with curl.
   */
  protected function checkValidUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT,10);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return ($code == 200);
  }

  /**
   * Attempt to strip applied image style.
   *
   * Try to find the uncropped original upload.
   */
  protected function getBaseStyle($url) {
    $to_remove = [
      '_thumb',
      '_200x250',
      '_cropped',
      '_179x110',
    ];
    $parts = explode('.', $url);
    foreach ($to_remove as $r) {
      if ($this->endsWith($parts[count($parts) - 2], $r)) {
        $tmp = str_replace($r, '', $url);
        if ($this->checkValidUrl($tmp)) {
          return $tmp;
        }
      }
    }
    return $url;
  }

  /**
   * Given an img tag, parses out src and then saves the image locally.
   */
  public function saveFromImageTag($image_tag, $dest) {

    // Require an actual img tag.
    if (empty($image_tag)) {
      return FALSE;
    }

    // Patternmatch src attribute.
    if (preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $image_tag, $matches, PREG_OFFSET_CAPTURE)) {
      $remote_url = $matches[1][0];
    }
    else {
      return FALSE;
    }

    $alt = '';
    if (preg_match('/< *img[^>]*alt *= *["\']?([^"\']*)/i', $image_tag, $matches, PREG_OFFSET_CAPTURE)) {
      $alt = $matches[1][0];
    }

    return $this->saveFromUrl($remote_url, $alt, $dest);
  }

  /**
   * Save a media entity from a URL.
   */
  public function saveFromUrl($remote_url, $dest) {
    // Attempt to get a higher-resolution image.
    $remote_url = $this->getBaseStyle($remote_url);

    // Create or find local media entity for image.
    if ($local_media = $this->saveMediaLocally($remote_url, '', $dest)) {

      // Return ID of media entity.
      return $local_media->id();
    }
    return FALSE;
  }

  /**
   * Check server headers for mimetype of file .
   */
  private function getRemoteMimeType($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_exec($ch);

    // Get the content type.
    return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  }

  /**
   * Determine appropriate image extension given mime type.
   */
  private function getFileExtension($url) {
    $mime = $this->getRemoteMimeType($url);
    if (strpos($mime, '/') !== FALSE) {
      $mime_parts = explode('/', $mime);
      return "." . $mime_parts[1];
    }
    return '';
  }

  /**
   * Check to see if a remote image filename has an extension or not.
   */
  private function fileHasExtension($filename) {
    return (substr($filename, -4, 1) == '.');
  }

  /**
   * Generates an alternate filename.
   *
   * If the remote imagename doesn't have an extension or is too large.
   * Otherwise just returns the filename as is.
   */
  private function generateFilename($remote_image) {
    $filesystem = \Drupal::service('file_system');
    $filename = $filesystem->basename($remote_image);

    // If file has no extension or is bigger than 50 characters.
    if (!$this->fileHasExtension($filename) || strlen($filename) > 50) {
      // Get the remote mime type, determine appropriate extension.
      $extension = $this->getFileExtension($remote_image);
      // Create hash of basename, add correct extension.
      $filename = md5($filename) . $extension;
    }

    return $filename;
  }

  /**
   * Saves a remote image as a file, then creates image media entity.
   */
  public function saveMediaLocally($url, $alt = '', $dest = 'public:/') {
    // Initializations.
    $filename = $this->generateFilename($url);
    $uri = $dest . '/' . $filename;

    if ( \Drupal::service('file_system')->prepareDirectory($dest, FileSystemInterface::CREATE_DIRECTORY)) {

      // Check to see if this image already exists as a file entity.
      $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
      $real_file = FALSE;
      $file = NULL;
      $found_uri = $uri;
      if (count($files)) {
        $file = array_shift($files);
        $found_uri = $file->getFileUri();
        if (file_exists($found_uri)) {
          $real_file = TRUE;
        }
      }
      if (!$real_file) {
        $found_uri = "";
        echo "fetching $url...";
        $ret = system_retrieve_file($url, $uri, FALSE, FileSystemInterface::EXISTS_REPLACE);
        echo $ret ? "OK\n" : "failed.\n";
      }
      if (!$file) {
        // Create file entity.
        $file = File::create();
      }

      if ($found_uri != $uri) {
        $file->setOwnerId(\Drupal::currentUser()->id());
        $file->setPermanent();
        $file->setFileUri($uri);
        $file->setMimeType(\Drupal::service('file.mime_type.guesser')->guess($uri));
        $file->setFileName($filename);
        $file->save();
      }

      // Check to see if it's been loaded as a media entity connected to that file.
      $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([$this->fieldName . '.target_id' => $file->id()]);

      // If so, load media entity.
      if (count($media) > 0) {
        $media_entity = array_shift($media);

        // Update.
        $media_entity->name->value = $filename;
        if ($this->bundle == 'image') {
          $media_entity->{$this->fieldName}->alt = substr($alt, 0, 512);
        }
      }
      else {
        $field_val = [
          'target_id' => $file->id(),
        ];
        if ($this->bundle == 'image') {
          $field_val['alt'] = substr($alt, 0, 512);
        }
        $media_entity = Media::create([
          'name' => $filename,
          'bundle' => $this->bundle,
          'uid' => \Drupal::currentUser()->id(),
          'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          $this->fieldName => $field_val,
        ]);
        $media_entity->save();
      }
      return $media_entity;
    }
    else {
      echo "Unable to create uri " . $dest . "\n";
    }

    return FALSE;
  }

  /**
   * Delete all files in a dir.
   */
  public function deleteAll($uri_dir, $force_all = FALSE) {
    $query = \Drupal::entityQuery('file');
    if (!$force_all) {
      $query->condition('uri', $uri_dir . '%', 'LIKE');
    }
    $file_ids = $query->execute();
    print_r($file_ids);
    if ($file_ids) {
      $query = \Drupal::entityQuery('media');
      if (!$force_all) {
        $query->condition($this->fieldName, $file_ids, 'IN');
      }
      $media_ids = $query->execute();
      print_r($media_ids);

      if ($media_ids) {
        $media_storage = \Drupal::entityTypeManager()->getStorage('media');
        $media = $media_storage->loadMultiple($media_ids);
        $media_storage->delete($media);
      }
      $file_storage = \Drupal::entityTypeManager()->getStorage('file');
      $files = $file_storage->loadMultiple($file_ids);
      $file_storage->delete($files);
    }
  }

}
