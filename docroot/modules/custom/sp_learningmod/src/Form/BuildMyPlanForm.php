<?php

namespace Drupal\sp_learningmod\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BuildMyPlanForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'sp_learningmod_buildmyplan_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $connection = Database::getConnection();

    $selected_query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $selected_query->execute()->fetchCol();

    $visited_query = $connection->select('sp_learningmod_visited_nodes', 'v')
      ->fields('v', ['nid'])
      ->condition('v.uid', $uid, '=');
    $visited_nids = $visited_query->execute()->fetchCol();

    $responses = [];
    $costs = [];
    foreach ($visited_nids as $nid) {
      $node = Node::load($nid);
      if ($node && $node->hasField('field_sp_associated_responses')) {
        $associated_responses = $node->get('field_sp_associated_responses')->getValue();
        foreach ($associated_responses as $response) {
          $response_node = Node::load($response['target_id']);
          if ($response_node) {
            $cost = (int) $response_node->get('field_cost')->value;
            $body = Markup::create($response_node->get('body')->value);
            $responses[$response_node->id()] = [
              'cost' => Markup::create('<strong>' . $cost . '%</strong>'),
              'res_body' => Markup::create('<div class="response-box">' . $body . '</div>'),
            ];
            $costs[$response_node->id()] = $cost;
          }
        }
      }
    }

    $banner_html = '<div class="container">
                      <div class="row justify-content-center">
                          <div class="col-md-12">
                              <div id="budget-banner" class="alert alert-info">
                                  <div class="info-container">
                                      <i class="fas fa-info-circle info-icon"></i>
                                      <div class="text-container">
                                          <div>Create My Plan</div>
                                          <div><strong>Budget spent:</strong></div>
                                          <div class="sp-progress-container">
                                              <div id="budget-progress-bar" class="sp-budget-bar" data-percentage="0">0%</div>
                                          </div>
                                      </div>
                                  </div>
                                  <span class="close-btn cerrar-boton" data-bs-dismiss="alert" role="button" aria-label="Close">
                                      <i class="fas fa-times"></i>
                                  </span>
                              </div>
                          </div>
                      </div>
                  </div>';

    $form['budget_progress'] = [
      '#type' => 'markup',
      '#markup' => $banner_html,
      '#prefix' => '<div id="budget-container">',
      '#suffix' => '</div>',
    ];

    $form['#attached']['library'][] = 'sp_learningmod/budget_update';

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container']],
    ];

    $form['container']['row'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row']],
    ];

    $form['container']['row']['content'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['layout__region', 'layout__region--first', 'col-md-12']],
    ];

    $form['container']['row']['content']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h1>Build My Plan</h1>',
    ];

    $form['container']['row']['content']['instructions'] = [
      '#type' => 'markup',
      '#markup' => '<p>Listed below are responses which have been revealed through your analysis but have not been added to your plan. Select which responses you would like to add to your plan by checking the boxes to the left.</p>
                   <p>When you have finished selecting all of the responses you would like to add to your plan, click the "Add to Plan" button at the bottom.</p>',
    ];

    if (empty($responses)) {
      $form['container']['row']['content']['markup'] = [
        '#type' => 'markup',
        '#markup' => '<p>You don\'t have any revealed responses. You need to do more research.</p><p><a class="btn btn-primary" href="/learning/prostitution/analyze-problem">Return to Problem Analysis</a></p>',
      ];
      return $form;
    }

    $header = [
      'cost' => t('Cost'),
      'res_body' => t('Response'),
    ];

    $form['container']['row']['content']['table_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['uds-table']],
    ];

    $form['container']['row']['content']['table_wrapper']['responses_table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $responses,
      '#empty' => t('No responses available.'),
      '#attributes' => [
        'id' => 'responses_table',
        'style' => 'margin-bottom: auto;',
        'data-costs' => json_encode($costs),
      ],
    ];

    $form['container']['row']['content']['table_wrapper']['responses_table']['#default_value'] = array_combine($selected_nids, $selected_nids);

    $form['container']['row']['content']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add to Plan'),
      '#attributes' => ['class' => ['btn', 'btn-primary'], 'style' => 'margin-top: 1rem;'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $uid = \Drupal::currentUser()->id();
    $connection = Database::getConnection();
    $selected_responses = array_filter($form_state->getValue('responses_table'));

    $connection->delete('sp_learningmod_selected_responses')
      ->condition('uid', $uid, '=')
      ->execute();

    foreach ($selected_responses as $response_nid) {
      $connection->insert('sp_learningmod_selected_responses')
        ->fields([
          'uid' => $uid,
          'response_nid' => $response_nid,
        ])
        ->execute();
    }

    $response = new RedirectResponse('/learning/prostitution/plan/reviewmyplan');
    $response->send();
  }
}
