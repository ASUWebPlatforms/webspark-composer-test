<?php

namespace Drupal\analytics_operations;

use Drupal;
use Exception;
use GuzzleHttp\Client;
use Drupal\group\Entity\Group;
use GuzzleHttp\Exception\RequestException;

class CreateGroup {

  protected static Client $httpClient;

  private static string $url;

  private static string $username;

  private static string $password;

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
    self::$url = !empty($sourceUrl) ? $sourceUrl : $settings->get(
      'analytics-source-url'
    );
    self::$username = $settings->get('analytics-source-username');
    self::$password = $settings->get('analytics-source-password');

    if (empty(self::$url)) {
      throw new Exception('Source URL is required but not provided.');
    }

    self::$httpClient = new Client([
      'base_uri' => self::$url,
      'auth' => [self::$username, self::$password],
      'headers' => ['Accept' => 'application/vnd.api+json'],
      'timeout' => 30,
    ]);
  }

  /**
   * Import missing Groups.
   *
   * @param string $sourceUrl
   * @param array $guuids
   * @param int $batchSize
   *
   * @return void
   */
  public static function batchInit(
    string $sourceUrl,
    array $guuids,
    int $batchSize = 10
  ): void {
    // Define batch operations
    $batch = [
      'title' => t('Importing groups...'),
      'operations' => [],
      'finished' => [get_called_class(), 'batchFinished'],
    ];

    // Split groups into chunks and add as separate operations
    $chunks = array_chunk($guuids, $batchSize);

    foreach ($chunks as $chunk) {
      $batch['operations'][] = [
        [get_called_class(), 'batchProcess'],
        [$sourceUrl, $chunk],
      ];
    }

    batch_set($batch);
  }

  /**
   * Process a subset of groups.
   *
   * @param string $sourceUrl
   * @param array $guuids
   * @param $context
   *
   * @return void
   * @throws \Exception
   */
  public static function batchProcess(
    string $sourceUrl,
    array $guuids,
    &$context
  ): void {
    foreach ($guuids as $guuid) {
      try {
        $group = static::getGroup($sourceUrl, $guuid);
        static::importGroup($group);
      }
      catch (Exception $e) {
        Drupal::logger('analytics_operations')->error(
          'Error importing group @guuid: @error',
          ['@guuid' => $guuid, '@error' => $e->getMessage()]
        );
        continue;
      }
    }

    $context['message'] = t('Processing groups...');
  }

  /**
   * Get the group data from the source API.
   *
   * @param string $sourceUrl
   * @param string $guuid
   *
   * @return array|null
   * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
   */
  public static function getGroup(string $sourceUrl, string $guuid): ?array {
    if (!isset(self::$url, self::$username, self::$password, self::$httpClient)) {
      self::initialize($sourceUrl);
    }

    $data = NULL;

    try {
      $response = self::$httpClient->get(
        '/jsonapi/group/content_owner_group_ty/' . $guuid
      );
      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);
    }
    catch (RequestException $e) {
      Drupal::logger('analytics_operations')->error(
        'HTTP error fetching group @guuid: @error',
        ['@guuid' => $guuid, '@error' => $e->getMessage()]
      );
    }
    catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Error fetching group @guuid: @error',
        ['@guuid' => $guuid, '@error' => $e->getMessage()]
      );
    }

    return $data;
  }

  /**
   * Import a group from the source website and create it locally.
   *
   * @param array $data The group data
   *
   * @return void
   */
  public static function importGroup(array $data): void {
    if (empty($data) || !isset($data['data'])) {
      Drupal::logger('analytics_operations')->warning(
        'No data found for group @guuid',
        ['@guuid' => $data['data']['id']]
      );
    }

    try {
      $arr = $data['data'];
      $attrs = $arr['attributes'] ?? [];
      $rels = $arr['relationships'] ?? [];

      $group = Group::create([
        'id' => $attrs['drupal_internal__id'],
        'type' => 'content_owner_group_ty',
        'uuid' => $arr['id'],
        'status' => $attrs['status'],
        'label' => $attrs['label'] ?? 'Imported Group',
        'uid' => 85,
        'adfs_claims_handler_pwr' => $attrs['adfs_claims_handler_pwr'],
        'adfs_claims_handler_vwr' => $attrs['adfs_claims_handler_vwr'],
        'field_access_message' => $attrs['field_access_message'],
        'field_ad_group' => $attrs['field_ad_group'],
        'field_allow_requests' => $attrs['field_allow_requests'],
        'field_body' => $attrs['field_body'],
        'field_public_message' => $attrs['field_public_message'],
        'field_default_page' => $attrs['field_default_page'],
        'field_doc_library_container_id' => $attrs['field_doc_library_container_id'],
        'field_report_server_container_id' => $attrs['field_report_server_container_id'],
        'field_tableau_container_id' => $attrs['field_tableau_container_id'],
        'field_group_description' => $attrs['field_group_description'],
        'field_request_access_link' => $attrs['field_request_access_link'],
        'field_servicenow_id' => $attrs['field_servicenow_id'],
        'field_featured_content' => $rels['field_featured_content'],
        'field_hero_image' => $rels['field_hero_image'],
      ]);

      $group->save();

      Drupal::logger('analytics_operations')->info(
        'Successfully imported group @guuid with ID @id',
        ['@guuid' => $data['data']['id'], '@id' => $group->id()]
      );
    }
    catch (Exception $e) {
      Drupal::logger('analytics_operations')->error(
        'Failed to create group @guuid. Error: @error',
        ['@guuid' => $data['data']['id'], '@error' => $e->getMessage()]
      );
    }
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
        'Create Group operation complete.'
      );
      Drupal::messenger()->addMessage('All groups have been processed.');
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
