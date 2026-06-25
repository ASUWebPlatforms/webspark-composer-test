<?php

namespace Drupal\asuaec_asulocal\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Client service for ASU Programs AppSync GraphQL API.
 */
class AsuProgramsClient {

  /**
   * HTTP client (Guzzle).
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $cache;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Logger for this module.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Default cache TTL for list responses (seconds).
   *
   * @var int
   */
  protected int $listTtl = 3600;

  /**
   * Default cache TTL for individual program details (seconds).
   *
   * @var int
   */
  protected int $detailTtl = 3600;

  /**
   * Constructs the AsuProgramsClient.
   *
   * Services to inject in services.yml:
   *   - @http_client
   *   - @cache.default
   *   - @config.factory
   *   - @logger.factory
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Guzzle HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(ClientInterface $http_client, CacheBackendInterface $cache, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $http_client;
    $this->cache = $cache;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('asuaec_asulocal');
  }

  /**
   * Return endpoint from config.
   *
   * @return string
   *   Endpoint URL (may be empty).
   */
  protected function getEndpoint(): string {
    return (string) $this->configFactory->get('asuaec_asulocal.settings')->get('endpoint') ?? '';
  }

  /**
   * Return API key from config.
   *
   * @return string
   *   API key (may be empty).
   */
  protected function getApiKey(): string {
    return (string) $this->configFactory->get('asuaec_asulocal.settings')->get('api_key') ?? '';
  }

