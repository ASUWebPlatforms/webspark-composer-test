<?php

namespace Drupal\aportal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Render\Markup;
use Drupal\aportal\Service\DashboardGlobals;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\TrustedRedirectResponse;

/**
 * Controller for the APortal landing pages and API endpoints.
 */
class AportalController extends ControllerBase {

  /**
   * The DashboardGlobals service.
   *
   * @var \Drupal\aportal\Service\DashboardGlobals
   */
  protected DashboardGlobals $globals;

  /**
   * Constructs the controller.
   *
   * @param \Drupal\aportal\Service\DashboardGlobals $globals
   *   The dashboard globals service.
   */
  public function __construct(DashboardGlobals $globals) {
    $this->globals = $globals;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aportal.dashboard_globals')
    );
  }

  /**
   * Render the access denied page for users without APortal access.
   *
   * @return array
   *   A render array containing the access denied message.
   */
  protected function accessDeniedPage(): array {
    return [
      '#markup' => '
          <h1>Access Required</h1>
          <p>You do not currently have access to Aportal.</p>
          <p>Please contact your delegate or the assessment team for assistance.</p>',
    ];
  }

  /**
   * Check whether the current user has any APortal access record.
   *
   * @return bool
   *   TRUE when the current user is found in the PA_User access table,
   *   FALSE otherwise.
   */
  protected function userHasAportalAccess(): bool {
    return has_access(\Drupal::currentUser()->getAccountName());
  }

  /**
   * Builds the Assessment Portal landing page and injects app settings.
   *
   * @return array
   *   Render array for the portal shell.
   */
  public function aportalLoad() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $asurite = \Drupal::currentUser()->getAccountName();
    $baseUrl = $GLOBALS['base_url'] ?? '';

    $delegateAdmin = assessment_access();
    $delegate = $delegateAdmin['delegate'];
    $admin = $delegateAdmin['admin'];
    $allAccess = $admin ? TRUE : FALSE;

    $collegeAccess = college_access_sql($asurite, $allAccess);
    $collegestr = $collegeAccess['collegestr'];
    $collegestrNonadmin = $collegeAccess['collegestr_nonadmin'];
    $programQry = program_access_sql($asurite, $allAccess, $delegate, $collegestr);

    $this->globals->saveUserValue('delegate', $delegate);
    $this->globals->saveUserValue('admin', $admin);
    $this->globals->saveUserValue('program_qry', $programQry);
    $this->globals->saveUserValue('collegestr_nonadmin', $collegestrNonadmin);
    $this->globals->saveUserValue('collegeaccess', $collegeAccess);

    $userOnly = [
      'programs' => \Drupal::database()->query($programQry)->fetchAll(),
      'useraccess' => \Drupal::database()->query('SELECT * FROM PA_User WHERE Asurite LIKE :u', [':u' => $asurite])->fetchAll(),
      'archivereport_open' => \Drupal::database()->query(
        "SELECT op.acadplan, op.No_Data, op.lowenr_confirm, op.submissionYear AS submissionyear, op.SubmissionCode AS submissioncode
          FROM PA_AssessmentReports_Archive_Open op
          INNER JOIN PA_Program pa ON op.acadplan=pa.acadplan
          WHERE SUBSTRING_INDEX(pa.College, '_', 1) IN ($collegestrNonadmin)"
      )->fetchAll(),
    ];

    $globalSnapshot = $this->globals->get();

    $assessment = array_merge([
      'baseurl' => $baseUrl,
      'publicbase' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://'),
      'asurite' => $asurite,
      'delegate' => $delegate,
      'admin' => $admin,
      'colldept' => $collegeAccess['colldept'],
    ], $globalSnapshot, $userOnly);

    $settings = [
      'assessment' => $assessment,
    ];

    return [
      '#type' => 'inline_template',
      '#cache' => [
        'contexts' => ['user'],
        'max-age' => 300,
        'tags' => ['aportal:bootstrap'],
      ],
      '#attached' => [
        'library' => ['aportal/aportal_app'],
        'drupalSettings' => $settings,
      ],
      '#template' => '<div class="aportal-wrapper"></div>',
    ];
  }

  /**
   * Processes single assessment portal updates submitted from the app.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   OK or error response.
   */
  public function assessmentPost() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $request = Request::createFromGlobals();
    $payload = json_decode($request->getContent(), TRUE);
    $body = $payload['body'] ?? [];

    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');
    $year = date('Y');
    $replaceArr = [];
    $removeArr = [];

    $confirmTbl = [
      'planinsertelement' => 'PA_AssessmentPlans',
      'addelement' => 'PA_AssessmentPlans',
      'movelement' => 'PA_AssessmentPlans',
      'replaceelement' => 'PA_AssessmentPlans',
      'replaceOutcome' => 'PA_AssessmentPlans',
      'replaceMeasure' => 'PA_AssessmentPlans',
      'commentsinsert' => 'PA_Comments',
      'commentsresolve' => 'PA_Comments',
      'removeresolve' => 'PA_Comments',
      'hideresolved' => 'PA_Comments',
      'unhideresolved' => 'PA_Comments',
      'reviewreport' => 'PA_AdminReviews',
      'reviewreports' => 'PA_AdminReviews',
      'reviewfeedback' => 'PA_AdminReviews',
      'reviewaprplans' => 'PA_AdminReviews_Apr_Log',
      'reviewplans' => 'PA_AdminReviews_Plans_Log',
      'reviewplanapp' => 'PA_AdminReviews_Apps_Log',
      'submitsa' => 'SA_Triggers',
      'reportinsertopen' => 'PA_CurrentReports_Open',
      'reportinsert' => 'PA_CurrentReports_Results',
    ];

    switch ($body['stmts'][0]['name']) {

      case 'replaceelement':

        $replace = $body['stmts'][0];
        $body['stmts'][0]['name'] = 'replace' . $replace['element'];

        $qry_removal = [
          'Outcome' => "SELECT ID FROM PA_AssessmentPlans WHERE (`active` IS NULL OR `active` LIKE '') AND acadplan LIKE '" . $replace['acadplan'] . "' AND outcome = " . $replace['outcome'],
          'Measure' => "SELECT ID FROM PA_AssessmentPlans WHERE (`active` IS NULL OR `active` LIKE '') AND acadplan LIKE '" . $replace['acadplan'] . "' AND outcome = " . $replace['outcome']
          . " AND measure = " . $replace['measure'],
        ];

        $qry_replace = [
          'Outcome' => "SELECT ID FROM pa_activeplanedit WHERE acadplan LIKE '" . $replace['acadplan'] . "' AND outcome > " . $replace['outcome'],
          'Measure' => "SELECT ID FROM pa_activeplanedit WHERE acadplan LIKE '" . $replace['acadplan'] . "' AND outcome = " . $replace['outcome']
          . " AND measure > " . $replace['measure'],
        ];

        // $markup.=$qry_removal[$replace['element']] ;
        $removals = \Drupal::database()->query($qry_removal[$replace['element']]);
        $replacements = \Drupal::database()->query($qry_replace[$replace['element']]);
        // $markup.=json_encode($removals) ;
        foreach ($replacements as $element) {
          // $markup.=$element['ID'] ;
          array_push($replaceArr, $element->ID);
          array_push($removeArr, $element->ID);
        };
        foreach ($removals as $element) {
          // $markup.=$element['ID'] ;
          array_push($removeArr, $element->ID);
        };

        // debug($ids ) ;.
        if (empty($replaceArr)) {
          unset($body['stmts'][0]);
          $body['confirm'] = 'replace' . $replace['element'];
        }
        else {
          $body['stmts'][0]['name'] = 'replace' . $replace['element'];
        }
        break;

      case 'publicinsert':
        $id = $body['stmts'][0]['values'][':id'];
        $idpo = $body['stmts'][0]['idpo'];
        $acadplan = $body['stmts'][0]['values'][':acadplan'];
        if ($id == $idpo) {
          \Drupal::database()->delete('pa_assessmentplans_public')
            ->condition('ID', $idpo)
            ->execute();
        }
        elseif ($idpo != '') {
          // debug($idpo);
          \Drupal::database()->update('pa_assessmentplans_public')
            ->fields([
              'acadplan' => "{$acadplan}_{$ts}",
              'user' => $id == 'removed' ? 'removed' : 'replaced' ,
            ])
            ->condition('ID', $idpo)
            ->execute();
        }
        break;

    }

    $queries = [
      'planlastaction' => 'UPDATE PA_Program SET planLastAction = :planLastAction, planLastUser = :user WHERE acadplan LIKE :acadplan;',
      'reportlastaction' => 'UPDATE PA_Program SET reportLastAction = :reportLastAction, reportLastUser = :user WHERE acadplan LIKE :acadplan;',
      'submitsa' => "INSERT INTO SA_Triggers (acadplan, submission_code, trigger_placement, trigger_instruction, trigger_certlicense, calendaryear, user) VALUES (:acadplan, :submission_code, :trigger_placement, :trigger_instruction, :trigger_certlicense, '$year', :user);",
      'setpregov' => "UPDATE PA_Program SET PlanStatus = 'Not Submitted', progstatus = 'Pre Gov' WHERE acadplan LIKE :acadplan;",
      'reportapprove' => "UPDATE PA_Program SET ReportStatus = 'Approved', approveTimeStamp_report = :timestamp, approveUser_report = :user WHERE acadplan LIKE :acadplan;",
      'reportsubmit' => "UPDATE PA_Program SET ReportStatus = 'Submitted', submitTimeStamp_report = :timestamp, reportLastAction = :reportLastAction, submitUser_report = :user, reportLastUser = :reportLastUser WHERE acadplan LIKE :acadplan;",
      'reportrevise' => "UPDATE PA_Program SET ReportStatus = 'Revise', reviseTimeStamp_report = :timestamp, reportLastAction = :reportLastAction, reviseUser_report = :user, reportLastUser = :reportLastUser WHERE acadplan LIKE :acadplan;",
      'planapprove' => "UPDATE PA_Program SET PlanStatus = 'Approved', approveTimeStamp_plan = :timestamp, approveUser_plan = :user WHERE acadplan LIKE :acadplan;",
      'plansubmit' => "UPDATE PA_Program SET PlanStatus = 'Submitted', submitTimeStamp_plan = :timestamp, planLastAction = :planLastAction, submitUser_plan = :user, planLastUser = :planLastUser WHERE acadplan LIKE :acadplan;",
      'plancontinue' => "UPDATE PA_Program SET PlanStatus = 'Continuing', contTimeStamp_plan = :timestamp, planLastAction = :planLastAction, contUser_plan = :user, planLastUser = :planLastUser WHERE acadplan LIKE :acadplan;",
      'planrevise' => "UPDATE PA_Program SET PlanStatus = 'Revise', reviseTimeStamp_plan = :timestamp, planLastAction = :planLastAction, reviseUser_plan = :user, planLastUser = :planLastUser WHERE acadplan LIKE :acadplan;",
      'delegatenotify' => "UPDATE PA_Program SET PlanStatus = 'Delegate Notify', submitTimeStamp_plan = :timestamp, planLastAction = :planLastAction, submitUser_plan = :user, planLastUser = :planLastUser WHERE acadplan LIKE :acadplan;",
      'requestfeedback' => "UPDATE PA_Program SET requestFeedback = 'Yes', requestFeedback_User = :user WHERE acadplan LIKE :acadplan;",
      'planinsertelement' => 'INSERT INTO PA_AssessmentPlans (element, element_type, outcome, measure, ref_measure, `description`, survey, canvastags, user, acadplan, eventdate) VALUES (:element, :element_type, :outcome, :measure, :ref_measure, :description, :survey, :canvastags, :user, :acadplan, :eventdate)',
      'moveoutcome' => 'INSERT INTO PA_AssessmentPlans (element, element_type, outcome, measure, ref_measure, `description`, user, acadplan, eventdate) SELECT element, element_type, :outcome AS outcome, measure, ref_measure, `description`, :user, acadplan, :eventdate FROM PA_AssessmentPlans WHERE ID IN (:ids) ;',
      'movemeasure' => 'INSERT INTO PA_AssessmentPlans (element, element_type, outcome, measure, ref_measure, `description`, user, acadplan, eventdate) SELECT element, element_type, :outcome, :measure, ref_measure, `description`, :user, acadplan, :eventdate FROM PA_AssessmentPlans WHERE ID IN (:ids) ;',
      'addelement' => 'INSERT INTO PA_AssessmentPlans (element, outcome, measure, user, acadplan, eventdate) VALUES (:element, :outcome, :measure, :user, :acadplan, :eventdate)',
      'removeelement' => 'UPDATE PA_AssessmentPlans SET active = Concat(\'Inactive, \' , :user, \' , \' , Current_Timestamp) WHERE ID IN (' . implode(',', $removeArr) . ')',
      'deactivateelements' => 'UPDATE PA_AssessmentPlans SET active = Concat(\'Inactive, \' , :user, \' , \' , Current_Timestamp) WHERE ID IN (:ids) ;',
      'replaceOutcome' => 'INSERT INTO PA_AssessmentPlans  (element, outcome, measure, `description`, user, acadplan, eventdate) SELECT element, outcome-1, measure, `description`, :user, acadplan, :eventdate FROM PA_AssessmentPlans WHERE ID IN (' . implode(',', $replaceArr) . ')',
      'replaceMeasure' => 'INSERT INTO PA_AssessmentPlans  (element, outcome, measure, `description`, user, acadplan, eventdate) SELECT element, outcome, measure-1, `description`, :user, acadplan, :eventdate FROM PA_AssessmentPlans WHERE ID IN (' . implode(',', $replaceArr) . ')',
      'reportinsertopen' => 'INSERT INTO PA_CurrentReports_Open (Fac_Names, No_Data, `Changes`, Program_Changes, user, acadplan, eventdate) VALUES (:Fac_Names, :No_Data, :Changes, :Program_Changes, :user, :acadplan, :eventdate)',
      'reportinsert' => 'INSERT INTO PA_CurrentReports_Results (disposition, exptext, result, acadplan, element, eventdate, measure, met, num, pop, outcome, user) VALUES (:disposition, :exptext, :result, :acadplan, :element, :eventdate, :measure, :met, :num, :pop, :outcome, :user)',
      'publicinsert' => 'INSERT INTO pa_assessmentplans_public (id, element, element_type, outcome, measure, ref_measure, `description`, survey, canvastags, user, acadplan) VALUES (:id, :element, :element_type, :outcome, :measure, :ref_measure, :description, :survey, :canvastags, :user, :acadplan)',
      'commentsinsert' => 'INSERT INTO PA_Comments (element, commenttxt, replyto, editpage, acadplan,  asurite, eventdate) VALUES (:element, :commenttxt, :replyto, :editpage, :acadplan, :asurite, :eventdate)',
      'commentsresolve' => "INSERT INTO PA_Comments (element, commenttxt, replyto, editpage, acadplan,  asurite, eventdate) VALUES (:element, 'Resolved', :replyto, :editpage, :acadplan, :asurite, :eventdate)",
      'removeresolve' => "UPDATE PA_Comments SET commenttxt=CONCAT('Unresolved--',:asurite,'--',:eventdate), editpage= CONCAT('--', :editpage, '--', :element) WHERE replyto=:replyto AND acadplan=:acadplan AND commenttxt='Resolved'",
      'hideresolved' => "INSERT INTO PA_Comments (element, commenttxt, replyto, editpage, acadplan,  asurite, eventdate) VALUES (:element, 'Hide', :replyto, :editpage, :acadplan, :asurite, :eventdate)",
      'unhideresolved' => "UPDATE PA_Comments SET commenttxt=CONCAT('Unhidden--',:asurite,'--',:eventdate), editpage= CONCAT('--', :editpage, '--', :element) WHERE replyto=:replyto AND acadplan=:acadplan AND commenttxt='Hide'",
      'commentsedit' => 'UPDATE PA_Comments SET commenttxt=:commenttxt, eventdate=:eventdate WHERE ID=:id',
      'commentsremove' => 'UPDATE PA_Comments SET element= CONCAT(\'--\', element, \'--\'), editpage = CONCAT(\'--\', editpage,\'--\'), eventdate=:eventdate WHERE ID=:id',
      'revisioncommentsinsert' => '',
      'reviewreports' => 'REPLACE INTO PA_AdminReviews(ReviewStatus, reviewtype, ReviewTimeStamp, submissioncode, submissionyear, acadplan, Reviewer, Disposition, disposition_comment, `Faculty Participation`, participation_comment, Measures, measures_comment, Rigor, rigor_comment, Dataentry, dataentry_comment, `Faculty Use Data`, datausage_comment, NoData, nodata_comment) VALUES (:ReviewStatus, :reviewtype, :ReviewTimeStamp, :submissioncode, :submissionyear, :acadplan, :reviewer, :disposition, :disposition_comment, :participation, :participation_comment, :measures, :measures_comment, :rigor, :rigor_comment, :dataentry, :dataentry_comment, :datausage, :datausage_comment, :nodata, :nodata_comment)',
      'reviewplans' => 'INSERT INTO PA_AdminReviews_Plans_Log(ReviewStatus, reviewtype, ReviewTimeStamp, submissioncode, submissionyear, acadplan, outcome, reviewer, disposition, mission, mission_chk, goals, goals_chk, outcomes, outcomes_chk, concepts, concepts_chk, competencies, competencies_chk, mapping, mapping_chk, measures, measures_chk, criteria, criteria_chk, aprocess, aprocess_chk ) VALUES (:ReviewStatus, :reviewtype, :ReviewTimeStamp, :submissioncode, :submissionyear, :acadplan, :outcome, :reviewer, :disposition, :mission, :mission_chk, :goals, :goals_chk, :outcomes, :outcomes_chk, :concepts, :concepts_chk, :competencies, :competencies_chk, :mapping, :mapping_chk, :measures, :measures_chk, :criteria, :criteria_chk, :aprocess, :aprocess_chk)',
      'reviewplan' => 'INSERT INTO PA_AdminReviews_Plans_Log (ReviewStatus, reviewtype, ReviewTimeStamp, submissioncode, submissionyear, acadplan, reviewer, disposition, exemplar,strengths, suggestions) VALUES (:ReviewStatus, :reviewtype, :ReviewTimeStamp, :submissioncode, :submissionyear, :acadplan, :reviewer, :disp, :exemplar, :strengths, :suggestions )',
      'reviewfeedback' => 'REPLACE INTO PA_AdminReviews(ReviewStatus, reviewtype, ReviewTimeStamp, submissioncode, submissionyear, acadplan, Reviewer, Disposition, exemplar, strengths, suggestions) VALUES (:ReviewStatus, :reviewtype, :ReviewTimeStamp, :submissioncode, :submissionyear, :acadplan, :reviewer, :disp, :exemplar, :strengths, :suggestions )',
      'feedback_update_pa' => 'UPDATE PA_Program SET requestFeedback=Null WHERE acadplan LIKE :acadplan',
      'reviewplanapp' => 'INSERT INTO PA_AdminReviews_Apps_Log(ReviewStatus, reviewtype, ReviewTimeStamp, submissioncode, submissionyear, acadplan, Reviewer, Disposition, strengths, suggestions) VALUES (:ReviewStatus, :reviewtype, :ReviewTimeStamp, :submissioncode, :submissionyear, :acadplan, :reviewer, :disp, :strengths, :suggestions )',
      'reviewplanapp_approve' => "UPDATE PA_Program SET UOEEEStatus=:uoeeestatus, PlanStatus='Approved', requestFeedback=Null WHERE acadplan LIKE :acadplan",
      'reviewplanapp_revise' => "UPDATE PA_Program SET UOEEEStatus=:uoeeestatus, PlanStatus='Revise', requestFeedback=Null WHERE acadplan LIKE :acadplan",
      'reviewaprplan_approve' => "UPDATE PA_Program SET UOEEEStatus='Approved', PlanStatus='Approved', requestFeedback=Null WHERE acadplan LIKE :acadplan",
      'reviewaprplan_revise' => "UPDATE PA_Program SET UOEEEStatus='Revise', PlanStatus='Revise', requestFeedback=Null WHERE acadplan LIKE :acadplan",
    ];

    try {
      if (isset($body['confirm'])) {
        $prevId = check_view_update($confirmTbl[$body['confirm']], $body['field'], $body['value']);
      }

      foreach (($body['stmts'] ?? []) as $stmt) {
        $query = $queries[$stmt['name']] ?? NULL;
        if ($query === NULL) {
          continue;
        }

        $inputArray = [];
        foreach (($stmt['values'] ?? []) as $key => $value) {
          $inputArray[$key] = ($value === 'timestamp') ? $ts : $value;
        }

        \Drupal::database()->query($query, $inputArray);
      }
    }
    catch (\Exception $e) {
      echo $e->getMessage();
    }

    if (isset($body['confirm'])) {
      switch ($body['confirm']) {
        case 'planinsertelement':
        case 'addelement':
        case 'movelement':
        case 'replaceelement':
        case 'commentsinsert':
        case 'commentsresolve':
        case 'removeresolve':
        case 'submitreview':
        case 'submitappreview':
        case 'submitaprreview':
        case 'submitsa':
        case 'reviewplan':
        case 'reportinsertopen':
        case 'reportinsert':
          $crrId = check_view_update($confirmTbl[$body['confirm']], $body['field'], $body['value']);
          echo $prevId . '-' . $crrId . "\r\n";
          $cnt = 0;
          while ($prevId == $crrId && $cnt < 500) {
            $crrId = check_view_update($confirmTbl[$body['confirm']], $body['field'], $body['value']);
            $cnt++;
          }
          echo $prevId . '-' . $crrId . "\r\n";
          break;
      }
    }

    return new Response('OK', Response::HTTP_OK);
  }

  /**
   * Processes batch assessment portal updates.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   OK or error response.
   */
  public function assessmentBatch() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $request = Request::createFromGlobals();
    $payload = json_decode($request->getContent(), TRUE);
    $body = $payload['body'] ?? [];

    $asurite = \Drupal::currentUser()->getAccountName();
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');

    try {
      switch ($body['action'] ?? '') {
        case 'batchplan':
          foreach (($body['acadplans'] ?? []) as $p) {
            $ele = $body['element'] ?? [];
            $element = $ele['element'] ?? '';
            $out = $ele['outcome'] ?? '';
            $meas = $ele['measure'] ?? '';
            $descr = $ele['description'] ?? '';
            $survey = $ele['survey'] ?? '';
            $canvas = $ele['canvastags'] ?? '';

            $rowCnt = \Drupal::database()->select('pa_activeplanedit', 'ap')
              ->condition('acadplan', $p, '=')
              ->condition('element', $element, '=')
              ->condition('Outcome', $out, '=')
              ->condition('Measure', $meas, '=')
              ->condition('Description', $descr, '=')
              ->condition('survey', $survey, '=')
              ->condition('canvastags', $canvas, '=')
              ->countQuery()
              ->execute()
              ->fetchField();

            if ((int) $rowCnt === 0) {
              \Drupal::database()->insert('PA_AssessmentPlans')
                ->fields([
                  'acadplan' => $p,
                  'element' => $element,
                  'outcome' => $out,
                  'measure' => $meas,
                  'description' => $descr,
                  'survey' => $survey,
                  'canvastags' => $canvas,
                  'user' => $asurite,
                  'eventdate' => $ts,
                  'notes' => $body['batchnote'] ?? '',
                ])
                ->execute();
            }
          }
          break;
      }

      return new Response('OK', Response::HTTP_OK);
    }
    catch (\Exception $e) {
      echo $e->getMessage();
      return new Response('ERROR', Response::HTTP_OK);
    }
  }

  /**
   * Returns unit-level assessment data for a department and college.
   *
   * @param string $department
   *   The department code.
   * @param string $college
   *   The college code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing unit data.
   */
  public function assessmentUnit($department, $college) {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $asurite = \Drupal::currentUser()->getAccountName();

    $programQry = $this->globals->loadUserValue('program_qry');
    $collegeAccess = $this->globals->loadUserValue('collegeaccess');
    $delegate = $this->globals->loadUserValue('delegate');
    $admin = $this->globals->loadUserValue('admin');
    $collegestrNonadmin = $this->globals->loadUserValue('collegestr_nonadmin');

    if ($delegate === NULL || $admin === NULL) {
      $delegateAdmin = assessment_access();
      $delegate = $delegateAdmin['delegate'];
      $admin = $delegateAdmin['admin'];
    }

    $allAccess = $admin ? TRUE : FALSE;

    if ($programQry === NULL || $collegeAccess === NULL || $collegestrNonadmin === NULL) {
      $collegeAccess = college_access_sql($asurite, $allAccess);
      $collegestr = $collegeAccess['collegestr'];
      $collegestrNonadmin = $collegeAccess['collegestr_nonadmin'];
      $programQry = program_access_sql($asurite, $allAccess, $delegate, $collegestr);

      $this->globals->saveUserValue('program_qry', $programQry);
      $this->globals->saveUserValue('collegestr_nonadmin', $collegestrNonadmin);
      $this->globals->saveUserValue('collegeaccess', $collegeAccess);
    }

    $queries = [
      'programs' => $programQry,
      'canvastags' => <<<SQL
          SELECT p.acadplan, p.element, p.outcome, p.measure, p.`description`, pp.`description` as pc, p.canvastags FROM pa_activeplanedit p
                          INNER JOIN (SELECT acadplan, outcome, measure, `description` FROM pa_activeplanedit WHERE element ='PC') pp
                          ON p.acadplan=pp.acadplan AND p.outcome=pp.outcome AND p.measure=pp.measure
                          INNER JOIN (SELECT acadplan, department FROM PA_Program WHERE department LIKE '$department') pa
                          ON p.acadplan=pa.acadplan
                          WHERE p.canvastags IS NOT NULL AND p.canvastags <> '' ;
          SQL,
    ];

    $syncInfo = [];
    foreach ($queries as $key => $query) {
      $syncInfo[$key] = \Drupal::database()->query($query)->fetchAll();
    }

    $globalSnapshot = $this->globals->get();
    $syncInfo['plancounts'] = $globalSnapshot['plancounts'] ?? [];

    return new JsonResponse($syncInfo);
  }

  /**
   * Returns the assessment edit page data for a plan or report.
   *
   * @param string $page
   *   The page type.
   * @param string $acadplan
   *   The academic plan code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing edit page data.
   */
  public function assessmentEdit($page, $acadplan) {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $result = \Drupal::database()->query("SELECT DISTINCT department FROM PA_Program WHERE acadplan LIKE '$acadplan'")->fetchObject();
    $department = $result->department ?? '';

    switch ($page) {
      case 'plan':
        $queries = [
          'activeplan' => "SELECT * FROM pa_activeplanedit WHERE acadplan LIKE '$acadplan' ORDER BY outcome, measure",
          'planhistory' => "SELECT * FROM PA_AssessmentPlans WHERE acadplan LIKE '$acadplan'",
          'comments' => "SELECT * FROM PA_Comments WHERE acadplan LIKE '$acadplan' AND editpage LIKE 'Plan'",
          'recentcomms' => "SELECT c.* FROM PA_Comments c JOIN PA_Program p ON c.acadplan=p.acadplan WHERE c.acadplan LIKE '$acadplan' AND c.editpage LIKE 'Plan' AND c.eventdate>p.planlastaction AND c.commenttxt <> 'Resolved' ",
          'canvastags' => <<<SQL
              SELECT p.acadplan, p.element, p.outcome, p.measure, p.`description`, pp.`description` as pc, p.canvastags FROM pa_activeplanedit p
                      INNER JOIN (SELECT acadplan, outcome, measure, `description` FROM pa_activeplanedit WHERE element ='PC') pp
                      ON p.acadplan=pp.acadplan AND p.outcome=pp.outcome AND p.measure=pp.measure
                      INNER JOIN (SELECT acadplan, department FROM PA_Program WHERE department LIKE '$department') pa
                      ON p.acadplan=pa.acadplan
                      WHERE p.canvastags IS NOT NULL AND p.canvastags <> '' ;
              SQL,
        ];
        break;

      case 'report':
        $queries = [
          'reportinfo' => "SELECT * FROM PA_CurrentReports_Info WHERE acadplan LIKE '$acadplan'",
          'reportresults' => "SELECT * FROM pa_activereportedit WHERE acadplan LIKE '$acadplan'",
          'reportopen' => "SELECT * FROM pa_activereportopen WHERE acadplan LIKE '$acadplan'",
          'reporthistory' => "SELECT * FROM PA_CurrentReports_Open WHERE acadplan LIKE '$acadplan' AND `user` NOT LIKE 'UOEEE'",
          'reportprogram_archive' => "SELECT p.*, RIGHT(p.SubmissionCode,9) AS submissionyear, p.SubmissionCode AS submissioncode, Disposition AS reportdisp, ReviewTimeStamp AS reportrvw FROM PA_AssessmentReports_Archive_Program p LEFT JOIN (SELECT DISTINCT SubmissionCode, Disposition, ReviewTimeStamp FROM PA_AdminReviews) r ON p.SubmissionCode=r.SubmissionCode WHERE acadplan LIKE '$acadplan'",
          'reportinfo_archive' => "SELECT *, submissionYear AS submissionyear, SubmissionCode AS submissioncode FROM PA_AssessmentReports_Archive_Info WHERE acadplan LIKE '$acadplan'",
          'reportopen_archive' => "SELECT *, submissionYear AS submissionyear, SubmissionCode AS submissioncode  FROM PA_AssessmentReports_Archive_Open WHERE acadplan LIKE '$acadplan'",
          'reportresults_archive' => "SELECT *, submissionYear AS submissionyear, SubmissionCode AS submissioncode FROM PA_AssessmentReports_Archive_Results WHERE acadplan LIKE '$acadplan'",
          'comments' => "SELECT * FROM PA_Comments WHERE acadplan LIKE '$acadplan' AND editpage LIKE 'Report'",
          'recentcomms' => "SELECT c.* FROM PA_Comments c JOIN PA_Program p ON c.acadplan=p.acadplan WHERE c.acadplan LIKE '$acadplan' AND c.editpage LIKE 'Report' AND c.eventdate>p.reportlastaction ",
        ];
        break;

      default:
        $queries = [];
    }

    $queries['program'] = "SELECT pa.*, SUBSTRING_INDEX(pa.College, '_', 1) AS CollegeCode, cn.la_division, CASE
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov') AND (d.active=0 OR p.active=0) THEN 0
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active=1 OR p.active=1) THEN 1
        ELSE null END AS apr, CASE
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active in (0,1)) THEN d.chair
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (p.active in (0,1)) THEN p.chair
        ELSE null END AS aprchair, CASE
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active in (0,1)) THEN d.contact
        WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (p.active in (0,1)) THEN p.contact
        ELSE null END AS aprcontact,
        CASE WHEN ca.acadplan IS null AND cd.department IS null THEN '0' ELSE '1' END AS canvaspilot
        FROM PA_Program pa
        LEFT JOIN PA_APR d ON pa.Department=d.department
        LEFT JOIN PA_APR p ON pa.acadplan=p.program
        LEFT JOIN pa_canvaspilot cd ON pa.Department=cd.department
        LEFT JOIN pa_canvaspilot ca ON pa.acadplan=ca.acadplan
        LEFT JOIN PA_CollegeNames cn ON SUBSTRING_INDEX(pa.College, '_', 1)=cn.College
        WHERE pa.acadplan LIKE '$acadplan'";

    $queries['aprs'] = 'SELECT * FROM PA_APR WHERE Active=1;';
    $queries['satriggers'] = "SELECT t.* FROM SA_Triggers t WHERE t.acadplan LIKE '$acadplan' ORDER BY ID DESC LIMIT 1";

    $syncPage = [];
    foreach ($queries as $key => $query) {
      $syncPage[$key] = \Drupal::database()->query($query)->fetchAll();
    }

    $reviewFields = 'SELECT * FROM';
    $subCode = $syncPage['program'] ? ($syncPage['program'][0]->SubmissionCode ?? '') : '';
    $syncPage['reviews'] = \Drupal::database()->query("$reviewFields PA_AdminReviews pa WHERE pa.acadplan LIKE '$acadplan' AND reviewtype='reports' ORDER BY pa.SubmissionCode DESC LIMIT 1;")->fetchAll();
    $archived = \Drupal::database()->query("$reviewFields PA_AdminReviews pa WHERE pa.acadplan LIKE '$acadplan' AND reviewtype='archived' AND pa.SubmissionCode NOT LIKE '$subCode' ORDER BY pa.SubmissionCode DESC LIMIT 1;");
    $syncPage['archived'] = $archived->fetchAll();
    $syncPage['archivedreviews'] = \Drupal::database()->query("$reviewFields PA_AdminReviews pa WHERE pa.acadplan LIKE '$acadplan' AND reviewtype='archived';")->fetchAll();
    $syncPage['planapps'] = \Drupal::database()->query("$reviewFields pa_adminreviews_apps_active WHERE acadplan LIKE '$acadplan%' AND reviewtype='planapps'")->fetchAll();
    $syncPage['gov'] = \Drupal::database()->query("$reviewFields pa_adminreviews_plans_active WHERE acadplan LIKE '$acadplan' AND reviewtype='gov'")->fetchAll();
    $syncPage['aprplans'] = \Drupal::database()->query("$reviewFields pa_adminreviews_plans_active WHERE acadplan LIKE '$acadplan' and reviewtype='aprplans'")->fetchAll();

    $syncPage['reportevidence'] = [];
    $dirUri = 'public://docs/assessment/report_evidence';
    $fs = \Drupal::service('file_system');
    $all = $fs->scanDirectory($dirUri, '/.*/', ['key' => 'uri']) ?: [];
    $evidence = array_filter(array_keys($all), static function ($uri) use ($acadplan) {
      return stripos(basename($uri), $acadplan) === 0;
    });

    foreach ($evidence as $evd) {
      $real = $fs->realpath($evd);
      $rel = \Drupal::service('file_url_generator')->generateString($evd);
      $name = basename($evd);
      $syncPage['reportevidence'][] = [
        'uri' => $evd,
        'path' => $rel,
        'name' => $name,
        'extension' => '.' . pathinfo($name, PATHINFO_EXTENSION),
        'modified' => ($real && is_file($real)) ? date('Y-m-d H:i:s', filemtime($real)) : NULL,
      ];
    }

    return new JsonResponse($syncPage);
  }

  /**
   * Builds report records from plan data for the given academic plan.
   *
   * @param string $acadplan
   *   The academic plan code.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   OK or error response.
   */
  public function assessmentPlanToReport($acadplan) {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    try {
      $sqlDelete = "DELETE FROM PA_CurrentReports_Info WHERE acadplan LIKE '$acadplan';";
      \Drupal::database()->query($sqlDelete)->execute();

      $sqlInfo = "INSERT INTO PA_CurrentReports_Info ( acadplan, element, outcome, measure, `description`, survey )
                    (SELECT pe.acadplan, pe.element, pe.outcome, pe.measure, pe.description, pe.survey
                    FROM pa_activeplanedit pe
                    INNER JOIN PA_Program pp ON pe.acadplan = pp.acadplan
                    LEFT JOIN PA_CurrentReports_Info ci ON pe.acadplan = ci.acadplan
                    WHERE pe.element IN ('Outcome', 'Measure', 'AP_1Process') AND pp.progstatus IN ('Report', 'Low Enrollment') AND pp.acadplan LIKE '$acadplan'
                    AND ci.acadplan Is Null);";
      \Drupal::database()->query($sqlInfo)->execute();

      $sqlPc = "UPDATE PA_CurrentReports_Info ci
                  INNER JOIN pa_activeplanedit pe ON ci.acadplan=pe.acadplan
                  AND ci.outcome=pe.outcome
                  AND ci.measure=pe.measure
                  SET ci.PC_text = pe.Description
                  WHERE ci.element='Measure' AND pe.element='PC' AND ci.acadplan='$acadplan';";
      \Drupal::database()->query($sqlPc)->execute();

      return new Response('OK', Response::HTTP_OK);
    }
    catch (\Exception $e) {
      echo $e->getMessage();
      return new Response('ERROR', Response::HTTP_OK);
    }
  }

  /**
   * Returns public assessment data for an academic plan.
   *
   * @param string $acadplan
   *   The academic plan code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing public outcomes.
   */
  public function assessmentPublic($acadplan) {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $data = \Drupal::database()->query("SELECT * FROM pa_assessmentplans_public WHERE acadplan LIKE '$acadplan'")->fetchAll();
    return new JsonResponse($data);
  }

  /**
   * Returns overview data for the currently authenticated user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing overview data.
   */
  public function assessmentOverview() {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    $asurite = \Drupal::currentUser()->getAccountName();

    $delegateAdmin = assessment_access();
    $delegate = $delegateAdmin['delegate'];
    $admin = $delegateAdmin['admin'];

    $allAccess = $admin ? TRUE : FALSE;
    $collegestr = college_access_sql($asurite, $allAccess)['collegestr'];
    $programQry = program_access_sql($asurite, $allAccess, $delegate, $collegestr);

    $queries = [
      'programs' => $programQry,
      'report_open' => "SELECT *, CASE WHEN No_Data=0 THEN 'Data Collected' ELSE 'No Data' END as collected FROM PA_CurrentReports_Open;",
      'reportsreviews' => "SELECT * FROM PA_AdminReviews pa WHERE pa.reviewtype LIKE 'reports' AND ReviewTimeStamp IS NOT NULL AND ReviewTimeStamp NOT LIKE ''",
      'plansreviews' => "SELECT * FROM pa_adminreviews_plans_active WHERE ReviewTimeStamp IS NOT NULL AND ReviewTimeStamp NOT LIKE ''",
      'feedbackreviews' => "SELECT * FROM PA_AdminReviews pa WHERE pa.reviewtype LIKE 'feedback' AND ReviewTimeStamp IS NOT NULL AND ReviewTimeStamp NOT LIKE ''",
      'planappsreviews' => "SELECT * FROM pa_adminreviews_apps_active pa WHERE pa.reviewtype LIKE 'planapps'",
      'govreviews' => "SELECT * FROM pa_adminreviews_plans_active pa WHERE pa.reviewtype LIKE 'gov'",
      'allappsreviews' => "SELECT * FROM PA_AdminReviews_Apps_Log WHERE ReviewTimeStamp IS NOT NULL AND ReviewTimeStamp NOT LIKE ''",
    ];

    $syncInfo = [];
    foreach ($queries as $key => $query) {
      $syncInfo[$key] = \Drupal::database()->query($query)->fetchAll();
    }

    return new JsonResponse($syncInfo);
  }

  /**
   * Returns reporting data for a department and college.
   *
   * @param string $department
   *   The department code.
   * @param string $college
   *   The college code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing reporting data.
   */
  public function assessmentReporting($department, $college) {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    $delegateAdmin = assessment_access();
    $delegate = $delegateAdmin['delegate'];
    $admin = $delegateAdmin['admin'];

    if ($department === 'All') {
      $department = '%';
    }
    if ($college === 'All') {
      $coll = " LIKE '%'";
    }
    else {
      $coll = " LIKE '$college'";
    }
    if ($college === 'LA') {
      $coll = " IN ('LB', 'LC', 'LD', 'LH', 'LM' )";
    }

    $data = ['reporting' => []];

    if ($admin || $delegate) {
      $qry = "SELECT SUBSTRING_INDEX(pa.College, '_', 1) AS college, pa.department, rp.acadplan, pa.reportStatus, pa.progstatus,
                rp.element, rp.outcome, rp.measure, rp.Disposition, rp.Result, rp.Num, rp.Met, rp.ExpText, rp.description, rp.PC_text
                FROM pa_activereportedit rp INNER JOIN PA_Program pa
                ON rp.acadplan=pa.acadplan
                WHERE SUBSTRING_INDEX(pa.College, '_', 1) $coll AND pa.department LIKE '$department'
                ORDER BY pa.department, pa.acadplan, rp.outcome, rp.measure";
      $data['reporting'] = \Drupal::database()->query($qry)->fetchAll();
    }

    return new JsonResponse($data);
  }

  /**
   * Returns plan elements data for a department and college.
   *
   * @param string $department
   *   The department code.
   * @param string $college
   *   The college code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing plan elements data.
   */
  public function assessmentPlanElements($department, $college) {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    // Implementation for retrieving plan elements data.

    if ($department === 'All') {
      $department = '%';
    }
    if ($college === 'All') {
      $coll = " LIKE '%'";
    }
    else {
      $coll = " LIKE '$college'";
    }
    if ($college === 'LA') {
      $coll = " IN ('LB', 'LC', 'LD', 'LH', 'LM' )";
    }

    $data = ['planning' => []];

    $qry = "SELECT LEFT(pp.College, 2) AS COLL, RIGHT(pp.College, 2) AS UGGR, pp.Department, pp.TRNSCR, pp.AcadPlan, pp.ProgStatus, pp.PlanStatus, pp.Enrolled, pm.outcome AS LO, pm.measure AS LM, po.description AS Outcome, pm.description AS Measure, pc.description AS PC, pm.survey, pm.canvastags
			FROM pa_activeplanedit pm
			INNER JOIN PA_Program pp ON pm.acadplan = pp.acadplan
      LEFT JOIN pa_activeplanedit po ON pm.acadplan = po.acadplan AND pm.outcome = po.outcome
      LEFT JOIN pa_activeplanedit pc ON pm.acadplan = pc.acadplan AND pm.outcome = pc.outcome AND pm.measure = pc.measure
      WHERE pp.progstatus IN ('Report', 'Plan', 'No Enrollment') AND pm.element = 'Measure' AND po.element = 'Outcome' AND pc.element = 'PC'
      AND SUBSTRING_INDEX(pp.College, '_', 1) $coll AND pp.department LIKE '$department'
      ORDER BY LEFT(pp.College, 2),pp.department, pp.acadplan, pm.outcome, pm.measure";

    $data['planning'] = \Drupal::database()->query($qry)->fetchAll();

    return new JsonResponse($data);

  }

  /**
   * Returns SA trigger records.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing SA triggers.
   */
  public function assessmentSaTriggers() {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    $qry = "SELECT sa.eventdate, sa.calendaryear, pa.College, pa.Department, sa.trigger_placement, sa.trigger_instruction, sa.trigger_certlicense,
                sa.acadplan, pa.Program_Name
                FROM (SELECT MAX(eventdate) AS eventdate, calendaryear, trigger_placement, trigger_instruction, trigger_certlicense, acadplan FROM SA_Triggers
                GROUP BY calendaryear, trigger_placement, trigger_instruction, trigger_certlicense, acadplan )
                sa JOIN PA_Program pa ON sa.acadplan = pa.acadplan
                WHERE (sa.trigger_placement LIKE 'Yes' OR sa.trigger_instruction LIKE 'Yes' OR sa.trigger_certlicense LIKE 'Yes')
                AND sa.acadplan NOT LIKE 'UE%' ORDER BY sa.eventdate DESC ;";

    $data = \Drupal::database()->query($qry)->fetchAll();
    return new JsonResponse($data);
  }

  /**
   * Uploads an evidence file into the assessment evidence directory.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response indicating upload status.
   */
  public function assessmentFileUpload(Request $request) {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    $request = Request::createFromGlobals();
    $dirs = [
      'evidence' => 'public://docs/assessment/report_evidence',
    ];

    $fs = \Drupal::service('file_system');
    $type = $request->request->get('type', 'evidence');
    $rename = $request->request->get('rename', '');
    $file = $request->files->get('file');

    $fs->prepareDirectory(
      $dirs[$type],
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );

    try {
      $uploaddir = $dirs[$type];
      $uploadfiletype = '.' . strtolower(pathinfo($file->getClientOriginalExtension(), PATHINFO_EXTENSION));
      $uploadfile = "{$uploaddir}/{$rename}{$uploadfiletype}";
      $destination = $fs->getDestinationFilename($uploadfile, FileSystemInterface::EXISTS_RENAME);
      $status = $fs->moveUploadedFile($file->getRealPath(), $destination);
    }
    catch (\Exception $e) {
      $status = FALSE;
    }

    return new JsonResponse($status);
  }

  /**
   * Removes an assessment evidence file by renaming it into the removed state.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the result of the file removal operation.
   */
  public function assessmentFileRemove() {
    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }
    $asurite = \Drupal::currentUser()->getAccountName();
    $payload = json_decode(file_get_contents('php://input'), TRUE)['body'] ?? [];
    $fs = \Drupal::service('file_system');
    $baseDir = 'public://docs/assessment/report_evidence/';
    $uri = $payload['uri'] ?? NULL;

    if (!$uri || strpos($uri, $baseDir) !== 0) {
      return new JsonResponse(['success' => FALSE, 'error' => 'Missing or invalid uri'], 400);
    }

    $name = basename($uri);
    $info = pathinfo($name);
    $stamp = date('YmdHis');
    $newUri = $baseDir . 'removed__BY' . $asurite . '_' . $stamp . '_' . $info['filename'] . (!empty($info['extension']) ? ('.' . $info['extension']) : '');
    $newUri = $fs->getDestinationFilename($newUri, FileSystemInterface::EXISTS_RENAME);
    $ok = $fs->move($uri, $newUri, FileSystemInterface::EXISTS_RENAME);

    if (!$ok) {
      \Drupal::logger('aportal')->error('fileremove: move failed from @old to @new', ['@old' => $uri, '@new' => $newUri]);
      return new JsonResponse(['success' => FALSE, 'error' => 'Move failed'], 500);
    }

    return (new JsonResponse([
      'success' => TRUE,
      'uri' => $newUri,
    ]))->setMaxAge(0);
  }

  /**
   * Saves peer review score data or artifact review actions.
   *
   * @return array|void
   *   A render array when access is denied, or no return value when the
   *   request is processed normally.
   */
  public function paReviewPost() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $request = Request::createFromGlobals();
    $payload = json_decode($request->getContent(), TRUE);
    $body = $payload['body'] ?? [];
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');

    $tables = [
      'score' => 'pa_rvw_scores',
    ];
    $tablesAll = [
      'score' => 'pa_rvw_scores_all',
    ];

    $action = $body['action'] ?? '';

    switch ($action) {
      case 'saveartifact':
        $id = $body['selArtifact']['id'] ?? '';
        $asurite = $body['selArtifact']['asurite'] ?? '';
        $group = $body['selArtifact']['group'] ?? '';
        $gened = $body['selArtifact']['gened'] ?? '';
        $strm = $body['selArtifact']['term'] ?? '';
        $artifact = $body['artifact'] ?? '';

        \Drupal::database()->query("INSERT INTO gened_artifacts (artifact_id, unscorable, selected, asurite, section, gened, externalText, strm, lastupdate, uploaddate) VALUES ( '$id', 0, 1,'$asurite','$group','$gened','$artifact', '$strm','$ts', '$ts' ) ON DUPLICATE KEY UPDATE externalText='$artifact', lastupdate='$ts'");
        break;

      case 'removeartifact':
        \Drupal::database()->delete('gened_artifacts')
          ->condition('artifact_id', $body['artifact_id'] ?? '', 'LIKE')
          ->execute();
        break;

      case 'unscorable':
        \Drupal::database()->update('gened_artifacts')
          ->fields([
            'selected' => 0,
            'unscorable' => 1,
          ])
          ->condition('artifact_id', $body['artifact_id'] ?? '', 'LIKE')
          ->execute();

        \Drupal::database()->delete('gened_log')
          ->condition('artifact_id', $body['artifact_id'] ?? '', 'LIKE')
          ->condition('reviewer', $body['reviewer'] ?? '', 'LIKE')
          ->execute();
        break;

      case 'score':
        $edits = 0;
        foreach (($body['score'] ?? []) as $area => $o) {
          $current = \Drupal::database()->query("SELECT score FROM `{$tables[$action]}` WHERE project='" . ($body['project'] ?? '') . "' AND artifact_id='" . ($body['artifact_id'] ?? '') . "' and area='$area'");
          $existing = count($current->fetchAll());
          $comment = $o['comment'] ?? '';
          $score = strlen($o['score'] ?? '') == 0 ? 'NULL' : intval($o['score']);

          if ($existing > 0) {
            $sqlUpdate = "UPDATE {$tables[$action]} SET score=$score, reviewer='" . ($body['reviewer'] ?? '') . "', review_ts='$ts', comment='$comment' WHERE artifact_id='" . ($body['artifact_id'] ?? '') . "' AND project='" . ($body['project'] ?? '') . "' AND area='$area'";
            \Drupal::database()->query($sqlUpdate)->execute();
            $edits++;
          }

          $sqlInsert = "INSERT INTO {$tablesAll[$action]} (artifact_id, project, area, score, comment, reviewer, review_ts) VALUES ('" . ($body['artifact_id'] ?? '') . "','" . ($body['project'] ?? '') . "','$area', $score,'$comment','" . ($body['reviewer'] ?? '') . "','$ts')";
          \Drupal::database()->query($sqlInsert)->execute();

          if ($existing == 0) {
            $sqlInsert = "INSERT INTO {$tables[$action]} (artifact_id, project, area, score, comment, reviewer, review_ts) VALUES ('" . ($body['artifact_id'] ?? '') . "','" . ($body['project'] ?? '') . "','$area', $score,'$comment','" . ($body['reviewer'] ?? '') . "','$ts')";
            \Drupal::database()->query($sqlInsert)->execute();
            $edits++;
          }
        }

        $dimensions = [];
        $cnt = 0;
        while (count($dimensions) < $edits && $cnt < 500) {
          $dimensions = \Drupal::database()->query("SELECT * FROM `{$tables[$action]}` WHERE artifact_id LIKE '" . ($body['artifact_id'] ?? '') . "' AND review_ts='$ts';")->fetchAll();
          $cnt++;
        }
        break;
    }
  }

  /**
   * Returns review submission data for a given project and artifact.
   *
   * @param string $project
   *   The project key.
   * @param string $aid
   *   The artifact ID.
   * @param int|string $len
   *   The expected number of rows.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing review data.
   */
  public function paReviewGetSubmit($project, $aid, $len) {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $scr = [
      'hlcc4' => 'pa_rvw_scores',
    ];

    $current = [];
    $cnt = 0;
    while (count($current) < $len && $cnt < 1000) {
      $current = \Drupal::database()->query("SELECT * FROM {$scr[$project]} WHERE artifact_id LIKE '$aid';")->fetchAll();
      $cnt++;
    }

    $data = [
      'scores' => \Drupal::database()->query('SELECT DISTINCT * FROM pa_rvw_scores ORDER BY review_ts DESC')->fetchAll(),
      'current' => $current,
    ];

    return new JsonResponse($data);
  }

  /**
   * Displays the new program submission form and handles form submission.
   *
   * @return array
   *   Render array for the submission page.
   */
  public function submitNewProgram() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $uname = \Drupal::currentUser()->getAccountName();
    $request = \Drupal::request();
    $post = $request->request->all();

    $markup = '<style>
              .border-3 { border-width:3px !important; border-color:black; }
              .asugold { background-color: rgba(255,198,39,.8); color: black}
            </style><div style="width:70%;margin:2% 15% 0 15%">';
    $admin = 0;
    $queryUser = "SELECT * FROM PA_User WHERE Element LIKE 'Admin' AND Asurite LIKE '$uname'";
    $resultUser = \Drupal::database()->query($queryUser);
    $rowCntUser = count($resultUser->fetchAll());

    if ($rowCntUser > 0) {
      $admin = 1;
    }

    if ($post) {
      if (isset($post['deptPost'])) {
        $grad = $post['grad'];
        $deptPost = $post['deptPost'];
        $degree = htmlspecialchars($post['degree']);
        $acadplan = htmlspecialchars($post['degree']) . idate('U', time());
        $maj = htmlspecialchars($post['maj']);
        $trans = htmlspecialchars($post['trans']);
        $concentration = $post['concentration'];
        $parentprogram = 'parentplan_' . htmlspecialchars($post['parentprogram']);
        $additionalusers = htmlspecialchars($post['additionalusers']);

        $queryCol = "SELECT SUBSTRING_INDEX(College, '_', 1) AS CollegeChosen FROM PA_Program WHERE Department LIKE '$deptPost' Order by College Limit 1";
        $resultCol = \Drupal::database()->query($queryCol);

        foreach ($resultCol as $row) {
          $college = $row->CollegeChosen;
        }
        $collegePost = $college . '_' . $grad;
        $prog = $college . '_' . $grad . '-' . $deptPost . '-' . $maj . '-' . $trans . '-' . $acadplan;
        $progName = $degree . ' in ' . $trans;

        $month = date('m');
        if ($month < 11) {
          $subcodeSuffix = '_' . date('Y') . '_' . (date('Y') + 1);
        }
        else {
          $subcodeSuffix = '_' . (date('Y') + 1) . '_' . (date('Y') + 2);
        }
        $subCode = $acadplan . $subcodeSuffix;

        \Drupal::database()->insert('PA_Program')
          ->fields([
            'College' => $collegePost,
            'Department' => $deptPost,
            'acadplan' => $acadplan,
            'Program_Name' => $progName,
            'ParentProgram' => $concentration == 'Yes' ? $parentprogram : '',
            'Feedback' => '',
            'Notes' => $prog,
            'progstatus' => $maj == 'Maj' && $concentration == 'No' ? 'Plan Application' : 'Pre Gov',
            'PlanStatus' => 'Not Submitted',
            'SubmissionCode' => $subCode,
            'TRNSCR' => $trans,
          ])
          ->execute();

        if ($admin == 0) {
          $query2 = "SELECT * FROM pa_userdepartment WHERE asurite LIKE '$uname' and (College LIKE '$collegePost' OR Department LIKE '$deptPost')";
          $result2 = \Drupal::database()->query($query2);
          $rowCnt = count($result2->fetchAll());
          if ($rowCnt == 0) {
            try {
              \Drupal::database()->insert('PA_User')
                ->fields([
                  'asurite' => $uname,
                  'element' => $acadplan,
                  'ElementType' => 'Acadplan',
                ])
                ->execute();
            }
            catch (\PDOException $e) {
              drupal_set_message(t('Error Add User: %message', ['%message' => $e->getMessage()]), 'error');
            }
          }
        }

        $admin1 = 'U091W7A753P';
        $admin2 = 'WFHGH9ZSQ';
        $mess = "<@$admin2>, <@$admin1>  -- $uname has created a new plan app shell.\n$progName\n$prog\n$additionalusers";
        aportal_slack_post($mess);

        header("Location: /aportal/plan-edit/$collegePost/$deptPost/$acadplan");
        exit('');
      }
    }

    if ($admin == 0) {
      $query2 = "SELECT College AS CollegeList, Department FROM pa_userdepartment WHERE asurite LIKE '$uname' ORDER BY College ASC, Department ASC";
    }
    else {
      $query2 = "SELECT CollegeList, Department
        FROM (
            SELECT DISTINCT LEFT(College, 2) AS CollegeList, Department, College
            FROM PA_Program
        ) AS sub
        ORDER BY College ASC, Department ASC;";
    }

    $result2 = \Drupal::database()->query($query2)->fetchAll();
    $rowCnt = count($result2);

    if ($rowCnt > 0) {
      $counter = 0;
      $fdept = [];
      foreach ($result2 as $row) {
        $fdept[$counter] = $row->Department;
        $counter++;
      }
      $udept = array_values(array_unique($fdept));

      $markup .= '<form method="post">';
      $markup .= '<table><tbody class="table table-striped"';
      $markup .= '<tr><td><b>Department:</b></td><td>';
      $markup .= '<select class="border" name="deptPost">';
      $arrLength = count($udept);
      for ($x = 0; $x < $arrLength; $x++) {
        $markup .= '<option value="' . $udept[$x] . '">' . $udept[$x] . '</option>';
      }
      $markup .= '</select>';
      $markup .= '</td></tr>
      <tr><td><b>GRAD or UGRD:</b></td><td><select name="grad" class="border"><option value="UG">Undergraduate</option><option value="GR">Graduate</option></select></td></tr>
      <tr><td><b>Major or Cert:</b></td><td><select name="maj" class="border">
      <option value="Maj">Major</option>
      <option value="Cer">Certificate</option>
      <option value="micro">Micro-Certificate</option>
      </select></td></tr>
      <tr><td><b>Degree Code (e.g. BA, MS, CERT, MCERT, AA, AS):</b></td><td><input required type="text" name="degree" class="border"></input></td></tr>
      <tr><td><b>Transcript Description:</b></td><td><textarea required type="text" name="trans" rows=1 cols="72" maxlength=150 class="border"></textarea></td></tr>
      <tr><td style="text-align:right;">Is this a concentration?</td><td><select name="concentration" class="border"><option value="No">No</option><option value="Yes">Yes</option></select></td></tr>
      <tr><td style="text-align:right;">If yes, please provide<br>the parent program<br> academic plan code or description.</td><td><textarea type="text" name="parentprogram" rows=1 cols="72" maxlength=150 class="border"></textarea></td></tr>
      </tbody></table>';

      $markup .= '<div style="margin-top:12px;">If there are any non college or department users who will also need access to this plan application, please list them here, including an asurite.</div>
                <div style="margin-bottom:12px;"><textarea type="text" name="additionalusers" rows=1 cols="72" class="border"></textarea></div>';

      $markup .= '<div style="text-align:right;"><button name="Submission" type="submit" value="Updated" class="asugold border-3 rounded-lg" style="width: 200px; height: 40px">Add New Program</button></div>
      </form></div>';
    }
    else {
      header('Location: /assessment-portal-request-access');
      exit('');
    }

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['aportal/aportal-app'],
      ],
      '#type' => 'markup',
      '#markup' => Markup::create($markup),
    ];
  }

  /**
   * Handles portal access requests and sends the request notification.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response from the notification request.
   */
  public function requestForm() {

    if (!$this->userHasAportalAccess()) {
      return $this->accessDeniedPage();
    }

    $request = Request::createFromGlobals();
    $payload = json_decode($request->getContent(), TRUE);
    $body = $payload['body'] ?? [];

    $admin1 = 'U091W7A753P';
    $admin2 = 'WFHGH9ZSQ';

    $userid = aportal_slack_user($body['asurite'] ?? '');
    $requestby = $body['requestby'] ?? '';
    $requester = $body['requester'] ?? '';

    switch ($body['action'] ?? '') {
      case 'newuserrequest':
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';
        $asurite = $body['asurite'] ?? '';
        $access = $body['access'] ?? '';
        $reqtype = 'access';

        $mess = "*New User Access Request*\n<@$admin2>, <@$admin1> -- ";
        $requestText = "$requester has submitted a new user access request for:\n- $name\n- $email\n- $asurite\n $access";
        break;

      case 'revisionrequest':
        $user = $body['user'] ?? '';
        $access = $body['access'] ?? '';
        $reqtype = 'access';

        $mess = "*User Access Revision Request*\n<@$admin2>, <@$admin1> -- ";
        $requestText = "$requester has submitted a user access revision request for:\n- $user\n $access";
        break;

      default:
        return new JsonResponse(['success' => FALSE, 'error' => 'Invalid action'], 400);
    }

    $site = "\nVisit https://uoeee.asu.edu/aportal-admin to fulfill the request.";

    \Drupal::database()->insert('AP_requests')
      ->fields([
        'requestby' => $requestby,
        'requesttype' => $reqtype,
        'request' => $requestText,
      ])
      ->execute();

    $result = aportal_slack_post($mess . $requestText . $site);

    return new JsonResponse([
      'slackUser' => $userid,
      'result' => $result instanceof JsonResponse ? $result->getData(TRUE) : $result,
    ]);
  }

}

