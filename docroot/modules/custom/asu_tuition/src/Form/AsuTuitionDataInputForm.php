<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;

/**
 *
 */
class AsuTuitionDataInputForm extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_data_input_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_data_input_settings',
    ];
  }

  /**
   * Builds the form.
   *
   * @return array The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table_name = NULL) {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = 'asu_tuition_' . $path_args[6];
    $url_table = $path_args[6];

    // Enable CSRF token protection for this form.
    $form['#token'] = $this->getFormId();

    $updateTime = \Drupal::state()->get($table . '_last_updated');
    $updateUser = \Drupal::state()->get($table . '_last_updated_by');
    $formatted = $updateTime ? \Drupal::service('date.formatter')->format(
      $updateTime,
      'custom',
      'm-d-Y H:i a'
    ) : 'unknown time';

    $form['back_link'] = [
      '#type' => 'markup',
      '#markup' => '<h4>' . $table . ' data</h4><div><a href="' . Url::fromUri('internal:/admin/config/content/tuition/tables/' . $url_table)->toString() . '">' . $this->t('Back to table') . '</a></div><p>This table last updated ' . $formatted . ' by ' . $updateUser . '</p>',
    ];

    $form['upload_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV File'),
      '#description' => $this->t('* Note: Upload a CSV file to import data. File cannot exceed 50MB in size.'),
      '#upload_location' => 'private://asu_tuition/csv_uploads/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      // 50MB limit
        'file_validate_size' => [50 * 1024 * 1024],
      ],
      '#required' => FALSE,
    ];

    $form['message'] = [
      '#markup' => '<h2>Or</h2>',
    ];

    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['filemime' => 'text/csv']);

    // Add "None" at the top.
    $options = ['' => $this->t('- None -')];
    foreach ($files as $file) {
      $options[$file->id()] = $file->getFilename();
    }

    $form['existing_csv'] = [
      '#type' => 'select',
      '#title' => $this->t('Select existing CSV'),
      '#options' => $options,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload and insert data'),
    ];
    return $form;
  }

  /**
   * Validates the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = 'asu_tuition_' . $path_args[6];
    $schema = $this->get_char_varchar_schema($table);
    $db_tables = \Drupal::service('editTableFields')->editTableFields($table);

    foreach ($db_tables as $f_field_name => $field) {
      $db_fields[] = $f_field_name;
    }

    parent::validateForm($form, $form_state);

    $file_ids = $form_state->getValue('upload_file');
    // ($file_ids,'uf');
    $existing_csv = $form_state->getValue('existing_csv');
    // dpm($existing_csv,'ev');.
    if (empty($file_ids) && empty($existing_csv)) {
      $form_state->setErrorByName('upload_file', $this->t('Please upload a CSV file or select an existing one.'));
      return;
    }
    if (!empty($file_ids[0])) {
      $file = File::load($file_ids[0]);
      $uri = $file->getFileUri();
      $real_path = \Drupal::service('file_system')->realpath($uri);
      if (($handle = fopen($real_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        // Customize.
        $expected_headers = $db_fields;

        // Check headers.
        /* if (array_map('strtolower', $headers) !== $expected_headers) {
        $form_state->setErrorByName('upload_file', $this->t('Invalid CSV headers.<br />Expected: @headers<br />Given: @given', ['@headers' => implode(', ', $expected_headers), '@given' => implode(', ', $headers)]));
        } */
        // Normalize headers to lowercase for comparison.
        $given_headers = array_map('strtolower', $headers);
        $expected_lower = array_map('strtolower', $expected_headers);

        // Check for same header set, ignoring order.
        sort($given_headers);
        sort($expected_lower);

        if ($given_headers !== $expected_lower) {
          $form_state->setErrorByName('upload_file', $this->t(
            'Invalid CSV headers.<br />Expected: @headers<br />Given: @given',
            [
              '@headers' => implode(', ', $expected_headers),
              '@given' => implode(', ', $headers),
            ]
          ));
        }

        $row_number = 1;

        while (($row = fgetcsv($handle)) !== FALSE) {
          $row_number++;

          foreach ($row as $i => $value) {

            $value = trim($value);
            $field = $headers[$i];

            if (!isset($schema[$field])) {
              // continue; // Skip columns not in schema.
            }

            if (isset($schema[$field])) {
              $max_length = $schema[$field]['length'] ?? NULL;
              $type = $schema[$field]['type'] ?? NULL;
            }
            else {
              // Fallback defaults for unexpected columns.
              $max_length = NULL;
              $type = NULL;
              \Drupal::logger('asu_tuition')->warning(
                'Field @field not found in schema for @table.',
                ['@field' => $field, '@table' => $table]
                          );
            }

            $errors = [];
            switch ($type) {
              case 'int':
                if (!empty($value) && !is_numeric($value)) {
                  $errors[] = "Row $row_number, '$field': must be a numeric value.";
                }
                break;

              case 'tinyint':
                if (!is_numeric($value) || $value < 0 || $value > 255) {
                  $errors[] = "Row $row_number, '$field': must be a numeric value.";
                }
                break;

              case 'varchar':
              case 'char':
                if (!is_string($value)) {
                  $errors[] = "Row $row_number, '$field': must be a string.";
                }

                if (mb_strlen($value) > $max_length) {
                  $errors[] = "Row $row_number, '$field': exceeds max length of $max_length.";
                }

                if (!mb_check_encoding($value, 'UTF-8')) {
                  $errors[] = "Row $row_number, '$field': contains invalid UTF-8 characters.";
                }
                break;

              case '':
                if (!is_string($value)) {
                  $errors[] = "Row $row_number, '$field': must be a string.";
                }

                // Example: 65,535 chars = typical TEXT max.
                if (mb_strlen($value) > 65535) {
                  $errors[] = "Row $row_number, '$field': exceeds max length of 65535 characters.";
                }

                // If you want to restrict to plain text only.
                if (!mb_check_encoding($value, 'UTF-8')) {
                  $errors[] = "Row $row_number, '$field': contains invalid UTF-8 characters.";
                }
                break;

              default:
                $errors[] = "Row $row_number, '$field': unsupported type text test '$type'";
                break;
            }
            // dpm($errors);
            if (!empty($errors)) {
              // Set error for the specific field.
              $form_state->setErrorByName("upload_file", implode(', ', $errors));
              // Delete uploaded temp file.
              $file->delete();
              $form_state->setValue('upload_file', NULL);
            }
          }
        }

        fclose($handle);
      }
      else {
        $form_state->setErrorByName('upload_file', $this->t('Could not read uploaded CSV file.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = 'asu_tuition_' . $path_args[6];

    $fid = $form_state->getValue('upload_file')[0] ?? $form_state->getValue('existing_csv');
    // dpm($fid);
    $file = File::load($fid);
    /*$file->setPermanent();
    $file->save();
    $file_id = $file->id();*/

    // ---  Auto-rename if same filename already exists ---
    $file_system = \Drupal::service('file_system');
    $directory = 'private://asu_tuition/csv_uploads/';
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Generate a new unique file name if one exists.
    $new_uri = $file_system->getDestinationFilename($directory . basename($file->getFilename()), FileSystemInterface::EXISTS_RENAME);

    // Move the file to the unique path.
    if ($file->getFileUri() !== $new_uri) {
      $file_system->move($file->getFileUri(), $new_uri, FileSystemInterface::EXISTS_RENAME);
      $file->setFileUri($new_uri);
    }

    // ---  Mark file as permanent and save ---
    $file->setPermanent();
    $file->save();
    $file_id = $file->id();

    // Get the absolute file system path.
    $file_path = $file_system->realpath($file->getFileUri());
    // Get file path.
    $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
    // dpm($file_path);
    // Open the CSV file.
    if (!file_exists($file_path) || !is_readable($file_path)) {
      throw new \Exception("CSV file not found or unreadable: $file_path");
    }

    $handle = fopen($file_path, 'r');
    if (!$handle) {
      throw new \Exception("Unable to open CSV file: $file_path");
    }

    // Read the header row.
    $header = fgetcsv($handle);
    if ($header === FALSE) {
      throw new \Exception("CSV file is empty or invalid.");
    }

    // Prepare batch.
    /* $batch = [
    'title' => $this->t('Importing CSV Data...'),
    'operations' => [],
    'finished' => '\Drupal\asu_tuition\Form\AsuTuitionDataInputForm::batchFinished',
    ]; */

    $batch = [
      'title' => $this->t('Importing CSV Data...'),
      'operations' => [],
      'finished' => [self::class, 'batchFinished'],
      'init_message' => $this->t('Starting import...'),
      'progress_message' => $this->t('Processed @current of @total.'),
      'error_message' => $this->t('An error occurred during import.'),
      'redirect' => Url::fromRoute('asu_tuition.admin_each_table_settings', [
        'table_name' => str_replace('asu_tuition_', '', $table),
      ]),
    ];

    // Adjust based on memory and performance.
    $chunkSize = 50;
    $chunk = [];
    // dpm($handle);
    while (($row = fgetcsv($handle)) !== FALSE) {
      $chunk[] = $row;
      if (count($chunk) >= $chunkSize) {
        // Add batch operation for this chunk.
        $batch['operations'][] = [
          '\Drupal\asu_tuition\Form\AsuTuitionDataInputForm::batchProcess',
          [$chunk, $header, $table, $file],
        ];
        // Reset chunk.
        $chunk = [];
      }
    }

    // Add final chunk if any remaining rows.
    if (!empty($chunk)) {
      $batch['operations'][] = [
        '\Drupal\asu_tuition\Form\AsuTuitionDataInputForm::batchProcess',
        [$chunk, $header, $table, $file],
      ];
    }

    fclose($handle);
    batch_set($batch);
  }

  /**
   * Public static function batchProcess($data, $table, $file, &$context) {.
   */
  public static function batchProcess(array $chunk, array $header, string $table, $file, array &$context) {
    // dpm($file);
    // Initialize batch context safely.
    if (empty($context['results']) || !is_array($context['results'])) {
      $context['results'] = [];
    }
    $context['results'] += [
      'rows' => 0,
      'table' => $table,
      'errors' => [],
      'imported' => [],
      'success' => TRUE,
      'file_id' => $file->id(),
    ];

    // Ensure imported array exists.
    if (!isset($context['results']['imported']) || !is_array($context['results']['imported'])) {
      $context['results']['imported'] = [];
    }

    // Optionally track total rows (first batch only)
    if (!isset($context['results']['total_rows'])) {
      $context['results']['total_rows'] = $context['results']['total_rows'] ?? count($chunk);
    }
    else {
      $context['results']['total_rows'] += count($chunk);
    }

    try {
      $connection = \Drupal::database();
      // \Drupal::logger('asu_tuition_chunk')->notice('Processing chunk with @rows rows for @table', [
      //   '@rows' => count($chunk),
      //   '@table' => $table,
      // ]);
      foreach ($chunk as $row_index => $row) {
        // Validate row length matches header.
        if (count($row) !== count($header)) {
          $context['results']['errors'][] = [
            'row' => $row,
            'message' => 'CSV row column count does not match header.',
          ];
          // Skip invalid row.
          continue;
        }

        // Combine header with row.
        $data = array_combine($header, $row);

        $insert_data = [];
        // dpm($data);
        /* foreach ($data as $key => $value) {
        if ($key === 'id') {
        // Skip ID column.
        continue;
        }
        $insert_data[$key] = trim((string) $value);
        } */
        foreach ($data as $key => $value) {
          // Skip auto-increment ID field.
          /* if ($key === 'id') {
          continue;
          } */

          // Skip empty headers or invalid keys.
          if (empty($key)) {
            continue;
          }
          // Clean and typecast values.
          $insert_data[$key] = is_string($value) ? trim($value) : $value;
        }
        // Handle ID field for auto-increment.
        if (!array_key_exists('id', $data) || empty($data['id'])) {
          // dpm('empty');.
          $maxquery = $connection->select($table, 't');
          $maxquery->addExpression('MAX(t.id)', 'max_id');

          $result = $maxquery->execute();
          $max_id = $result->fetchField();

          // Assign the next available ID.
          $insert_data['id'] = $max_id + 1;
        }
        else {
          // Case 2: ID provided — check if it already exists.
          $id_to_check = (int) $data['id'];
          // dpm($id_to_check, 'id to check');.
          $exists_query = $connection->select($table, 't')
            ->fields('t', ['id'])
            ->condition('id', $id_to_check);
          $exists = $exists_query->execute()->fetchField();
          // dpm($exists, 'exists');.
          if ($exists) {
            // Duplicate found — handle gracefully.
            throw new \Exception("Duplicate ID entry detected for ID {$id_to_check} in table {$table}.");
          }

          // Safe to use provided ID.
          $insert_data['id'] = $id_to_check;
        }

        if (empty($insert_data)) {
          $context['results']['errors'][] = [
            'row' => $row,
            'message' => 'No valid data to insert for this row.',
          ];
          // Skip empty rows.
          continue;
        }

        // Create readble sql for logging and pass to batch finished.
        $columns = array_keys($insert_data);
        $quoted_values = array_map(function ($v) {
          return is_numeric($v) ? $v : "'" . addslashes((string) $v) . "'";
        }, array_values($insert_data));
        $readable_sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ')' . ' VALUES (' . implode(', ', $quoted_values) . ')';

        // Insert row.
        $query = $connection->insert($table)->fields($insert_data);
        $query->execute();

        // Update results.
        $context['results']['rows']++;
        if (count($context['results']['imported']) < 20) {
          $context['results']['imported'][] = $insert_data;
          $context['results']['query'][] = $readable_sql;
        }
      }

      // Update progress message with percentage.
      $total = $context['results']['total_rows'] ?? 1;
      $processed = $context['results']['rows'];
      $percent = min(100, round(($processed / $total) * 100));

      $context['message'] = t('Processed @processed of @total rows (@percent%)...', [
        '@processed' => $processed,
        '@total' => $total,
        '@percent' => $percent,
      ]);

    }
    catch (\Throwable $e) {
      $context['results']['success'] = FALSE;
      $context['results']['errors'][] = [
        'row' => $row ?? [],
        'message' => $e->getMessage(),
      ];

      \Drupal::messenger()->addError(t('Error inserting row into @table: @error', [
        '@table' => $table,
        '@error' => $e->getMessage(),
      ]));

      \Drupal::logger('asu_tuition')->error('Batch insert error: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Implements hook_batch_finished().
   */
  public static function batchFinished($success, $results, $operations) {
    $table = $results['table'] ?? 'unknown';
    $total_rows = $results['rows'] ?? 0;
    $errors = $results['errors'] ?? [];
    $queryList = [];
    // Log the query history.
    $logger = \Drupal::service('asu_tuition.query_logger');
    if (!empty($results['query'])) {
      foreach ($results['query'] as $item) {
        $queryList[] = $item;

      }
    }
    $logger->saveQueryHistoryToConfig($queryList, $table);

    if (!empty($results['errors'])) {
      $successOp = FALSE;
    }
    else {
      $successOp = TRUE;
    }
    if ($successOp) {
      \Drupal::messenger()->addStatus(t(
            'Successfully imported @count rows into @table.',
            ['@count' => $total_rows, '@table' => $table]
        ));

      $current_user = \Drupal::currentUser();
      $username = $current_user->getDisplayName();
      \Drupal::state()->set("{$table}_last_updated", \Drupal::time()->getCurrentTime());
      \Drupal::state()->set("{$table}_last_updated_by", $username);
    }
    else {
      \Drupal::messenger()->addStatus(t(
        'failed to import rows into @table. Imported @count rows before error.',
        ['@count' => $total_rows, '@table' => $table]
      ));
      foreach ($errors as $error) {
        \Drupal::messenger()->addError(t('Row import failed: @message', ['@message' => $error['message']]));
      }
    }

  }

  /**
   * Get the character and varchar schema for a table.
   */
  public function get_char_varchar_schema($table_name) {
    $connection = \Drupal::database();
    $query = $connection->query("SHOW COLUMNS FROM {$table_name}");
    $columns = $query->fetchAllAssoc('Field');

    $schema = [];
    foreach ($columns as $column) {
      // Match types like VARCHAR(255), CHAR(50)
      if (preg_match('/^(varchar|char|int|tinyint)\((\d+)\)/i', $column->Type, $matches)) {
        $schema[$column->Field] = [
          'type' => strtolower($matches[1]),
          'length' => isset($matches[2]) ? (int) $matches[2] : NULL,
        ];
      }
    }
    return $schema;
  }

}
