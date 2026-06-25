<?php

namespace Drupal\uoeee_rest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller for file uploads (multipart/form-data).
 */
class FileUploadController extends ControllerBase {

  protected FileSystemInterface $fileSystem;
  protected LoggerInterface $logger;

  public function __construct(FileSystemInterface $file_system, LoggerInterface $logger) {
    $this->fileSystem = $file_system;
    $this->logger = $logger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('logger.factory')->get('uoeee_rest')
    );
  }

  public function upload(Request $request) {
    $uploaded = $request->files->all();
    if (empty($uploaded)) {
      return new JsonResponse(['error' => 'No files found in request. Use multipart/form-data.'], 400);
    }

    $parent_nid = $request->get('parent_nid');
    $destination_folder = trim($request->get('destination_folder', 'uploads'), '/');
    $target_dir = 'public://' . ($destination_folder !== '' ? $destination_folder . '/' : '');

    try {
      $this->fileSystem->prepareDirectory($target_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    } catch (\Throwable $e) {
      $this->logger->error('Prep dir failed: @m', ['@m' => $e->getMessage()]);
      return new JsonResponse(['error' => 'Unable to prepare directory.'], 500);
    }

    // Real filesystem path for move_uploaded_file and checks
    $real_target_dir = $this->fileSystem->realpath($target_dir);
    if ($real_target_dir === FALSE) {
      $this->logger->error('Unable to resolve realpath for @dir', ['@dir' => $target_dir]);
      return new JsonResponse(['error' => 'Unable to resolve destination directory.'], 500);
    }

    $results = [];
    $lock_service = \Drupal::service('lock');

    foreach ($uploaded as $input_name => $file_or_array) {
      $file_items = is_array($file_or_array) ? $file_or_array : [$file_or_array];
      foreach ($file_items as $file) {
        try {
          // Validate uploaded file first
          if (!method_exists($file, 'isValid') || !$file->isValid()) {
            $err = method_exists($file, 'getError') ? $file->getError() : 'Invalid upload';
            $this->logger->warning('Upload invalid for input @in: @err', ['@in' => $input_name, '@err' => $err]);
            $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => 'Upload invalid: ' . $err];
            continue;
          }

          $orig = $file->getClientOriginalName();
          $tmp = $file->getRealPath();
          $filename = substr($request->get('filename_' . $input_name) ?: $orig, 0, 255);
          $target_uri = $target_dir . $filename;
          $final_real_path = rtrim($real_target_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

          // Acquire lock per target URI to avoid concurrent writes
          $lock_name = 'uoeee_rest:upload:' . md5($target_uri);
          if (!$lock_service->acquire($lock_name, 30)) {
            $this->logger->warning('Could not acquire lock for @uri', ['@uri' => $target_uri]);
            $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => 'Resource busy'];
            continue;
          }

          try {
            // Move uploaded file to a temp file in the real dir
            $tmpname = $filename . '.tmp-' . uniqid('', TRUE);
            $temp_real_path = $real_target_dir . DIRECTORY_SEPARATOR . $tmpname;

            // Ensure uploaded file is a valid uploaded file and try move_uploaded_file for atomic move
            $moved = false;
            if (is_uploaded_file($tmp)) {
              // attempt 2 tries (covers transient FS issues)
              $attempts = 0;
              while ($attempts < 2 && !$moved) {
                $attempts++;
                if (@move_uploaded_file($tmp, $temp_real_path)) {
                  $moved = true;
                  break;
                }
                clearstatcache(true, $tmp);
                usleep(100000); // brief pause before retry
              }
            } else {
              // fallback: try Symfony's move() (in some SAPI configurations)
              try {
                $file->move($real_target_dir, $tmpname);
                $moved = file_exists($temp_real_path);
              } catch (\Throwable $e) {
                $this->logger->error('Fallback move() failed: @m', ['@m' => $e->getMessage()]);
              }
            }

            if (!$moved) {
              $this->logger->error('Failed to move uploaded file to temp path: @tmp -> @dest', ['@tmp' => $tmp, '@dest' => $temp_real_path]);
              $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => 'Failed to write uploaded file to disk'];
              continue;
            }

            // Set permissions so Drupal can read it
            @chmod($temp_real_path, 0644);

            // Atomic rename into final name (replaces existing file on same filesystem)
            if (!@rename($temp_real_path, $final_real_path)) {
              // If rename fails, try unlink existing remote and rename again
              if (file_exists($final_real_path) && is_writable($final_real_path)) {
                @unlink($final_real_path);
                if (!@rename($temp_real_path, $final_real_path)) {
                  // final fallback: copy then unlink
                  if (!@copy($temp_real_path, $final_real_path)) {
                    $this->logger->error('Failed to place file into final location after temp move.');
                    @unlink($temp_real_path);
                    $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => 'Failed to finalize file placement'];
                    continue;
                  }
                  @unlink($temp_real_path);
                }
              } else {
                $this->logger->error('Cannot rename temp to final; target not writable or exists locked: @final', ['@final' => $final_real_path]);
                @unlink($temp_real_path);
                $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => 'Target not writable'];
                continue;
              }
            }

            // Ensure final permissions
            @chmod($final_real_path, 0644);

            // Compute checksum from new file on disk (optional)
            $checksum = @md5_file($final_real_path);

            // Build file entity URI
            $file_entity_uri = $target_uri; // 'public://...'

            // Lookup existing file entity by uri (no access check)
            $fids = \Drupal::entityQuery('file')->condition('uri', $file_entity_uri)->accessCheck(FALSE)->execute();
            if (!empty($fids)) {
              $fid = reset($fids);
              $file_entity = File::load($fid);
              if ($file_entity) {
                $file_entity->setFilename($filename);
                $file_entity->setFileUri($file_entity_uri);
                $file_entity->setPermanent();
                $file_entity->save();
                $file_action = 'updated';
              } else {
                $file_entity = File::create(['uri' => $file_entity_uri, 'filename' => $filename]);
                $file_entity->setPermanent();
                $file_entity->save();
                $file_action = 'created';
              }
            } else {
              $file_entity = File::create(['uri' => $file_entity_uri, 'filename' => $filename]);
              $file_entity->setPermanent();
              $file_entity->save();
              $file_action = 'created';
            }

            // Optionally set checksum field if exists
            if ($checksum && $file_entity->hasField('field_checksum')) {
              $file_entity->set('field_checksum', $checksum);
              $file_entity->save();
            }

            $results[] = [
              'input' => $input_name,
              'status' => 'success',
              'file_action' => $file_action,
              'file' => [
                'fid' => $file_entity->id(),
                'uri' => $file_entity->getFileUri(),
                'filename' => $file_entity->getFilename(),
              ],
              'checksum' => $checksum ?? NULL,
            ];
          } finally {
            // Always release lock
            $lock_service->release($lock_name);
          }
        } catch (\Throwable $e) {
          $this->logger->error('Upload error: @m', ['@m' => $e->getMessage()]);
          $results[] = ['input' => $input_name, 'status' => 'failed', 'message' => $e->getMessage()];
        }
      }
    }

    return new JsonResponse(['results' => $results], 200);
  }

}