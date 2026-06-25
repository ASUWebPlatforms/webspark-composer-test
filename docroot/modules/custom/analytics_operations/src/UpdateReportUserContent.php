<?php

namespace Drupal\analytics_operations;

use Drupal;
use Exception;
use GuzzleHttp\Client;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

class UpdateReportUserContent
{
  protected static Client $httpClient;
  private static string $url;
  private static string $username;
  private static string $password;

  /**
   * Update the fields for reports of given groups.
   *
   * @param array $groups
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(array $groups, int $batchSize = 10): void
  {
    $reports = static::getReportsByGroup($groups);

    // Define batch operations
    $batch = [
      'title' => t('Updating group reports...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished']
    ];

    // Split reports into chunks and add as separate operations
    $chunks = array_chunk($reports, $batchSize);

    foreach ($chunks as $chunk) {
      $batch['operations'][] = [[get_called_class(), 'batchProcess'], [$chunk]];
    }

    batch_set($batch);
  }

  /**
   * Get reports for the given group from the existing website.
   * This excludes reports where the current revision is from the AnalyticsAPIUser user.
   *
   * @param array $guuids
   *
   * @return array
   */
  public static function getReportsByGroup(array $guuids): array
  {
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize();
    }

    $reports = [];

    foreach ($guuids as $guuid) {
      $nextPageUri = null;
      $params = [
        'query' => [
          'filter[field_group.id][value]' => $guuid,
          'filter[exclude_api_user][condition][path]' => 'revision_uid.uid',
          'filter[exclude_api_user][condition][operator]' => '<>',
          'filter[exclude_api_user][condition][value]' => '7',
        ],
      ];

      do {
        try {
          if ($nextPageUri) {
            $response = self::$httpClient->get($nextPageUri);
          } else {
            $response = self::$httpClient->get('/jsonapi/node/report', $params);
          }
          $body = $response->getBody()->getContents();
          $data = json_decode($body, true);

          $reports = array_merge($reports, $data['data'] ?? []);
          $nextPageUri = $data['links']['next']['href'] ?? null;
        } catch (Exception $e) {
          Drupal::logger('analytics_operations')->error(
            'Error fetching reports for group @group: @error',
            ['@group' => $guuid, '@error' => $e->getMessage()]
          );
          break;
        }
      } while ($nextPageUri);
    }

    return $reports;
  }

  /**
   * Initializes the API properties.
   *
   * @return void
   */
  public static function initialize(): void
  {
    $settings = Drupal::service('settings');
    self::$url = $settings->get('analytics-source-url');
    self::$username = $settings->get('analytics-source-username');
    self::$password = $settings->get('analytics-source-password');
    self::$httpClient = new Client([
      'base_uri' => self::$url,
      'auth' => [self::$username, self::$password],
      'headers' => ['Accept' => 'application/vnd.api+json'],
    ]);
  }

  /**
   * Process a subset of nodes.
   *
   * @param array $reports
   * @param $context
   *
   * @return void
   */
  public static function batchProcess(array $reports, &$context): void
  {
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize();
    }

    foreach ($reports as $report) {
      try {
        $nid = Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
          'field_external_id' => $report['attributes']['field_external_id']
        ]);
        $node = reset($nid);
      } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
        Drupal::logger('analytics_operations')->error(
          'Error loading node: @error',
          ['@error' => $e->getMessage()]
        );
        continue;
      }

      if (empty($node)) {
        Drupal::logger('analytics_operations:beta_missing_report')->notice(
          'Missing Report ID: @id',
          ['@id' => $report['attributes']['field_external_id']]
        );
        continue;
      }

      // Process the data
      static::updateReportData($node, $report);
    }

    $context['message'] = t('Processing group reports...');
  }

  /**
   * Update the local report with matching data from the source report.
   *
   * @param $node
   * @param array $data
   *
   * @return void
   */
  public static function updateReportData($node, array $data): void
  {
    if (!$node) {
      return;
    }

    $atts = $data["attributes"];
    $attsFields = [
      'body',
      'field_description',
      'field_hidden_from_gsearch',
      'status',
    ];
    $refs = $data["relationships"];
    $refsFields = [
      'field_data_area',
      'field_enterprise',
      'field_file_type',
      'field_level_of_detail',
      'field_purpose',
      'field_related_resources',
      'field_related_to',
      'field_reporting_level',
      'field_report_manager',
      'field_report_type',
      'field_keywords',
      'field_subject_domain',
      'field_tool',
      'field_unit',
    ];

    try {
      foreach ($attsFields as $field) {
        if (!empty($atts[$field])) {
          $node->set($field, $atts[$field]);
        }
      }
      foreach ($refsFields as $field) {
        if (!empty($refs[$field]["data"])) {
          // Ensure to merge the existing keywords with the new ones
          if ($field == 'field_keywords') {
            $existingKeywords = array_map(function ($item) {
              return $item["target_id"];
            }, $node->get("field_keywords")->getValue());

            $newKeywords = array_map(function ($item) {
              return $item["meta"]["drupal_internal__target_id"];
            }, $refs[$field]["data"]);

            $keywords = array_merge($existingKeywords, $newKeywords);
            $node->set($field, array_unique($keywords));
          } else {
            $values = static::processRefsData($refs[$field]["data"]);
            $node->set($field, $values);
          }
        }
      }
      $node->save();
    } catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Failed to update @node. Error: @error',
        ['@node' => $node->id(), '@error' => $e->getMessage()]
      );
      return;
    }
  }

  /**
   * Process the relationship fields, ensuring the proper data structure.
   *
   * @param $data
   *
   * @return array
   */
  public static function processRefsData($data): array
  {
    if (isset($data['meta'])) {
      $data = [$data];
    }

    return array_map(function ($item) {
      return $item['meta']['drupal_internal__target_id'];
    }, $data);
  }

  /**
   * Finish callback for the batch.
   *
   * @param $success
   * @param $results
   * @param $operations
   *
   * @return void
   */
  public static function batchFinished($success, $results, $operations): void
  {
    if ($success) {
      Drupal::logger('analytics_operations')->notice('Update Report User Content operation complete.');
      Drupal::messenger()->addMessage('All nodes have been processed.');
    } else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError(
        'An error occurred while processing @operation',
        ['@operation' => $error_operation[0]]
      );
    }
  }
}
