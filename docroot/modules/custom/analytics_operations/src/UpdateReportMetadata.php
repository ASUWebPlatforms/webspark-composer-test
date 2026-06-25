<?php

namespace Drupal\analytics_operations;

use Drupal;
use Exception;
use GuzzleHttp\Client;

class UpdateReportMetadata {

  protected static Client $httpClient;

  private static string $url;

  private static string $username;

  private static string $password;

  /**
   * Update the Drupal metadata for reports of given groups.
   *
   * @param string $sourceUrl
   * @param array $groups
   * @param string $ruuid
   * @param int $batchSize
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public static function batchInit(
    string $sourceUrl,
    array $groups,
    string $ruuid,
    int $batchSize = 100
  ): void {
    // Define batch operations
    $batch = [
      'title' => t('Updating group reports...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished'],
    ];

    $reports = [];

    if (!empty($groups[0])) {
      $reports = static::getReportsByGroup($sourceUrl, $groups);
    }
    if (!empty($ruuid)) {
      $reports[] = static::getReportByUUID($sourceUrl, $ruuid);
    }

    // Split reports into chunks and add as separate operations
    $chunks = array_chunk($reports, $batchSize);

    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        [get_called_class(), 'batchProcess'],
        [$sourceUrl, $chunk],
      ];
    }

    batch_set($batch);
  }

  /**
   * Get a single report by its UUID from the existing website.
   *
   * @param string $sourceUrl
   * @param string $ruuid
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public static function getReportByUUID(
    string $sourceUrl,
    string $ruuid
  ): array {
    // TODO: Just make this a constructor from now on
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize($sourceUrl);
    }

    $report = [];

    try {
      $response = self::$httpClient->get('/jsonapi/node/report/' . $ruuid);
      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);
      $report = $data['data'];
    }
    catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Error fetching report @ruuid: @error',
        ['@ruuid' => $ruuid, '@error' => $e->getMessage()]
      );
    }

    return $report;
  }

  /**
   * Get reports for the given group from the existing website.
   * This excludes reports from deleted groups, and reports where the current
   * revision is from the API_CALLER user.
   *
   * @param string $sourceUrl
   * @param array $guuids
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public static function getReportsByGroup(
    string $sourceUrl,
    array $guuids
  ): array {
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize($sourceUrl);
    }

    $reports = [];

    foreach ($guuids as $guuid) {
      $nextPageUri = NULL;
      $params = [
        'query' => [
          'filter[field_group.id][value]' => $guuid,
          'filter[revision_uid.id][operator]' => '<>',
          'filter[revision_uid.id][value]' => 'e704043a-efe7-49bc-a2e2-cfaae03c3839',
        ],
      ];
      do {
        try {
          if ($nextPageUri) {
            $response = self::$httpClient->get($nextPageUri);
          }
          else {
            $response = self::$httpClient->get('/jsonapi/node/report', $params);
          }
          $body = $response->getBody()->getContents();
          $data = json_decode($body, TRUE);

          $reports = array_merge($reports, $data['data'] ?? []);
          $nextPageUri = $data['links']['next']['href'] ?? NULL;
        }
        catch (Exception $e) {
          Drupal::logger('analytics_operations')->error(
            'Error fetching reports for group @group: @error',
            ['@group' => $guuid, '@error' => $e->getMessage()]
          );
          break;
        }
      }
      while ($nextPageUri);
    }

    return $reports;
  }

  /**
   * Initializes the API properties.
   *
   * @param string $sourceUrl
   *
   * @return void
   * @throws \Exception
   */
  public static function initialize(string $sourceUrl): void {
    $settings = Drupal::service('settings');

    $url = !empty($sourceUrl) ? $sourceUrl : $settings->get(
      'analytics-source-url'
    );

    if (empty($url)) {
      throw new Exception('Source URL is required but not provided.');
    }

    self::$url = $url;
    self::$username = $settings->get('analytics-source-username');
    self::$password = $settings->get('analytics-source-password');

    self::$httpClient = new Client([
      'base_uri' => self::$url,
      'auth' => [self::$username, self::$password],
      'headers' => ['Accept' => 'application/vnd.api+json'],
      'timeout' => 30,
    ]);
  }

