<?php

namespace Drupal\uto_events_migrate\Plugin\migrate_plus\data_fetcher;

use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\Http;
use Drupal\migrate\MigrateException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Retrieve data over an HTTP connection for migration.
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: url
 *   data_fetcher_plugin: http_aventri
 *   headers:
 *     Accept: application/json
 *     User-Agent: Internet Explorer 6
 *     Authorization-Key: secret
 *     Arbitrary-Header: foobarbaz
 * @endcode
 *
 * @DataFetcher(
 *   id = "http_aventri",
 *   title = @Translation("HTTP Aventri")
 * )
 */
class HttpAventri extends Http {

  /**
   * {@inheritdoc}
   */
  public function getResponse($url): ResponseInterface {
    try {
      // Get access token from AuthAventri plugin and add it as a param to the url for API call.
      if (!empty($this->configuration['authentication'])) {
        $access_token = $this->getAuthenticationPlugin()->getAuthenticationOptions();
        $url .= '&accesstoken=' . $access_token['accesstoken'];
      }
      $response = $this->httpClient->get($url);
      if (empty($response)) {
        throw new MigrateException('No response at ' . $url . '.');
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at ' . $url . '.');
    }
    return $response;
  }

}
