<?php

namespace Drupal\aportal\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;
use Psr\Log\LoggerInterface;

use Drupal\user\UserDataInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Builds and caches global (non user-specific) datasets for the landing page.
 */
final class DashboardGlobals {

  // How long to cache (seconds). Adjust to taste.
  public const TTL = 240;

  public function __construct(
    private CacheBackendInterface $cache,
    private Connection $db,
    private TimeInterface $time,
    private LoggerInterface $logger,
    private UserDataInterface $userData,
    private AccountProxyInterface $currentUser,
  ) {}

  private const USER_DATA_KEY = 'user_program_data';
  /**
   * Allowed keys the dynamic accessor will accept.
   *
   * Keep this list explicit to avoid accidental storage of arbitrary values.
   */
  private const ALLOWED_USER_KEYS = [
    'program_qry',
    'collegeaccess',
    'collegestr_nonadmin',
    'delegate',
    'admin'
    // Add future keys here as needed.
  ];

  /**
   * Return the current global snapshot, building it if needed.
   */
  public function get(): array {
    $cid = 'aportal:dashboard:globals';
    if ($item = $this->cache->get($cid)) {
      return $item->data;
    }
    $data = $this->build();
    $this->cache->set($cid, $data, $this->time->getRequestTime() + self::TTL, ['aportal:dashboard']);
    return $data;
  }

  /**
   * Load the per-user data blob and return a normalized array.
   *
   * @return array
   *   Associative array with allowed keys present (value or NULL).
   */
  private function loadUserData(): array {
    $val = $this->userData->get('aportal', (int) $this->currentUser->id(), self::USER_DATA_KEY);
    $defaults = array_fill_keys(self::ALLOWED_USER_KEYS, NULL);
    if ($val === NULL || !is_array($val)) {
      return $defaults;
    }
    return array_merge($defaults, array_intersect_key($val, $defaults));
  }

  /**
   * Load a single named value from the per-user blob.
   *
   * @param string $key
   *   One of the keys in self::ALLOWED_USER_KEYS.
   * @param mixed $default
   *   Value to return if the key is not set.
   *
   * @return mixed
   *   If the array key exists, return its value (which may be NULL). Otherwise return $default.
   *
   * @throws \InvalidArgumentException
   *   If the key is not allowed.
   */
  public function loadUserValue(string $key, $default = NULL) {
    if (!in_array($key, self::ALLOWED_USER_KEYS, TRUE)) {
      throw new \InvalidArgumentException("Unsupported user data key: {$key}");
    }

    $data = $this->loadUserData();
    return array_key_exists($key, $data) ? $data[$key] : $default;
  }

  /**
   * Save a single named value into the per-user blob.
   *
   * @param string $key
   *   One of the keys in self::ALLOWED_USER_KEYS.
   * @param mixed $value
   *   The value to store. Should be scalar or small array. Avoid large blobs.
   *
   * @throws \InvalidArgumentException
   *   If the key is not allowed.
   */
  public function saveUserValue(string $key, $value): void {
    if (!in_array($key, self::ALLOWED_USER_KEYS, TRUE)) {
      throw new \InvalidArgumentException("Unsupported user data key: {$key}");
    }

    $data = $this->loadUserData();
    $data[$key] = $value;
    $this->userData->set('aportal', (int) $this->currentUser->id(), self::USER_DATA_KEY, $data);
  }

  /**
   * Clear a single key or the entire per-user data blob.
   *
   * @param string|null $key
   *   If provided, must be in ALLOWED_USER_KEYS. If NULL, delete the whole blob.
   *
   * @throws \InvalidArgumentException
   *   If the key is not allowed.
   */
  public function clearUserValue(?string $key = NULL): void {
    $uid = (int) $this->currentUser->id();

    if ($key === NULL) {
      $this->userData->delete('aportal', $uid, self::USER_DATA_KEY);
      return;
    }

    if (!in_array($key, self::ALLOWED_USER_KEYS, TRUE)) {
      throw new \InvalidArgumentException("Unsupported user data key: {$key}");
    }

    $data = $this->userData->get('aportal', $uid, self::USER_DATA_KEY) ?? [];
    unset($data[$key]);
    $this->userData->set('aportal', $uid, self::USER_DATA_KEY, $data);
  }