  /**
   * Process a subset of nodes.
   *
   * @param string $sourceUrl
   * @param array $reports
   * @param $context
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public static function batchProcess(
    string $sourceUrl,
    array $reports,
    &$context
  ): void {
    foreach ($reports as $report) {
      try {
        $query = Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties([
            'field_external_id' => $report['attributes']['field_external_id'],
            'type' => 'report',
          ]);
        if (empty($query)) {
          Drupal::logger('analytics_operations:missing_report')->notice(
            'Missing report with External ID: @report',
            ['@report' => $report['attributes']['field_external_id']]
          );
          continue;
        }
        $node = reset($query);
        static::updateReportData($sourceUrl, $node, $report);
      }
      catch (Exception $e) {
        Drupal::logger('analytics_operations')->error(
          'Error processing report @report: @error',
          [
            '@report' => $report['attributes']['title'],
            '@error' => $e->getMessage(),
          ]
        );
        continue;
      }
    }

    $context['message'] = t('Processing group reports...');
  }

  /**
   * Update the local report with matching data from the existing website.
   *
   * @param string $sourceUrl
   * @param $node
   * @param array $report
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public static function updateReportData(
    string $sourceUrl,
    $node,
    array $report
  ): void {
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize($sourceUrl);
    }

    try {
      $oldContacts = $report['attributes']['field_contact'] ?? [];
      $oldKeywords = $report['relationships']['field_keywords'] ?? [];

      $node->body->value = $report['attributes']['body']['value'];
      $node->body->format = $report['attributes']['body']['format'];
      $node->field_description = $report['attributes']['field_description'];

      if (!empty($oldKeywords)) {
        $keywords = static::extractTermIds($sourceUrl, 'keyword', $oldKeywords);
        $node->field_keywords = [];
        foreach ($keywords as $keyword) {
          $node->field_keywords->appendItem(['target_id' => $keyword->id()]);
        }
      }

      if (!empty($oldContacts)) {
        // NOTE: Only the first 5 values will be saved due to the field settings
        $node->set('field_contact', $oldContacts);
      }

      $node->save();
    }
    catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Failed to update @report. Error: @error',
        [
          '@report' => $report['attributes']['title'],
          '@error' => $e->getMessage(),
        ]
      );
      return;
    }
  }

  /**
   * Helper function to extract term IDs.
   *
   * @param string $sourceUrl
   * @param string $type
   * @param array $items
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  protected static function extractTermIds(
    string $sourceUrl,
    string $type,
    array $items
  ): array {
    if (empty($items['data'])) {
      return [];
    }

    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize($sourceUrl);
    }

    try {
      $response = self::$httpClient->get($items['links']['related']['href']);
      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);
    }
    catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Error extracting term IDs from @type: @error',
        ['@type' => $type, '@error' => $e->getMessage()]
      );
      return [];
    }

    $items = array_column($data['data'], 'attributes');
    $terms = [];

    foreach ($items as $item) {
      try {
        $query = Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->loadByProperties([
            'name' => $item['name'],
            'vid' => $type,
          ]);
      }
      catch (Exception $e) {
        Drupal::logger('analytics_operations')->error(
          'Error fetching term @term: @error',
          ['@term' => $item['name'], '@error' => $e->getMessage()]
        );
      }

      if (empty($query)) {
        try {
          $term = Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->create([
              'name' => $item['name'],
              'vid' => $type,
            ]);
          $term->save();
          Drupal::logger('analytics_operations')->notice(
            'Created term @term',
            ['@term' => $term->id()]
          );
          $terms[] = $term;
        }
        catch (Exception $e) {
          Drupal::logger('analytics_operations')->error(
            'Error creating term @term: @error',
            ['@term' => $item['name'], '@error' => $e->getMessage()]
          );
        }
      }
      else {
        $terms[] = reset($query);
      }
    }

    return $terms;
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
  public static function batchFinished($success, $results, $operations): void {
    if ($success) {
      Drupal::logger('analytics_operations')->notice(
        'Update Report Content operation complete.'
      );
      Drupal::messenger()->addMessage('All nodes have been processed.');
    }
    else {
      $error_operation = reset($operations);
      Drupal::messenger()->addError(
        'An error occurred while processing @operation',
        ['@operation' => $error_operation[0]]
      );
    }
  }

}