/**
 * Look up a Slack user by ASURITE.
 *
 * @param string $asurite
 *   The ASURITE username.
 *
 * @return mixed
 *   Slack API response.
 */
function aportal_slack_user($asurite) {
  $token = 'xoxb-664923153986-1256246461685-BA2LQJsm4bz46hboJWP8yotH';
  $data = json_encode([
    'token' => $token,
    'email' => "$asurite.asu.edu",
  ]);

  $options = [
    'method' => 'GET',
    'body' => $data,
    'timeout' => 15,
    'headers' => [
      'Content-Type' => 'application/json',
      'Authorization' => "Bearer $token",
    ],
  ];

  return \Drupal::httpClient()->post('https://slack.com/api/users.lookupByEmail', $options);
}

/**
 * Post a message to Slack.
 *
 * @param string $message
 *   The message text.
 *
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 *   JSON response wrapping the Slack API result.
 */
function aportal_slack_post($message) {

  if (!$this->userHasAportalAccess()) {
    return $this->accessDeniedPage();
  }

  $channel = 'C012F36MJC9';
  $token = 'xoxb-664923153986-1256246461685-BA2LQJsm4bz46hboJWP8yotH';
  $data = json_encode([
    'token' => $token,
    'channel' => $channel,
    'text' => $message,
  ]);

  $options = [
    'method' => 'POST',
    'body' => $data,
    'timeout' => 15,
    'headers' => [
      'Content-Type' => 'application/json',
      'Authorization' => "Bearer $token",
    ],
  ];

  $result = \Drupal::httpClient()->post('https://slack.com/api/chat.postMessage', $options);
  return new JsonResponse($result);
}

