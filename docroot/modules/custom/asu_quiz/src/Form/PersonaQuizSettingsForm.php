<?php

/**
 *@file
 *contains \Drupal\asu_quiz\Form\PersonaQuizSettingsForm
 **/

 namespace Drupal\asu_quiz\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class PersonaQuizSettingsForm extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_quiz_admin_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_quiz.admin_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_quiz.admin_settings');
         
         
         $form['persona_questions_count'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter count of muber of persona quiz questions',
                '#maxlength' => 10,
                '#default_value' => $config->get('persona_questions_count'),
                       
        );
         
         $form['persona_webform_id'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter node id of the persona webform',
                '#maxlength' => 10,
                '#default_value' => $config->get('persona_webform_id'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
               
        );
         
         $form['focused_futurist_content'] = array(
                '#type' => 'textarea',
                '#title' => 'Enter Degree driven (focused futurist) confirmation content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('focused_futurist_content'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
               
        );
         
          $form['focused_futurist_image'] = array(
                '#type' => 'textarea',
                '#title' => 'Enter Degree driven (focused futurist) confirmation image',
                '#maxlength' => 100000,
                '#default_value' => $config->get('focused_futurist_image'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
          
         $form['deep_diver_content'] = array(
                '#type' => 'textarea',
                '#title' => 'Enter life long (deep_diver) confirmation content',
                '#maxlength' => 100000,
                '#default_value' => $config->get('deep_diver_content'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['deep_diver_image'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter life long (deep_diver) confirmation image',
                '#default_value' => $config->get('deep_diver_image'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['trailblazer_content'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter Self actualizer (trailblazer) confirmation content',
                '#default_value' => $config->get('trailblazer_content'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['trailblazer_image'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter Self actualizer (trailblazer) confirmation image',
                '#default_value' => $config->get('trailblazer_image'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['natural_networker_content'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter Social Involved (natural networker) confirmation content',
                '#default_value' => $config->get('natural_networker_content'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['natural_networker_image'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter Social Involved (natural networker) confirmation image',
                '#default_value' => $config->get('natural_networker_image'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
         $form['superfan_content'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => $this->t('Enter Athletic (superfan) confirmation content'),
                '#default_value' => $config->get('superfan_content'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
                '#format' => 'full_html',
        );
         
          $form['superfan_image'] = array(
                '#type' => 'textarea',
                '#maxlength' => 100000,
                '#title' => 'Enter Athletic (superfan) confirmation image',
                '#default_value' => $config->get('superfan_image'),
                '#prefix' => '<div>',
                '#suffix' => '</div>',
               '#format' => 'full_html',
        );
         
        return parent::buildForm($form, $form_state);
     }
     
      /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
         
        $persona_questions_count = $form_state->getValue('persona_questions_count'); 
         
        $focused_futurist_content_value = $form_state->getValue('focused_futurist_content');
        $focused_futurist_image_value = $form_state->getValue('focused_futurist_image');
        
        $deep_diver_content_value = $form_state->getValue('deep_diver_content');
        $deep_diver_image_value = $form_state->getValue('deep_diver_image');
        
        $trailblazer_content_value = $form_state->getValue('trailblazer_content');
        $trailblazer_image_value = $form_state->getValue('trailblazer_image');
        
        $natural_networker_content_value = $form_state->getValue('natural_networker_content');
        $natural_networker_image_value = $form_state->getValue('natural_networker_image');
        
        $superfan_content_value = $form_state->getValue('superfan_content');
        $superfan_image_value = $form_state->getValue('superfan_image');
        
        $this->config('asu_quiz.admin_settings')
        // $this-> configFactory->getEditable('asu_quiz.settings')
        // $this->configFactory->getEditable(static::asu_quiz_settings)
          ->set('focused_futurist_content',  $form_state->getValue('focused_futurist_content'))
          ->set('focused_futurist_image', $focused_futurist_image_value)
          ->set('deep_diver_content', $deep_diver_content_value)
          ->set('deep_diver_image', $deep_diver_image_value)
          ->set('trailblazer_content', $trailblazer_content_value)
          ->set('trailblazer_image', $trailblazer_image_value)
          ->set('natural_networker_content', $natural_networker_content_value)
          ->set('natural_networker_image', $natural_networker_image_value)
          ->set('superfan_content', $superfan_content_value)
          ->set('superfan_image', $superfan_image_value)
          ->set('persona_webform_id',$form_state->getValue('persona_webform_id'))
          ->set('persona_questions_count', $persona_questions_count)
          ->save();
          
          
          
        // Drupal::logger('grouprowsin')->notice(print_r($this->config(), TRUE));
         // \Drupal::logger('my_module')->notice(\Drupal::state()->get('focused_futurist_content'));
      }
      
  

 }