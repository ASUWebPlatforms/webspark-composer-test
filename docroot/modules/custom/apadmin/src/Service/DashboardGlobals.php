<?php

namespace Drupal\apadmin\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface; 
use Psr\Log\LoggerInterface;

/**
 * Builds and caches global (non user-specific) datasets for the landing page.
 */
final class DashboardGlobals {

  // How long to cache (seconds). Adjust to taste.
  public const TTL = 180;

  public function __construct(
    private CacheBackendInterface $cache,
    private Connection $db,
    private TimeInterface $time,  
    private LoggerInterface $logger,
  ) {}

  /**
   * Return the current global snapshot, building it if needed.
   */
  public function get(bool $fresh = FALSE): array {
    $cid = 'apadmin:dashboard:globals';

    // Return cached unless caller forces a refresh.
    if (!$fresh && ($item = $this->cache->get($cid))) {
      return $item->data;
    }

    // Build fresh data.
    $data = $this->build();

    // Store in cache for next time.
    $this->cache->set(
      $cid,
      $data,
      $this->time->getRequestTime() + self::TTL,
      ['apadmin:dashboard']
    );

    return $data;
  }

  /**
   * Build the global snapshot (no user/asurite inputs here).
   */
  private function build(): array {
    $asOf = $this->time->getRequestTime();

    // --- Queries copied from your controller that are NOT user-specific. ---
    // If you later need to trim/limit any of these, do it here once.
    $queries = apinfo() ;

    $out = ['globalsAsOf' => $asOf];

    foreach ($queries as $key => $sql) {
      try {
        $out[$key] = $this->db->query($sql)->fetchAll();
      } catch (\Throwable $e) {
        $this->logger->error('DashboardGlobals query failed (@key): @msg', [
          '@key' => $key,
          '@msg' => $e->getMessage(),
        ]);
        $out[$key] = [];
      }
    }


    return $out;
  }
}


