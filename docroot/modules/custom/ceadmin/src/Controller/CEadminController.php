<?php

namespace Drupal\ceadmin\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for CE admin pages and AJAX endpoints.
 */
class CEadminController extends ControllerBase {

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
   * Render the access denied page for users without CEADMIN access.
   *
   * @return array
   *   A render array containing the access denied message.
   */
  protected function accessDeniedPage(): array {
    return [
      '#markup' => '
          <h1>Access Required</h1>
          <p>You do not currently have access to CEADMIN.</p>
          <p>Please contact the course evaluation team at CourseEvals@exchange.asu.edu for assistance.</p>',
    ];
  }

  /**
   * Check whether the current user has any CEADMIN access record.
   *
   * @return bool
   *   TRUE when the current user is found in the CE_User access table,
   *   FALSE otherwise.
   */
  protected function userHasCeadminAccess(): bool {
    return has_access(\Drupal::currentUser()->getAccountName());
  }

  /**
   * Returns request payload either from form parameters or JSON body.
   *
   * This helper avoids direct use of PHP superglobals and satisfies the
   * code sniffer rule about using the RequestStack.
   *
   * @return array
   *   The decoded request data (associative array). Empty array if none.
   */
  private function getRequestData(): array {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return [];
    }

    // First, check typical request parameters (form-encoded).
    $data = $request->request->all();
    if (!empty($data)) {
      return $data;
    }

