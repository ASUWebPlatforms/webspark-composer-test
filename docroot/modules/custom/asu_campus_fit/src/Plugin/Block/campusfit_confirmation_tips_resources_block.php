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
 *   id = "campusfit_confirmation_tips_resources_block",
 *   admin_label = @Translation("Campus fit Tips and Resources section confirmation block from the module"),
 * )
 */
class campusfit_confirmation_tips_resources_block extends BlockBase {

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
    //dpm($nid);
    
    $config_data = \Drupal::config('asu_campus_fit.admin_settings');
    if($nid == $config_data->get('on_campus_nid')){
      
    }
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
    $online_sub = $submission_data['how_would_you_rather_learn_'] ?? '';
    
    // Dymanic content for tips and resources.
    $dynamic_options = ['what_excites_you_about_the_college_experience_check_all_that_app', 'what_are_you_the_most_nervous_about_check_all_that_apply_', 'what_excites_you_about_graduate_studies_check_all_that_apply_'];

    foreach ($dynamic_options as $sub_options) {
      if (!empty($submission_data[$sub_options])) {
        if($online_sub == 'online-6q'){
          $accordion_head = '<div class="container no-tips-online"><h2><span class="highlight-gold">Tips and resources</span></h2><div class="accordion" id="accordion-resources">';
        }
        else{
          $accordion_head = '<div class="container"><h2><span class="highlight-gold">Tips and resources</span></h2><div class="accordion" id="accordion-resources">';
        }
        $accordion_end = '</div></div>';

        foreach ($submission_data[$sub_options] as $key => $sub_value) {
          $sub_value_content = $config_data->get(htmlspecialchars($sub_value), ENT_QUOTES, 'UTF-8');
          $sub_html_value = htmlspecialchars($sub_value, ENT_QUOTES, 'UTF-8');
          $exploded_value = explode('-', $sub_html_value);
          $heading_value = $exploded_value[0] . '_heading';
          $excited_dynamic_content = $config_data->get($exploded_value[0]);
          $heading_dynamic_content = $config_data->get($heading_value);
          $card_header = '<div class="accordion-item mt-3"><div class="accordion-header"><h4>
				<a id="accordion-header-' . $exploded_value[0] . '" class="collapsed" href="#accordion-header-' . $exploded_value[0] . '"  data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="accordion-content-' . $exploded_value[0] . '" data-ga-event="collapse" data-ga-name="onclick"  data-ga-type="click"  data-ga-section="accordion block" data-ga-region="main content">' . $heading_dynamic_content . '<span class="fas fa-chevron-up"></span></a></h4></div>';
          $card_body = '<div aria-labelledby="' . $heading_dynamic_content . '" class="collapse accordion-body" aria-labelledby="accordion-header-' . $exploded_value[0] . '" data-bs-parent="#accordion-resources" id="accordion-header-' . $exploded_value[0] . '" style="">' . $excited_dynamic_content . '</div></div>';
          $excited_accordion_data[] = $card_header . $card_body;
        }

      }
      else {
        $excited_accordion_data[] = '';
      }
    }
    // \Drupal::logger('excited_accordion_data')->info('<pre>' . print_r($excited_accordion_data, TRUE) . '</pre>');
    if (!empty($excited_accordion_data)) {
      foreach ($excited_accordion_data as $acdata) {
        $all_ac_data[] = $acdata;
        $all_tips_data = implode('', $all_ac_data);
      }
      if (!empty($all_tips_data)) {
        $accordion = $accordion_head . $all_tips_data . $accordion_end . "<br />";
      }
      else {
        $accordion = '';
      }
    }
    else {
      $accordion = '';
    }
    // \Drupal::logger('accordion')->info('<pre>' . print_r($accordion, TRUE) . '</pre>');
    $rendered_content = $accordion;
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