function apinfo() {

  $queries = array(
    'apsettings' => "SELECT * FROM AP_settings",
    'admins' => "SELECT * FROM AP_user",
    'asupeople' => "SELECT DISTINCT t.asurite, t.lastname, t.firstname, t.email, access AS lastvisit, usertext  
                        FROM pa_usernamesemails t LEFT JOIN users_field_data tt ON t.asurite=tt.`name` 
                    UNION 
                    SELECT ASU_ASURITE_ID AS asurite, LAST_NM AS lastname, FIRST_NM as firstname, ASU_EMAIL_ADDR AS email, NUll as lastvisit,
                        CONCAT(FIRST_NM, ' ', LAST_NM, ' (', ASU_ASURITE_ID, ')' ) AS usertext
                        FROM ASU_Person_Active ap LEFT JOIN pa_usernamesemails ue ON ap.ASU_ASURITE_ID=ue.asurite WHERE ue.asurite IS NULL;",
    'users' => "SELECT t.asurite, t.lastname, t.firstname, t.email, access AS lastvisit, usertext
                  FROM pa_usernamesemails t LEFT JOIN users_field_data tt
                  ON t.asurite=tt.`name` ;",
    'allaccess' => "SELECT asurite, element, elementtype, ghost FROM PA_User;",
    'delegates' => "SELECT * FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y'  UNION
                    SELECT asurite, email, 'LA_UG', status, id, SendEmail FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y' AND 
                    college IN ('LB_UG','LC_UG','LD_UG','LH_UG','LM_UG') UNION
                    SELECT asurite, email, 'LA_GR', status, id,SendEmail FROM PA_Delegates WHERE status='Delegate' AND SendEmail LIKE 'Y' AND 
                    college IN ('LB_GR','LC_GR','LD_GR','LH_GR','LM_GR') ",
    'delegateassignees' => "SELECT * FROM PA_DelegateAssign",
    'collegenames' => "SELECT DISTINCT College AS college, CollegeName AS name FROM PA_CollegeNames",
    'acadunits' => "SELECT DISTINCT 'acadplan' as element, department as parent, acadplan as acadcode, pp.TRNSCR, CONCAT(acadplan, ' (', pp.TRNSCR, ')' ) as unittext, pp.progstatus FROM PA_Program pp UNION 
          SELECT DISTINCT 'department' as element, Left(college,2) as parent, department as acadcode, department as descr, department as unittext, '' as progstatus FROM PA_Program  UNION 
          SELECT DISTINCT 'college' as element, '' as parent, pp.college as acadcode, CollegeName as descr, CONCAT(pp.college, ' (', CollegeName, ')' ) as unittext, '' as progstatus FROM PA_Program pp JOIN PA_CollegeNames cn ON LEFT(pp.college,2)=cn.college ;",
    'apr' => "SELECT * FROM PA_APR",
    'requestslog' => "SELECT r.*, usertext FROM AP_requests r LEFT JOIN pa_usernamesemails u ON r.requestby=u.Asurite ",
    'academicplaninfo' => "SELECT *, academicplancode AS acadplan FROM AP_academicplaninfo",
    'statuscurrent' => "SELECT * FROM ap_statuscurrent",
    'statuslog' => "SELECT * FROM AP_statuslog",
    'customdescr' => "SELECT * FROM AP_customdescr",
    'customdept' => "SELECT * FROM AP_customdept",
    'customdisagg' => "SELECT * FROM AP_customdisagg;",
    'customparent' => "SELECT * FROM AP_customparent WHERE acadplan NOT LIKE '%-unlinked-%'",
    'handbook' => "SELECT * FROM PA_NodeContentText",
    'estplanapps' => "SELECT planapp as acadplan, 'programs' as need FROM AP_statuslog l JOIN PA_Program p ON l.planapp=p.acadplan UNION
                          SELECT DISTINCT l.planapp as acadplan, 'plans' as need FROM AP_statuslog l JOIN (SELECT DISTINCT acadplan FROM PA_AssessmentPlans) p ON l.planapp=p.acadplan ;",
    'archived' => "SELECT COUNT(*) as archived, 'Program' as archive FROM PA_AssessmentReports_Archive_Program t JOIN AP_settings f ON t.submissionyear=f.val WHERE f.apsetting='submissionyear' UNION
        SELECT COUNT(*) as archived, 'Info' as archive FROM PA_AssessmentReports_Archive_Info t JOIN AP_settings f ON t.submissionyear=f.val WHERE f.apsetting='submissionyear' UNION
        SELECT COUNT(*) as archived, 'Open' as archive FROM PA_AssessmentReports_Archive_Open t JOIN AP_settings f ON t.submissionyear=f.val WHERE f.apsetting='submissionyear' UNION
        SELECT COUNT(*) as archived, 'Results' as archive FROM PA_AssessmentReports_Archive_Results t JOIN AP_settings f ON t.submissionyear=f.val WHERE f.apsetting='submissionyear';",
    'reports' => "SELECT COUNT(DISTINCT acadplan) as reports, 'info' as shells FROM PA_CurrentReports_Info UNION
        SELECT COUNT(*) as reports, 'open' as shells FROM PA_CurrentReports_Open UNION
        SELECT COUNT(DISTINCT acadplan) as reports, 'results' as shells FROM PA_CurrentReports_Results ;",
    'canvaspilot' => "SELECT CASE WHEN acadplan IS NULL OR acadplan = '' THEN department ELSE acadplan END as units, acadplan, department FROM pa_canvaspilot;",
    'plancounts' => "SELECT p.acadplan,
                      SUM(CASE WHEN p.canvastags IS NULL OR p.canvastags = '' Then 0 ELSE 1 END) AS tags,
                      SUM(CASE WHEN p.survey IS NULL OR p.survey = '' Then 0 ELSE 1 END) AS surveys
                      FROM pa_activeplanedit p
                      GROUP BY p.acadplan ;",
    'programs' => "SELECT pa.*, SUBSTRING_INDEX(pa.College, '_', 1) AS CollegeCode, cn.la_division, pb.publicready, po.publicmismatch, 
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
                  pra ON pa.acadplan=pra.acadplan" ,
    'planelements' => "SELECT acadplan, SUM(CASE WHEN element = 'Outcome' THEN 1 ELSE 0 END) AS outcomes, SUM(CASE WHEN element = 'Measure' THEN 1 ELSE 0 END) AS measures
                FROM pa_activeplanedit GROUP BY acadplan ;"
  );

  return $queries ;
}