<?php

namespace Drupal\analytics_groups\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Exception;

class AnalyticsGroupsController extends ControllerBase
{
  private static string $lambda;

  /**
   * Initializes the AWS constants.
   *
   * @return void
   */
  public static function initialize(): void
  {
    $settings = Drupal::service('settings');
    self::$lambda = $settings->get('group-automation-url');
  }

  /**
   * Create a new group.
   *
   * @param array $data
   * @return void
   */
  public static function createGroup(array $data): void
  {
    if (!isset(self::$lambda)) {
      self::initialize();
    }

    $lambda = self::$lambda;
    $lambda = $lambda . '?folder_name=' . $data['group_name'];
    $lambda = $lambda . '&folder_policy=' . $data['service_path'];

    $options = [
      // 'headers' => ['Content-Type' => 'application/json'],
      'timeout' => 15,
    ];

    foreach ($data['services'] as $service) {
      $lambda = $lambda . '&task=' . $service;

      // In the original code, there was a 3-second wait before creating the Drupal group
      try {
        Drupal::httpClient()->post($lambda, $options);
      } catch (Exception $e) {
        watchdog_exception('analytics_groups', $e);
      }
    }

    Drupal::messenger()->addMessage('Analytics group created.');
  }

  /**
   * Form for adding new Analytics groups.
   *
   * @return array
   */
  public function createGroupForm(): array
  {
    return Drupal::formBuilder()->getForm('Drupal\analytics_groups\Form\AnalyticsGroupsForm');
  }
}
