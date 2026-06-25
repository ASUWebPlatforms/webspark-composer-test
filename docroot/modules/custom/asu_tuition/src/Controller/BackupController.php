<?php

namespace Drupal\asu_tuition\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class BackupController extends ControllerBase {

  /**
   *
   */
  public function download($filename) {
    $uri = 'private://asu_tuition/backup/' . $filename;
    $real_path = \Drupal::service('file_system')->realpath($uri);

    if (!file_exists($real_path)) {
      throw new NotFoundHttpException('File not found.');
    }

    $response = new BinaryFileResponse($real_path);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
    return $response;
  }

}
