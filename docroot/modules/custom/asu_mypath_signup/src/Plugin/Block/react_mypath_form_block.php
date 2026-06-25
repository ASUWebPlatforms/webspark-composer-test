<?php

namespace Drupal\asu_mypath_signup\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 @file
 * Contains \Drupal\asu_mypath_signup\Plugin\Block\react_mypath_form_block
 */






/**
 * Provides a MyPath2ASU form block.
 *
 * @Block(
 *   id = "react_mypath_form_block",
 *   admin_label = @Translation("React ASU MyPath2ASU form block"),
 *   category = @Translation("React ASU MyPath2ASU form block"),
 * )
 */
class react_mypath_form_block extends BlockBase {



   /**
   * {@inheritdoc}
   */
  public function build() {
   $build = [];
   $defaultValues = \Drupal::config('asu_mypath_signup.settings');
   $mypathFieldSettingsValues = [
     'maricopaInstIds' => $defaultValues->get('maricopa_inst_ids'),
     'apiEndpoint' => $defaultValues->get('maricopa_api_url'),
     'maricopaNoMatchText' => $defaultValues->get('maricopa_nomatch_text'),
     //'debugMode' => $defaultValues->get('debug_mode'),
     'enableMaricopaField' => $defaultValues->get('enable_maricopa_field'),
     'desktopConsentText' => $defaultValues->get('desktop_consent_text'),
     'mobileConsentText' => $defaultValues->get('mobile_consent_text'),
  ];
   //dpm($mypathFieldSettingsValues,'MyPath Field Settings Values'); 
   $build['react_mapp_block'] = [
     '#markup' => '<div id="my-app-target"></div>',
     '#attached' => [
       'library' => ['asu_mypath_signup/my-app'],
       'drupalSettings' => [
         'asu_mypath_signup' => $mypathFieldSettingsValues,
       ],
     ],
   ];
   return $build;
 }
}