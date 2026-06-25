<?php

namespace Drupal\apadmin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Controller for AP Admin operations.
 *
 * Provides endpoints for the AP Admin Vue application including
 * dashboard loading, synchronization, archive access, and
 * administrative POST actions.
 */
class ApAdminController extends ControllerBase {

  /**
   * Maps frontend table keys to actual database table names.
   *
   * The web application sends a logical table key rather than the
   * real table name. This map resolves the key to the corresponding
   * database table used in queries.
   *
   * @var array<string,string>
   */
  protected array $tableMap = [
    'program' => 'PA_Program',
    'plans' => 'PA_AssessmentPlans',
    'activeplanedit' => 'pa_activeplanedit',
    'info' => 'PA_CurrentReports_Info',
    'openactive' => 'pa_activereportopen',
    'open' => 'PA_CurrentReports_Open',
    'resultsactive' => 'pa_activereportedit',
    'results' => 'PA_CurrentReports_Results',
    'adminreviews' => 'PA_AdminReviews',
    'adminreviewslogapps' => 'PA_AdminReviews_Apps_Log',
    'adminreviewsappsactive' => 'pa_adminreviews_apps_active',
    'adminreviewslogplans' => 'PA_AdminReviews_Plans_Log',
    'adminreviewsplansactive' => 'pa_adminreviews_plans_active',
    'comments' => 'PA_Comments',
    'apr' => 'PA_APR',
    'planspublic' => 'pa_assessmentplans_public',
  ];

  /**
   * Builds the main AP Admin application page.
   *
   * Loads the current user information and global dashboard data,
   * attaches the Vue application library, and returns the container
   * markup used to bootstrap the frontend.
   *
   * @return array
   *   A render array containing the inline template and attached settings.
   */
  public function apadminLoad() {

    $asurite = \Drupal::currentUser()->getAccountName();
    $base_url = \Drupal::request()->getSchemeAndHttpHost();

    if (!$this->userHasApadminAccess()) {
      $response = new TrustedRedirectResponse($base_url . '/aportal');
      $response->getCacheableMetadata()->setCacheMaxAge(0);
      return $response;
    }

    // GLOBAL snapshot (fast, cached)
    /** @var \Drupal\apadmin\Service\DashboardGlobals $apinfo */
    $apinfo = \Drupal::service('apadmin.dashboard_globals')->get();

    $apadmin = array_merge($apinfo, [
        'baseurl' => $base_url,
        'asurite' => $asurite
    ]);

    // debug($program_qry);
    $settings = ["apadmin" => $apadmin];
    return [
        '#type' => 'inline_template',
        '#cache' => [
          'contexts' => ['user'],
      // 5 minutes – adjust later
          'max-age' => 300,
          'tags' => ['apadmin:bootstrap']
        ],
        '#attached' =>
            [
              'library' => ['apadmin/apadmin-app'],
              'drupalSettings' => $settings
            ],
        '#template' => '<div class="apadmin-wrapper"></div>'
      ];
  }

  /**
   * Determines whether the current user has AP Admin access.
   *
   * Checks the AP_user table for a record matching the current user's
   * ASURITE (account name). If a row exists, the user is considered
   * authorized to use AP Admin functionality.
   *
   * @return bool
   *   TRUE if the current user has AP Admin access, FALSE otherwise.
   */
  protected function userHasApadminAccess(): bool {
    $asurite = \Drupal::currentUser()->getAccountName();

    $count = \Drupal::database()
      ->select('AP_user', 'u')
      ->condition('u.asurite', $asurite)
      ->countQuery()
      ->execute()
      ->fetchField();

    return (bool) $count;
  }

  /**
   * Returns archived data from a specified table.
   *
   * @param string $tableKey
   *   The table name to query.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing the table data.
   */
  public function apadminArchive(string $tableKey) {

    // 1) Auth + permission
    if (!$this->userHasApadminAccess()) {
      throw new AccessDeniedHttpException('User not authorized for AP Admin.');
    }

    $realTable = $this->tableMap[$tableKey] ?? NULL;
    if (!$realTable) {
      throw new \InvalidArgumentException('Invalid table key.');
    }

    $query = \Drupal::database()
      ->select($realTable, 't')
      ->fields('t');

    $data = $query->execute()->fetchAll();

    return new JsonResponse($data);
  }

  /**
   * Returns the latest global dashboard data.
   *
   * Forces a refresh of cached dashboard globals and returns them
   * as a JSON payload for the admin interface.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing global dashboard data.
   */
  public function apadminSync() {

    /* all query keys -> 'apsettings','admins','asupeople','users','allaccess','delegates','delegateassignees','collegenames',
    'acadunits','apr','requestslog','academicplaninfo','statuscurrent','statuslog','customdescr','customdept',
    customdisagg','customparent','handbook','programs' */

    /** @var \Drupal\apadmin\Service\DashboardGlobals $globals */
    $globals = \Drupal::service('apadmin.dashboard_globals')->get(TRUE);

    // $skips = array("asupeople", "programs", "acadunits", "academicplaninfo", "statuscurrent", "statuslog" );

    return new JsonResponse($globals);

  }

