<?php

/**
 *@file
 *contains \Drupal\asu_digital_book\Form\DigitalBookSettingsForm
 **/

 namespace Drupal\asu_digital_book\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Digital View Book content settings
  */
 
 class DigitalBookSettingsForm extends ConfigFormBase{
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
		 
		 $form['confirmation_page_nid'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter view book confirmation node id',
                '#maxlength' => 100,
                '#default_value' => $config->get('confirmation_page_nid'),
  				
               
          );
		 
		      
		  $form['passion_intro'] = array(
			'#type' => 'details',
			'#title' => t('Premise 1 page settings'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
		  );
		 
		 $form['passion_intro']['passion_hero_block'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter Passion permise hero block',
                '#default_value' => $config->get('passion_intro.passion_hero_block.value'),
                '#format' => $config->get('passion_intro.passion_hero_block')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
          );
         
         
          $form['passion_intro']['what_to_do_with_life'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for what to do with life block',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('passion_intro.what_to_do_with_life.value'),
  				'#format' => $config->get('passion_intro.what_to_do_with_life')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
          );
         
         $form['passion_intro']['passion_anchor_link'] =  array(
                '#type' => 'textfield',
                '#title' => 'Passion anchor link',
			 	'#maxlength' => 10000,
			 	'#size' => 10000,
                '#default_value' => $config->get('passion_intro.passion_anchor_link'),
               // '#format' => $config->get('passion_intro.passion_anchor_link')['format'] ?: 'full_html',
			   // '#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		 $form['passion_intro']['asu_tour_video'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for video block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('passion_intro.asu_tour_video'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		 $form['passion_intro']['asu_tour_poster'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for poster block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('passion_intro.asu_tour_poster'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		
		$form['passion_intro']['do_you_know_what_you_want_to_do'] =  array(
                '#type' => 'text_format',
                '#title' => 'So, do you know what you want to do? question animation',
                '#default_value' => $config->get('passion_intro.do_you_know_what_you_want_to_do.value'),
                '#format' => $config->get('passion_intro.do_you_know_what_you_want_to_do')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		$form['passion_intro']['know_what_to_do'] = array(
			'#type' => 'details',
			'#title' => t('What\'s your why options? '),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
			'#attributes' => array(
				 'open' => 'open',
    		),
		);
		 
		 
		$form['passion_intro']['know_what_to_do']['i_know'] =  array(
                '#type' => 'text_format',
                '#title' => 'I know what to do option content',
                '#default_value' => $config->get('passion_intro.i_know.value'),
                '#format' => $config->get('passion_intro.i_know')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );
		 
		$form['passion_intro']['know_what_to_do']['have_ideas_but_not_sure'] =  array(
                '#type' => 'text_format',
                '#title' => 'I have some ideas but not sure yet',
                '#default_value' => $config->get('passion_intro.have_ideas_but_not_sure.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_not_sure')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         ); 
		 
		 $form['passion_intro']['know_what_to_do']['have_ideas_but_one'] =  array(
                '#type' => 'text_format',
                '#title' => 'And its Ok to change your mind along the way content',
                '#default_value' => $config->get('passion_intro.have_ideas_but_one.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_one')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         ); 
		 
		 $form['passion_intro']['know_what_to_do']['have_ideas_but_two'] =  array(
                '#type' => 'text_format',
                '#title' => 'Stats content',
                '#default_value' => $config->get('passion_intro.have_ideas_but_two.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_two')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         ); 
		 
		 $form['passion_intro']['know_what_to_do']['have_ideas_but_three'] =  array(
                '#type' => 'text_format',
                '#title' => 'Its your time to discover what you love content',
                '#default_value' => $config->get('passion_intro.have_ideas_but_three.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_three')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         ); 
		 
		 $form['passion_intro']['know_what_to_do']['have_ideas_but_four'] =  array(
                '#type' => 'text_format',
                '#title' => 'Sometimes the process is not linear',
                '#default_value' => $config->get('passion_intro.have_ideas_but_four.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_four')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );
		 
		 $form['passion_intro']['know_what_to_do']['have_ideas_but_five'] =  array(
                '#type' => 'text_format',
                '#title' => 'Reasons people goto college',
                '#default_value' => $config->get('passion_intro.have_ideas_but_five.value'),
                '#format' => $config->get('passion_intro.have_ideas_but_five')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
         );
		 
		 $form['passion_intro']['whats_your_why'] =  array(
                '#type' => 'text_format',
                '#title' => 'what\'s your why question animation',
                '#default_value' => $config->get('passion_intro.whats_your_why.value'),
                '#format' => $config->get('passion_intro.whats_your_why')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		 /** Premise 2 **/
		 
		   $form['belong_intro'] = array(
			'#type' => 'details',
			'#title' => t('Premise 2 page settings'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
		  );
		 
		 $form['belong_intro']['belong_hero_block'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter Belong permise hero image block',
                '#default_value' => $config->get('belong_intro.belong_hero_block.value'),
                '#format' => $config->get('belong_intro.belong_hero_block')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
          );
         
         
          $form['belong_intro']['belong_hero'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for hero text block',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('belong_intro.belong_hero.value'),
  				'#format' => $config->get('belong_intro.belong_hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
          );
         
          $form['belong_intro']['deciding_on_college'] =  array(
                '#type' => 'text_format',
                '#title' => 'Enter deciding on college content',
                '#default_value' => $config->get('belong_intro.deciding_on_college.value'),
                '#format' => $config->get('belong_intro.deciding_on_college')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		
		$form['belong_intro']['belong_anchor_link'] =  array(
                '#type' => 'textfield',
                '#title' => 'Belong anchor link',
				'#maxlength' => 10000,
				'#size' => 10000,
                '#default_value' => $config->get('belong_intro.belong_anchor_link'),
                //'#format' => $config->get('belong_intro.belong_anchor_link')['format'] ?: 'full_html',
			    //'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		$form['belong_intro']['belong_video'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for video block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('belong_intro.belong_video'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		 $form['belong_intro']['belong_poster'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for poster block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('belong_intro.belong_poster'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['belong_intro']['why_going_to_college'] =  array(
                '#type' => 'text_format',
                '#title' => 'Why going to college content',
                '#default_value' => $config->get('belong_intro.why_going_to_college.value'),
                '#format' => $config->get('belong_intro.why_going_to_college')['format'] ?: 'full_html',
			    '#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		 
		/** Premise 3 **/
		 
		$form['unknown_intro'] = array(
			'#type' => 'details',
			'#title' => t('Premise 3 page settings'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
		);
		 
		 $form['unknown_intro']['unknown_hero_image_block'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for hero image block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.unknown_hero_image_block.value'),
  				'#format' => $config->get('unknown_intro.unknown_hero_image_block')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		 
		 
		$form['unknown_intro']['unknown_hero'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for hero text block',
                '#maxlength' => 100000,
                //'#default_value' => $config->get('what_to_do_with_life'),
                //'#format' => 'full_html',
			    '#default_value' => $config->get('unknown_intro.unknown_hero.value'),
  				'#format' => $config->get('unknown_intro.unknown_hero')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
               
        );
		 
		 $form['unknown_intro']['unknown_anchor_link'] = array(
                '#type' => 'textfield',
                '#title' => 'Unpredicatble anchor link',
                '#maxlength' => 100000,
			    '#size' => 10000,
                '#default_value' => $config->get('unknown_intro.unknown_anchor_link'),
  				//'#format' => $config->get('unknown_intro.unknown_anchor_link')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		 
		$form['unknown_intro']['unknown_my_life_turned'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for My life turned exactly block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.unknown_my_life_turned.value'),
  				'#format' => $config->get('unknown_intro.unknown_my_life_turned')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
	    $form['unknown_intro']['ten_years_from_now'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for Ten years from now block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.ten_years_from_now.value'),
  				'#format' => $config->get('unknown_intro.ten_years_from_now')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['career_options'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for career options block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.career_options.value'),
  				'#format' => $config->get('unknown_intro.career_options')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['it_makes_you_wonder'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for it makes you wonder block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.it_makes_you_wonder.value'),
  				'#format' => $config->get('unknown_intro.it_makes_you_wonder')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['parallax_block'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for Parallax block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.parallax_block.value'),
  				'#format' => $config->get('unknown_intro.parallax_block')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['radically_different'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for radically different block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.radically_different.value'),
  				'#format' => $config->get('unknown_intro.radically_different')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['invent_future'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for Invent future block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.invent_future.value'),
  				'#format' => $config->get('unknown_intro.invent_future')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		/*  $form['unknown_intro']['invent_video'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for video block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.invent_video.value'),
  				'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );*/
		 
		 $form['unknown_intro']['invent_video'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for video block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('unknown_intro.invent_video'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		  $form['unknown_intro']['invent_poster'] = array(
                '#type' => 'textfield',
                '#title' => 'Enter content for poster block',
                '#maxlength' => 1000,
                '#default_value' => $config->get('unknown_intro.invent_poster'),
  				//'#format' => $config->get('unknown_intro.invent_video')['format'] ?: 'full_html',
			  	//'#format_selector' => FALSE, // To lock the text format to 'full_html'
        );
		 
		$form['unknown_intro']['why_college'] = array(
                '#type' => 'text_format',
                '#title' => 'Enter content for why college in first place block',
                '#maxlength' => 100000,
                '#default_value' => $config->get('unknown_intro.why_college.value'),
  				'#format' => $config->get('unknown_intro.why_college')['format'] ?: 'full_html',
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
		  $config = $this->config('asu_digital_book.admin_settings');
		 // Set the 'confirmation_page_nid' configuration value.
    	 $config->set('confirmation_page_nid', $values['confirmation_page_nid']);
		 foreach($values as $key => $each_value){
			 $this->config('asu_digital_book.admin_settings')
				 ->set('passion_intro.'.$key, $each_value)
				 ->set('belong_intro.'.$key, $each_value)
				 ->set('unknown_intro.'.$key, $each_value)
				 ->save();
		 } 
        
      }
      
  

 }