<?php

/**
 *@file
 *contains \Drupal\asu_digital_book\Form\LearningForm
 **/

 namespace Drupal\asu_digital_book\Form;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


 /**
  *Defines a form to configure Digital View Book content settings
  */

 class LearningForm extends ConfigFormBase{
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

		  $form['learning'] = array(
			'#type' => 'details',
			'#title' => t('Learning'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		  );


          $form['learning']['hero'] = array(
                '#type' => 'text_format',
                '#title' => 'Learning modality hero',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('learning.hero.value'),
  				'#format' => $config->get('learning.hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'

          );

       /* $form['learning']['learning_focused_futurist_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Learning  job video content',
                '#default_value' => $config->get('learning.learning_focused_futurist_video.value'),
                '#format' => $config->get('learning.learning_focused_futurist_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['learning_deep_diver_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Learning Mastery video content',
                '#default_value' => $config->get('learning.learning_deep_diver_video.value'),
                '#format' => $config->get('learning.learning_deep_diver_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['learning_trailblazer_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Learning Impact video content',
                '#default_value' => $config->get('learning.learning_trailblazer_video.value'),
                '#format' => $config->get('learning.learning_trailblazer_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );


		 $form['learning']['learning_superfan_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Learning college experience video content',
                '#default_value' => $config->get('learning.learning_superfan_video.value'),
                '#format' => $config->get('learning.learning_superfan_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		  $form['learning']['learning_networker_video'] =  array(
                '#type' => 'text_format',
                '#title' => 'Learning network video content',
                '#default_value' => $config->get('learning.learning_networker_video.value'),
                '#format' => $config->get('learning.learning_networker_video')['format'] ?: 'full_html',
		 );*/

		 $form['learning']['learning_focused_futurist_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning job video content',
                '#default_value' => $config->get('learning.learning_focused_futurist_video'),
               // '#format' => $config->get('learning.learning_focused_futurist_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['learning_focused_futurist_poster'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning job video content',
                '#default_value' => $config->get('learning.learning_focused_futurist_poster'),
               // '#format' => $config->get('learning.learning_focused_futurist_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );



		 $form['learning']['learning_deep_diver_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning Mastery video content',
                '#default_value' => $config->get('learning.learning_deep_diver_video'),
              //  '#format' => $config->get('learning.learning_deep_diver_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['learning_trailblazer_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning Impact video content',
                '#default_value' => $config->get('learning.learning_trailblazer_video'),
               // '#format' => $config->get('learning.learning_trailblazer_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );


		 $form['learning']['learning_superfan_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning college experience video content',
                '#default_value' => $config->get('learning.learning_superfan_video'),
               // '#format' => $config->get('learning.learning_superfan_video')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		  $form['learning']['learning_networker_video'] =  array(
                '#type' => 'textfield',
                '#title' => 'Learning network video content',
                '#default_value' => $config->get('learning.learning_networker_video'),
              //  '#format' => $config->get('learning.learning_networker_video')['format'] ?: 'full_html',
		 );






		$form['learning']['whats_next_option'] = array(
			'#type' => 'details',
			'#title' => t('What\'s next for you options? '),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		);


		$form['learning']['whats_next_option']['associate_degree'] =  array(
                '#type' => 'text_format',
                '#title' => 'Associates degree option content',
                '#default_value' => $config->get('learning.associate_degree.value'),
                '#format' => $config->get('learning.associate_degree')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		$form['learning']['whats_next_option']['bachelors_degree'] =  array(
                '#type' => 'text_format',
                '#title' => 'I plan to earn my bachelor\'s degree option content',
                '#default_value' => $config->get('learning.bachelors_degree.value'),
                '#format' => $config->get('learning.bachelors_degree')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		 $form['learning']['whats_next_option']['advanced_degree'] =  array(
                '#type' => 'text_format',
                '#title' => 'I plan to earn an advanced degree such as a master or PhD option content',
                '#default_value' => $config->get('learning.advanced_degree.value'),
                '#format' => $config->get('learning.advanced_degree')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );

		 $form['learning']['asu_is_an_excellent'] =  array(
                '#type' => 'text_format',
                '#title' => 'ASU is an excellent place to achieve your goals content',
                '#default_value' => $config->get('learning.asu_is_an_excellent.value'),
                '#format' => $config->get('learning.asu_is_an_excellent')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['ranking'] =  array(
                '#type' => 'text_format',
                '#title' => 'ASU Ranking content',
                '#default_value' => $config->get('learning.ranking.value'),
                '#format' => $config->get('learning.ranking')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['military_affiliate'] =  array(
                '#type' => 'text_format',
                '#title' => 'Militray affiliate content',
                '#default_value' => $config->get('learning.military_affiliate.value'),
                '#format' => $config->get('learning.military_affiliate')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['excellent_teaching'] =  array(
                '#type' => 'text_format',
                '#title' => 'Excellent teaching content',
                '#default_value' => $config->get('learning.excellent_teaching.value'),
                '#format' => $config->get('learning.excellent_teaching')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'

        );

		 $form['learning']['student_faculty_ratio'] =  array(
                '#type' => 'text_format',
                '#title' => 'Student to faculty ratio content',
                '#default_value' => $config->get('learning.student_faculty_ratio.value'),
                '#format' => $config->get('learning.student_faculty_ratio')['format'] ?: 'full_html',
			    '#format_selector' => FALSE,
		 );

		 $form['learning']['what_do_you_want_to_study'] =  array(
                '#type' => 'text_format',
                '#title' => 'What do you want to study content',
                '#default_value' => $config->get('learning.what_do_you_want_to_study.value'),
                '#format' => $config->get('learning.what_do_you_want_to_study')['format'] ?: 'full_html',
			    '#format_selector' => FALSE,
		 );

		 $form['learning']['no_matter_what_you_choose'] =  array(
                '#type' => 'text_format',
                '#title' => 'No matter what you choose content',
                '#default_value' => $config->get('learning.no_matter_what_you_choose.value'),
                '#format' => $config->get('learning.no_matter_what_you_choose')['format'] ?: 'full_html',
			    '#format_selector' => FALSE,
		 );

		 $form['learning']['no_matter_text_content'] =  array(
                '#type' => 'text_format',
                '#title' => 'No matter what you choose Text version content',
                '#default_value' => $config->get('learning.no_matter_text_content.value'),
                '#format' => $config->get('learning.no_matter_text_content')['format'] ?: 'full_html',
			    '#format_selector' => FALSE,
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
				 ->set('learning.'.$key, $each_value)
				 //->set($key.format, $each_value.format)
				 ->save();
		 }

      }



 }