  /**
   * Processes admin actions submitted from the frontend.
   *
   * Handles a variety of administrative tasks such as user access
   * updates, delegate assignments, APR updates, program hierarchy
   * changes, report archiving, and cycle launch operations.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   HTTP response indicating success.
   */
  public function apadminPost() {
    $post = json_decode(file_get_contents('php://input'), TRUE)['body'];
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');
    $asurite = \Drupal::currentUser()->getAccountName();
    $query = "SELECT val FROM AP_settings WHERE apsetting = 'nextyear'";
    $result = \Drupal::database()->query($query)->fetchObject();
    $nextyear = $result->val;
    $cyclequeries = cyclelaunch();
    $uoeeereq = '';
    $year = date('Y');

    try {
      $rowsAffected = "Affected rows: ";

      foreach ($post['edits'] as $s) {

        switch ($s['task']) {

          // LastName, FirstName, asurite, Email, `element`, ElementType, ID, Graduate, Undergraduate, ghost.
          case 'accessadd':
            $sql = \Drupal::database()->insert('PA_User')
              ->fields([
                'LastName' => $s['lname'],
                'FirstName' => $s['fname'],
                'asurite' => $s['asurite'],
                'Email' => $s['email'],
                'element' => $s['element'],
                'ElementType' => $s['elementtype'],
                'ghost' => $s['ghost'],
              ])
              ->execute();

            $uoeeereq = $s['elementtype'] . '-' . $s['element'];
            break;

          case 'accessremove':
            foreach ($s['removals'] as $r) {
              $sql = \Drupal::database()->delete('PA_User')
                ->condition('asurite', $s['asurite'])
                ->condition('element', $r)
                ->execute();
              $uoeeereq = $uoeeereq . '-' . $r;
            }
            break;

          // asurite, Email, college, Status, id, SendEmail.
          case 'delegateadd':
            $query = \Drupal::database()->select('PA_Delegates', 'd')->condition('d.asurite', $s['asurite'], '=');
            $del_rows = $query->countQuery()->execute()->fetchField();
            if ($del_rows > 0) {
              $sql = \Drupal::database()->insert('PA_Delegates')
                ->fields([
                  'asurite' => $s['asurite'],
                  'Email' => $s['email'],
                  'college' => $s['unit'],
                  'Status' => 'Delegate',
                  'SendEmail' => 'N',
                ])
                ->execute();
            }

            $uoeeereq = $s['unit'];
            break;

          case 'delegateremove':
            $sql = \Drupal::database()->delete('PA_Delegates')
              ->condition('asurite', $s['asurite'])
              ->condition('college', $s['unit'])
              ->execute();

            $uoeeereq = $s['unit'];
            break;

          // College, Department, DelName, DelEmail, DelAsurite.
          case 'assigneeadd':

            // Remove any previous assignments.
            $sql = \Drupal::database()->delete('PA_DelegateAssign')
              ->condition('Department', $s['element'])
              ->condition('college', $s['unit'])
              ->execute();

            $sql = \Drupal::database()->delete('PA_Delegates')
              ->condition('asurite', $s['asurite'])
              ->condition('college', $s['unit'])
              ->execute();

            $sql = \Drupal::database()->delete('PA_User')
              ->condition('asurite', $s['asurite'])
              ->condition('element', $s['unit'])
              ->execute();

            // Add new assignment.
            $sql = \Drupal::database()->insert('PA_User')
              ->fields([
                'asurite' => $s['asurite'],
                'Email' => $s['email'],
                'element' => $s['unit'],
                'FirstName' => $s['fname'],
                'LastName' => $s['lname'],
                'ElementType' => 'College',
              ])
              ->execute();

            $sql = \Drupal::database()->insert('PA_Delegates')
              ->fields([
                'asurite' => $s['asurite'],
                'Email' => $s['email'],
                'college' => $s['unit'],
                'Status' => 'Delegate',
                'SendEmail' => 'Y',
              ])
              ->execute();

            $sql = \Drupal::database()->insert('PA_DelegateAssign')
              ->fields([
                'DelName' => $s['fname'] . ' ' . $s['lname'],
                'DelAsurite' => $s['asurite'],
                'DelEmail' => $s['email'],
                'Department' => $s['element'],
                'College' => $s['unit'],
              ])
              ->execute();

            $uoeeereq = $s['element'] . '-' . $s['unit'];
            dump($uoeeereq);
            break;

          case 'assigneeremove':
            $sql = \Drupal::database()->delete('PA_DelegateAssign')
              ->condition('asurite', $s['asurite'])
              ->condition('Department', $s['element'])
              ->condition('college', $s['unit'])
              ->execute();

            $uoeeereq = $s['element'] . '-' . $s['unit'];
            break;

          case 'apr-addunit':
            // (id, college, department, department_description, program, program_description, feedback, academicyear, active, chair, contact)
            // edits.value.push( { task:'apr-addunit', label: 'Add unit', unit:unit.acadcode, tier:tier, description:org, acadyear:acadyear, programd:pd, act:act, review:review} )
            $sql = \Drupal::database()->upsert('PA_APR')
              ->fields(['college', 'department_description', 'department', 'program',
                'program_description', 'academicyear', 'active'
])
              ->key('department_description', 'department', 'program')
              ->values([
                'college' => $s['college'],
                'department_description' => $s['description'],
                'department' => $s['department'],
                'program' => $s['program'],
                'program_description' => $s['programd'],
                'academicyear' => $s['acadyear'],
                'active' => $s['act'],
              ])
              ->execute();
            break;

          case 'apr-removeunit':
            // (id, college, department, department_description, program, program_description, feedback, academicyear, active, chair, contact)
            // edits.value.push( { task:'apr-removeunit', unit:u, tier:tier, description:aprname } )
            $mess = "$ts - $asurite : Unit removed with apadmin";
            $d = $s['description'];
            $t = $s['tier'];
            $u = $s['unit'];
            $query = "INSERT INTO PA_APR_deletes (id, college, department, department_description, program, program_description, feedback, academicyear, active, chair, contact)
                        SELECT id, college, department, department_description, program, program_description, '$mess', academicyear, active, chair, contact
                        FROM PA_APR WHERE department_description='$d' AND $t='$u';";
            // dump($query);
            $q = \Drupal::database()->query($query);

            $sql = \Drupal::database()->delete('PA_APR')
              ->condition('department_description', $d)
              ->condition($t, $u)
              ->execute();
            break;

          case 'apr-movecycle':
            // (id, college, department, department_description, program, program_description, feedback, academicyear, active, chair, contact)
            // edits.value.push( { task:'apr-movecycle', label: 'Change cycle', unit:org, description:org, cycle:newcycle.key, acadyear:newcycle.acadyear, act:newcycle.active, review:newcycle.label } )
            $sql = \Drupal::database()->update('PA_APR')
              ->fields(['academicyear' => $s['acadyear'], 'active' => $s['act']])
              ->condition('department_description', $s['description'])
              ->execute();
            break;

          case 'apr-setchair':
          case 'apr-setcontact':
            // (id, college, department, department_description, program, program_description, feedback, academicyear, active, chair, contact)
            // edits.value.push( { task:'apr-'+action, label: 'Set ' + action, field:field, description:org, person:asurite, review:asuperson.value[asurite] ? asuperson.value[asurite].usertext : asurite } )
            $sql = \Drupal::database()->update('PA_APR')
              ->fields([$s['field'] => $s['person']])
              ->condition('department_description', $s['description'])
              ->execute();
            break;

          case 'replaceplaninfo':

            $sql = \Drupal::database()->delete('AP_academicplaninfo')
              ->execute();

            $query = \Drupal::database()->insert('AP_academicplaninfo')
              ->fields(['termcode', 'academicplancode', 'academicplandescr',
                          'academicplanadjdescr', 'trnscr_descr',
                          'aboracademicplancode', 'aboracademicplandescr', 'aboracadplanapprovalyear',
                          'academicplaneffectiveterm', 'academicplandisestabterm', 'academiccareercode',
                          'academiccareerdescr', 'academicprogramcode', 'academicprogramdescr',
                          'collegecode', 'collegeshortdescr', 'divisionadjcode', 'divisionadjdescr',
                          'departmentadjcode', 'departmentadjdescr', 'degreecode', 'degreedescr',
                          'degreelevelcode', 'degree_type', 'enrolled7year', 'enrolled3year', 'enrolled', 'online',
                          'onlinepct', 'graduatedprev', 'graduated'
]);
            foreach ($s['xlsxdata'] as $r) {
              $query->values(
                [
                  'termcode' => $r['termcode'],
              'academicplancode' => $r['academicplancode'],
              'academicplandescr' => $r['academicplandescr'],
                  'academicplanadjdescr' => $r['academicplanadjdescr'],
              'trnscr_descr' => $r['trnscr_descr'],
                  'aboracademicplancode' => $r['aboracademicplancode'],
              'aboracademicplandescr' => $r['aboracademicplandescr'],
                  'aboracadplanapprovalyear' => $r['aboracadplanapprovalyear'],
              'academicplaneffectiveterm' => $r['academicplaneffectiveterm'],
                  'academicplandisestabterm' => $r['academicplandisestabterm'],
              'academiccareercode' => $r['academiccareercode'],
                  'academiccareerdescr' => $r['academiccareerdescr'],
              'academicprogramcode' => $r['academicprogramcode'],
                  'academicprogramdescr' => $r['academicprogramdescr'],
              'collegecode' => $r['collegecode'],
              'collegeshortdescr' => $r['collegeshortdescr'],
                  'divisionadjcode' => $r['divisionadjcode'],
              'divisionadjdescr' => $r['divisionadjdescr'],
              'departmentadjcode' => $r['departmentadjcode'],
                  'departmentadjdescr' => $r['departmentadjdescr'],
              'degreecode' => $r['degreecode'],
              'degreedescr' => $r['degreedescr'],
                  'degreelevelcode' => $r['degreelevelcode'],
              'degree_type' => $r['degree_type'],
                  'enrolled7year' => $r['Enrolled7year'],
              'enrolled3year' => $r['Enrolled3year'],
              'enrolled' => $r['Enrolled'],
              'online' => $r['Online'],
              'onlinepct' => (float) $r['Online PCT'],
                  'graduatedprev' => $r['Graduated Prev'],
              'graduated' => $r['Graduated']
                ]
                );
            }
            $query->execute();

            $u = \Drupal::database()->update('AP_academicplaninfo')->fields(['aboracadplanapprovalyear' => NULL])->condition('aboracadplanapprovalyear', '% %', 'LIKE')->execute();
            $u = \Drupal::database()->update('AP_academicplaninfo')->fields(['academicplaneffectiveterm' => NULL])->condition('academicplaneffectiveterm', '% %', 'LIKE')->execute();
            $u = \Drupal::database()->update('AP_academicplaninfo')->fields(['academicplandisestabterm' => NULL])->condition('academicplandisestabterm', '% %', 'LIKE')->execute();

            break;

          // INSERT INTO db.AP_statuslog (id, programstatus, acadplan, notes, reviewdt, reviewcycle, planapp)
          case 'statusupdate':
            $sql = \Drupal::database()->insert('AP_statuslog')
              ->fields([
                'programstatus' => $post['determination']['status'],
                'acadplan' => $s['academicplancode'],
                'notes' => $post['determination']['note'],
                'reviewdt' => $ts,
                'reviewcycle' => $post['reviewcycle'],
                'planapp' => $post['determination']['planapp'],
                'reviewer' => $asurite
              ])
              ->execute();

            if (($post['determination']['update'] ?? FALSE) === TRUE) {
              $planstatus = in_array($post['determination']['status'], ['Report', 'Low Enrollment', 'Plan']) ? 'Not Submitted' : '';
              $reportstatus = in_array($post['determination']['status'], ['Report', 'Low Enrollment']) ? 'Not Submitted' : '';
              $sql = \Drupal::database()->update('PA_Program')
                ->fields([
                  'progstatus' => $post['determination']['status'],
                  'planStatus' => $planstatus,
                  'reportstatus' => $reportstatus,
                ])
                ->condition('acadplan', $s['acadplan'])
                ->execute();
            }
            break;

          case 'create-parent':
            $childplan = $post['acadplan'];
            $parentplan = $s['acadplan'];
            $trnscr = $s['trnscr'];
            $query = "
                      INSERT INTO PA_Program (
                        College, Department, acadplan, SubmissionCode, Program_Name,
                        TRNSCR, ParentProgram, Feedback, Notes, progstatus, ReportFeedback, reportStatus, PlanStatus, RevisionComments_plan,
                        approveUser_plan, submitUser_plan, contUser_plan, reviseUser_plan, approveTimestamp_plan, submitTimestamp_plan,
                        contTimestamp_plan, reviseTimestamp_plan, RevisionComments_report, approveUser_report, submitUser_report,
                        reviseUser_report, approveTimestamp_report, submitTimestamp_report, reviseTimestamp_report,
                        UOEEEStatus, reportLastAction, planLastAction, changemaker, requestFeedback, requestFeedback_User, reportLastUser,
                        planLastUser, lastUpdated, enrolled, enrolledonline, enrolled3yr, enrolled7yr
                      )
                      SELECT
                        College,
                        Department,
                        :parentplan AS acadplan,
                        REPLACE(SubmissionCode, :childplan, :parentplan) AS SubmissionCode,
                        CONCAT(REPLACE(College, '_', '-'), '-', Department, '-MAJ-', :trnscr, '-', :parentplan) AS Program_Name,
                        :trnscr AS TRNSCR,
                        '' AS ParentProgram,
                        Feedback,
                        Notes,
                        progstatus,
                        ReportFeedback,
                        reportStatus,
                        PlanStatus,
                        RevisionComments_plan,
                        approveUser_plan,
                        submitUser_plan,
                        contUser_plan,
                        reviseUser_plan,
                        approveTimestamp_plan,
                        submitTimestamp_plan,
                        contTimestamp_plan,
                        reviseTimestamp_plan,
                        RevisionComments_report,
                        approveUser_report,
                        submitUser_report,
                        reviseUser_report,
                        approveTimestamp_report,
                        submitTimestamp_report,
                        reviseTimestamp_report,
                        UOEEEStatus,
                        reportLastAction,
                        planLastAction,
                        changemaker,
                        requestFeedback,
                        requestFeedback_User,
                        reportLastUser,
                        planLastUser,
                        lastUpdated,
                        enrolled,
                        enrolledonline,
                        enrolled3yr,
                        enrolled7yr
                      FROM PA_Program pp
                      WHERE acadplan = :childplan
                      AND NOT EXISTS (
                        SELECT 1
                        FROM PA_Program p2
                        WHERE p2.acadplan = :parentplan
                      );
                      ";
            // dump($query);
            $args = [
              ':parentplan' => $parentplan,
              ':childplan'  => $childplan,
              ':trnscr'     => $trnscr,
            ];
            $q = \Drupal::database()->query($query, $args);
            $sums_args = [
              ':parent_program_tag' => 'child_' . $s['parentprogram'],
            ];
            $query = "
                    SELECT
                      SUM(enrolled)       AS enrolled,
                      SUM(enrolled3yr)    AS enrolled3yr,
                      SUM(enrolled7yr)    AS enrolled7yr,
                      SUM(enrolledonline) AS enrolledonline
                    FROM PA_Program
                    WHERE ParentProgram = :parent_program_tag";
            $sums = \Drupal::database()->query($query, $sums_args)->fetchObject();
            $sql = \Drupal::database()->update('PA_Program')
              ->fields([
            'enrolled' => $sums->enrolled,
            'enrolled3yr' => $sums->enrolled3yr,
                'enrolled7yr' => $sums->enrolled7yr,
            'enrolledonline' => $sums->enrolledonline
])
              ->condition('acadplan', $parentplan)
              ->execute();
            break;

          // INSERT INTO db.AP_customparent (id, acadplan, parentprogram, college, department, trnscr, requestnote, requestedby, modifieddt)
          case 'add-child':
            $sql = \Drupal::database()->insert('AP_customparent')
              ->fields([
                'acadplan' => $s['acadplan'],
                'parentprogram' => $s['parentprogram'],
                'college' => $s['college'],
                'department' => $s['department'],
                'trnscr' => $s['trnscr'],
                'note' => $post['note'],
                'editor' => $asurite
              ])
              ->execute();
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['ParentProgram' => "child_{$s['parentprogram']}"])
              ->condition('acadplan', $s['acadplan'])
              ->execute();

            $sums_args = [
              ':parent_program_tag' => 'child_' . $s['parentprogram'],
            ];
            $qry = "SELECT SUM(enrolled) as enrolled, SUM(enrolled3yr) as enrolled3yr,
                            SUM(enrolled7yr) as enrolled7yr, SUM(enrolledonline) as enrolledonline FROM PA_Program WHERE ParentProgram=:parent_program_tag";
            $sums = \Drupal::database()->query($qry, $sums_args)->fetchObject();
            $sql = \Drupal::database()->update('PA_Program')
              ->fields([
            'enrolled' => $sums->enrolled,
            'enrolled3yr' => $sums->enrolled3yr,
            'enrolled7yr' => $sums->enrolled7yr,
            'enrolledonline' => $sums->enrolledonline
])
              ->condition('acadplan', $s['parentprogram'])
              ->execute();
            break;

          case 'remove-parent':
            $sql = \Drupal::database()->delete('PA_Program')
              ->condition('acadplan', $s['acadplan'])
              ->execute();
            break;

          case 'remove-child':
            $max_length = 50;
            $suffix = '-unlink-' . date('YmdHis');
            $acadplan = substr($s['acadplan'], 0, $max_length - strlen($suffix)) . $suffix;
            $parentprogram = substr($s['parentprogram'], 0, $max_length - strlen($suffix)) . $suffix;
            $sql = \Drupal::database()->update('AP_customparent')
              ->fields([
            'acadplan' => $acadplan,
                'parentprogram' => $parentprogram
])
              ->condition('acadplan', $s['acadplan'])
              ->execute();
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['ParentProgram' => ''])
              ->condition('acadplan', $s['acadplan'])
              ->execute();

            $query = \Drupal::database()->select('PA_Program')->condition('ParentProgram', "child_{$s['parentprogram']}", '=');
            $child_rows = $query->countQuery()->execute()->fetchField();
            // dump($child_rows) ;.
            if ($child_rows == 100) {
              $sql = \Drupal::database()->delete('PA_Program')
                ->condition('acadplan', $s['parentprogram'])
                ->execute();
            }

            break;

          case 'copy-child':
            $sql = \Drupal::database()->insert('AP_statuslog')
              ->fields([
                'programstatus' => $post['determination']['status'],
                'acadplan' => $s['academicplancode'],
                'notes' => $post['determination']['note'],
                'reviewdt' => $ts,
                'reviewcycle' => $post['reviewcycle'],
                'planapp' => $post['determination']['planapp'],
                'reviewer' => $asurite
              ])
              ->execute();
            break;

          case 'editdisagg':
            $sql = \Drupal::database()->upsert('AP_customdisagg')
              ->fields(['acadplan', 'disaggregate', 'note', 'modifier'])
              ->key('acadplan')
              ->values([
                'acadplan' => $s['acadplan'],
                'disaggregate' => $s['disaggregate'],
                'note' => $s['note'],
                'modifier' => $asurite,
              ])
              ->execute();
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['ParentProgram' => "disaggregate_{$s['disaggregate']}"])
              ->condition('acadplan', $s['acadplan'])
              ->execute();
            break;

          case 'PA_Program':
            $query = "Replace PA_AssessmentReports_Archive_Program (College, Department, acadplan, submissionyear, SubmissionCode, Program_Name, TRNSCR,
                    ParentProgram, Feedback, Notes, progstatus, ReportFeedback, reportStatus, PlanStatus, RevisionComments_plan, approveUser_plan,
                    submitUser_plan, contUser_plan, reviseUser_plan, approveTimestamp_plan, submitTimestamp_plan, contTimestamp_plan, reviseTimestamp_plan,
                    RevisionComments_report, approveUser_report, submitUser_report, reviseUser_report, approveTimestamp_report, submitTimestamp_report,
                    reviseTimestamp_report, UOEEEStatus, reportLastAction, planLastAction, changemaker, requestFeedback, requestFeedback_User, reportLastUser,
                    planLastUser, lastUpdated, enrolled, enrolledonline, enrolled3yr, enrolled7yr, establishterm)
                    SELECT College, Department, acadplan, replace(p.submissionCode, Concat(p.acadplan,'_'), ''), SubmissionCode, Program_Name, TRNSCR, ParentProgram, Feedback, Notes, progstatus, ReportFeedback,
                    reportStatus, PlanStatus, RevisionComments_plan, approveUser_plan, submitUser_plan, contUser_plan, reviseUser_plan, approveTimestamp_plan,
                    submitTimestamp_plan, contTimestamp_plan, reviseTimestamp_plan, RevisionComments_report, approveUser_report, submitUser_report, reviseUser_report,
                    approveTimestamp_report, submitTimestamp_report, reviseTimestamp_report, UOEEEStatus, reportLastAction, planLastAction, changemaker,
                    requestFeedback, requestFeedback_User, reportLastUser, planLastUser, lastUpdated,
                    enrolled, enrolledonline, enrolled3yr, enrolled7yr, establishterm
                    FROM PA_Program p;";
            // dump($query);
            $q = \Drupal::database()->query($query);
            break;

