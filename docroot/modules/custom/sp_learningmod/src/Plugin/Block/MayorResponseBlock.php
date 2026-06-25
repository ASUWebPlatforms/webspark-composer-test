<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Mayor's Response' Block.
 *
 * @Block(
 *   id = "mayor_response_block",
 *   admin_label = @Translation("Mayor's Response Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class MayorResponseBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $connection = Database::getConnection();

    $submitted_query = $connection->select('sp_learningmod_submitted_plans', 'p')
      ->fields('p', ['uid'])
      ->condition('p.uid', $uid, '=')
      ->execute()
      ->fetchField();

    if (!$submitted_query) {
      return [
        '#theme' => 'mayors_response',
        '#submitted' => FALSE,
        '#cache' => ['max-age' => 0]
      ];
    }

    $query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $query->execute()->fetchCol();

    $planbudget = 0;
    $risk_arr = [];
    $crimeprevent_arr = [];
    $lawenforce_arr = [];
    $success_arr = [];
    $impactterm_arr = [];
    $responses_titles = [];

    foreach ($selected_nids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $planbudget += (int) $node->get('field_cost')->value;
        $risk_arr[] = (int) $node->get('field_risk')->value;
        $crimeprevent_arr[] = (int) $node->get('field_crimeprevent')->value;
        $lawenforce_arr[] = (int) $node->get('field_lawenforce')->value;
        $success_arr[] = (int) $node->get('field_success')->value;
        $impactterm_arr[] = (int) $node->get('field_impactterm')->value;
        $responses_titles[] = $node->getTitle();
      }
    }

    $risk_avg = !empty($risk_arr) ? round(array_sum($risk_arr) / count($risk_arr), 2) : NULL;
    $crimeprevent_avg = !empty($crimeprevent_arr) ? round(array_sum($crimeprevent_arr) / count($crimeprevent_arr), 2) : NULL;
    $lawenforce_avg = !empty($lawenforce_arr) ? round(array_sum($lawenforce_arr) / count($lawenforce_arr), 2) : NULL;
    $success_avg = !empty($success_arr) ? round(array_sum($success_arr) / count($success_arr), 2) : NULL;
    $impactterm_avg = !empty($impactterm_arr) ? round(array_sum($impactterm_arr) / count($impactterm_arr), 2) : NULL;

    switch (TRUE) {
      case $risk_avg <= 2.5:
        $risk_message = '<p>Your overall plan is <strong>low-risk</strong>. By not recommending any drastic changes, you have avoided the possibility of bad publicity or lawsuits. <strong>Community support for your plan should be high</strong>. However, it may be difficult to effect real change in the current situation without taking some risks.</p>';
        break;

      case $risk_avg > 2.5 && $risk_avg < 3.3:
        $risk_message = '<p>Your overall plan is of <strong>moderate risk</strong>. Although the possibility for conflict, bad publicity, and lawsuits exists, it would be difficult to effect any real change without taking some risks. <strong>Community support for your plan should be good</strong>.</p>';
        break;

      case $risk_avg >= 3.3:
        $risk_message = '<p><strong>You chose a number of high-risk responses</strong> that could result in loss of community support or even unrest, not to mention bad publicity, and the possibility of lawsuits. Some of the changes you have recommended may be too drastic in this situation.</p>';
        break;
    }

    switch (TRUE) {
      case $crimeprevent_avg <= 2.5:
        $crimeprevent_message = '<p>Your overall plan seems <strong>biased against environmental changes</strong>. You have chosen a number of <strong>short-term solutions</strong> which do not address the underlying causes of the problem.</p>';
        break;

      case $crimeprevent_avg > 2.5 && $crimeprevent_avg < 3.3:
        $crimeprevent_message = '<p>Your overall plan is <strong>moderately biased towards environmental changes</strong>, which will have <strong>long-term, permanent effects</strong>. Many of your responses get at the underlying cause of the problem.</p>';
        break;

      case $crimeprevent_avg >= 3.3:
        $crimeprevent_message = '<p>Your overall plan is <strong>strongly biased towards environmental changes</strong>, which will have <strong>long-term, permanent effects</strong>. Most of your responses get at the underlying causes of the problem.</p>';
        break;
    }

    switch (TRUE) {
      case $lawenforce_avg <= 2.5:
        $lawenforce_message = '<p>Your overall plan seems <strong>biased against extensive involvement of the criminal justice system</strong>.</p>';
        break;

      case $lawenforce_avg > 2.5 && $lawenforce_avg < 3.3:
        $lawenforce_message = '<p>Your overall plan is <strong>moderate</strong> in terms of the <strong>involvement of the criminal justice and legal systems</strong>.</p>';
        break;

      case $lawenforce_avg >= 3.3:
        $lawenforce_message = '<p>Your overall plan is <strong>biased strongly towards extensive involvement of the criminal justice system</strong>.</p>';
        break;
    }

    switch (TRUE) {
      case $success_avg <= 3:
        $success_message = '<strong>low</strong>';
        break;

      case $success_avg > 3 && $success_avg < 4:
        $success_message = '<strong>moderate</strong>';
        break;

      case $success_avg >= 4:
        $success_message = '<strong>high</strong>';
        break;
    }

    switch (TRUE) {
      case $impactterm_avg <= 2.5:
        $impact_message = '<strong>short-term</strong>';
        break;

      case $impactterm_avg > 2.5:
        $impact_message = '<strong>long-term</strong>';
        break;
    }

    $final_message = "<p>From my experience, the probability of the success of your plan is {$success_message}. Your overall plan seems to favor {$impact_message} <strong>solutions</strong>, <strong><a href='/learning/prostitution/feedback/year-later'>but only time will tell</a></strong>.</p>";

    $budget_message = ($planbudget <= 100) ?
      '<div class="underbudget"><p>Thank you for submitting your plan. You have chosen an interesting variety of possible responses and have kept within your budget allocation. Although I have some concerns as noted below, <strong>I am prepared to push ahead and get council approval to implement all your recommendations</strong>.</p></div>' :
      '<div class="overbudget"><p> Thank you for submitting your plan. You have chosen an interesting variety of possible responses, but <strong>unfortunately have gone over budget</strong>. This means that I will have to be very sure that your recommendations are solid before I attempt to get the council&rsquo;s approval.</p></div>';

    return [
      '#theme' => 'mayors_response',
      '#submitted' => TRUE,
      '#planbudget' => $planbudget,
      '#budget_message' => $budget_message,
      '#risk_message' => $risk_message,
      '#crimeprevent_message' => $crimeprevent_message,
      '#lawenforce_message' => $lawenforce_message,
      '#final_message' => $final_message,
      '#responses_titles' => $responses_titles,
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account)
  {
    return AccessResult::allowed();
  }
}
