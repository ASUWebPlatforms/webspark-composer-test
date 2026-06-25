<?php

namespace Drupal\svyreport\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SvyReportController.
 *
 * Updated to remove use of superglobals and use parameterized SQL.
 */
class SvyReportController extends ControllerBase {

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Primary function for loading the survey reporting page.
   *
   * @return array
   *   Render array.
   */
  public function svyreportLoad(): array {

    $asurite = \Drupal::currentUser()->getAccountName();

    $ednas = [
      'BI.WG.UOEEECA.VWR',
      'BI.WG.UOEEECA.PWR',
      'BI.WG.UOEEEIR.VWR',
      'BI.WG.UOEEEIR.PWR',
    ];

    // $ednachek = new EdnaChecksController();
    // $ednachecks = $ednachek->getUserAccess($asurite, $ednas);
    // debug($ednachecks);

    global $base_url;
    $delegateadmin = assessment_access_sr($asurite);
    $delegate = $delegateadmin['delegate'];
    $admin = $delegateadmin['admin'];

    $allaccess = $admin ? TRUE : FALSE;
    $collegeaccess = college_access_sql_sr($asurite, $allaccess);
    $collegestr_array = $collegeaccess['colleges'];
    $collegestr = $collegeaccess['collegestr'];
    $program_qry_struct = program_access_sql_sr($asurite, $allaccess, $delegate, $collegestr_array);

    // Build simple queries with parameterized args.
    $queries = [
      'programs' => $program_qry_struct,
      'useraccess' => [
        'sql' => "SELECT * FROM PA_User WHERE Asurite LIKE :asurite",
        'args' => [':asurite' => $asurite . '%'],
      ],
      'collegenames' => [
        'sql' => "SELECT DISTINCT College AS college, CollegeName AS name FROM PA_CollegeNames",
        'args' => [],
      ],
      'xref' => [
        'sql' => "SELECT * FROM XREF ORDER BY `Order`",
        'args' => [],
      ],
      'departments' => [
        'sql' => "SELECT d.DeptCode AS code, DeptDescr AS descr FROM WPL_departments d INNER JOIN
                      (SELECT DeptCode FROM WPL_managers WHERE manager LIKE :asurite) m2
                      ON d.DeptCode=m2.DeptCode",
        'args' => [':asurite' => $asurite . '%'],
      ],
    ];

    $surveyreporting = [
      'baseurl' => $base_url,
      'asurite' => $asurite,
      'delegate' => $delegate,
      'admin' => $admin,
      'colldept' => $collegeaccess['colldept'],
      'publicbase' => \Drupal::service('file_url_generator')->generateAbsoluteString('public://'),
      'surveys' => file_get_contents('public://reports/siteassets/surveys/Analytics_Surveys.json'),
      'reportinfo' => file_get_contents('public://reports/siteassets/surveys/Analytics_Reports.json'),
      'reportinfo_wp' => file_get_contents('public://reports/siteassets/surveys/Analytics_WP_Reports.json'),
    ];

    // Execute queries and collect results. program_qry_struct already contains SQL+args.
    foreach ($queries as $key => $q) {
      if ($key === 'programs') {
        // program_qry_struct already prepared by program_access_sql_sr()
        $res = \Drupal::database()->query($program_qry_struct['sql'], $program_qry_struct['args']);
        $surveyreporting[$key] = $res->fetchAll();
      }
      else {
        $res = \Drupal::database()->query($q['sql'], $q['args']);
        $surveyreporting[$key] = $res->fetchAll();
      }
    }

    $settings = ['surveyreporting' => $surveyreporting];

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['svyreport/svyreport-app'],
        'drupalSettings' => $settings,
      ],
      '#markup' => '<div id="svyreport-wrapper"></div>',
    ];
  }

  /**
   * Handle POST actions from the front-end.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   HTTP response.
   */
  public function svyreportPost(): Response {

    $request = $this->requestStack->getCurrentRequest();
    $raw = $request->getContent();
    $body = json_decode($raw, TRUE);
    $post = is_array($body) && isset($body['body']) ? $body['body'] : [];

    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');
    $asurite = \Drupal::currentUser()->getAccountName();

    switch ($post['action'] ?? '') {
      case 'runreport':
        $rp = $post['reportpath'] ?? '';
        $qs = isset($post['questions']) ? substr((string) $post['questions'], 0, 500) : '';

        $q = "SELECT ID FROM sr_reporting_log
              WHERE asurite = :asurite
                AND reportpath = :reportpath
                AND questions = :questions
                AND DATE(requestdate) = CURDATE()
                AND TIMESTAMPDIFF(HOUR, requestdate, :ts) < 6";

        $args = [
          ':asurite' => $asurite,
          ':reportpath' => $rp,
          ':questions' => $qs,
          ':ts' => $ts,
        ];

        $report = \Drupal::database()->query($q, $args);
        $report_rows = count($report->fetchAll());

        if ($report_rows === 0) {
          \Drupal::database()->insert('sr_reporting_log')
            ->fields([
              'asurite' => $asurite,
              'requestdate' => $ts,
              'reportpath' => $rp,
              'questions' => $qs,
            ])
            ->execute();
        }
        break;

      case 'sitevisit':
        // Query shift info.
        $query = "SELECT ID FROM sr_reporting_log
                  WHERE asurite = :asurite
                    AND TIMESTAMPDIFF(HOUR, requestdate, :ts) < 8";
        $visit_args = [
          ':asurite' => $asurite,
          ':ts' => $ts,
        ];
        $visit = \Drupal::database()->query($query, $visit_args);
        $visit_rows = count($visit->fetchAll());

        if ($visit_rows === 0) {
          \Drupal::database()->insert('sr_reporting_log')
            ->fields([
              'asurite' => $asurite,
              'reportpath' => 'sitevisit',
              'requestdate' => $ts,
            ])
            ->execute();
        }
        break;
    }

    return new Response('OK', Response::HTTP_OK);
  }

}