          case 'PA_CurrentReports_Info':
            $query = "Replace PA_AssessmentReports_Archive_Info (acadplan, element, outcome, measure, descriptionTxt, PC_text, canvastags,survey,submissionYear, SubmissionCode)
                    SELECT cri.acadplan, element, outcome, measure, description, PC_text,canvastags,survey,RIGHT(p.submissionCode,9), p.submissionCode
                    FROM PA_CurrentReports_Info cri
                    INNER JOIN PA_Program p ON cri.acadplan=p.acadplan;";
            // dump($query);
            $q = \Drupal::database()->query($query);
            break;

          case 'pa_activereportopen':
            $query = "Replace PA_AssessmentReports_Archive_Open (acadplan, Fac_Names, Current_Activities, No_Data, Upcoming_Changes, Program_Changes, Assessment_Changes, SubmissionYear, SubmissionCode)
                    SELECT cro.acadplan, Fac_Names, Current_Activities, No_Data, Changes, Program_Changes, Assessment_Changes, replace(p.submissionCode, Concat(p.acadplan,'_'), ''), p.submissionCode
                    FROM pa_activereportopen cro
                    INNER JOIN PA_Program p ON cro.acadplan=p.acadplan;";
            // dump($query);
            $q = \Drupal::database()->query($query);
            break;

