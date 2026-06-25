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

 class InvestForm extends ConfigFormBase{
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



		  $form['invest'] = array(
			'#type' => 'details',
			'#title' => t('Invest modality content'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		  );


          $form['invest']['in_hero'] = array(
                '#type' => 'text_format',
                '#title' => 'Invest modality hero',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('invest.in_hero.value'),
  				'#format' => $config->get('invest.in_hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'

          );

		 $form['invest']['invest_in_yourself'] =  array(
                '#type' => 'text_format',
                '#title' => 'Invest in yourself content',
                '#default_value' => $config->get('invest.invest_in_yourself.value'),
                '#format' => $config->get('invest.invest_in_yourself')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

         /* $form['invest']['in_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Invest video content',
                '#default_value' => $config->get('invest.in_video.value'),
                '#format' => $config->get('invest.in_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

          );*/

		   $form['invest']['in_video'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for video block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('invest.in_video'),
  				//'#format' => $config->get('unknown_intro.in_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );

		 $form['invest']['invest_poster'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for poster block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('invest.invest_poster'),

        );


		 $form['invest']['fiske_rank'] =  array(
                '#type' => 'text_format',
                '#title' => 'Fiske ranks and High quality photo content',
                '#default_value' => $config->get('invest.fiske_rank.value'),
                '#format' => $config->get('invest.fiske_rank')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		  $form['invest']['fiske_rank_text_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'Fiske ranks and High quality text content',
                '#default_value' => $config->get('invest.fiske_rank_text_content.value'),
                '#format' => $config->get('invest.fiske_rank_text_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );


		 $form['invest']['tuition_programs'] =  array(
                '#type' => 'text_format',
                '#title' => 'ASU tuition programs content',
                '#default_value' => $config->get('invest.tuition_programs.value'),
                '#format' => $config->get('invest.tuition_programs')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['invest']['personalized_information'] =  array(
                '#type' => 'text_format',
                '#title' => 'ASU Personalize content',
                '#default_value' => $config->get('invest.personalized_information.value'),
                '#format' => $config->get('invest.personalized_information')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['invest']['arizona_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'Arizona content',
                '#default_value' => $config->get('invest.arizona_content.value'),
                '#format' => $config->get('invest.arizona_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['invest']['california_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'California content',
                '#default_value' => $config->get('invest.california_content.value'),
                '#format' => $config->get('invest.california_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['invest']['oos_wue_states_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'OOS WUE states content',
                '#default_value' => $config->get('invest.oos_wue_states_content.value'),
                '#format' => $config->get('invest.oos_wue_states_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		$form['invest']['oos_other_states_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'Other OOS states content',
                '#default_value' => $config->get('invest.oos_other_states_content.value'),
                '#format' => $config->get('invest.oos_other_states_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		$form['invest']['international_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'International content',
                '#default_value' => $config->get('invest.international_content.value'),
                '#format' => $config->get('invest.international_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['invest']['what_next'] =  array(
                '#type' => 'text_format',
                '#title' => 'What is next content',
                '#default_value' => $config->get('invest.what_next.value'),
                '#format' => $config->get('invest.what_next')['format'] ?: 'full_html',
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
				 ->set('invest.'.$key, $each_value)
				 //->set($key.format, $each_value.format)
				 ->save();
		 }

      }



 }
