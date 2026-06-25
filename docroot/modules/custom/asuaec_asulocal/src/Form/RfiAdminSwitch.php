<?php

/**
 * @file
 * Conttains Drupal\asuaec_asulocal\Form\RfiAdminSwitch
 */

namespace Drupal\asuaec_asulocal\Form;

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
  const SETTINGS = 'asuaec_asulocal.customadmin_settings'; // Config variable name for module.

  /**
   *{ @inheritdoc}
   */
  public function getFormId(){
    return 'asuaec_asulocal_settings';
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
    $config = $this->config('asuaec_asulocal.customadmin_settings');

    // Site name
    $form['heading3'] = array(
      '#markup' => "<h3>Site name</h3>",
    );
    $form['sitename'] =  array(
      '#type' => 'textfield',
      '#title' => 'Acquia site name',
      '#maxlength' => 100,
      '#description' => t("For example: studentsasu"),
      '#default_value' => $config->get('sitename'),
    );
    
    // Prod domain
    $form['heading4'] = array(
      '#markup' => "<h3>Prod domain</h3>",
    );
    $form['proddomain'] =  array(
      '#type' => 'textfield',
      '#title' => 'Prod domain',
      '#maxlength' => 100,
      '#description' => t("For example: https://students.asu.edu"),
      '#default_value' => $config->get('proddomain'),
    );

    // Ground -- There is no Ground for ASU Local.
    // $form['heading1'] = array(
    //   '#markup' => "<h3>Ground</h3>",
    // );
    // $form['ground_sourceid_prod'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Ground source ID - Prod',
    //   '#maxlength' => 100,
    //   '#description' => t("Ground source ID - Prod<br />For example: 7016T000002Ti32QAC (Admission site)"),
    //   '#default_value' => $config->get('ground_sourceid_prod'),
    // );
    // $form['ground_sourceid_dev'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Ground source ID - DEV',
    //   '#maxlength' => 100,
    //   '#description' => t("Ground source ID - DEV<br />For example: 7016T000002c8qMQAQ"),
    //   '#default_value' => $config->get('ground_sourceid_dev') != ''?$config->get('ground_sourceid_dev'):'7016T000002c8qMQAQ',
    // );
    // $form['ground_posturl_prod'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Ground posting URL - Prod',
    //   '#maxlength' => 255,
    //   '#description' => t("Ground posting URL - Prod<br />For example: https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/ (Admission site)"),
    //   '#default_value' => $config->get('ground_posturl_prod'),
    // );
    // $form['ground_posturl_dev'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Ground posting URL - DEV',
    //   '#maxlength' => 255,
    //   //'#description' => t("Ground posting URL - DEV<br />For example: https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/"),
    //   '#description' => t("Ground posting URL - DEV<br />For example: https://3ceccsb54wpba5wrdg6kgxmlv40obcjl.lambda-url.us-west-2.on.aws/"),
    //   '#default_value' => $config->get('ground_posturl_dev'),
    // );

    /**
     * Block content IDs for embedded Online RFI blocks (vary by env).
     */
    $form['heading5'] = array(
      '#markup' => "<h3>Webform block_content IDs for Online RFI</h3>",
    );
    $form['online_rfi_block_content_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Online RFI Webform block_content IDs'),
      '#description' => $this->t('Enter the Block Content IDs (numbers) for the Online RFI Webform block. One per line. Get it from /admin/structure/block/list/renovation page. Example: 951 for PROD and 961 for DEV'),
      '#default_value' => $config->get('online_rfi_block_content_ids') ?: '',
      '#rows' => 5,
    ];

    // Online
    $form['heading2'] = array(
      '#markup' => "<h3>Online</h3>",
    );
    $form['online_sourceid_prod'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online source ID - Prod',
      '#maxlength' => 100,
      '#description' => t("Online source ID - Prod<br />For example: UFCW99-I+sCa5YI73uLG9kA"),
      '#default_value' => $config->get('online_sourceid_prod'),
    );
    $form['online_sourceid_dev'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online source ID - DEV',
      '#maxlength' => 100,
      '#description' => t("Online source ID - DEV<br />For example: UFCW99-I+sCa5YI73uLG9kA"),
      '#default_value' => $config->get('online_sourceid_dev'),
    );
    $form['online_posturl_prod'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online posting URL - Prod',
      '#maxlength' => 255,
      '#description' => t("Online posting URL - Prod<br />For example: https://api.edpl.us/v1/asuo/rfi-leads"),
      '#default_value' => $config->get('online_posturl_prod') != ''?$config->get('online_posturl_prod'):'https://api.edpl.us/v1/asuo/rfi-leads',
    );
    $form['online_posturl_dev'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online posting URL - DEV',
      '#maxlength' => 255,
      '#description' => t("Online posting URL - DEV<br />For example: https://qa-api.edpl.us/v1/asuo/rfi-leads"),
      '#default_value' => $config->get('online_posturl_dev') != '' ? $config->get('online_posturl_dev') : 'https://qa-api.edpl.us/v1/asuo/rfi-leads',
    );
    $form['online_leadclass_prod'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online lead class - Prod',
      '#maxlength' => 100,
      '#description' => t("Online lead class - Prod<br />For example: CORP"),
      '#default_value' => $config->get('online_leadclass_prod'),
    );
    $form['online_leadclass_dev'] =  array(
      '#type' => 'textfield',
      '#title' => 'Online lead class - DEV',
      '#maxlength' => 100,
      '#description' => t("Online lead class - DEV<br />For example: CORP"),
      '#default_value' => $config->get('online_leadclass_dev'),
    );
    // ASU Local - Sub class will come from "ASU Local Site" dropdown list.
    // $form['online_subclass_prod'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Online sub-class - Prod',
    //   '#maxlength' => 100,
    //   '#description' => t("Online sub-class - Prod<br />For example: AMZ"),
    //   '#default_value' => $config->get('online_subclass_prod'),
    // );
    // $form['online_subclass_dev'] =  array(
    //   '#type' => 'textfield',
    //   '#title' => 'Online sub-class - DEV',
    //   '#maxlength' => 100,
    //   '#description' => t("Online sub-class - DEV<br />For example: AMZ"),
    //   '#default_value' => $config->get('online_subclass_dev'),
    // );

    return parent::buildForm($form, $form_state);
  }

  /**
   *{@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    parent::submitForm($form, $form_state);

    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configurations on our config.
      ->set('sitename', $form_state->getValue('sitename'))
      ->set('proddomain', $form_state->getValue('proddomain'))

      ->set('online_rfi_block_content_ids', $form_state->getValue('online_rfi_block_content_ids'))

      // ->set('ground_sourceid_prod', $form_state->getValue('ground_sourceid_prod'))
      // ->set('ground_sourceid_dev', $form_state->getValue('ground_sourceid_dev'))
      // ->set('ground_posturl_prod', $form_state->getValue('ground_posturl_prod'))
      // ->set('ground_posturl_dev', $form_state->getValue('ground_posturl_dev'))
      ->set('online_sourceid_prod', $form_state->getValue('online_sourceid_prod'))
      ->set('online_sourceid_dev', $form_state->getValue('online_sourceid_dev'))
      ->set('online_leadclass_prod', $form_state->getValue('online_leadclass_prod'))
      ->set('online_leadclass_dev', $form_state->getValue('online_leadclass_dev'))
      // ->set('online_subclass_prod', $form_state->getValue('online_subclass_prod'))
      // ->set('online_subclass_dev', $form_state->getValue('online_subclass_dev'))
      ->set('online_posturl_prod', $form_state->getValue('online_posturl_prod'))
      ->set('online_posturl_dev', $form_state->getValue('online_posturl_dev'))

      ->save();

    drupal_flush_all_caches();
  }
}
