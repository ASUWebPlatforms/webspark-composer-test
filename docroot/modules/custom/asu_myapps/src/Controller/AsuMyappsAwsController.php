<?php

namespace Drupal\asu_myapps\Controller;

use Aws\CloudFront\UrlSigner;
use Aws\S3\S3Client;
use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Exception;

class AsuMyappsAwsController extends ControllerBase
{
  private static string $awsHost;
  private static string $awsKeyPairId;
  private static string $awsPrivateKeyFile;
  private static string $awsRegion;
  private static string $awsBucket;
  private static int $awsExpires;

  /**
   * Initializes the AWS properties.
   *
   * It is important to note that the properties are set in the Pantheon
   * runtime environment.
   *
   * @return void
   */
  public static function initialize(): void
  {
    $settings = Drupal::service('settings');

    // Retrieve AWS configuration from settings.php
    self::$awsHost = $settings->get('aws-host');
    self::$awsKeyPairId = $settings->get('aws-key-pair-id');
    self::$awsPrivateKeyFile = $settings->get('aws-private-key-file');
    self::$awsExpires = $settings->get('aws-expires');
    self::$awsRegion = $settings->get('aws-region');
    self::$awsBucket = $settings->get('aws-bucket');
  }

  /**
   * Creates a signed URL for a CloudFront resource.
   *
   * This method will first check if the proper AWS constants are set. If not,
   * it will initialize them. It will then create a signed URL for the given
   * file and return it.
   *
   * The AWS SDK requires a private key file to create a signed URL. This method
   * will create a temporary file in the Pantheon tmp storage and write the
   * contents of the private key to it. It will then create the signed URL and
   * delete the temporary file.
   *
   * @param string $file
   * @param int|null $expires
   * @return string|null
   */
  public static function createSignedUrl(string $file, int $expires = null): ?string
  {
    if (!isset(self::$awsHost)) {
      self::initialize();
    }

    $signedUrl = null;

    // If the file begins with a slash, remove it
    if (str_starts_with($file, '/')) {
      $file = substr($file, 1);
    }

    $url = self::$awsHost . '/' . $file;
    $expires = empty($expires) ? self::$awsExpires : $expires;
    $expires = time() + $expires;

    // Create a file in tmp storage on Pantheon
    $tmp_file = tempnam('//tmp', 'tmp');

    // Write the contents of the secret to the tmp file
    file_put_contents($tmp_file, self::$awsPrivateKeyFile);

    try {
      $urlSigner = new UrlSigner(self::$awsKeyPairId, $tmp_file);
      $signedUrl = $urlSigner->getSignedUrl($url, $expires);

      // Pantheon does not clean up the tmp file, we need to do it ourselves
      unlink($tmp_file);
      Drupal::messenger()->addMessage('Your download will begin shortly.');
    } catch (Exception $e) {
      Drupal::logger('asu_myapps')->error($e->getMessage());
      Drupal::messenger()->addMessage('There was an error downloading your file. Please try again soon.');
    }

    return $signedUrl;
  }

  /**
   * Creates a pre-signed URL for an S3 object using AWS Signature Version 4.
   *
   * This method will first check if the proper AWS constants are set. If not,
   * it will initialize them. It will then create a pre-signed URL for the given
   * file and return it.
   *
   * This method is only provided as a fallback in case we can no longer use the
   * CloudFront signed URLs. It is not currently used.
   *
   * @param string $file
   * @param string|null $expires
   * @return string|null
   */
  public static function createPresignedUrl(string $file, string $expires = null): ?string
  {
    if (!isset(self::$awsRegion)) {
      self::initialize();
    }

    $awsConfig = [
      'version' => 'latest',
      'region' => self::$awsRegion,
      'credentials' => false,
    ];

    $s3Client = new S3Client($awsConfig);
    $cmd = $s3Client->getCommand('GetObject', [
      'Bucket' => self::$awsBucket,
      'Key' => $file,
    ]);
    $expires = empty($expires) ? self::$awsExpires : $expires;

    try {
      $request = $s3Client->createPresignedRequest($cmd, $expires);
    } catch (Exception $e) {
      Drupal::logger('asu_myapps')->error($e->getMessage());

      return null;
    }

    return (string)$request->getUri();
  }
}
