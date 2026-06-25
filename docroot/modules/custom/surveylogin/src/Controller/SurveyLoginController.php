<?php

namespace Drupal\surveylogin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Controller for survey login redirects and verification.
 */
class SurveyLoginController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * SurveyLoginController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Factory method for container injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return static
   */
  public static function create($container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Entry point: verify permissions / direct to tracking or login.
   *
   * @param string $survey
   *   The survey/project machine name.
   *
   * @return Drupal\Core\Routing\TrustedRedirectResponse|null
   *   Redirect to appropriate URL or null.
   */
  public function surveyLoginLoad(string $survey) {

    $asurite = \Drupal::currentUser()->getAccountName();

    if ($this->trackingViewer($survey, $asurite)) {
      // Redirect to tracking page.
      return new TrustedRedirectResponse(
        Url::fromUserInput("/tracking/$survey")->toString()
      );
    }

    // If not a tracking viewer, verify file distribution or cohort and then redirect.
    $fileRedirect = $this->verifyFile($survey, $asurite);
    if ($fileRedirect instanceof TrustedRedirectResponse) {
      return $fileRedirect;
    }

    return $this->verifyCohort($survey, $asurite);
  }

  /**
   * Check whether a user is an admin or viewer for a project.
   *
   * @param string $project
   *   Project name.
   * @param string $asurite
   *   Asurite id.
   *
   * @return bool
   *   TRUE when user is viewer/admin for the project.
   */
  protected function trackingViewer(string $project, string $asurite): bool {
    $fileProject = $project;
    $adminProjects = [$project];

    switch ($project) {
      case 'connections':
      case 'fys-connections':
      case 'asuh-connections':
        $fileProject = 'connections';
        $adminProjects = ['connections', 'fys-connections', 'asuh-connections', 'transfer', 'dept-transfer'];
        break;

      case 'transfer':
      case 'dept-transfer':
        $fileProject = 'transfer';
        $adminProjects = ['connections', 'fys-connections', 'asuh-connections', 'transfer', 'dept-transfer'];
        break;

      default:
        // Keep defaults set above.
        break;
    }

    $db = $this->database;

    // Check admins.
    $selectAdmin = $db->select('pt_admins', 'a')
      ->fields('a', ['admin_id'])
      ->condition('project_id', $adminProjects, 'IN')
      ->condition('admin_id', $asurite, 'LIKE');

    // Check viewers for fileProject.
    $selectViewer = $db->select('pt_participants', 'p')
      ->fields('p', ['viewer_id'])
      ->condition('project_id', $fileProject, '=')
      ->condition('viewer_id', $asurite, 'LIKE');

    // UNION distinct results and execute.
    $selectAdmin->union($selectViewer, 'DISTINCT');
    $result = $selectAdmin->execute();
    $rows = $result->fetchAll();

    return !empty($rows);
  }

  /**
   * Verify cohort membership and redirect to appropriate Qualtrics link.
   *
   * @param string $survey
   *   Survey/project name.
   * @param string $asurite
   *   Asurite id.
   *
   * @return Drupal\Core\Routing\TrustedRedirectResponse|null
   *   Redirect to the proper survey URL.
   */
  protected function verifyCohort(string $survey, string $asurite): TrustedRedirectResponse {

    // Ensure Drupal page caches won't return a stale redirect for this request.
    \Drupal::service('page_cache_kill_switch')->trigger();

    $db = $this->database;

    $cohortArray = [$survey];
    switch ($survey) {
      case 'connections':
      case 'transfer':
        $cohortArray = ['transfer', 'connections', 'veterans'];
        break;

      default:
        $cohortArray = [$survey];
        break;
    }

    $select = $db->select('sv_cohort', 's')
      ->fields('s', ['cohort'])
      ->condition('cohort', $cohortArray, 'IN')
      ->condition('asurite', $asurite, 'LIKE');

    $resultRows = $select->execute()->fetchAll();

    if (!empty($resultRows)) {
      $cohortInfo = reset($resultRows);
      $cohortName = $cohortInfo->cohort;
      $surveyInfo = $db->select('Qualtrics_SurveyLogin', 'q')
        ->fields('q')
        ->condition('survey', $cohortName, '=')
        ->execute()
        ->fetchObject();
    }
    else {
      $nonCohort = $survey . 'noncohort';
      $surveyInfo = $db->select('Qualtrics_SurveyLogin', 'q')
        ->fields('q')
        ->condition('survey', $nonCohort, '=')
        ->execute()
        ->fetchObject();
    }

    if ($survey === 'fdsk') {
      $passUrl = $surveyInfo->qualtrics_link . '?method=knowledge';
    }
    else {
      $passUrl = $surveyInfo->qualtrics_link
        . '?asurite=' . rawurlencode($asurite)
        . '&method=login';
    }

    return new TrustedRedirectResponse($passUrl);
  }

  /**
   * Verify if a file distribution entry exists and redirect to distribution if so.
   *
   * @param string $project
   *   Project name.
   * @param string $asurite
   *   Asurite id.
   *
   * @return Drupal\Core\Routing\TrustedRedirectResponse|null
   *   TrustedRedirectResponse if a file distribution exists, otherwise NULL.
   */
  protected function verifyFile(string $project, string $asurite): ?TrustedRedirectResponse {
    $db = $this->database;

    $cohort = [$project];
    switch ($project) {
      case 'connections':
      case 'transfer':
        $cohort = ['transfer', 'connections', 'veterans'];
        break;

      default:
        $cohort = [$project];
        break;
    }

    // Use query builder to handle the IN clause.
    $select = $db->select('UOEEE_FileDistribution_Log', 'f')
      ->fields('f', ['project', 'fileinfo'])
      ->condition('project', $cohort, 'IN')
      ->condition('useridentifier', $asurite, 'LIKE')
      ->range(0, 1);

    $verifyInfo = $select->execute()->fetchObject();

    if ($verifyInfo) {
      return new TrustedRedirectResponse('/fdist/' . $verifyInfo->project);
    }

    return NULL;
  }

  /**
   * Redirect a graduating survey request to appropriate Qualtrics link.
   *
   * @param string $survey
   *   Survey name.
   * @param string $asurite
   *   Asurite id.
   * @param string $reportRequestNumber
   *   Report request number.
   * @param string $trm
   *   Term (short).
   * @param string $pln
   *   Plan.
   * @param string $clg
   *   College.
   * @param string $cmp
   *   Campus.
   *
   * @return Drupal\Core\Routing\TrustedRedirectResponse|null
   *   Redirect response to the survey.
   */
  public function graduatingLoad(string $survey, string $asurite, string $reportRequestNumber, string $trm, string $pln, string $clg, string $cmp): TrustedRedirectResponse {
    $db = $this->database;

    $surveyInfo = $db->select('Qualtrics_SurveyLogin', 'q')
      ->fields('q')
      ->condition('survey', $survey, '=')
      ->execute()
      ->fetchObject();

    if (strpos((string) $surveyInfo->qualtrics_link, 'qualtrics') !== FALSE) {
      $passUrl = $surveyInfo->qualtrics_link
        . '?asurite=' . rawurlencode($asurite)
        . '&ReportRequestNumber=' . rawurlencode($reportRequestNumber)
        . '&trm=' . rawurlencode($trm)
        . '&pln=' . rawurlencode($pln)
        . '&clg=' . rawurlencode($clg)
        . '&cmp=' . rawurlencode($cmp);
    }
    else {
      $passUrl = $surveyInfo->qualtrics_link
        . '?custom1=' . rawurlencode($asurite)
        . '&custom2=' . rawurlencode($reportRequestNumber)
        . '&custom3=' . rawurlencode($trm)
        . '&custom4=' . rawurlencode($pln)
        . '&custom5=' . rawurlencode($clg)
        . '&custom6=' . rawurlencode($cmp);
    }

    return new TrustedRedirectResponse($passUrl);
  }

  /**
   * Dean evaluation load redirect.
   *
   * @param string $deanName
   *   Dean short name.
   *
   * @return Drupal\Core\Routing\TrustedRedirectResponse|null
   *   Redirect to the dean survey.
   */
  public function deansEvalLoad(string $deanName): TrustedRedirectResponse {
    $asurite = \Drupal::currentUser()->getAccountName();
    $db = $this->database;

    $surveyInfo = $db->select('Qualtrics_SurveyLogin', 'q')
      ->fields('q')
      ->condition('survey', 'dean_' . $deanName, '=')
      ->execute()
      ->fetchObject();

    if (strpos((string) $surveyInfo->qualtrics_link, 'qualtrics') !== FALSE) {
      $passUrl = $surveyInfo->qualtrics_link . '?asurite=' . rawurlencode($asurite);
    }
    else {
      $passUrl = $surveyInfo->qualtrics_link . '?custom1=' . rawurlencode($asurite);
    }

    return new TrustedRedirectResponse($passUrl);
  }

}