/**
 * Check the most recent row ID for a view update.
 *
 * @param string $tbl
 *   The table or view name.
 * @param string $field
 *   The field to match.
 * @param string $value
 *   The value to search for.
 *
 * @return int
 *   The matching ID, or 0 if nothing was found.
 */
function check_view_update($tbl, $field, $value) {
  $result = \Drupal::database()->query("SELECT ID FROM `$tbl` WHERE $field LIKE '$value' ORDER BY ID DESC LIMIT 1")->fetchObject();
  return is_object($result) ? (int) $result->ID : 0;
}

/**
 * Build college access SQL fragments for the current user.
 *
 * @param string $asurite
 *   The ASURITE username.
 * @param bool $admin
 *   Whether the user has admin access.
 *
 * @return array
 *   Access SQL fragments and related college data.
 */
function college_access_sql($asurite, $admin) {
  if (!$admin) {
    $queryColl = "SELECT DISTINCT ud.College AS CollegeCode, cn.CollegeName,Department
                FROM pa_userdepartment ud
                INNER JOIN PA_CollegeNames cn ON ud.College=cn.College
                INNER JOIN PA_Delegates dg ON ud.asurite=dg.asurite AND ud.College=left(dg.college,2)
                WHERE ud.Asurite LIKE '$asurite' ORDER BY CollegeCode;";
    $collegestrNonadmin = "SELECT DISTINCT ud.College AS CollegeCode, cn.CollegeName,Department
                FROM pa_userdepartment ud
                INNER JOIN PA_CollegeNames cn ON ud.College=cn.College
                WHERE ud.Asurite LIKE '$asurite' ORDER BY CollegeCode;";
  }
  else {
    $queryColl = "SELECT DISTINCT SUBSTRING_INDEX(pg.College, '_', 1) AS CollegeCode, cn.CollegeName, Department
                FROM PA_Program pg
                INNER JOIN PA_CollegeNames cn
                ON SUBSTRING_INDEX(pg.College, '_', 1)=cn.College
                ORDER BY SUBSTRING_INDEX(pg.College, '_', 1)";
    $collegestrNonadmin = $queryColl;
  }

  $colldept = \Drupal::database()->query($queryColl)->fetchAll();
  $colleges = [];
  foreach ($colldept as $u) {
    $code = $u->CollegeCode;
    if (!in_array($code, $colleges, TRUE)) {
      $colleges[] = $code;
    }
  }
  $collegestr = "'" . implode("','", $colleges) . "'";

  $coll = \Drupal::database()->query($collegestrNonadmin)->fetchAll();
  $colleges = [];
  foreach ($coll as $u) {
    $code = $u->CollegeCode;
    if (!in_array($code, $colleges, TRUE)) {
      $colleges[] = $code;
    }
  }
  $collegestrNonadmin = "'" . implode("','", $colleges) . "'";

  return [
    'collegestr' => $collegestr,
    'colldept' => $colldept,
    'collegestr_nonadmin' => $collegestrNonadmin,
  ];
}

