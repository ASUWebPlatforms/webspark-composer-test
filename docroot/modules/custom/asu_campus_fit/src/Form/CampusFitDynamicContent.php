<?php

namespace Drupal\asu_campus_fit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure Campus Fit quiz confirmation page dynamic content settings.
 */
class CampusFitDynamicContent extends ConfigFormBase {

  /**
   * { @inheritdoc}
   */
  public function getFormID() {
    return 'asu_campus_fit_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_campus_fit.admin_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('asu_campus_fit.admin_settings');

    $form['fit_quiz_nid'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of campus fit node',
      '#maxlength' => 100,
      '#default_value' => $config->get('fit_quiz_nid'),

    ];

    $form['result_nids'] = [
      '#type' => 'details',
      '#title' => t('Result node ids'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['result_nids']['ulcres'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of California results',
      '#maxlength' => 100,
      '#default_value' => $config->get('ulcres'),

    ];

    $form['result_nids']['lalocalresults'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of LA local results',
      '#maxlength' => 100,
      '#default_value' => $config->get('lalocalresults'),

    ];

    $form['result_nids']['lalocalCAresults'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of LA California local results',
      '#maxlength' => 100,
      '#default_value' => $config->get('lalocalCAresults'),

    ];

    $form['result_nids']['result_nids']['onlres'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Online results',
      '#maxlength' => 100,
      '#default_value' => $config->get('onlres'),

    ];

    $form['result_nids']['tempe_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Tempe campus dynamic results',
      '#maxlength' => 100,
      '#default_value' => $config->get('tempe_res'),

    ];

    $form['result_nids']['downtown_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Downtown campus results',
      '#maxlength' => 100,
      '#default_value' => $config->get('downtown_res'),

    ];

    $form['result_nids']['west_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of West campus dynamic results',
      '#maxlength' => 100,
      '#default_value' => $config->get('west_res'),

    ];

    $form['result_nids']['poly_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Poly campus dynamic results',
      '#maxlength' => 100,
      '#default_value' => $config->get('poly_res'),

    ];

    $form['result_nids']['havasu_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Havasu campus dynamic results',
      '#maxlength' => 100,
      '#default_value' => $config->get('havasu_res'),

    ];

    $form['result_nids']['tempe_simple_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Tempe campus simple results',
      '#maxlength' => 100,
      '#default_value' => $config->get('tempe_simple_res'),

    ];

    $form['result_nids']['downtown_simple_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Downtown campus simple results',
      '#maxlength' => 100,
      '#default_value' => $config->get('downtown_simple_res'),

    ];

    $form['result_nids']['west_simple_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of West campus simple results',
      '#maxlength' => 100,
      '#default_value' => $config->get('west_simple_res'),

    ];

    $form['result_nids']['poly_simple_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Poly campus simple results',
      '#maxlength' => 100,
      '#default_value' => $config->get('poly_simple_res'),

    ];

    $form['result_nids']['havasu_simple_res'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Havasu campus simple results',
      '#maxlength' => 100,
      '#default_value' => $config->get('havasu_simple_res'),

    ];

    $form['result_nids']['ccres'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of Catalyst results',
      '#maxlength' => 100,
      '#default_value' => $config->get('ccres'),

    ];

    $form['result_nids']['asu4ures'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of ASU for you results',
      '#maxlength' => 100,
      '#default_value' => $config->get('asu4ures'),

    ];

    $form['result_nids']['asuLocalLAGrad'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of ASU Local graudate results',
      '#maxlength' => 100,
      '#default_value' => $config->get('asuLocalLAGrad'),

    ];

    $form['result_nids']['asuLocalHawai'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of ASU Local Hawai results',
      '#maxlength' => 100,
      '#default_value' => $config->get('asuLocalHawai'),

    ];

    $form['result_nids']['londonRes'] = [
      '#type' => 'textfield',
      '#title' => 'Enter node id of London campus dynamic results',
      '#maxlength' => 100,
      '#default_value' => $config->get('londonRes'),

    ];

    $form['intro_content'] = [
      '#type' => 'details',
      '#title' => t('Campus intro content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['intro_content']['tempe_intro_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Tempe campus intro conent',
      '#maxlength' => 10000,
      '#default_value' => $config->get('tempe_intro_content'),
      '#format' => 'full_html',
    ];

    $form['intro_content']['poly_intro_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Polye campus intro conent',
      '#maxlength' => 10000,
      '#default_value' => $config->get('poly_intro_content'),
      '#format' => 'full_html',
    ];

    $form['intro_content']['west_intro_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter West campus intro conent',
      '#maxlength' => 10000,
      '#default_value' => $config->get('west_intro_content'),
      '#format' => 'full_html',
    ];

    $form['intro_content']['downtown_intro_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Downtown campus intro conent',
      '#maxlength' => 10000,
      '#default_value' => $config->get('downtown_intro_content'),
      '#format' => 'full_html',
    ];

    $form['intro_content']['havasu_intro_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Havasu campus intro conent',
      '#maxlength' => 10000,
      '#default_value' => $config->get('havasu_intro_content'),
      '#format' => 'full_html',
    ];

    $form['scholarship_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Scholarship content',
      '#maxlength' => 10000,
      '#default_value' => $config->get('scholarship_content'),
      '#format' => 'full_html',
    ];

    $form['military_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Military content',
      '#maxlength' => 10000,
      '#default_value' => $config->get('military_content'),
      '#format' => 'full_html',
    ];

    $form['affiliates'] = [
      '#type' => 'details',
      '#title' => t('Starbucks, uber, Catalysta and other partnerships dynamic content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['affiliates']['starbucks_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Starbucks dynamic content for Online',
      '#maxlength' => 10000,
      '#default_value' => $config->get('starbucks_content'),
      '#format' => 'full_html',
    ];

    $form['affiliates']['uber_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter Uber dynamic content for Online',
      '#default_value' => $config->get('uber_content'),
      '#format' => 'full_html',
    ];

    $form['affiliates']['otherparterns_content'] = [
      '#type' => 'textarea',
      '#title' => 'Enter other partnerships dynamic content',
      '#default_value' => $config->get('otherparterns_content'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app'] = [
      '#type' => 'details',
      '#title' => t('What excites you about the college experience? Check all that apply dynamic content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['meeting_people_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Meeting new people and networking accordion hading',
      '#default_value' => $config->get('meeting_people_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['meeting_people'] = [
      '#type' => 'textarea',
      '#title' => 'Meeting new people and networking',
      '#default_value' => $config->get('meeting_people'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['living_on_my_own_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Living on my own and learning how to “adult” heading',
      '#default_value' => $config->get('living_on_my_own_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['living_on_my_own'] = [
      '#type' => 'textarea',
      '#title' => 'Living on my own and learning how to “adult”',
      '#default_value' => $config->get('living_on_my_own'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_from_professors_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Learning from professors who are on the cutting edge of their field heading',
      '#default_value' => $config->get('learning_from_professors_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_from_professors'] = [
      '#type' => 'textarea',
      '#title' => 'Learning from professors who are on the cutting edge of their field',
      '#default_value' => $config->get('learning_from_professors'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_how_to_start_business_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Learning how to start my own business or get funding for my ideas heading',
      '#default_value' => $config->get('learning_how_to_start_business_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_how_to_start_business'] = [
      '#type' => 'textarea',
      '#title' => 'Learning how to start my own business or get funding for my ideas',
      '#default_value' => $config->get('learning_how_to_start_business'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['work_on_innovative_ideas_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Work on innovative ideas and/or research that is improving our future heading',
      '#default_value' => $config->get('work_on_innovative_ideas_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['work_on_innovative_ideas'] = [
      '#type' => 'textarea',
      '#title' => 'Work on innovative ideas and/or research that is improving our future',
      '#default_value' => $config->get('work_on_innovative_ideas'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_in_innovative_ways_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Learning in innovative ways heading',
      '#default_value' => $config->get('learning_in_innovative_ways_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_in_innovative_ways'] = [
      '#type' => 'textarea',
      '#title' => 'Learning in innovative ways',
      '#default_value' => $config->get('learning_in_innovative_ways'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_to_think_creatively_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Learning to think creatively and work collaboratively heading',
      '#default_value' => $config->get('learning_to_think_creatively_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['learning_to_think_creatively'] = [
      '#type' => 'textarea',
      '#title' => 'Learning to think creatively and work collaboratively',
      '#default_value' => $config->get('learning_to_think_creatively'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['living_in_fun_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Living in a fun and future-focused city, with lot of things to do and opps for internships heading',
      '#default_value' => $config->get('living_in_fun_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['living_in_fun'] = [
      '#type' => 'textarea',
      '#title' => 'Living in a fun and future-focused city, with lot of things to do and opps for internships',
      '#default_value' => $config->get('living_in_fun'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['meeting_people_from_world_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Meeting people from all over the world, and/or studying abroad heading.',
      '#default_value' => $config->get('meeting_people_from_world_heading'),
      '#format' => 'full_html',
    ];

    $form['what_excites_you_about_the_college_experience_check_all_that_app']['meeting_people_from_world'] = [
      '#type' => 'textarea',
      '#title' => 'Meeting people from all over the world, and/or studying abroad.',
      '#default_value' => $config->get('meeting_people_from_world'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_'] = [
      '#type' => 'details',
      '#title' => t('What are you the most nervous about? Check all that apply dynamic content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['new_people_heading'] = [
      '#type' => 'textfield',
      '#title' => 'A new place with new people heading.',
      '#default_value' => $config->get('new_people_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['new_people'] = [
      '#type' => 'textarea',
      '#title' => 'A new place with new people.',
      '#default_value' => $config->get('new_people'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['rigorous_academics_of_college_heading'] = [
      '#type' => 'textfield',
      '#title' => 'The rigorous academics of college heading.',
      '#default_value' => $config->get('rigorous_academics_of_college_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['rigorous_academics_of_college'] = [
      '#type' => 'textarea',
      '#title' => 'The rigorous academics of college',
      '#default_value' => $config->get('rigorous_academics_of_college'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['living_first_time_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Living on my own for the first time heading.',
      '#default_value' => $config->get('living_first_time_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['living_first_time'] = [
      '#type' => 'textarea',
      '#title' => 'Living on my own for the first time',
      '#default_value' => $config->get('living_first_time'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['right_major_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Choosing the right major.',
      '#default_value' => $config->get('right_major_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['right_major'] = [
      '#type' => 'textarea',
      '#title' => 'Choosing the right major',
      '#default_value' => $config->get('right_major'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['paying_for_college_nervous_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Paying for college heading.',
      '#default_value' => $config->get('paying_for_college_nervous_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['paying_for_college_nervous'] = [
      '#type' => 'textarea',
      '#title' => 'Paying for college',
      '#default_value' => $config->get('paying_for_college_nervous'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['making_friends_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Making friends heading.',
      '#default_value' => $config->get('making_friends_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['making_friends'] = [
      '#type' => 'textarea',
      '#title' => 'Making friends',
      '#default_value' => $config->get('making_friends'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['my_way_around_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Finding my way around.',
      '#default_value' => $config->get('my_way_around_heading'),
      '#format' => 'full_html',
    ];

    $form['what_are_you_the_most_nervous_about_check_all_that_apply_']['my_way_around'] = [
      '#type' => 'textarea',
      '#title' => 'Finding my way around',
      '#default_value' => $config->get('my_way_around'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content'] = [
      '#type' => 'details',
      '#title' => t('AZ, CA and WUE dynamic content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['residency_dynamic_content']['campusRes_heading'] = [
      '#type' => 'textfield',
      '#title' => 'Arizona residents dynamic content heading.',
      '#default_value' => $config->get('campusRes_heading'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['campusRes'] = [
      '#type' => 'textarea',
      '#title' => 'Arizona residents dynamic content',
      '#default_value' => $config->get('campusRes'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['CARes_heading'] = [
      '#type' => 'textfield',
      '#title' => 'California residents dynamic content heading.',
      '#default_value' => $config->get('CARes_heading'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['CARes'] = [
      '#type' => 'textarea',
      '#title' => 'California residents dynamic content',
      '#default_value' => $config->get('CARes'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['wueRes_heading'] = [
      '#type' => 'textfield',
      '#title' => 'WUE residents dynamic content heading.',
      '#default_value' => $config->get('wueRes_heading'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['wueRes'] = [
      '#type' => 'textarea',
      '#title' => 'WUE residents dynamic content',
      '#default_value' => $config->get('wueRes'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['terRes_heading'] = [
      '#type' => 'textfield',
      '#title' => 'In another U.S. state or territory dynamic content heading.',
      '#default_value' => $config->get('terRes_heading'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['terRes'] = [
      '#type' => 'textarea',
      '#title' => 'In another U.S. state or territory dynamic content.',
      '#default_value' => $config->get('terRes'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['intlRes_heading'] = [
      '#type' => 'textfield',
      '#title' => 'International residents dynamic content heading.',
      '#default_value' => $config->get('intlRes_heading'),
      '#format' => 'full_html',
    ];

    $form['residency_dynamic_content']['intlRes'] = [
      '#type' => 'textarea',
      '#title' => 'International residents dynamic content',
      '#default_value' => $config->get('intlRes'),
      '#format' => 'full_html',
    ];

    $form['asu_local_dynamic_content'] = [
      '#type' => 'details',
      '#title' => t('ASU LOCAL DC, YUMA LA dynamic content'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['asu_local_dynamic_content']['asuLocalDc'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Local DC dynamic content',
      '#default_value' => $config->get('asuLocalDc'),
      '#format' => 'full_html',
    ];

    $form['asu_local_dynamic_content']['asuLocalYuma'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Local Yuma dynamic content',
      '#default_value' => $config->get('asuLocalYuma'),
      '#format' => 'full_html',
    ];

    $form['asu_local_dynamic_content']['asuLocalLA'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Local LA dynamic content',
      '#default_value' => $config->get('asuLocalLA'),
      '#format' => 'full_html',
    ];

    $form['asu_local_dynamic_content']['asuLongBeach'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Long Beach dynamic content',
      '#default_value' => $config->get('asuLongBeach'),
      '#format' => 'full_html',
    ];

    $form['asu_local_dynamic_content']['asuWestHawai'] = [
      '#type' => 'textarea',
      '#title' => 'ASU WestHawai dynamic content',
      '#default_value' => $config->get('asuWestHawai'),
      '#format' => 'full_html',
    ];
    $form['asu_local_dynamic_content']['asuWestHavasu'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Lake Havasu dynamic content',
      '#default_value' => $config->get('asuWestHavasu'),
      '#format' => 'full_html',
    ];
    $form['asu_local_dynamic_content']['asuChulaVista'] = [
      '#type' => 'textarea',
      '#title' => 'ASU Chula Vista dynamic content',
      '#default_value' => $config->get('asuChulaVista'),
      '#format' => 'full_html',
    ];
    /*$form['asu_local_dynamic_content']['asuLocalLAGrad'] =  array(
    '#type' => 'textarea',
    '#title' => 'ASU Local LA Graduate dynamic content',
    '#default_value' => $config->get('asuLocalLAGrad'),
    '#format' => 'full_html',
    );  */

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // \Drupal::logger('grouprowsin')->notice(print_r($form_state->getValue('focused_futurist_content'), TRUE));
    parent::submitForm($form, $form_state);
    // ksm($form_state->getValues());
    $values = $form_state->getValues();
    foreach ($values as $key => $each_value) {
      $this->config('asu_campus_fit.admin_settings')
        ->set($key, $each_value)
        ->save();
    }

  }

}
