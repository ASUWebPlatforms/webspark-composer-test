<?php

/**
 *@file
 *contains \Drupal\asuaec_rfi_custompost\Form\RfiAdminSwitch
 **/

 namespace Drupal\asuaec_rfi_custompost\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


/**
  * Defines a form
  */
class RfiAdminSwitch extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asuaec_rfi_custompost_customadmin_settings';
    }

    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asuaec_rfi_custompost.customadmin_settings'
           ];
    }

    /*
     **{@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
      $config = $this->config('asuaec_rfi_custompost.customadmin_settings');

      // Site name
      $form['heading3'] = array(
        '#markup' => "<h3>Site name</h3>",
      );
      $form['sitename'] =  array(
        '#type' => 'textfield',
        '#title' => 'Acquia site name',
        '#maxlength' => 100,
        '#description' => t("For example: admissionasu"),
        '#default_value' => $config->get('sitename') != '' ? $config->get('sitename') : 'admissionasu',
      );

      // Prod domain
      $form['heading4'] = array(
        '#markup' => "<h3>Prod domain</h3>",
      );
      $form['proddomain'] =  array(
        '#type' => 'textfield',
        '#title' => 'Prod domain',
        '#maxlength' => 100,
        '#description' => t("For example: https://admission.asu.edu"),
        '#default_value' => $config->get('proddomain') != '' ? $config->get('proddomain') : 'https://admission.asu.edu',
      );

      // Ground
      $form['heading1'] = array(
        '#markup' => "<h3>Ground</h3>",
      );
      // Pass source id from Webform's hidden field.
      // $form['ground_sourceid_prod'] =  array(
      //   '#type' => 'textfield',
      //   '#title' => 'Ground source ID - Prod',
      //   '#maxlength' => 100,
      //   '#description' => t("Ground source ID - Prod<br />For example: 7016T000002Ti32QAC (Admission site)"),
      //   '#default_value' => $config->get('ground_sourceid_prod') != '' ? $config->get('ground_sourceid_prod') : '7016T000002Ti32QAC',
      // );
      // $form['ground_sourceid_dev'] =  array(
      //   '#type' => 'textfield',
      //   '#title' => 'Ground source ID - DEV',
      //   '#maxlength' => 100,
      //   '#description' => t("Ground source ID - DEV<br />For example: 7016T000002c8qMQAQ"),
      //   '#default_value' => $config->get('ground_sourceid_dev') != '' ? $config->get('ground_sourceid_dev') : '7016T000002c8qMQAQ',
      // );
      $form['ground_posturl_prod'] =  array(
        '#type' => 'textfield',
        '#title' => 'Ground posting URL - Prod',
        '#maxlength' => 255,
        '#description' => t("Ground posting URL - Prod<br />For example: https://5gu33wnsdm2mpgmob4c2rt3mbq0mngfo.lambda-url.us-west-2.on.aws/ (Admission site)"),
        '#default_value' => $config->get('ground_posturl_prod') != '' ? $config->get('ground_posturl_prod') : 'https://crm-request-form-submission-router-prod.apps.asu.edu/v1/event/rfi/',
      );
      $form['ground_posturl_dev'] =  array(
        '#type' => 'textfield',
        '#title' => 'Ground posting URL - DEV',
        '#maxlength' => 255,
        //'#description' => t("Ground posting URL - DEV<br />For example: https://eakemwmmmpql5o523dnfkvvtem0ezhhc.lambda-url.us-west-2.on.aws/"),
        '#description' => t("Ground posting URL - DEV<br />For example: https://3ceccsb54wpba5wrdg6kgxmlv40obcjl.lambda-url.us-west-2.on.aws/"),
        '#default_value' => $config->get('ground_posturl_dev') != '' ? $config->get('ground_posturl_dev') : 'https://crm-request-form-submission-router-qa.apps.asu.edu/v1/event/rfi/',
      );

      // Online --- We don't need online because posting it to middleware. Middleware handles online.
      $form['heading2'] = array(
        '#markup' => "<h3>Online - Middleware handles it.</h3>",
      );


      return parent::buildForm($form, $form_state);
     }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
      parent::submitForm($form, $form_state);
      $values =  $form_state->getValues();
      foreach($values as $key => $each_value){
        $this->config('asuaec_rfi_custompost.customadmin_settings')
          ->set($key, $each_value)
          ->save();
      }
      drupal_flush_all_caches();
    }
 }
