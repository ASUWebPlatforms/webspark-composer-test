<?php

/**
 * @file
 * Conttains Drupal\asuaec_viewbook_webformhandler\Form\RfiAdminSwitch
 */

namespace Drupal\asuaec_viewbook_webformhandler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 *Defines a form
 */
class RfiAdminSwitch extends ConfigFormBase{

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'asuaec_viewbook_webformhandler.customadmin_settings'; // Config variable name for module.

  /**
   *{ @inheritdoc}
   */
  public function getFormID(){
    return 'asuaec_viewbook_webformhandler_settings';
  }

  /**
   *{@inheritdoc}
   */
  protected function getEditableConfigNames(){
    return [
      static::SETTINGS,
    ];
  }

  /**
   *{@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asuaec_viewbook_webformhandler.customadmin_settings');

    $form['heading1'] = array(
      '#markup' => "<h3>Middleware</h3>",
    );
    // Middleware
    $form['posturl_prod'] =  array(
      '#type' => 'textfield',
      '#title' => 'Posting URL - Prod',
      '#maxlength' => 255,
      '#description' => t("Posting URL - Prod<br />For example: https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/ (Admission site)"),
      '#default_value' => $config->get('posturl_prod'),
    );
    $form['posturl_dev'] =  array(
      '#type' => 'textfield',
      '#title' => 'Posting URL - DEV',
      '#maxlength' => 255,
      '#description' => t("Posting URL - DEV<br />For example: https://3ceccsb54wpba5wrdg6kgxmlv40obcjl.lambda-url.us-west-2.on.aws/"),
      '#default_value' => $config->get('posturl_dev'),
    );

    $form['ground_sourceid_prod'] =  array(
     '#type' => 'textfield',
     '#title' => 'Ground source ID - Prod',
     '#maxlength' => 100,
     '#description' => t("Ground source ID - Prod<br />For example: 7016T000002Ti32QAC (Admission site)"),
     '#default_value' => $config->get('ground_sourceid_prod'),
    );
    $form['ground_sourceid_dev'] =  array(
      '#type' => 'textfield',
      '#title' => 'Ground source ID - DEV',
      '#maxlength' => 100,
      '#description' => t("Ground source ID - DEV<br />For example: 7016T000002c8qMQAQ"),
      '#default_value' => $config->get('ground_sourceid_dev') != ''?$config->get('ground_sourceid_dev'):'7016T000002c8qMQAQ',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   *{@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    parent::submitForm($form, $form_state);
//    $values =  $form_state->getValues();
//    foreach($values as $key => $each_value){
//      $this->config('asuaec_viewbook_webformhandler.customadmin_settings')
//         ->set($key, $each_value)
//         ->save();
//    }

    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configurations on our config.
      ->set('ground_sourceid_prod', $form_state->getValue('ground_sourceid_prod'))
      ->set('ground_sourceid_dev', $form_state->getValue('ground_sourceid_dev'))
      ->set('posturl_prod', $form_state->getValue('posturl_prod'))
      ->set('posturl_dev', $form_state->getValue('posturl_dev'))
      ->save();

    drupal_flush_all_caches();
  }
}
