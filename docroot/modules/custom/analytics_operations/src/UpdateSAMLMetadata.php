<?php

namespace Drupal\analytics_operations;

use Drupal;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use SimpleSAML\Error\Exception;
use SimpleSAML\Metadata\SAMLParser;
use SimpleSAML\Utils;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;

class UpdateSAMLMetadata
{
  /**
   * @param string $metadata
   * @param string $file
   *
   * @return void
   * @throws GuzzleException
   */
  public static function init(string $metadata, string $file): void
  {
    $destination = Drupal::service('file_system')->realpath($file);
    if (!file_exists($destination)) {
      Drupal::messenger()->addError('Local metadata file not found.');
      return;
    }

    $xml_content = static::download($metadata);
    if ($xml_content) {
      try {
        $php_content = static::convert($xml_content);

        if ($php_content) {
          file_put_contents($destination, $php_content);
          Drupal::logger('analytics_operations')->info('SAML metadata updated successfully.');
          Drupal::messenger()->addMessage('SAML metadata updated successfully.');
        } else {
          Drupal::logger('analytics_operations')->error('Failed to update SAML metadata.');
          Drupal::messenger()->addError('Failed to update SAML metadata.');
        }
      } catch (Exception|ExceptionInterface $e) {
        Drupal::logger('analytics_operations')->error($e->getMessage());
        Drupal::messenger()->addError($e->getMessage());
      }
    } else {
      Drupal::logger('analytics_operations')->error('Failed to download XML metadata.');
      Drupal::messenger()->addError('Failed to download XML metadata.');
    }
  }

  /**
   * Downloads the XML file.
   *
   * @param string $url
   *
   * @return bool|string
   * @throws GuzzleException
   */
  private static function download(string $url): bool|string
  {
    try {
      $client = Drupal::httpClient();
      $response = $client->get($url);
      if ($response->getStatusCode() == 200) {
        return $response->getBody()->getContents();
      }
    } catch (RequestException $e) {
      Drupal::logger('analytics_operations')->error('Error downloading XML: @message', ['@message' => $e->getMessage()]);
      Drupal::messenger()->addError('Error downloading XML, check logs for more details.');
    }
    return false;
  }

  /**
   * Converts XML content to PHP using SimpleSAML's XML to PHP metadata converter.
   *
   * A bulk of the code is taken directly from:
   * SimpleSAML\Module\admin\Controller\Federation::metadataConverter
   *
   * @param string $xmldata
   *
   * @return SAMLParser[]|string|void|null
   * @throws ExceptionInterface
   * @throws Exception
   */
  private static function convert(string $xmldata)
  {
    if (!empty($xmldata)) {
      // Create a temporary file for the XML content
      $temp_xml = tempnam(sys_get_temp_dir(), 'saml_xml_');
      file_put_contents($temp_xml, $xmldata);

      $xmlUtils = new Utils\XML();
      $xmlUtils->checkSAMLMessage($xmldata, 'saml-meta');

      try {
        $entities = SAMLParser::parseDescriptorsString($xmldata);
      } catch (Exception $e) {
        $entities = null;
        Drupal::logger('analytics_operations')->error('Error converting XML to PHP: @message', ['@message' => $e->getMessage()]);
        Drupal::messenger()->addError('Error converting XML to PHP, check logs for more details.');
      }

      if ($entities !== null) {
        // Get all metadata for the entities
        foreach ($entities as &$entity) {
          $entity = [
            'saml20-sp-remote' => $entity->getMetadata20SP(),
            'saml20-idp-remote' => $entity->getMetadata20IdP(),
          ];
        }

        // Transpose from $entities[entityid][type] to $output[type][entityid]
        $arrayUtils = new Utils\Arrays();
        $output = $arrayUtils->transpose($entities);

        // Merge all metadata of each type to a single string which should be added to the corresponding file
        foreach ($output as $type => &$entities) {
          $text = '';
          foreach ($entities as $entityId => $entityMetadata) {
            if ($entityMetadata === null) {
              continue;
            }

            /**
             * Remove the entityDescriptor element because it is unused,
             * and only makes the output harder to read
             */
            unset($entityMetadata['entityDescriptor']);

            /**
             * Remove any expire from the metadata. This is not so useful
             * for manually converted metadata and frequently gives rise
             * to unexpected results when copy-pasted statically
             */
            unset($entityMetadata['expire']);

            $text .= "<?php\n\n";
            $text .= '$metadata[' . var_export($entityId, true) . '] = ' . VarExporter::export($entityMetadata) . ";\n";
          }

          $entities = $text;
        }
      }

      // Clean up the temporary file
      unlink($temp_xml);
      return $entities;
    }
  }
}
