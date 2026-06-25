<?php

namespace Drupal\sp_learningmod\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\sp_learningmod\Service\CouncilMeetingService;
use Drupal\sp_learningmod\Service\UserBudgetService;
use Drupal\Core\Database\Database;

/**
 * Provides the City Council Meeting form.
 */
class CouncilMeetingForm extends FormBase
{

  protected $councilMeetingService;
  protected $userBudgetService;

  public function __construct(CouncilMeetingService $councilMeetingService, UserBudgetService $userBudgetService)
  {
    $this->councilMeetingService = $councilMeetingService;
    $this->userBudgetService = $userBudgetService;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sp_learningmod.council_meeting'),
      $container->get('sp_learningmod.user_budget')
    );
  }

  public function getFormId()
  {
    return 'sp_learningmod_council_meeting_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $uid = \Drupal::currentUser()->id();
    $questions = $this->councilMeetingService->getQuestions();
    $total_questions = count($questions);
    $current_question = $this->getCurrentQuestion($uid);
    $feedback_shown = $this->getUserFeedbackStatus($uid);

    if ($current_question >= $total_questions) {
      return $this->buildFinalPage($form, $form_state);
    }

    $question_index = ($feedback_shown && $current_question > 0) ? $current_question - 1 : $current_question;

    if (!isset($questions[$question_index])) {
      \Drupal::logger('sp_learningmod')->error('Invalid question index: @index for user @uid', [
        '@index' => $question_index,
        '@uid' => $uid,
      ]);
      return [
        '#markup' => '<p>Error: No questions available. Please contact support.</p>',
      ];
    }

    $question_data = $questions[$question_index];
    $correct_answer = $question_data['correct_answer'];
    $answers = $question_data['options'] ?? [];

    $user_answer = $this->getUserAnswer($uid, $question_index);
    $is_correct = ($user_answer === $correct_answer);

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container', 'bg-white', 'p-4', 'rounded', 'shadow', 'mt-5'],
        'style' => 'max-width:700px;',
      ],
    ];

    $form['container']['title'] = [
      '#markup' => '<h1 class="fw-bold">City Council Meeting</h1>',
    ];

    $form['container']['progress'] = [
      '#markup' => '<h3 class="mt-3">Question ' . ($question_index + 1) . ' of ' . $total_questions . '</h3>',
    ];

    if ($feedback_shown) {
      $selected_answer_text = isset($answers[$user_answer]) ? $answers[$user_answer] : 'Unknown Answer';
      $feedback_html = $is_correct
        ? $question_data['correct_explanation']
        : str_replace('{USER_ANSWER}', '<strong>' . $selected_answer_text . '</strong>', $question_data['incorrect_explanation']);

      $form['container']['feedback'] = [
        '#type' => 'markup',
        '#markup' => '<div class="mt-4">' . $feedback_html . '</div>',
      ];

      if ($current_question === ($total_questions)) {
        $form['container']['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => 'Finish',
          '#attributes' => ['class' => ['btn', 'btn-primary', 'mt-3']],
        ];
        return $form;
      }
    } else {
      $form['container']['question'] = [
        '#type' => 'markup',
        '#markup' => '<div class="mt-4"><strong>' . $question_data['question'] . '</strong></div>',
      ];

      if ($current_question < $total_questions) {
        $form['container']['answer'] = [
          '#type' => 'radios',
          '#options' => $answers,
          '#required' => TRUE,
        ];
      }
    }

    $form['container']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Continue',
      '#attributes' => ['class' => ['btn', 'btn-maroon', 'mt-3']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $uid = \Drupal::currentUser()->id();
    $current_question = $this->getCurrentQuestion($uid);
    $feedback_shown = $this->getUserFeedbackStatus($uid);
    $questions = $this->councilMeetingService->getQuestions();
    $total_questions = count($questions);

    if ($feedback_shown) {
      $this->setCurrentQuestion($uid, $current_question + 1);
      $this->setUserFeedbackStatus($uid, FALSE);
    } else {
      $selected_answer = $form_state->getValue('answer');
      $correct_answer = $questions[$current_question]['correct_answer'];
      $is_correct = ($selected_answer === $correct_answer);

      $this->saveUserAnswer($uid, $current_question, $selected_answer, $is_correct);
      $this->setUserFeedbackStatus($uid, TRUE);
    }

    $form_state->setRebuild(TRUE);
  }

  private function getUserFeedbackStatus($uid)
  {
    $connection = \Drupal::database();
    $result = $connection->select('sp_learningmod_council_progress', 'p')
      ->fields('p', ['feedback_shown'])
      ->condition('p.uid', $uid)
      ->execute()
      ->fetchField();

    return ($result !== NULL) ? (int) $result : 0;
  }

  private function setUserFeedbackStatus($uid, $status)
  {
    $connection = \Drupal::database();

    $exists = $connection->select('sp_learningmod_council_progress', 'p')
      ->fields('p', ['uid'])
      ->condition('p.uid', $uid)
      ->execute()
      ->fetchField();

    if (!$exists) {
      $connection->insert('sp_learningmod_council_progress')
        ->fields([
          'uid' => $uid,
          'current_question' => 0,
          'feedback_shown' => (int) $status,
          'passed' => 0,
        ])
        ->execute();

      \Drupal::logger('sp_learningmod')->notice("Inserted new record for user @uid in sp_learningmod_council_progress", ['@uid' => $uid]);
    } else {
      $updated = $connection->update('sp_learningmod_council_progress')
        ->fields(['feedback_shown' => (int) $status])
        ->condition('uid', $uid)
        ->execute();

      if (!$updated) {
        \Drupal::logger('sp_learningmod')->warning("Failed to update feedback_shown for user @uid", ['@uid' => $uid]);
      } else {
        \Drupal::logger('sp_learningmod')->notice("Updated feedback_shown for user @uid to @status", [
          '@uid' => $uid,
          '@status' => $status,
        ]);
      }
    }
  }

  private function saveUserAnswer($uid, $question_number, $answer, $is_correct)
  {
    $connection = Database::getConnection();
    $connection->merge('sp_learningmod_council_answers')
      ->key(['uid' => $uid, 'question_number' => $question_number])
      ->fields([
        'answer' => $answer,
        'is_correct' => $is_correct ? 1 : 0,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();
  }

  private function getUserAnswer($uid, $question_number)
  {
    $connection = Database::getConnection();
    return $connection->select('sp_learningmod_council_answers', 'a')
      ->fields('a', ['answer'])
      ->condition('a.uid', $uid, '=')
      ->condition('a.question_number', $question_number, '=')
      ->execute()
      ->fetchField();
  }

  private function getCurrentQuestion($uid)
  {
    $connection = Database::getConnection();
    $last_question = $connection->select('sp_learningmod_council_answers', 'a')
      ->fields('a', ['question_number'])
      ->condition('a.uid', $uid, '=')
      ->orderBy('question_number', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();

    return $last_question !== FALSE ? $last_question + 1 : 0;
  }

  private function setCurrentQuestion($uid, $question_number)
  {
    $connection = \Drupal::database();
    $timestamp = \Drupal::time()->getRequestTime();

    $connection->update('sp_learningmod_council_progress')
      ->fields([
        'current_question' => $question_number,
        'updated' => $timestamp,
      ])
      ->condition('uid', $uid)
      ->execute();
  }

  private function buildFinalPage(array &$form, FormStateInterface $form_state)
  {
    $uid = \Drupal::currentUser()->id();
    $questions = $this->councilMeetingService->getQuestions();
    $total_questions = count($questions);
    $correct_count = 0;

    $user_answers = $this->getUserAnswers($uid);

    foreach ($questions as $index => $question) {
      if (isset($user_answers[$index]) && $user_answers[$index] === $question['correct_answer']) {
        $correct_count++;
      }
    }

    $score = "{$correct_count}/{$total_questions}";
    $pass_threshold = 8;

    if ($correct_count < $pass_threshold) {
      $this->deleteUserAnswers($uid);
      $this->setUserFailed($uid);
    } else {
      $this->setUserPassed($uid);
    }

    $budget_spent = 100 - $this->userBudgetService->getBudget();
    $budget_spent_text = number_format($budget_spent, 0) . '%';

    $form['memo'] = [
      '#markup' => ($correct_count < $pass_threshold) ? $this->getFailureMemo($score, $budget_spent_text) : $this->getSuccessMemo($score, $budget_spent_text),
    ];

    return $form;
  }

  private function deleteUserAnswers($uid)
  {
    $connection = \Drupal::database();
    $connection->delete('sp_learningmod_council_answers')
      ->condition('uid', $uid)
      ->execute();
  }

  private function setUserFailed($uid)
  {
    $connection = \Drupal::database();
    $connection->update('sp_learningmod_council_progress')
      ->fields(['passed' => 0, 'current_question' => 1, 'feedback_shown' => 0])
      ->condition('uid', $uid)
      ->execute();
  }

  private function setUserPassed($uid)
  {
    $connection = \Drupal::database();
    $connection->update('sp_learningmod_council_progress')
      ->fields(['passed' => 1])
      ->condition('uid', $uid)
      ->execute();
  }

  private function getUserAnswers($uid)
  {
    $connection = \Drupal::database();
    $results = $connection->select('sp_learningmod_council_answers', 'a')
      ->fields('a', ['question_number', 'answer'])
      ->condition('a.uid', $uid)
      ->execute()
      ->fetchAllAssoc('question_number');

    $answers = [];
    foreach ($results as $question_number => $record) {
      $answers[$question_number] = $record->answer;
    }
    return $answers;
  }

  private function getFailureMemo($score, $budget_spent_text)
  {
    return '
        <div class="container bg-white p-4 rounded shadow margin-top-96" style="max-width:700px;">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <p class="text-muted mt-2">
                        <strong>Mark L. Coleman</strong><br>
                        <span>Office of the Mayor</span><br>
                        <span>325 Scott Avenue</span><br>
                        <span>Central City</span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <img src="/sites/default/files/cc-logo.gif" alt="Central City logo" width="108" height="76">
                </div>
            </div>
            <h3 class="fw-bold">Memorandum</h3>
            <h2 class="text-danger">You need to do more research!</h2>
            <p><strong>You scored ' . $score . '.</strong></p>
            <p class="text-danger"><strong>You have failed to answer at least 80% of the City Council’s questions correctly!</strong></p>
            <p>I am extremely disappointed! Your analysis revealed inaccurate information. However, you have only spent <strong>' . $budget_spent_text . '</strong> of the budget. So, you have some room in the budget for more in-depth analysis. I suggest you <a href="/learning/prostitution/analyze-problem">continue your investigation</a> so you can better answer the city council’s questions.</p>
            <p>You may also forge ahead to <a href="/learning/prostitution/plan/buildmyplan">create your plan</a>, but be warned that choosing responses based on incomplete information could be disastrous.</p>
            <div class="mt-4">
                <img class="img-fluid" src="/sites/default/files/inline-images/memo_r3_c1_0.png" alt="Signature" width="152" height="48">
                <p><strong>M.L. Coleman</strong></p>
            </div>
        </div>';
  }

  private function getSuccessMemo($score, $budget_spent_text)
  {
    return '
        <div class="container bg-white p-4 rounded shadow margin-top-96" style="max-width:700px;">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <img src="/sites/default/files/cc-lh-coleman.gif" alt="Mark L. Coleman, Office of the Mayor" width="165" height="85">
                </div>
                <div class="col-md-6 text-end">
                    <img src="/sites/default/files/cc-logo.gif" alt="Central City logo" width="108" height="76">
                </div>
            </div>
            <h3 class="fw-bold">Memorandum</h3>
            <h2 style="font-size: 1.6em; font-weight: bold; color: #336699;">Excellent!</h2>
            <p><strong>You scored ' . $score . '.</strong></p>
            <p>Since your score meets the required 80% and you completed your analysis within the budget constraints I provided, you may now proceed to build your plan.</p>
            <p>As you begin the plan-building process, please evaluate the revealed responses and decide which ones are best suited for your response plan.</p>
            <p>I am giving you a new budget for creating your plan, which I hope you will spend wisely.</p>
            <div class="mt-4">
                <img class="img-fluid" src="/sites/default/files/memo_r3_c1.gif" alt="Signature" width="152" height="48">
                <p><strong>M.L. Coleman</strong></p>
            </div>
            <p class="text-center">
                <a class="btn btn-primary" href="/learning/prostitution/plan/buildmyplan">Start my plan</a>
            </p>
        </div>';
  }
}