/**
 * Build the base APR program query.
 *
 * @return string
 *   SQL query string.
 */
function pa_program_apr() {
  return "SELECT pa.*, SUBSTRING_INDEX(pa.College, '_', 1) AS CollegeCode, cn.la_division, pb.publicready, po.publicmismatch, pr.publicremove,
          CASE WHEN prp.acadplan IS NULL THEN '' WHEN prp.publics=pra.actives THEN '' ELSE 'Y' END AS publiccounter,
          CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active=0 OR p.active=0) THEN '0'
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active=1 OR p.active=1) THEN '1'
          ELSE null END AS apr, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active in (0,1)) THEN d.department_description
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (p.active in (0,1)) THEN p.department_description
          ELSE null END AS aprdescr, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active in (0,1)) THEN d.chair
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (p.active in (0,1)) THEN p.chair
          ELSE null END AS aprchair, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (d.active in (0,1)) THEN d.contact
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved' , 'Pre Gov')  AND (p.active in (0,1)) THEN p.contact
          ELSE null END AS aprcontact,
          CASE WHEN ca.acadplan IS null AND cd.department IS null THEN '0' ELSE '1' END AS canvaspilot
          FROM PA_Program pa
          LEFT JOIN PA_APR d ON pa.Department=d.department
          LEFT JOIN PA_APR p ON pa.acadplan=p.program
          LEFT JOIN pa_canvaspilot cd ON pa.Department=cd.department
          LEFT JOIN pa_canvaspilot ca ON pa.acadplan=ca.acadplan
          LEFT JOIN PA_CollegeNames cn ON SUBSTRING_INDEX(pa.College, '_', 1)=cn.College
          LEFT JOIN (SELECT DISTINCT acadplan, 'Y' AS publicready FROM pa_assessmentplans_public) pb ON pa.acadplan=pb.acadplan
          LEFT JOIN (SELECT DISTINCT pe.acadplan, 'Y' AS publicmismatch FROM pa_activeplanedit pe JOIN pa_assessmentplans_public pb ON
                      pe.acadplan=pb.acadplan AND pe.outcome=pb.outcome
                      WHERE pe.element='Outcome' AND pe.ID<>pb.ID ) po ON pa.acadplan=po.acadplan
          LEFT JOIN (SELECT DISTINCT pb.acadplan, 'Y' AS publicremove FROM pa_assessmentplans_public pb LEFT JOIN pa_activeplanedit pe ON
                                pe.acadplan=pb.acadplan AND pe.outcome=pb.outcome
                                WHERE pe.ID IS NULL) pr ON pa.acadplan=pr.acadplan
          LEFT JOIN (SELECT acadplan, COUNT(acadplan) AS publics FROM pa_assessmentplans_public WHERE element = 'Outcome' GROUP BY acadplan)
                    prp ON pa.acadplan=prp.acadplan
          LEFT JOIN (SELECT acadplan, COUNT(acadplan) AS actives FROM pa_activeplanedit WHERE element = 'Outcome'  GROUP BY acadplan)
                    pra ON pa.acadplan=pra.acadplan";
}