/**
 * Return the pa program APR SQL fragment.
 *
 * @return string
 *   SQL query fragment.
 */
function pa_program_ap_rsr(): string {

  $qry = "SELECT pa.*, LEFT(pa.College, 2) AS CollegeCode, cn.la_division, pb.publicready, po.publicmismatch, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (d.active=0 OR p.active=0) THEN '0'
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (d.active=1 OR p.active=1) THEN '1'
          ELSE NULL END AS apr, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (d.active in (0,1)) THEN d.department_description
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (p.active in (0,1)) THEN p.department_description
          ELSE NULL END AS aprdescr, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (d.active in (0,1)) THEN d.chair
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (p.active in (0,1)) THEN p.chair
          ELSE NULL END AS aprchair, CASE
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (d.active in (0,1)) THEN d.contact
          WHEN pa.progstatus NOT IN ('Plan Application', 'ABOR Approved')  AND (p.active in (0,1)) THEN p.contact
          ELSE NULL END AS aprcontact,
          CASE WHEN ca.acadplan IS NULL AND cd.department IS NULL THEN '0' ELSE '1' END AS canvaspilot
          FROM PA_Program pa
          LEFT JOIN PA_APR d ON pa.Department=d.department
          LEFT JOIN PA_APR p ON pa.acadplan=p.program
          LEFT JOIN pa_canvaspilot cd ON pa.Department=cd.department
          LEFT JOIN pa_canvaspilot ca ON pa.acadplan=ca.acadplan
          LEFT JOIN PA_CollegeNames cn ON LEFT(pa.College, 2)=cn.College
          LEFT JOIN (SELECT DISTINCT acadplan, 'Y' AS publicready FROM pa_assessmentplans_public) pb ON pa.acadplan=pb.acadplan
          LEFT JOIN (SELECT DISTINCT pe.acadplan, 'Y' AS publicmismatch FROM pa_activeplanedit pe JOIN pa_assessmentplans_public pb ON
                      pe.acadplan=pb.acadplan AND pe.outcome=pb.outcome
                      WHERE pe.element='Outcome' AND pe.ID<>pb.ID ) po ON pa.acadplan=po.acadplan";

  return $qry;
}

/**
 * Determine assessment access for specified user.
 *
 * @param string $asurite
 *   User asurite.
 *
 * @return array
 *   Array with keys 'delegate' and 'admin' booleans.
 */
