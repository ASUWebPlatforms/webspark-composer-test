<?php

namespace Drupal\asu_campus_fit\Plugin\Block;

use Drupal\Core\Render\Markup;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a Campusfit confirmation block.
 *
 * @Block(
 *   id = "campusfit_confirmation_scholar_residency_military_dynamic_block",
 *   admin_label = @Translation("Campus fit dynamic Scholar, Military, and residency content confirmation block from the module"),
 * )
 */
class campusfit_confirmation_scholar_residency_military_dynamic_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current request directly using \Drupal::service('request_stack')
    $request = \Drupal::service('request_stack')->getCurrentRequest();

    // Get query parameters from the URL.
    $sid = $request->query->get('sid', '11');
    $nid = $request->query->get('nid', '');
    $config_data = \Drupal::config('asu_campus_fit.admin_settings');
    // Access the CampusFitConfirmController service using \Drupal::service()
    $webform = Webform::load('campus_fit');
    if ($webform->hasSubmissions()) {
      $query = \Drupal::entityQuery('webform_submission')
        ->condition('webform_id', 'campus_fit')
        ->condition('sid', intval($sid))
        ->accessCheck(FALSE);
      $result = $query->execute();
      $submission_data = [];
      foreach ($result as $item) {
        $submission = WebformSubmission::load($item);
        $submission_data = $submission->getData();
      }
    }
    $military_content = '';
    $scholar_content = '';
    $show_scholar_loc = '';
    $residency_full_content = '';
    $card_content = [];
    //dpm($submission_data, 'submission_data');
    /** Dynamic content for scholarship
    * Check if any asu local campus options have been selected, if they are selected, then do not show scholarship info below.
    **/
    if (!empty($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'])) {
      if (($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'] == 'The metro Phoenix area-7q') || ($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'] == 'Online-13q')) {
        $show_scholar_loc = 'yes';
      }
      else {
        $show_scholar_loc = 'no';
      }
    }
   //dpm($show_scholar_loc, 'show_scholar_loc');
   /*  if (!empty($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'])) {
      dpm('sbm',$submission_data['which_of_these_locations_are_you_interested_in_living_and_learni']);
      if ($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'] == 'London-londonRes') {
        $show_scholar = 'no';
      }
      else {
        $show_scholar = 'yes';
      }
    }  */

    $studentType = $submission_data['asu_can_help_you_achieve_your_academic_goals_no_matter_what_they'] ?? '';
    if(!empty($studentType)){
      if ($studentType != 'advanced degree-21q'){
        if($submission_data['which_of_these_locations_are_you_interested_in_living_and_learni'] != 'London-londonRes'){
          $show_scholar = 'yes';
        }
        else{
          $show_scholar = 'no';
        }
      }
      else {
        $show_scholar = 'no';
      }
    }
    else{
      $show_scholar = 'no';
    }

   // dpm($show_scholar, 'show_scholar1');

    $content_of_scholar = $config_data->get('scholarship_content');
    if(($show_scholar == 'no') && ($show_scholar_loc == 'no')){
      $scholar_content = '';
    }
    else{
      $scholar_content = "<div class='container'>$content_of_scholar</div>";
    }
    
    $online_sub = $submission_data['how_would_you_rather_learn_'] ?? '';
   
    if (($online_sub != 'online-6q')) {
      
      // Dynamic content for military.
      if (!empty($submission_data['what_are_you_doing_now_'])) {
        if ($submission_data['what_are_you_doing_now_'] == "Serving in the military-4q") {
          $military_content = $config_data->get('military_content');
        }
        else {
          $military_content = '';
        }
      }
      else {
        $military_content = '';
      }
      $resident_option = ['i_am_a_resident_of_campus', 'i_am_a_resident_of_grad_options'];
      foreach ($resident_option as $resident_select) {
        if (!empty($submission_data[$resident_select])) {
          $res_selected_data = htmlspecialchars($submission_data[$resident_select], ENT_QUOTES, 'UTF-8');
          if (($res_selected_data == "US citizen that lives abroad") ||  ($res_selected_data == "I have a unique citizenship status")) {
            $residency_full_content = '';
          }
          else {
            $res_exploded_value = explode('-', $res_selected_data);
            $res_heading = $res_exploded_value[1] . '_heading';
            $res_heading_config = $config_data->get($res_heading);
            $res_data = $config_data->get($res_exploded_value[1]);
            $residency_content_header = "<div class='container'><h3><span class='highlight-gold'>$res_heading_config</span></h3>";
            $residency_full_content = $residency_content_header . $res_data . "</div><br />";
          }
        }

      }

      $rendered_residency_content = !empty($residency_full_content) ? $residency_full_content : '';
      
      $dynamic_content_array = [$scholar_content, $military_content, $rendered_residency_content];
      
      // \Drupal::logger('dynamic_content_array')->info('<pre>' . print_r($dynamic_content_array, TRUE) . '</pre>');
      $dynamic_content_array = array_filter($dynamic_content_array);
      // \Drupal::logger('dynamic_content_array_one')->info('<pre>' . print_r($dynamic_content_array, TRUE) . '</pre>');
      if (!empty($dynamic_content_array)) {
        $dynamic_cards_begin = "<div class='uds-card-arrangement-card-container auto-arrangement two-columns custom-mil-res-content'>";
        foreach ($dynamic_content_array as $card_value) {
          $card_content[] = "<div class='card-wrapper'><div class='card cards-components'><div class='card-body'>" . $card_value . "</div></div></div>";
        }
        // \Drupal::logger('$card_content')->info('<pre>' . print_r($card_content, TRUE) . '</pre>');
        $dynamic_cards_end = "</div>";
        $dynamic_all_data = $dynamic_cards_begin . implode('', $card_content) . $dynamic_cards_end;
      }
      else {
        $dynamic_all_data = '';
      }
      // \Drupal::logger('dynamic_all_data')->info('<pre>' . print_r($dynamic_all_data, TRUE) . '</pre>');
      $email_button = "<div class='container'><span class='btn btn-maroon btn-primary fit_email_result'>Email a copy of results</span><div id='email_form'></div></div>";

      // Code to print email form.
      $emailwebform = \Drupal::entityTypeManager()->getStorage('webform')->load('campus_fit_email_confirmation_fo');
      $view_builder = \Drupal::service('entity_type.manager')->getViewBuilder('webform');
      $form_build = $view_builder->view($emailwebform);
      $confirmation_email_form = "<br /><div class='container'><div id='email_confirm_form' class='fit-group'>";
      $confirmation_email_form .= \Drupal::service('renderer')->render($form_build);
      $confirmation_email_form .= "</div></div>";

      //$rendered_content = $dynamic_all_data . $confirmation_email_form;
      $rendered_content = $dynamic_all_data;
    }
    else {
      $rendered_content = '';
    }
    // Return $accordion;.
    return [
      '#markup' => Markup::create($rendered_content),
      '#cache' => [
        'max-age' => 0,
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