/**
 * Determine whether the current user is a delegate or admin.
 *
 * @return array
 *   Delegate/admin access flags.
 */
function assessment_access() {
  $asurite = \Drupal::currentUser()->getAccountName();
  $delegate = FALSE;
  $admin = FALSE;

  $queryDel = \Drupal::database()->query("SELECT * FROM PA_Delegates WHERE asurite LIKE '$asurite'");
  if (count($queryDel->fetchAll()) > 0) {
    $delegate = TRUE;
  }

  $queryUser = \Drupal::database()->query("SELECT * From PA_User WHERE Element LIKE 'Admin' and Asurite LIKE '$asurite'");
  if (count($queryUser->fetchAll()) > 0) {
    $admin = TRUE;
  }

  return [
    'delegate' => $delegate,
    'admin' => $admin,
  ];
}

/**
 * Build the access-limited program query.
 *
 * @param string $asurite
 *   The ASURITE username.
 * @param bool $admin
 *   Whether the user has admin access.
 * @param bool $delegate
 *   Whether the user has delegate access.
 * @param string $collegestr
 *   The quoted college list string.
 *
 * @return string
 *   SQL query string.
 */
function program_access_sql($asurite, $admin, $delegate, $collegestr) {
  $qry = pa_program_apr();

  if ($admin || $delegate) {
    $programQry = "$qry WHERE SUBSTRING_INDEX(pa.College, '_', 1) IN ($collegestr) UNION ";
  }
  else {
    $programQry = '';
  }

  $programQry = "$programQry
                    $qry
                      JOIN `PA_User` tt on pa.acadplan = tt.element WHERE tt.asurite LIKE '$asurite' AND  tt.ElementType LIKE 'AcadPlan'
                    UNION
                    $qry
                      JOIN `PA_User` tt on pa.department = tt.element WHERE tt.asurite LIKE '$asurite' AND  tt.ElementType LIKE 'Department'
                      UNION
                    $qry
                      JOIN `PA_User` tt on pa.college = tt.element WHERE tt.asurite LIKE '$asurite' AND  tt.ElementType LIKE 'College' ;";

  return $programQry;
}

