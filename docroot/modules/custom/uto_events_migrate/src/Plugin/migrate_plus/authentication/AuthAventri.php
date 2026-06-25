<?php

namespace Drupal\uto_events_migrate\Plugin\migrate_plus\authentication;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate_plus\AuthenticationPluginBase;
use Drupal\Component\Serialization\Json;

/**
 * Provides Aventri authentication for the HTTP resource.
 *
 * @Authentication(
 *   id = "auth_aventri",
 *   title = @Translation("Aventri")
 * )
 */
class AuthAventri extends AuthenticationPluginBase implements ContainerFactoryPluginInterface {
/**
 * {@inheritdoc}
 */
public function getAuthenticationOptions($url = NULL): array {
  $http_client = \Drupal::httpClient();

  // Use provided URL if passed, otherwise use the hardcoded auth URL.
  $auth_url = $url ?? 'https://api-na.eventscloud.com/api/v2/global/authorize.json';

  // If we are using the fallback URL, append credentials.
  if ($url === NULL) {
    $accountid = '6377';
    $key = '2b4d6b282617f897b7054391086683e6d89db953';
    $auth_url .= '?accountid=' . $accountid . '&key=' . $key;
  }

  try {
    $response = $http_client->get($auth_url);
    $body = (string) $response->getBody();
    $decoded = Json::decode($body);
    return is_array($decoded) ? $decoded : (array) $decoded;
  }
  catch (\Exception $e) {
    \Drupal::logger('uto_events_migrate')->error('Aventri auth request failed: @msg', ['@msg' => $e->getMessage()]);
    return [];
  }
}

}