          case 'pa_activereportedit':
            $query = "Replace PA_AssessmentReports_Archive_Results (acadplan, element, outcome, measure, disposition,descriptionTxt, PC_text,  expText, result, pop, met, num, SubmissionYear,  SubmissionCode)
                    SELECT crr.acadplan, crr.element, crr.outcome, crr.measure, crr.disposition, crr.description, crr.PC_text, crr.expText, crr.result,
                    CASE WHEN crr.pop IS NULL THEN '' ELSE crr.pop END,
                    CASE WHEN crr.met='' THEN NULL ELSE crr.met END,
                    CASE WHEN crr.num='' THEN NULL ELSE crr.num END,
                    replace(p.submissionCode, Concat(p.acadplan,'_'), ''),
                    p.submissionCode
                    FROM pa_activereportedit crr
                    INNER JOIN PA_Program p ON crr.acadplan=p.acadplan;";
            // dump($query);
            $q = \Drupal::database()->query($query);
            break;

          case 'clearpaprogram':
            $sql = \Drupal::database()->delete('PA_Program')
              ->condition('progstatus', ['Plan Application', 'ABOR Approved', 'Pre Gov'], 'NOT IN')
              ->execute();
            $sql = \Drupal::database()->delete('PA_Program')
              ->condition('progstatus', 'IS NULL')
              ->execute();
            break;