/**
 * Load a Google Sheet through the configured Google service client.
 *
 * @param string $spread
 *   Spreadsheet ID.
 *
 * @return mixed
 *   Google Sheets API response.
 */
function get_google_sheet($spread) {
  $googleApiServiceClient = \Drupal::entityTypeManager()->getStorage('google_api_service_client')->load('uoeee_g_api');
  $googleService = \Drupal::service('google_api_service_client.client');
  $googleService->setGoogleApiClient($googleApiServiceClient);

  try {
    $object = $googleService->getServiceObjects();
    $sheets = $object['sheets'];
    $singleSheet = $sheets->spreadsheets_values;
    $response = $singleSheet->get($spread, 'Sheet1');
  }
  catch (\Exception $e) {
    $response = NULL;
  }

  return $response;
}

/**
 * Determine whether the provided ASUrite has an access record.
 *
 * @param string $asurite
 *   The ASUrite identifier to check.
 *
 * @return bool
 *   TRUE when a matching record exists in the PA_User table, FALSE otherwise.
 */
function has_access(string $asurite): bool {
  $query = \Drupal::database()->select('PA_User', 'u');
  $query->addField('u', 'asurite');
  $query->condition('u.asurite', $asurite);
  $query->range(0, 1);
  return (bool) $query->execute()->fetchField();
}
