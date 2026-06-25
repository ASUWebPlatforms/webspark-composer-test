<?php

/**
 *@file
 *contains \Drupal\asu_quiz\Form\CustomLibrariesSettingsFrom
 **/

 namespace Drupal\asu_quiz\Form;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 /**
  *Defines a form to set libraries for specific pages
  */

 class CustomLibrariesSettingsForm extends ConfigFormBase
 {
     /**
      *{ @inheritdoc}
      */
     public function getFormID()
     {
         return 'asu_quiz_libraries_admin_settings';
     }

     /*
      **{@inheritdoc}
      */
     protected function getEditableConfigNames()
     {
         return [
             'asu_quiz.libraries_admin_settings'
         ];
     }

     /*
      **{@inheritdoc}
      */
     public function buildForm(array $form, FormStateInterface $form_state)
     {
         $config = $this->config('asu_quiz.libraries_admin_settings');


         $form['libraries_field_count'] = array(
             '#type' => 'textfield',
             '#title' => 'Enter count of number of libraries fields',
             '#maxlength' => 10,
             '#default_value' => $config->get('libraries_field_count'),

         );

         $count = $config->get('libraries_field_count');
         for ($i = 1; $i <= $count; $i++) {
             $j = $i+1;
             $form["libraries_field_$i"] = array(
                 '#type' => 'textfield',
                 '#title' => "$i. Enter node id and library path, seperated by ':'  ",
                 '#maxlength' => 1000,
                 '#default_value' => $config->get("libraries_field_$i"),

             );
         }


         return parent::buildForm($form, $form_state);
     }

     public function submitForm(array &$form, FormStateInterface $form_state)
     {
         // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
         parent::submitForm($form, $form_state);
         $libraries_count = $form_state->getValue('libraries_field_count');
        //ksm($form_state->getValue('libraries_field_count'));
         $this->config('asu_quiz.libraries_admin_settings')
             ->set('libraries_field_count', $form_state->getValue('libraries_field_count') )
             ->save();

         for($i = 1; $i <= $libraries_count; $i++){
             //$get_Lib_values = $form_state->getValue("libraries_field_$i");
             $this->config('asu_quiz.libraries_admin_settings')
                 ->set("libraries_field_$i", $form_state->getValue("libraries_field_$i") )
                 ->save();
         }

     }
 }
