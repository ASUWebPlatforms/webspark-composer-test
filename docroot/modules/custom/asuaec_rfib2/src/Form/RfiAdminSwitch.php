<?php

/**
 *@file
 *contains \Drupal\asuaec_rfib2\Form\RfiAdminSwitch
 **/

 namespace Drupal\asuaec_rfib2\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


/**
  * Defines a form
  */
class RfiAdminSwitch extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormId(){
        return 'asuaec_rfib2_customadmin_settings';
    }

    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asuaec_rfib2.customadmin_settings'
           ];
    }

    /*
     **{@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
      $config = $this->config('asuaec_rfib2.customadmin_settings');

      // How to pull degrees
      $form['heading5'] = array(
        '#markup' => "<h3>How to pull degrees</h3>",
      );

      $form['wsdirect'] =  array(
        '#type' => 'select',
        '#options' => array(
          '0' => t('--- SELECT ---'),
          'db' => t('Pull from database'),
          'wsdirect' => t('Pull directly from Web service')
        ),
        '#title' => 'Pull degrees from Web service directly and display or pull degrees from database and display',
        '#description' => t("Pull degrees/interests from Web service directly and display or pull degrees/interests from database and display"),
        '#default_value' => $config->get('wsdirect'),
      );

      //---- Added on 9/3/2025 ----//

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
      $form['ground_sourceid_prod'] =  array(
        '#type' => 'textfield',
        '#title' => 'Ground source ID - Prod',
        '#maxlength' => 100,
        '#description' => t("Ground source ID - Prod<br />For example: 7016T000002Ti32QAC (Admission site)"),
        '#default_value' => $config->get('ground_sourceid_prod') != '' ? $config->get('ground_sourceid_prod') : '7016T000002Ti32QAC',
      );
      $form['ground_sourceid_dev'] =  array(
        '#type' => 'textfield',
        '#title' => 'Ground source ID - DEV',
        '#maxlength' => 100,
        '#description' => t("Ground source ID - DEV<br />For example: 7016T000002c8qMQAQ"),
        '#default_value' => $config->get('ground_sourceid_dev') != '' ? $config->get('ground_sourceid_dev') : '7016T000002c8qMQAQ',
      );
      // Added on 3/23/2026.
      $form['ground_sourceid_prod_by_path'] = [
        '#type' => 'textarea',
        '#title' => 'Ground source IDs by path - Prod',
        '#description' => $this->t('NEW! Optional. Enter one mapping per line in the format <path>|<source id>. Example: /apply/health|7016T00000XXXXX /n Works with CheqCustomSourceIdsRfiWebformHandler.php.'),
        '#default_value' => $config->get('ground_sourceid_prod_by_path') ?: '',
      ];
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

      // Added on 10/7/2025
      $form['heading6'] = array(
        '#markup' => "<h3>Degree Web service switch</h3>",
      );
      $form['degreews'] =  array(
        '#type' => 'select',
        '#options' => array(
          '0' => t('--- SELECT ---'),
          'proddegreews' => t('Use Prod degree Web service'),
          'devdegreews' => t('Use Dev degree Web service')
        ),
        '#title' => 'Use Prod or Dev degree Web service',
        '#description' => t("Switch to use Prod or Dev degree Web service"),
        '#default_value' => $config->get('degreews'),
      );

      // Webform IDs. Added on 3/17/2026.
      $form['heading7'] = [
        '#markup' => '<h3>Webform machine names</h3>',
      ];

      $form['webform_ids'] = [
        '#type' => 'textarea',
        '#title' => 'Webform machine names',
        '#description' => $this->t('Enter one Webform machine name per line. Example: rfi_b2 or rfib'),
        '#default_value' => $config->get('webform_ids') ?: "rfi_b2\nrfib",
      ];

      return parent::buildForm($form, $form_state);
     }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      parent::submitForm($form, $form_state);

      $keys = [
        'wsdirect',
        'sitename',
        'proddomain',
        'ground_sourceid_prod',
        'ground_sourceid_dev',
        'ground_posturl_prod',
        'ground_posturl_dev',
        'degreews',
        'webform_ids', // Added on 3/17/2026.
        'ground_sourceid_prod_by_path', // Added on 3/23/2026.
      ];

      $config = $this->config('asuaec_rfib2.customadmin_settings');

      foreach ($keys as $key) {
        $config->set($key, $form_state->getValue($key));
      }

      $config->save();

      drupal_flush_all_caches();
    }

 }