          case 'loadpaprogram':
          case 'loadparents':
          case 'updatechild':
          case 'updatedisaggregate':
          case 'transferplans':
          case 'deleteplanapp':
          case 'loadreportinfo':
          case 'archivereportreviews':
            $placeholders = [
              ':nextyear' => $nextyear,
            ];
            run_cycle_query($s['task'], $cyclequeries, $placeholders);
            $entry = $cyclequeries[$s['task']];
            break;

          case 'updatestatus':
            // Report edit status.
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['reportstatus' => 'Not Submitted'])
              ->condition('progstatus', ['Report', 'Low Enrollment'], 'IN')
              ->execute();

            // Plan edit status.
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['planStatus' => 'Not Submitted'])
              ->condition('progstatus', ['Report', 'Low Enrollment', 'Plan', 'Insufficient Plan'], 'IN')
              ->execute();

            break;

          case 'aprrevise':
            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['planStatus' => 'Not Submitted'])
              ->condition('progstatus', ['Report', 'Low Enrollment', 'Insufficient Plan', 'Plan'], 'IN')
              ->condition('planStatus', 'Revise')
              ->execute();

            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['planStatus' => ''])
              ->condition('progstatus', 'No Enrollment')
              ->condition('planStatus', 'Revise')
              ->execute();

            $sql = \Drupal::database()->update('PA_Program')
              ->fields(['planStatus' => 'Revise'])
              ->condition('acadplan', $s['aprs'], 'IN')
              ->execute();
            break;

          case 'clearreports':
            \Drupal::database()->delete('PA_CurrentReports_Info')->execute();
            \Drupal::database()->delete('PA_CurrentReports_Results')->execute();
            \Drupal::database()->delete('PA_CurrentReports_Open')->execute();
            break;

          // Cycle launch apr code needs to move the 0s to 99 the 1s to 0 and the assigned next acad year to next year 1.
          case 'transferaprs':
            $sql = \Drupal::database()->update('PA_APR')
              ->fields(['active' => 99])
              ->condition('active', 0)
              ->execute();

            $sql = \Drupal::database()->update('PA_APR')
              ->fields(['active' => 0])
              ->condition('active', 1)
              ->execute();

            $sql = \Drupal::database()->update('PA_APR')
              ->fields(['active' => 1])
              ->condition('academicyear', $s['nextnext'])
              ->execute();

            break;

          // Cycle launch apr revise code sets all the apr program plans to revise, probably need to set to Not Submitted or blank first or at least all revise are not submitted and then set to revise.

        }

        if ($uoeeereq != '' && $s['req'] != 'uoeee') {
          // requestby='', requesttype='', request='', resolved='N', resolvenote=NULL, resolvedby=NULL, resolveddt=NULL.
          $u = \Drupal::database()->update('AP_requests')
            ->fields([
              'resolved' => 'Y',
              'resolvedby' => $asurite,
              'resolveddt' => $ts,
              'resolvenote' => $s['note'],
            ])
            ->condition('id', $s['req'])
            ->execute();
        }
        elseif ($uoeeereq != '') {
          // requestby='', requesttype='', request='', resolved='N', resolvenote=NULL, resolvedby=NULL, resolveddt=NULL.
          $i = \Drupal::database()->insert('AP_requests')
            ->fields([
              'requestdt' => $ts,
              'requestby' => $asurite,
              'requesttype' => $s['task'],
              'request' => 'UOEEE user access change-' . $s['asurite'] . '-' . $uoeeereq,
              'resolved' => 'Y',
              'resolvedby' => $asurite,
              'resolveddt' => $ts,
              'resolvenote' => $s['note'],
            ])
            ->execute();
        }
      }

    }
    catch (Exception $e) {
      echo($e);
    }

    return new Response('OK', Response::HTTP_OK);

  }

}