    // If empty, attempt to decode JSON body.
    $content = $request->getContent();
    if ($content !== NULL && $content !== '') {
      $decoded = json_decode($content, TRUE);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        // If the client wraps payload in a "body" key, return that to
        // stay compatible with existing clients.
        if (isset($decoded['body']) && is_array($decoded['body'])) {
          return $decoded['body'];
        }
        return $decoded;
      }
    }

    return [];
  }

  /**
   * Page load for CE admin app.
   *
   * @return array
   *   Render array for the CE admin app shell.
   */
  public function ceadminLoad() {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }

    $asurite = \Drupal::currentUser()->getAccountName();
    global $base_url;

    $logUnit = \Drupal::database()
      ->query("SELECT unit FROM CE_userlog WHERE asurite = :asurite AND DATEDIFF(CURRENT_DATE,lastmodified)<30", [':asurite' => $asurite])
      ->fetchObject();

    $queryTerm = \Drupal::database()
      ->query("SELECT t.* FROM CE_terms t INNER JOIN CE_userlog l ON t.code=l.term WHERE t.active='Y' AND l.asurite = :asurite AND DATEDIFF(CURRENT_DATE,l.lastmodified)<30", [':asurite' => $asurite])
      ->fetchAssoc();

    if ($queryTerm) {
      $term = $queryTerm['code'];
    }
    else {
      $queryTerm = \Drupal::database()
        ->query("SELECT * FROM CE_terms WHERE active='Y' ORDER BY CE_terms.code DESC LIMIT 1")
        ->fetchAssoc();
      $term = $queryTerm['code'];
    }

    // Get accessible units - check uoeee/admin status.
    $queryAdmin = \Drupal::database()
      ->query("SELECT * FROM CE_User WHERE asurite LIKE :asurite AND `role` IN ('UOEEE')", [':asurite' => $asurite]);
    $uoeee = FALSE;
    $admin_rows = count($queryAdmin->fetchAll());
    if ($admin_rows > 0) {
      $queryUnit = "SELECT department as unit FROM CE_Departments";
      $uoeee = TRUE;
    }

    // Check college access.
    $college_rows = 0;
    if ($admin_rows == 0) {
      $queryCollege = \Drupal::database()
        ->query("SELECT * FROM CE_User WHERE asurite LIKE :asurite AND `college` NOT LIKE '' AND `college` IS NOT NULL AND (unit LIKE '' OR unit IS NULL)", [':asurite' => $asurite]);
      $colleges = $queryCollege->fetchAll();
      $college_rows = count($colleges);
    }

    // Create unit query based on access level.
    if ($admin_rows > 0) {
      $queryUnit = "SELECT DISTINCT evaldepartment as unit FROM CE_Departments";
    }
    elseif ($college_rows > 0) {
      $carr = $colleges;
      $collegestr = "";
      foreach ($colleges as $c) {
        $collegestr .= "'" . $c->college . "',";
      }
      $collegestr = rtrim($collegestr, ',');
      $queryUnit = "SELECT DISTINCT evaldepartment as unit FROM CE_Departments WHERE college IN (" . $collegestr . ")";
    }
    else {
      $queryUnit = "SELECT * FROM CE_User WHERE asurite LIKE '" . $asurite . "'";
    }

    $units = \Drupal::database()->query($queryUnit)->fetchAll();
    $uarr = $units;
    $unitstr = "";
    $selUnit = "";
    foreach ($units as $u) {
      $unitstr .= "'" . $u->unit . "',";
      if (!$logUnit || $selUnit == '' || $logUnit->unit == $u->unit) {
        $selUnit = $u->unit;
      }
    }
    $unitstr = rtrim($unitstr, ',');

    $querySec = "SELECT DISTINCT sectionInfo, enrollment, recorded_enrollment, orig_instructors, orig_tas, ps_instructors, ps_tas, responserate FROM CE_json_Sections WHERE period LIKE :term AND (Department LIKE :selUnit OR EvalDepartment LIKE :selUnit)";
    $queryXlist = "SELECT DISTINCT sectionInfo, enrollment, recorded_enrollment, orig_instructors, orig_tas, ps_instructors, ps_tas, responserate FROM CE_CrossListSections WHERE SelUnit LIKE :selUnit AND period LIKE :term";

    $queries = [
      'sections' => "$querySec UNION $queryXlist",
      'instructors' => "SELECT attributes FROM CE_json_Instructors",
      'departments' => "SELECT * FROM CE_Departments",
      'surveys' => "SELECT s.college, s.rep, d.department, s.survey FROM CE_Surveys s INNER Join CE_Departments d ON s.rep=d.rep ;" ,
      'projects' => "SELECT * FROM CE_projects p WHERE p.project NOT LIKE'' and p.project IS NOT NULL ;" ,
      'log' => "SELECT * FROM CE_Log WHERE period LIKE :term" ,
      'terms' => "SELECT *, CONCAT(code,' - ',term,`period`) AS label FROM CE_terms WHERE active='Y'",
      'users' => "SELECT * FROM CE_User",
    ];

    $ceadmin = [];
    foreach ($queries as $key => $q) {
      // When queries need parameters, use simple substitution for term/selUnit.
      if (strpos($q, ':term') !== FALSE || strpos($q, ':selUnit') !== FALSE) {
        $ceadmin[$key] = \Drupal::database()->query(
          str_replace([':term', ':selUnit'], ['\'' . $term . '\'', '\'' . $selUnit . '\''], $q)
        )->fetchAll();
      }
      else {
        $ceadmin[$key] = \Drupal::database()->query($q)->fetchAll();
      }
    }

    $ceadmin['baseurl'] = $base_url;
    $ceadmin['asurite'] = $asurite;
    $ceadmin['units'] = $units;
    $ceadmin['uoeee'] = $uoeee;
    $ceadmin['selUnit'] = $selUnit;
    $ceadmin['selTerm'] = $queryTerm;
    $settings = ["ceadmin" => $ceadmin];

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['ceadmin/ceadmin-app'],
        'drupalSettings' => $settings,
      ],
      '#markup' => '<div id="ceadmin-wrapper"></div>',
    ];
  }

  /**
   * Sync endpoint: returns sections/log/surveys for a department & period.
   *
   * @param string $dept
   *   Department / unit.
   * @param string $period
   *   Term/period code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON payload with sections, log, surveys.
   */
  public function ceadminSync($dept, $period) {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }

    $asurite = \Drupal::currentUser()->getAccountName();
    $querySec = "SELECT DISTINCT sectionInfo, enrollment, recorded_enrollment, orig_instructors, orig_tas, ps_instructors, ps_tas, responserate FROM CE_json_Sections WHERE period LIKE :period AND (Department LIKE :dept OR EvalDepartment LIKE :dept)";
    $queryXlist = "SELECT DISTINCT sectionInfo, enrollment, recorded_enrollment, orig_instructors, orig_tas, ps_instructors, ps_tas, responserate FROM CE_CrossListSections WHERE SelUnit LIKE :dept AND period LIKE :period";
    $log = \Drupal::database()->query("SELECT * FROM CE_Log WHERE period LIKE :period", [':period' => $period]);
    $querysvy = \Drupal::database()->query("SELECT s.college, s.rep, d.department, s.survey FROM CE_Surveys s INNER Join CE_Departments d ON s.rep=d.rep");
    $sql = $querySec . " UNION " . $queryXlist;
    $selSections = \Drupal::database()->query($sql, [
      ':period' => $period,
      ':dept' => $dept,
    ])->fetchAll();

    $data = [
      'sections' => $selSections,
      'log'  => $log->fetchAll(),
      'surveys'  => $querysvy->fetchAll(),
    ];

    // Upsert CE_userlog.
    $sql = \Drupal::database()->upsert('CE_userlog')
      ->fields(['unit', 'term', 'asurite'])
      ->key('asurite')
      ->values([
        'unit' => $dept,
        'term' => $period,
        'asurite' => $asurite,
      ])
      ->execute();

    return new JsonResponse($data);
  }

  /**
   * Returns sections/log/surveys for a period.
   *
   * @param string $period
   *   Term/period code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing sections, log, and survey data.
   */
  public function ceadminSections($period) {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }
    $queries = [
      'sections' => "SELECT sectionInfo, enrollment, recorded_enrollment, ps_instructors, ps_tas, responserate FROM CE_json_Sections WHERE period LIKE :period",
      'log' => "SELECT * FROM CE_Log WHERE period LIKE :period" ,
      'surveys' => "SELECT s.college, s.rep, d.department, s.survey FROM CE_Surveys s INNER Join CE_Departments d ON s.rep=d.rep ;" ,
    ];

    $data = [];
    foreach ($queries as $key => $q) {
      if (strpos($q, ':period') !== FALSE) {
        $data[$key] = \Drupal::database()->query(str_replace(':period', '\'' . $period . '\'', $q))->fetchAll();
      }
      else {
        $data[$key] = \Drupal::database()->query($q)->fetchAll();
      }
    }

    return new JsonResponse($data);
  }

  /**
   * Handle POSTed changes/commands from the CE admin app.
   *
   * This endpoint expects either form-encoded POST parameters or JSON payloads.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Plain text "OK" on success.
   */
  public function ceadminPost() {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }
    $post = $this->getRequestData();
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');

    if (isset($post['field'])) {
      switch ($post['field']) {

        case 'sectionInfo':
          \Drupal::database()->update('CE_json_Sections')
            ->fields([
              'sectionInfo' => $post['sectionInfo'],
              'EvalDepartment' => $post['evaldepartment'],
              'modifiedby' => $post['modifiedby'],
              'modifieddate' => $post['modifieddate'],
            ])
            ->condition('section_Id', $post['section_id'])
            ->execute();
          break;

        case 'peoplesoftupdate':
          foreach ($post['sections'] as $sections) {
            \Drupal::database()->update('CE_json_Sections')
              ->fields([
                'sectionInfo' => $sections['sectionInfo'],
                'recorded_enrollment' => $sections['enrollment'],
                'modifiedby' => $post['modifiedby'],
                'modifieddate' => $post['modifieddate'],
              ])
              ->condition('section_Id', $sections['section_id'])
              ->execute();
          }
          break;

        case 'submit':
        case 'unlock':
          \Drupal::database()->insert('CE_Log')
            ->fields([
              'submit_ts' => $ts ,
              'asurite' => $post['asurite'],
              'period' => $post['period'],
              'evaldepartment' => $post['evaldepartment'],
              'target' => $post['evaldepartment'],
              'action' => $post['field'],
            ])
            ->execute();
          break;

        case 'attributes':
          \Drupal::database()->insert('CE_json_Instructors')
            ->fields([
              'asurite' => $post['asurite'],
              'attributes' => $post['attributes'],
            ])
            ->execute();
          break;
      }
    }

    if (isset($post['action'])) {
      switch ($post['action']) {
        case 'Copy':
          \Drupal::database()->insert('CE_json_Sections')
            ->fields([
              'period' => $post['period'],
              'section_Id' => $post['section_Id'],
              'sectionInfo' => $post['sectionInfo'],
              'enrollment' => $post['enrollment'],
              'Department' => $post['department'],
              'EvalDepartment' => $post['evaldepartment'],
              'modifiedby' => $post['modifiedby'],
              'modifieddate' => $post['modifieddate'],
            ])
            ->execute();
          break;

        case 'Remove':
          \Drupal::database()->delete('CE_json_Sections')
            ->condition('section_Id', $post['copies'], 'IN')
            ->execute();
          break;

        case 'addmissing':
          foreach ($post['missing'] as $m) {
            \Drupal::database()->upsert('CE_json_Instructors')
              ->fields([
                'asurite' => $m['asurite'],
                'attributes' => $m['data'],
              ])
              ->key('asurite')
              ->execute();
          }
          break;
      }
    }
    return new Response('OK', Response::HTTP_OK);
  }

  /**
   * Return instructor attributes.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing instructor attributes
   */
  public function ceadminInstructors() {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }

    $data = [];
    $data['instructors'] = \Drupal::database()->query("SELECT attributes FROM CE_json_Instructors;")->fetchAll();
    return new JsonResponse($data);
  }

  /**
   * Bulk add active persons (inserts rows into CE_json_Instructors).
   *
   * Expects 'people' as an array of asurites or person ids.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating success.
   */
  public function ceadminActiveperson() {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }
    $post = $this->getRequestData();

    if (empty($post['people']) || !is_array($post['people'])) {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'No people provided',
      ], 400);
    }

    $people = implode("','", $post['people']);
    $q = <<<EOT
