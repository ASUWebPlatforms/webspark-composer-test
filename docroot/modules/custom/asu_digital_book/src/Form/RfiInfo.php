<?php

/**
 *@file
 *contains \Drupal\asu_digital_book\Form\InvestForm
 **/

 namespace Drupal\asu_digital_book\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


 /**
  *Defines a form to configure Digital View Book content settings
  */

 class RfiInfo extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_digital_book_admin_settings';
    }

    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_digital_book.admin_settings'
           ];
    }

    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_digital_book.admin_settings');



		  $form['rfi'] = array(
			'#type' => 'details',
			'#title' => t('RFI page content'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		  );


          $form['rfi']['rfi_hero'] = array(
                '#type' => 'text_format',
                '#title' => 'RFI hero',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('rfi.rfi_hero.value'),
  				'#format' => $config->get('rfi.rfi_hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'

          );

		 $form['rfi']['rfi_intro'] =  array(
                '#type' => 'text_format',
                '#title' => 'RFI Intro content',
                '#default_value' => $config->get('rfi.rfi_intro.value'),
                '#format' => $config->get('rfi.rfi_intro')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

        return parent::buildForm($form, $form_state);
     }

      /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
         $values =  $form_state->getValues();
		//   ksm($values);
		 foreach($values as $key => $each_value){
			 $this->config('asu_digital_book.admin_settings')
				 ->set('rfi.'.$key, $each_value)
				 //->set($key.format, $each_value.format)
				 ->save();
		 }

      }



 }