/**
 * Return prepared SQL statements for the cycle launch process.
 *
 * Each item is ['sql' => '...', 'args' => [':placeholder' => $value, ...]].
 */
function cyclelaunch() {
  $queries = [
    'loadpaprogram' => "
      INSERT INTO PA_Program (
        SubmissionCode, ReportStatus, PlanStatus, College, Department, AcadPlan, progstatus, Program_Name, ParentProgram, TRNSCR,
        Enrolled, enrolledOnline, enrolled3yr, enrolled7yr, establishterm
      )
      SELECT
        CONCAT(currst.acadplan, '_', :nextyear) AS SubmissionCode,
        '' AS ReportStatus,
        '' AS PlanStatus,
        CONCAT(
          CASE WHEN ladiv.val IS NULL THEN pi.CollegeCode ELSE ladiv.val END,
          '_',
          LEFT(REPLACE(pi.AcademicCareerCode,'LAW','GRAD'), 2)
        ) AS College,
        pi.DepartmentAdjCode AS Department,
        currst.acadplan AS AcadPlan,
        currst.programstatus AS progstatus,
        CONCAT(
          pi.CollegeCode,'-',
          LEFT(pi.AcademicCareerCode,2),'-',
          pi.DepartmentAdjCode,'-',
          CASE WHEN pi.DEGREE_TYPE='Certificate' THEN 'CER' ELSE 'MAJ' END,'-',
          CASE WHEN pi.TRNSCR_DESCR IS NULL OR pi.TRNSCR_DESCR = '' THEN pi.AcademicPlanAdjDescr ELSE pi.TRNSCR_DESCR END,
          '-', pi.AcademicPlanCode
        ) AS Program_Name,
        '' AS ParentProgram,
        AcademicPlanAdjDescr AS TRNSCR,
        pi.Enrolled,
        pi.Online AS EnrolledOnline,
        pi.Enrolled3year,
        pi.Enrolled7year,
        pi.academicplaneffectiveterm AS establishterm
      FROM ap_statuscurrent AS currst
      INNER JOIN AP_academicplaninfo pi ON currst.AcadPlan = pi.AcademicPlanCode
      LEFT JOIN PA_Program pp ON currst.AcadPlan = pp.acadplan
      LEFT JOIN AP_customparent cp ON pi.AcademicPlanCode = cp.AcadPlan
      LEFT JOIN AP_settings ladiv ON pi.DepartmentAdjCode = ladiv.apsetting
      WHERE currst.programstatus NOT IN ('Not Assessed', 'Disestablished', 'Non Terminal Degree')
        AND pp.acadplan IS NULL
    ",

    'loadparents' => "
      INSERT INTO PA_Program (
        College, Department, acadplan, Program_Name, ParentProgram, progstatus, SubmissionCode, TRNSCR,
        Enrolled, enrolledOnline, enrolled3yr, enrolled7yr
      )
      SELECT
        sp.College,
        sp.Department,
        sp.ParentProgram,
        CONCAT(REPLACE(sp.College, '_', '-'), '-', sp.Department, '-MAJ-', sp.TRNSCR) AS Program_Name,
        'parent' AS ParentProgram,
        'Report' AS progstatus,
        CONCAT(sp.ParentProgram, '_', :nextyear) AS SubmissionCode,
        sp.TRNSCR,
        SUM(pa.Enrolled),
        SUM(pa.enrolledOnline),
        SUM(pa.enrolled3yr),
        SUM(pa.enrolled7yr)
      FROM PA_Program AS pa
      INNER JOIN AP_customparent AS sp ON pa.acadplan = sp.acadplan
      GROUP BY sp.College, sp.Department, sp.ParentProgram, sp.TRNSCR
    ",

    'updatechild' => "
      UPDATE PA_Program pa
      INNER JOIN AP_customparent sp ON pa.acadplan = sp.acadplan
      SET pa.ParentProgram = CONCAT('child_', sp.parentprogram)
    ",

    'updatedisaggregate' => "
      UPDATE PA_Program pa
      INNER JOIN AP_customdisagg sp ON pa.acadplan = sp.acadplan
      SET pa.ParentProgram = CONCAT('disaggregate_', sp.disaggregate)
    ",

    'transferplans' => "
      UPDATE PA_AssessmentPlans pa
      INNER JOIN AP_statuslog sl ON pa.acadplan = sl.planapp
      SET pa.acadplan = sl.acadplan
    ",

    'deleteplanapp' => "
      DELETE pp
      FROM PA_Program pp
      INNER JOIN AP_statuslog sl ON pp.acadplan = sl.planapp
    ",

    'loadreportinfo' => "
      INSERT INTO PA_CurrentReports_Info ( acadplan, element, outcome, measure, `description`, PC_text, canvastags, survey )
      SELECT
        pe.acadplan,
        pe.element,
        pe.outcome,
        pe.measure,
        pe.description,
        pe2.description AS PC_text,
        pe.canvastags,
        pe.survey
      FROM pa_activeplanedit pe
      INNER JOIN PA_Program pp ON pe.acadplan = pp.acadplan
      LEFT JOIN (
        SELECT acadplan, outcome, measure, `description`
        FROM pa_activeplanedit
        WHERE element = 'PC'
      ) pe2 ON pe2.acadplan = pe.acadplan
        AND pe2.outcome = pe.outcome
        AND pe2.measure = pe.measure
      LEFT JOIN PA_CurrentReports_Info ri ON pe.acadplan = ri.acadplan
      WHERE pe.element IN ('Outcome','Measure','AP_1Process')
        AND pp.progstatus IN ('Report', 'Low Enrollment')
        AND pp.ParentProgram NOT LIKE 'child_%'
        AND ri.acadplan IS NULL
    ",

    'archivereportreviews' => "
      UPDATE PA_AdminReviews
      SET reviewtype = 'archived'
      WHERE reviewtype = 'reports'
        AND submissionyear NOT LIKE :nextyear
    ",
  ];

  return $queries;
}

