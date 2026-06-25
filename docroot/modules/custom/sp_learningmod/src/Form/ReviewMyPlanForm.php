<?php

namespace Drupal\sp_learningmod\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReviewMyPlanForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'sp_learningmod_reviewmyplan_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $connection = Database::getConnection();
    $query = $connection->select('sp_learningmod_selected_responses', 's')
      ->fields('s', ['response_nid'])
      ->condition('s.uid', $uid, '=');
    $selected_nids = $query->execute()->fetchCol();

    $responses = [];
    $totalCost = 0;
    foreach ($selected_nids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $cost = (int) $node->get('field_cost')->value;
        $body = Markup::create($node->get('body')->value);
        $responses[$node->id()] = [
          'cost' => Markup::create('<strong>' . $cost . '%</strong>'),
          'res_body' => Markup::create('<div class="response-box">' . $body . '</div>'),
        ];
        $totalCost += $cost;
      }
    }

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
      '#markup' => '<h1>Review My Plan</h1>',
    ];

    if (empty($responses)) {
      $form['container']['row']['content']['empty_message'] = [
        '#type' => 'markup',
        '#markup' => '<p>You have not selected any responses yet.</p><p><a class="btn btn-secondary" href="/learning/prostitution/plan/buildmyplan">Go Back to Build My Plan</a></p>',
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
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $responses,
      '#empty' => t('No responses selected.'),
      '#attributes' => [
        'style' => 'margin-bottom: auto;',
      ],
    ];

    $submitted_query = $connection->select('sp_learningmod_submitted_plans', 'p')
      ->fields('p', ['uid'])
      ->condition('p.uid', $uid, '=')
      ->execute()
      ->fetchField();

    $submit_label = $submitted_query ? t('Re-Submit My Plan') : t('Submit My Plan');

    if ($submitted_query) {
      $form['container']['row']['content']['budget_summary_text'] = [
        '#type' => 'markup',
        '#markup' => '<h3>Total amount of budget being allocated: ' . $totalCost . '%</h3>',
      ];
    }

    $form['container']['row']['content']['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_label,
      '#attributes' => ['class' => ['btn', 'btn-primary'], 'style' => 'margin-top: 1rem;'],
    ];

    $form['container']['row']['content']['back'] = [
      '#type' => 'markup',
      '#markup' => '<a href="/learning/prostitution/plan/buildmyplan" class="btn cq-secondary-button margin-top1">Go Back to Build My Plan</a>',
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

    $exists = $connection->select('sp_learningmod_submitted_plans', 'p')
      ->fields('p', ['uid'])
      ->condition('p.uid', $uid, '=')
      ->execute()
      ->fetchField();

    if (!$exists) {
      $connection->insert('sp_learningmod_submitted_plans')
        ->fields([
          'uid' => $uid,
          'submitted_at' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();
    }

    $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput('/learning/prostitution/feedback/mayors-response'));
  }
}
