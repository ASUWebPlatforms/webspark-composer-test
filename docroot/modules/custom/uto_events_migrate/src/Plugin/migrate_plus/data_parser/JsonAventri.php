<?php

namespace Drupal\uto_events_migrate\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Drupal\Component\Serialization\Json as ComponentJson;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "json_aventri",
 *   title = @Translation("JSON Aventri")
 * )
 */
class JsonAventri extends Json {

  /**
   * Retrieves the JSON data and returns it as an array.
   *
   * @param string $url
   *   URL of a JSON feed.
   * @param string|int $item_selector
   *   Optional selector.
   *
   * @return array
   *   The selected data to be iterated.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  protected function getSourceData(string $url, string|int $item_selector = '') {
    // Use the existing data fetcher that this parser is configured to use for the
    // primary feed.
    $response = $this->getDataFetcherPlugin()->getResponseContent($url);
    $source_data = json_decode($response, TRUE);

    // If json_decode() returned NULL, try utf8_encode fallback.
    if (is_null($source_data)) {
      $utf8response = utf8_encode($response);
      $source_data = json_decode($utf8response, TRUE);
    }

    // If still null, return empty array.
    if (is_null($source_data)) {
      \Drupal::logger('uto_events_migrate')->error('Primary source JSON decode failed for URL: @url', ['@url' => $url]);
      return [];
    }

    // ---------------------------------------------------------------------
    // Now fetch additional event detail objects by eventid using the
    // migrate_plus http data_fetcher plugin (http_aventri).
    // Use the plugin manager so Drupal supplies the correct constructor args.
    // ---------------------------------------------------------------------

    // TODO: move base URL & auth config to module config rather than hardcoding.
    $base_url = 'https://api-na.eventscloud.com/api/v2/ereg/getEvent.json?eventid=';

    /** @var \Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\DataFetcherPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.migrate_plus.data_fetcher');

    // Prepare common configuration for the fetcher plugin. This config should
    // match what migrate_plus/http expects; adjust if you added custom keys.
    $fetcher_config = [
      // documentation/examples show 'urls' or 'url' keys; we will pass URLs
      // directly to the fetcher call below, so this can be empty for now.
      'urls' => [],
      'authentication' => [
        // Use your auth plugin id (AuthAventri).
        'plugin' => 'auth_aventri',
      ],
      // additional http fetcher options can go here (headers, cache, etc).
    ];

    // Create an instance of the http_aventri data fetcher plugin.
    // Drupal will pass the required constructor arguments so you don't get
    // ArgumentCountError.
    $fetcher = $plugin_manager->createInstance('http_aventri', $fetcher_config);

    // Walk through source items and augment with details.
foreach ($source_data as $key => $data) {
  // Skip rows that don't have an eventid.
  if (empty($data['eventid'])) {
    continue;
  }

      $eventid = $data['eventid'];
      $event_url = $base_url . $eventid;

      // Use the fetcher's public API to get the event JSON. This mirrors how
      // the parser requested the primary feed.
      try {
        // Many fetchers expect to be able to accept the URL as an argument to
        // getResponseContent(); use that (it matched your earlier usage).
        $details_raw = $fetcher->getResponseContent($event_url);
        $details_data = json_decode($details_raw, TRUE);

        if (is_null($details_data)) {
          // Try utf8 fallback for the event details.
          $details_data = json_decode(utf8_encode($details_raw), TRUE);
        }

        if (!is_array($details_data)) {
          // If still not an array, log and skip merging.
          \Drupal::logger('uto_events_migrate')->warning('Failed to decode event details for eventid @id', ['@id' => $eventid]);
          continue;
        }

        // Extract img src from description (if present).
        if (!empty($details_data['description'])) {
          $details_data['imgsrc'] = $this->getImgUrl($details_data['description']);
        }

        // Merge primary row with the details (details take precedence).
        $source_data[$key] = array_merge($source_data[$key], $details_data);
      }
      catch (\Exception $e) {
        // Log errors per-event and continue; don't fatal.
        \Drupal::logger('uto_events_migrate')->error('Error fetching details for eventid @id: @msg', [
          '@id' => $eventid,
          '@msg' => $e->getMessage(),
        ]);
        // Keep the original source row (no merge) and continue.
        continue;
      }
    }

    // ---------------------------------------------------------------------
    // Backwards-compatibility for depth selection / xpath-like selectors
    // ---------------------------------------------------------------------
    if (is_int($this->itemSelector)) {
      return $this->selectByDepth($source_data);
    }

    $selectors = explode('/', trim($this->itemSelector, '/'));
    foreach ($selectors as $selector) {
      if ($selector === '') {
        continue;
      }
      if (!isset($source_data[$selector])) {
        // If a selector path doesn't exist, return empty to avoid notices.
        \Drupal::logger('uto_events_migrate')->warning('Selector @sel not found in source data', ['@sel' => $selector]);
        return [];
      }
      $source_data = $source_data[$selector];
    }
    

    return $source_data;
  }

  /**
   * Extract image src uri from the image element in the description field.
   *
   * @param string $description
   *   HTML description text.
   *
   * @return string
   *   The first img src found or a default image.
   */
  protected function getImgUrl($description){
    // collect image tags
    preg_match_all('/<img[^>]+>/i',$description, $results);
    $src = '';
    if (!empty($results[0])) {
      // Use the first img tag.
      $img_tag = $results[0][0];
      if (!empty($img_tag)) {
        // Suppress warnings while parsing possibly malformed HTML.
        $doc = new \DOMDocument();
        @$doc->loadHTML($img_tag);
        $xpath = new \DOMXPath($doc);
        $src = $xpath->evaluate("string(//img/@src)");
      }
    }

    if (empty($src)) {
      // Fallback default.
      $src = 'https://tech.asu.edu/sites/default/files/2021-10/ASU-UTO-home.png';
    }
    return $src;
  }

}
