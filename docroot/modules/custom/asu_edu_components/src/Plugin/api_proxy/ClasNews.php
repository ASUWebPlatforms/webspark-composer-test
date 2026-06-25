<?php

namespace Drupal\asu_edu_components\Plugin\api_proxy;

use Drupal\api_proxy\Plugin\api_proxy\HttpApiCommonConfigs;
use Drupal\api_proxy\Plugin\HttpApiPluginBase;
use Drupal\Core\Form\SubformStateInterface;

/**
 * The Clas News API.
 *
 * @HttpApi(
 *   id = "api-clas-news",
 *   label = @Translation("Clas News API"),
 *   description = @Translation("Proxies requests to the Clas News API."),
 *   serviceUrl = "https://asunow.asu.edu/feeds-json",
 * )
 */
final class ClasNews extends HttpApiPluginBase {

  use HttpApiCommonConfigs;

  /**
   * {@inheritdoc}
   */
  public function addMoreConfigurationFormElements(array $form, SubformStateInterface $form_state): array {
    return $form;
  }

}