  /**
   * Build the global snapshot (no user/asurite inputs here).
   */
  private function build(): array {
    $asOf = $this->time->getRequestTime();

    // --- Queries copied from your controller that are NOT user-specific. ---
    // If you later need to trim/limit any of these, do it here once.
    $queries = [
      'programsObj' => "SELECT pa.*, SUBSTRING_INDEX(pa.College, '_', 1) AS CollegeCode, cn.la_division, pb.publicready, po.publicmismatch,
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
          LEFT JOIN (SELECT acadplan, COUNT(acadplan) AS publics FROM pa_assessmentplans_public WHERE element = 'Outcome' GROUP BY acadplan)
                    prp ON pa.acadplan=prp.acadplan
          LEFT JOIN (SELECT acadplan, COUNT(acadplan) AS actives FROM pa_activeplanedit WHERE element = 'Outcome'  GROUP BY acadplan)
                    pra ON pa.acadplan=pra.acadplan;",

      'delegates' => "SELECT * FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y' UNION
                      SELECT asurite, email, 'LA_UG', status, id, SendEmail FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y' AND
                      college IN ('LB_UG','LC_UG','LD_UG','LH_UG','LM_UG') UNION
                      SELECT asurite, email, 'LA_GR', status, id,SendEmail FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y' AND
                      college IN ('LB_GR','LC_GR','LD_GR','LH_GR','LM_GR')",

      'delegateassignees' => "SELECT * FROM PA_DelegateAssign",

      'report_open' => "SELECT *, CASE WHEN No_Data=0 THEN 'Data Collected' ELSE 'No Data' END AS collected
                        FROM PA_CurrentReports_Open",

      'guidance' => "SELECT * FROM PA_NodeContentText",

      'collegenames' => "SELECT DISTINCT College AS college, CollegeName AS name
                         FROM PA_CollegeNames",

      'users' => "SELECT t.asurite, t.lastname, t.firstname, t.email, access AS lastvisit
                    FROM pa_usernamesemails t
               LEFT JOIN users_field_data tt ON t.asurite = tt.`name`",

      'allaccess' => "SELECT asurite, element, elementtype FROM PA_User",

      'reportsreviews' => "SELECT DISTINCT ar.acadplan, ar.SubmissionCode, ar.ReviewStatus, reviewtype, ReviewTimeStamp
                             FROM PA_AdminReviews ar
                             JOIN (
                               SELECT op.SubmissionCode FROM PA_AssessmentReports_Archive_Open op JOIN PA_Program pa ON op.acadplan=pa.acadplan
                               UNION
                               SELECT pa.SubmissionCode FROM PA_CurrentReports_Open op JOIN PA_Program pa ON op.acadplan=pa.acadplan
                             ) op ON ar.SubmissionCode = op.SubmissionCode
                            WHERE ar.reviewtype LIKE 'reports'",

      'plansreviews'    => "SELECT acadplan, SubmissionCode, ReviewStatus, reviewtype, ReviewTimeStamp
                            FROM pa_adminreviews_plans_active",

      'planappsreviews' => "SELECT acadplan, SubmissionCode, ReviewStatus, reviewtype, ReviewTimeStamp
                            FROM pa_adminreviews_apps_active
                            WHERE reviewtype='planapps'",

      'govreviews'      => "SELECT acadplan, SubmissionCode, ReviewStatus, reviewtype, ReviewTimeStamp
                            FROM pa_adminreviews_apps_active
                            WHERE reviewtype='gov'",

      'feedbackreviews' => "SELECT acadplan, SubmissionCode, ReviewStatus, reviewtype, ReviewTimeStamp
                            FROM PA_AdminReviews
                            WHERE reviewtype LIKE 'feedback'",

      'allreviews'      => "SELECT acadplan, SubmissionCode, ReviewStatus, Disposition, Reviewer, reviewtype, ReviewTimeStamp, submissionyear
                              FROM PA_AdminReviews
                             WHERE reviewtype NOT LIKE 'archived' AND ReviewTimeStamp IS NOT NULL AND ReviewTimeStamp <> ''
                             UNION
                            SELECT acadplan, SubmissionCode, ReviewStatus, Disposition, Reviewer, reviewtype, ReviewTimeStamp, submissionyear
                              FROM pa_adminreviews_plans_active
                             WHERE ReviewTimeStamp IS NOT NULL AND Disposition IS NOT NULL AND ReviewTimeStamp <> ''
                             UNION
                            SELECT acadplan, SubmissionCode, ReviewStatus, Disposition, Reviewer, reviewtype, ReviewTimeStamp, submissionyear
                              FROM pa_adminreviews_apps_active
                             WHERE ReviewTimeStamp IS NOT NULL AND Disposition IS NOT NULL AND ReviewTimeStamp <> ''",

      'xref' => "SELECT * FROM XREF ORDER BY `Order`",

      'announcements' => "SELECT n.type, n.title, b.body_value, a.field_targetaudience_value, p.field_targetpage_value,
                                 s.field_startdate_value, e.field_enddate_value
                            FROM node_field_data n
                       LEFT JOIN node__body b ON n.vid=b.revision_id
                       LEFT JOIN node__field_targetaudience a ON n.vid=a.revision_id
                       LEFT JOIN node__field_targetpage p ON n.vid=p.revision_id
                       LEFT JOIN node__field_startdate s ON n.vid=s.revision_id
                       LEFT JOIN node__field_enddate e ON n.vid=e.revision_id
                           WHERE n.type LIKE 'assessment_announcement'",

      'emails' => "SELECT * FROM PA_emails",

      'canvas' => "SELECT * FROM pa_canvas",

      'plancounts' => "SELECT p.acadplan,
                          SUM(CASE WHEN p.canvastags IS NULL OR p.canvastags = '' THEN 0 ELSE 1 END) AS tags,
                          SUM(CASE WHEN p.survey    IS NULL OR p.survey    = '' THEN 0 ELSE 1 END) AS surveys
                        FROM pa_activeplanedit p
                       GROUP BY p.acadplan",

      'scores' => "SELECT DISTINCT * FROM pa_rvw_scores ORDER BY review_ts DESC",

      'groups' => "SELECT * FROM pa_rvw_groups",

      'participants' => "SELECT * FROM pa_rvw_participants",

    ];

    $out = ['globalsAsOf' => $asOf];

    foreach ($queries as $key => $sql) {
      try {
        $out[$key] = $this->db->query($sql)->fetchAll();
      }
      catch (\Throwable $e) {
        $this->logger->error('DashboardGlobals query failed (@key): @msg', [
          '@key' => $key,
          '@msg' => $e->getMessage(),
        ]);
        $out[$key] = [];
      }
    }

    $out['surveys'] = aportal_file('public://reports/siteassets/surveys/Analytics_Surveys.json');
    return $out;
  }

}

/**
 * Read and return contents of a file, or NULL on failure. Logs errors.
 */
function aportal_file($uri) {
  $data = NULL;
  try {
    // Resolve the public stream wrapper to a real path.
    $path = \Drupal::service('file_system')->realpath($uri);
    if ($path && file_exists($path)) {
      $data = file_get_contents($path);
      // $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR); // array
    }
    else {
      \Drupal::logger('aportal')->warning('File not found at @path', ['@path' => $path ?: $uri]);
    }
  }
  catch (\Throwable $e) {
    \Drupal::logger('aportal')->error('load/parse failed: @msg', ['@msg' => $e->getMessage()]);
    $data = NULL;
  }
  return $data;
}