INSERT INTO CE_json_Instructors SELECT ASU_ASURITE_ID as asurite, CONCAT(CHAR(123), '"', ASU_ASURITE_ID, '" : ', CHAR(123), '"fname" : "', FIRST_NM, '", "lname" : "', LAST_NM , '",
  "email" : "', ASU_EMAIL_ADDR , '", "personid" : "', PERSON_ID , '", "nickname" : ""', CHAR(125), CHAR(125) ) AS attributes
  FROM ASU_Person_Active a LEFT JOIN CE_json_Instructors i ON a.ASU_ASURITE_ID=i.asurite
  WHERE (ASU_ASURITE_ID IN ('$people') OR PERSON_ID IN ('$people')) AND i.asurite IS NULL ;
EOT;

    \Drupal::database()->query($q);

    $arr = [];
    $cnt = 0;
    while (count($arr) < count($post['people']) && $cnt < 500) {
      $arr = \Drupal::database()->query("SELECT asurite FROM `CE_json_Instructors` WHERE asurite IN ('$people');")->fetchAll();
      $cnt++;
      // Optionally add a small usleep() if needed to avoid tight loop.
    }

    return new JsonResponse(['status' => 'ok']);
  }

  /**
   * Post a message to Slack via chat.postMessage.
   *
   * Expects 'message' in payload.
   */
  public function slackpost() {
    if (!$this->userHasCeadminAccess()) {
      return $this->accessDeniedPage();
    }

    $post = $this->getRequestData();
    $result = "";
    $channel = 'C017KJ5MUQ2';
    $token = 'xoxb-664923153986-1256246461685-BA2LQJsm4bz46hboJWP8yotH';
    $data = json_encode([
      "token" => $token ,
      "channel" => $channel,
      "text" => $post['message'] ?? '',
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

    $result = \Drupal::httpClient()->post("https://slack.com/api/chat.postMessage", $options);
    return new JsonResponse($result);
  }

  /**
   * Lookup Slack IDs by email and persist to CE_User.slackid.
   *
   * Note: This uses the Slack token and will log results with debug(); adapt as needed.
   */
  public function setslackid() {
    $token = 'xoxb-664923153986-1256246461685-BA2LQJsm4bz46hboJWP8yotH';
    $options = [
      'method' => 'GET',
      'timeout' => 15,
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer $token",
      ],
    ];
    $asuemails = \Drupal::database()->query("SELECT DISTINCT asurite, CONCAT(asurite,'@asu.edu') as asuemail FROM CE_User WHERE slackid is NULL OR slackid =''")->fetchAll();
    $result = NULL;
    foreach ($asuemails as $e) {
      $email = $e->asuemail;
      $result = \Drupal::httpClient()->get("https://slack.com/api/users.lookupByEmail?email=$email&token=$token");
      // debug($result);
      if (isset($result['ok']) && $result['ok']) {
        \Drupal::database()->update('CE_User')
          ->fields([
            'slackid' => $result['user']['id'],
          ])
          ->condition('asurite', $e->asurite)
          ->execute();
      }
    }
    return new JsonResponse($result);
  }

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
  $query = \Drupal::database()->select('CE_User', 'u');
  $query->addField('u', 'asurite');
  $query->condition('u.asurite', $asurite);
  $query->range(0, 1);
  return (bool) $query->execute()->fetchField();
}
