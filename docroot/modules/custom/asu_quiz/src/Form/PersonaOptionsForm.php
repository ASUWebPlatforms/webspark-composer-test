<?php

/**
 *@file
 *contains \Drupal\asu_quiz\Form\PersonaOptionsForm
 **/

 namespace Drupal\asu_quiz\Form;
 
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 
 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class PersonaOptionsForm extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'asu_quiz_persona_form_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'personaOption.persona_form_settings'
           ];
    }
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('personaOption.persona_form_settings');
        /* $entity = \Drupal::entityTypeManager()->getStorage('webform')->load('quiz');
        $form = $entity->getSubmissionForm();
        $elements = $form['elements'];
        foreach($elements as $key => $options_data){
               $new_options_data[] = $options_data;
              
        }
        foreach($new_options_data as $inner_data){
          //   \Drupal::logger('innerdata1')->notice('<pre><code>' . print_r($inner_data, TRUE) . '</code></pre>');
            if(!empty($inner_data)){
                if(isset($inner_data['#type']) && ($inner_data['#type'] == "container") ){
            //       \Drupal::logger('innerdata')->notice('<pre><code>' . print_r($inner_data['i_m_known_for_'], TRUE) . '</code></pre>');
                }
            }
        }
      */
        $countconfig = \Drupal::config('asu_quiz.admin_settings');
        $question_count = $countconfig->get('persona_questions_count');
        
        // \Drupal::logger('configvar')->notice('<pre><code>' . print_r($key , TRUE) . '</code></pre>');
       
        $form['persona_config'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter q1 persona key',
                '#maxlength' => 10,
                '#default_value' => $question_count,
                       
        );
        
        for($i = 1; $i <= $question_count; $i++){
        //\Drupal::logger('configvar')->notice('<pre><code>' . print_r('persona_q'.$i.'_key' , TRUE) . '</code></pre>');
         $form['persona_q'.$i.'_key'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter q'.$i.' persona key',
                '#maxlength' => 10,
                '#default_value' => $config->get('persona_q'.$i.'_key'),
                       
        );
        }
         
       
         /* $form['persona_q2_key'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter q1 persona key',
                '#maxlength' => 10,
                '#default_value' => $config->get('persona_q2_key'),
                
               
        );
          
        $form['persona_q2_key'] =  array(
                '#type' => 'textfield',
                '#title' => 'Enter q1 persona key',
                '#maxlength' => 10,
                '#default_value' => $config->get('persona_q2_key'),
                
               
        );*/
        
        return parent::buildForm($form, $form_state);
     }
     
 
 
   /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
       // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
         //\Drupal::logger('innerdata1')->notice('<pre><code>' . print_r($form_state, TRUE) . '</code></pre>');
         $number_of_questions = $form_state->getValue('persona_config');
         $q11 = $form_state->getValue('persona_q11_key');
        /* foreach ($form_state as $key => $value) {
             \Drupal::logger('values')->notice('<pre><code>' . print_r($value, TRUE) . '</code></pre>');
         }*/
        for($j = 1 ;$j <= $number_of_questions; $j++){ 
          $persona_q[$j] = $form_state->getValue('persona_q'.$j.'_key'); 
        //  \Drupal::logger('innerdata'.$j)->notice('<pre><code>' . print_r($persona_q[$j], TRUE) . '</code></pre>');
          \Drupal::service('config.factory')->getEditable('personaOption.persona_form_settings')->set('persona_q'.$j.'_key' , $persona_q[$j])->save();
        }
       /*\Drupal::logger('innerdatanew')->notice('<pre><code>' . print_r($persona_q[1], TRUE) . '</code></pre>');
       
        $k = 1;
         $this->config('personaOption.persona_form_settings')
          ->set('persona_q11_key',  $q11)
          
          
          ->save();
       
      }
       */
      }
      
 }