/**
 * Execute a cycle query by name using a compact placeholders map.
 *
 * @param string $name
 *   Key returned by cyclelaunch().
 * @param array $cyclequeries
 *   The array from cyclelaunch().
 * @param array $placeholders
 *   Map of possible placeholder values, e.g. [':nextyear' => '2026', ':foo' => 'bar'].
 */
function run_cycle_query(string $name, array $cyclequeries, array $placeholders = []) {
  $connection = \Drupal::database();

  if (!isset($cyclequeries[$name])) {
    throw new \InvalidArgumentException("Unknown cycle query: $name");
  }

  $sql = (string) $cyclequeries[$name];

  // Build args only for placeholders actually present in the SQL.
  $args = [];
  foreach ($placeholders as $ph => $val) {
    if ($val !== NULL && strpos($sql, $ph) !== FALSE) {
      $args[$ph] = $val;
    }
  }

  // Execute (transaction per statement by default).
  $transaction = $connection->startTransaction();
  try {
    $connection->query($sql, $args);
    \Drupal::logger('cyclelaunch')->notice('Executed {name}', ['{name}' => $name]);
  }
  catch (\Exception $e) {
    \Drupal::logger('cyclelaunch')->error('Query {name} failed: @msg', ['{name}' => $name, '@msg' => $e->getMessage()]);
    throw $e;
  }
}
