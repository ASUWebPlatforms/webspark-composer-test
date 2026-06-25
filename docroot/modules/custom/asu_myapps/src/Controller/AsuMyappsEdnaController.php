<?php

namespace Drupal\asu_myapps\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class AsuMyappsEdnaController extends ControllerBase
{
  private static string $ednaHost;
  private static string $ednaKey;
  private static string $ednaSecret;

  /**
   * Initializes the EDNA properties.
   *
   * It is important to note that the properties are set in the Pantheon
   * runtime environment.
   *
   * @return void
   */
  public static function initialize(): void
  {
    $settings = Drupal::service('settings');

    self::$ednaHost = $settings->get('edna-host');
    self::$ednaKey = $settings->get('edna-key');
    self::$ednaSecret = $settings->get('edna-secret');

    // Check if the host has a trailing slash, and if not, add one
    if (!str_ends_with(self::$ednaHost, '/')) {
      self::$ednaHost .= '/';
    }
  }

  /**
   * Build a user array to pass to EDNA.
   *
   * This method will return an array with the user's ASURITE and an array of
   * access groups for EDNA to check against.
   *
   * @param $user
   * @param $access_groups
   * @return array
   */
  public static function getUserData($user, $access_groups): array
  {
    $data = [
      'asurite' => $user->getAccountName(),
      'access_groups' => [],
    ];

    foreach ($access_groups as $item) {
      $data['access_groups'][] = Term::load($item['target_id']);
    }

    return $data;
  }

  /**
   * Determine if the current user has access to the given group.
   *
   * This method will first check if the proper EDNA constants are set. If not,
   * it will initialize them. It will then create a generator function to create
   * a pool of requests to EDNA.
   *
   * It is important to note that this method is using the EDNA Proxy service, as the
   * default EDNA port is no longer open to the internet.
   *
   * @see https://github.com/ASU/edna-checkaccess-proxy
   *
   * @param string $asurite
   * @param array $access_groups
   * @return bool
   */
  public static function getUserAccess(string $asurite, array $access_groups): bool
  {
    if (!isset(self::$ednaHost)) {
      self::initialize();
    }

    $client = Drupal::httpClient();
    $accessResults = [];

    // Create a generator function for the requests
    $requestGenerator = function () use ($asurite, $access_groups) {
      foreach ($access_groups as $group) {
        $params = [
          'oauth_key' => self::$ednaKey,
          'oauth_secret' => self::$ednaSecret,
          'principal' => $asurite,
          'servicePath' => 'MyApps.' . $group->getName(),
        ];

        $url = self::$ednaHost . '?' . http_build_query($params);
        yield new Request('GET', $url);
      }
    };

    // Create a pool of requests with a concurrency limit of 5
    $pool = new Pool($client, $requestGenerator(), [
      'concurrency' => 5,
      'fulfilled' => function (ResponseInterface $response) use (&$accessResults) {
        $result = json_decode($response->getBody(), true);
        $accessResults[] = $result['permitAccess'];
      },
      'rejected' => function (RequestException $e) {
        Drupal::logger('asu_myapps')->error('EDNA checkAccess connection error: @error', ['@error' => $e->getMessage()]);
      },
    ]);

    // Initiate the transfers, create a promise, and force the pool of requests to complete
    $promise = $pool->promise();
    $promise->wait();

    return in_array(true, $accessResults, true);
  }
}