  /**
   * Generic helper to post a GraphQL payload and decode JSON.
   *
   * Uses Guzzle's 'json' option so the body/Content-Type are consistent.
   */
  public function postGraphQL(array $payload, int $timeout = 15): ?array {
    $endpoint = $this->getEndpoint();
    $api_key = $this->getApiKey();

    if (empty($endpoint) || empty($api_key)) {
      $this->logger->error('AppSync endpoint or API key is not configured.');
      return NULL;
    }

    // Log payload for debugging (truncated).
    $this->logger->debug('AppSync request: @payload', ['@payload' => json_encode($payload)]);

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'x-api-key' => $api_key,
      ],
      // Use Guzzle's json option so encoding and content-type are guaranteed correct.
      'json' => $payload,
      'timeout' => $timeout,
      'connect_timeout' => 5,
      'http_errors' => FALSE,
    ];

    try {
      $response = $this->httpClient->post($endpoint, $options);
      $status = $response->getStatusCode();
      $body = (string) $response->getBody();
      $this->logger->debug('AppSync response status: @status', ['@status' => $status]);
      $this->logger->debug('AppSync response body (truncated): @body', ['@body' => substr($body, 0, 2000)]);
      $decoded = json_decode($body, TRUE);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->logger->error('Failed to decode JSON response: @err', ['@err' => json_last_error_msg()]);
        return NULL;
      }
      return $decoded;
    }
    catch (\Throwable $e) {
      $this->logger->error('HTTP GraphQL request failed: @msg', ['@msg' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * Retrieve a single program by id.
   *
   * Performs a GraphQL query requesting only safe fields (matching schema).
   * Falls back to list search if direct lookup fails or returns partial/null.
   *
   * @param string $id
   *   Program UUID.
   *
   * @return array|null
   *   Program associative array or NULL if not found.
   */
  public function getProgramById(string $id): ?array {
    $cache_key = "asuaec_asulocal:program:{$id}";
    // Try cache first.
    if ($cache = $this->cache->get($cache_key)) {
      return $cache->data;
    }

    // Minimal / safe fields present in ProgramUndergraduate.
    $query = <<<'GQL'
query GetProgramUndergraduate($id: String!) {
  getProgramUndergraduate(id: $id) {
    id
    title
    short_description
    detail_page
    degree_image
    program_code
    total_credit_hours
    weeks_per_class
    total_classes
    featured_courses_title
    featured_courses_description
    related_careers_title
    related_careers_description
    related_careers_image
    next_start_date
    interest_areas {
      title
      slug
    }
    bamm_program {
      career_items
    }
    curriculum_course {
      course_items
    }
  }
}
GQL;

    $payload = [
      'query' => $query,
      'variables' => ['id' => $id],
    ];

      $this->logger->notice('id:' . $id);
      $this->logger->notice('payload:<pre>' . print_r($payload, true) . '</pre>');

    try {
      $resp = $this->postGraphQL($payload, 10);
    }
    catch (\Throwable $e) {
      // If request threw because of timeout or other error, log and return NULL.
      $this->logger->error('GraphQL getProgramById failed for @id: @msg', ['@id' => $id, '@msg' => $e->getMessage()]);
      return NULL;
    }

    if (empty($resp)) {
      return NULL;
    }

    // If GraphQL returned errors, log them.
    if (!empty($resp['errors'])) {
      $this->logger->error('GraphQL returned errors for getProgramById(@id): @errors', ['@id' => $id, '@errors' => json_encode($resp['errors'])]);
      // If the errors are about nullability or undefined fields, fallback to list search.
    }

    $program = $resp['data']['getProgramUndergraduate'] ?? NULL;

    if (!empty($program['degree_image'])) {
      $program['degree_image'] = $this->normalizeDegreeImageUrl($program['degree_image']);
    }

    // If the response is null or missing id (schema non-null violation) fall back.
    if (empty($program) || empty($program['id'])) {
      // Fallback: search list for the id.
      $this->logger->notice('getProgramById returned null or missing id; falling back to list search for @id', ['@id' => $id]);
      $found = $this->findInListById($id);
      if ($found) {
        // Cache found program for shorter TTL.
        $this->cache->set($cache_key, $found, time() + $this->detailTtl);
      }
      return $found;
    }

    // Cache and return.
    $this->cache->set($cache_key, $program, time() + $this->detailTtl);
    return $program;
  }

  /**
   * List programs using the allProgramsUndergraudate query (single page).
   *
   * @param int $limit
   *   Page size (GraphQL limit).
   * @param string|null $nextToken
   *   Next token to resume paging (or NULL for first page).
   *
   * @return array|null
   *   Query result array with keys 'nextToken' and 'items' or NULL on failure.
   */
  public function listAllPrograms(int $limit = 50, ?string $nextToken = NULL): ?array {
    $cache_key = 'asuaec_asulocal:list:' . $limit . ':' . ($nextToken ?? 'null');

    if ($cache = $this->cache->get($cache_key)) {
      return $cache->data;
    }

    $query = <<<'GQL'
query ($limit:Int, $nextToken:String) {
  allProgramsUndergraudate(limit: $limit, nextToken: $nextToken) {
    nextToken
    items {
      id
      title
      short_description
      degree_image
      program_code
      total_credit_hours
      code
      interest_areas {
        title
        slug
      }      
    }
  }
}
GQL;

    // Build variables but omit nextToken entirely when NULL.
    $variables = ['limit' => $limit];
    if (!is_null($nextToken)) {
      $variables['nextToken'] = $nextToken;
    }

    $payload = [
      'query' => $query,
      'variables' => $variables,
    ];

    try {
      $resp = $this->postGraphQL($payload, 15);
    }
    catch (\Throwable $e) {
      $this->logger->error('listAllPrograms GraphQL request failed: @msg', ['@msg' => $e->getMessage()]);
      return NULL;
    }

    if (empty($resp)) {
      return NULL;
    }

    if (!empty($resp['errors'])) {
      $this->logger->error('GraphQL errors on listAllPrograms: @errors', ['@errors' => json_encode($resp['errors'])]);
      // AppSync sandbox timeout often returns errors array; try a fallback small page.
      // Fallback behavior: if we requested a large page, try a small page to get some items.
      if ($limit > 10) {
        $this->logger->notice('listAllPrograms timed out or errored; retrying with limit=10');
        return $this->listAllPrograms(10, NULL);
      }
      return NULL;
    }

    $result = $resp['data']['allProgramsUndergraudate'] ?? NULL;
    if ($result) {
      $result['items'] = $result['items'] ?? [];
      $this->cache->set($cache_key, $result, time() + $this->listTtl);
    }

    if (!empty($result['items']) && is_array($result['items'])) {
      foreach ($result['items'] as &$item) {
        if (!empty($item['degree_image'])) {
          $item['degree_image'] = $this->normalizeDegreeImageUrl($item['degree_image']);
        }
      }
      unset($item);
    }
    return $result;
  }

  /**
   * Helper that pages through allProgramsUndergraudate and returns aggregated items.
   *
   * Use with care; may perform multiple network calls.
   *
   * @param int $maxPages
   *   Maximum pages to request (safety).
   * @param int $pageSize
   *   Items per page to request.
   *
   * @return array|null
   *   Aggregated items array or NULL on complete failure.
   */
  public function getAllPrograms(int $maxPages = 10, int $pageSize = 100): ?array {
    $cache_key = "asuaec_asulocal:all_programs:{$pageSize}:{$maxPages}";
    if ($cache = $this->cache->get($cache_key)) {
      return $cache->data;
    }

    $items = [];
    $next = NULL;
    $page = 0;

    do {
      $page++;
      $resp = $this->listAllPrograms($pageSize, $next);
      if (empty($resp) || !isset($resp['items'])) {
        $this->logger->notice('Stopped paging getAllPrograms early: page @p response empty.', ['@p' => $page]);
        break;
      }
      $items = array_merge($items, $resp['items']);
      $next = $resp['nextToken'] ?? NULL;
      // Safety: stop if no token or reached page limit
    } while (!empty($next) && $page < $maxPages);

    // cache aggregated list
    $this->cache->set($cache_key, $items, time() + $this->listTtl);
    return $items;
  }

  /**
   * Find a program in a cached list by slug/code.
   *
   * The slug passed from your route typically looks like "ugba-baaccbs".
   * This compares the lowercase of program 'code' or 'program_code' to the slug.
   *
   * @param string $slug
   *   Slug to search for.
   *
   * @return array|null
   *   Matching program item or NULL.
   */
  public function findInListBySlug(string $slug): ?array {
    $slug = trim(strtolower($slug));

    // Prefer cached aggregated list.
    $all = $this->getAllPrograms(5, 100);
    if (empty($all) || !is_array($all)) {
      // If aggregated list failed, try a single page to at least search one page.
      $page = $this->listAllPrograms(100, NULL);
      $items = $page['items'] ?? [];
    }
    else {
      $items = $all;
    }

    foreach ($items as $item) {
      $code = strtolower($item['code'] ?? $item['program_code'] ?? '');
      if ($code === $slug) {
        return $item;
      }
      // Also support matching slug to id.
      if (($item['id'] ?? '') === $slug) {
        return $item;
      }
    }

    return NULL;
  }

  /**
   * Find a program in a cached list by id.
   *
   * @param string $id
   *   Program id.
   *
   * @return array|null
   *   Matching program or NULL.
   */
  protected function findInListById(string $id): ?array {
    $all = $this->getAllPrograms(5, 100);
    if (empty($all)) {
      return NULL;
    }
    foreach ($all as $item) {
      if (($item['id'] ?? '') === $id) {
        return $item;
      }
    }
    return NULL;
  }

  /**
   * Retrieve a program by slug: preferred direct lookup, else list search.
   *
   * @param string $slug
   *   Slug like "ugba-baaccbs".
   *
   * @return array|null
   *   Program array or NULL if not found.
   */
  public function getProgramBySlug(string $slug): ?array {
    $slug = trim(strtolower($slug));

    // Some programs can be looked up by id directly if slug looks like UUID.
    if (preg_match('/^[0-9a-fA-F\-]{36}$/', $slug)) {
      // treat as id
      return $this->getProgramById($slug);
    }

    // Try to get by scanning list (fast if cached).
    $found = $this->findInListBySlug($slug);
    if ($found) {
      // If we only have list-level fields but want details, attempt getProgramById.
      if (!empty($found['id'])) {
        $details = $this->getProgramById($found['id']);
        return $details ?? $found;
      }
      return $found;
    }

    // Not found in list; return NULL.
    return NULL;
  }

  /**
   * Alter the image from Development to use the Production URL.
   *
   * @param string|null $url
   *   Original image URL.
   *
   * @return string|null
   *   Rewritten image URL or original value.
   */
  protected function normalizeDegreeImageUrl(?string $url): ?string {
    if (empty($url)) {
      return $url;
    }

    return str_replace(
      'https://live-asuocms.ws.asu.edu',
      'https://cms.asuonline.asu.edu',
      $url
    );
  }

}
