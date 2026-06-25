<?php

/**
 *@file
 *contains \Drupal\asu_digital_book\Form\CustomizeForm
 **/

 namespace Drupal\asu_digital_book\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


 /**
  *Defines a form to configure Digital View Book content settings
  */

 class CustomizeForm extends ConfigFormBase{
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



		  $form['customize'] = array(
			'#type' => 'details',
			'#title' => t('Customize modality content'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		  );


          $form['customize']['cus_hero'] = array(
                '#type' => 'text_format',
                '#title' => 'Customize modality hero',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('customize.cus_hero.value'),
  				'#format' => $config->get('customize.hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'

          );

        /*  $form['customize']['customize_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Customize video content',
                '#default_value' => $config->get('customize.customize_video.value'),
                '#format' => $config->get('customize.customize_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );*/

		  $form['customize']['customize_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Customize video content',
                '#default_value' => $config->get('customize.customize_video'),
               // '#format' => $config->get('customize.customize_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		  $form['customize']['customize_poster'] =  array(
                '#type' => 'textfield',
                '#title' => 'Customize poster content',
                '#default_value' => $config->get('customize.customize_poster'),
               // '#format' => $config->get('customize.customize_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );


		$form['customize']['where_you_want_to_study'] =  array(
                '#type' => 'text_format',
                '#title' => 'Customize where you want to study content',
                '#default_value' => $config->get('customize.where_you_want_to_study.value'),
                '#format' => $config->get('customize.where_you_want_to_study')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		$form['customize']['find_learning_environment'] =  array(
                '#type' => 'text_format',
                '#title' => 'Find the learning environment that fits you content',
                '#default_value' => $config->get('customize.find_learning_environment.value'),
                '#format' => $config->get('customize.find_learning_environment')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		 $form['customize']['asu_campuses_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'ASU campuses video content',
                '#default_value' => $config->get('customize.asu_campuses_video.value'),
                '#format' => $config->get('customize.asu_campuses_video')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		 $form['customize']['options_to_learn_onine'] =  array(
                '#type' => 'text_format',
                '#title' => 'Options to learn fully online content',
                '#default_value' => $config->get('customize.options_to_learn_onine.value'),
                '#format' => $config->get('customize.options_to_learn_onine')['format'] ?: 'full_html',
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
		  // ksm($values);
		 foreach($values as $key => $each_value){
			 $this->config('asu_digital_book.admin_settings')
				 ->set('customize.'.$key, $each_value)
				 //->set($key.format, $each_value.format)
				 ->save();
		 }

      }



 }