function assessment_access_sr(string $asurite): array {

  $delegate = FALSE;
  $admin = FALSE;

  $queryDel = \Drupal::database()->query("SELECT COUNT(*) AS c FROM PA_Delegates WHERE asurite LIKE :asurite", [':asurite' => $asurite . '%']);
  $countDel = (int) $queryDel->fetchObject()->c;
  if ($countDel > 0) {
    $delegate = TRUE;
  }

  $queryUser = \Drupal::database()->query("SELECT COUNT(*) AS c FROM PA_User WHERE Element LIKE 'Admin' AND Asurite LIKE :asurite", [':asurite' => $asurite . '%']);
  $countUser = (int) $queryUser->fetchObject()->c;
  if ($countUser > 0) {
    $admin = TRUE;
  }

  return [
    'delegate' => $delegate,
    'admin' => $admin,
  ];
}

/**
 * Return college access details for a user.
 *
 * @param string $asurite
 *   User asurite.
 * @param bool $admin
 *   Admin flag.
 *
 * @return array
 *   Array with 'collegestr' (string), 'colleges' (array), and 'colldept'.
 */
function college_access_sql_sr(string $asurite, bool $admin): array {
  if (!$admin) {
    $queryColl = "SELECT DISTINCT ud.College AS CollegeCode, cn.CollegeName, Department
                FROM pa_userdepartment ud
                INNER JOIN PA_CollegeNames cn ON ud.College=cn.College
                WHERE ud.Asurite LIKE :asurite
                ORDER BY CollegeCode";
    $args = [':asurite' => $asurite . '%'];
  }
  else {
    $queryColl = "SELECT DISTINCT LEFT(pg.College, 2) AS CollegeCode, cn.CollegeName, Department
                FROM PA_Program pg
                INNER JOIN PA_CollegeNames cn
                ON LEFT(pg.College, 2)=cn.College
                ORDER BY LEFT(pg.College, 2)";
    $args = [];
  }

  $colldept = \Drupal::database()->query($queryColl, $args)->fetchAll();
  $colleges = [];
  foreach ($colldept as $u) {
    $code = $u->CollegeCode;
    if (!in_array($code, $colleges, TRUE)) {
      $colleges[] = $code;
    }
  }

  $collegestr = '';
  if (!empty($colleges)) {
    $quoted = array_map(function ($i) {
      return (string) $i;
    }, $colleges);
    $collegestr = "'" . implode("','", $quoted) . "'";
  }

  return ['collegestr' => $collegestr, 'colleges' => $colleges, 'colldept' => $colldept];
}

/**
 * Build program access SQL and args for a user.
 *
 * @param string $asurite
 *   User asurite.
 * @param bool $admin
 *   Admin flag.
 * @param bool $delegate
 *   Delegate flag.
 * @param string[] $colleges
 *   Array of college codes for IN() clause.
 *
 * @return array
 *   ['sql' => string, 'args' => array]
 */
function program_access_sql_sr(string $asurite, bool $admin, bool $delegate, array $colleges): array {

  $qry = pa_program_ap_rsr();

  $args = [];
  $sql_parts = [];

  // If admin or delegate, add filtered branch for specified colleges.
  if ($admin || $delegate) {
    if (!empty($colleges)) {
      // Build named placeholders for IN().
      $placeholders = [];
      foreach ($colleges as $idx => $code) {
        $ph = ':college_' . $idx;
        $placeholders[] = $ph;
        $args[$ph] = $code;
      }
      $in_clause = implode(', ', $placeholders);
      $sql_parts[] = "$qry WHERE LEFT(pa.College, 2) IN ($in_clause) UNION";
    }
    else {
      // No colleges — use a FALSE condition so it returns no rows for this branch.
      $sql_parts[] = "$qry WHERE 1=0 UNION";
    }
  }

  $args[':asurite'] = $asurite . '%';

  $sql_parts[] = "$qry JOIN `PA_User` tt on pa.acadplan = tt.element WHERE tt.asurite LIKE :asurite AND tt.ElementType LIKE 'AcadPlan'
                  UNION";
  $sql_parts[] = "$qry JOIN `PA_User` tt on pa.department = tt.element WHERE tt.asurite LIKE :asurite AND tt.ElementType LIKE 'Department'
                  UNION";
  $sql_parts[] = "$qry JOIN `PA_User` tt on pa.college = tt.element WHERE tt.asurite LIKE :asurite AND tt.ElementType LIKE 'College';";

  $full_sql = implode("\n", $sql_parts);

  return ['sql' => $full_sql, 'args' => $args];
}
