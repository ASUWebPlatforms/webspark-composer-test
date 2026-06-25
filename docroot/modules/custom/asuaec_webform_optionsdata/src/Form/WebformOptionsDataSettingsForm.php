<?php

/**
 * @file
 * contains \Drupal\asuaec_webform_optionsdata\Form\WebformOptionsDataSettingsForm
 **/

 namespace Drupal\asuaec_webform_optionsdata\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  * Defines a form for asuaec_webform_optionsdata module config page
  */
 
 class WebformOptionsDataSettingsForm extends ConfigFormBase {
    /**
     * { @inheritdoc}
     */
    public function getFormID(){
      return 'asuaec_webform_optionsdata_admin_settings';
    }

   /**
    * { @inheritdoc}
    */
    protected function getEditableConfigNames(){
      return [
        'asuaec_webform_optionsdata.admin_settings' ///// Make it module_name.admin_settings.
      ];
    }

    /**
    * { @inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state) {
      $config = $this->config('asuaec_webform_optionsdata.admin_settings');


      $form['asuaec_webform_optionsdata_cron_interval_admin'] =  array(
        '#type' => 'textfield',
        '#title' =>  $this->t('Enter cron interval. 2629800000 for 1 month. 120000 for 2 min for testing'),
        '#maxlength' => 12,
        '#default_value' => $config->get('asuaec_webform_optionsdata_cron_interval_admin'),
        '#description' => $this->t("If left blank, 2629800000 will be used defined internally by the asuaec_webform_optionsdata module."),
      );
      $form['asuaec_webform_optionsdata_formmanager_webservice_url_admin'] =  array(
        '#type' => 'textfield',
        '#title' =>  $this->t('Formmanager webservice url'),
        '#maxlength' => 50,
        '#default_value' => $config->get('asuaec_webform_optionsdata_formmanager_webservice_url_admin'),
        '#description' => $this->t("Recommended to be left blank to use default data source (https://webapp4.asu.edu/formmanager/ws) defined internally by the asuaec_webform_optionsdata module."),
      );
      $form['asuaec_webform_optionsdata_datapotluck_webservice_url_admin'] =  array(
        '#type' => 'textfield',
        '#title' =>  $this->t('ASU Data Potluck webservice url'),
        '#maxlength' => 50,
        '#default_value' => $config->get('asuaec_webform_optionsdata_datapotluck_webservice_url_admin'),
        '#description' => $this->t("Recommended to be left blank to use default data source (https://api.myasuplat-dpl.asu.edu/api/codeset) defined internally by the asuaec_webform_optionsdata module."),
      );
      return parent::buildForm($form, $form_state);
    }

    /**
    * { @inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
      parent::submitForm($form, $form_state);

      $this->config('asuaec_webform_optionsdata.admin_settings')
        ->set('asuaec_webform_optionsdata_cron_interval_admin',  $form_state->getValue('asuaec_webform_optionsdata_cron_interval_admin'))
        ->set('asuaec_webform_optionsdata_formmanager_webservice_url_admin',  $form_state->getValue('asuaec_webform_optionsdata_formmanager_webservice_url_admin'))
        ->set('asuaec_webform_optionsdata_datapotluck_webservice_url_admin',  $form_state->getValue('asuaec_webform_optionsdata_datapotluck_webservice_url_admin'))
        ->save();
      }
 }