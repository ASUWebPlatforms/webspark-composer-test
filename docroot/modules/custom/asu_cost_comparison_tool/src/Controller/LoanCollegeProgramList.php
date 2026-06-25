<?php

namespace Drupal\asu_cost_comparison_tool\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for college and program option endpoints.
 *
 * Expected database tables:
 *
 * asu_loan_colleges
 *   cid    INT (PK, auto-increment)
 *   name   VARCHAR(255)
 *   status TINYINT(1)  — 1 = active
 *
 * asu_loan_programs
 *   pid    INT (PK, auto-increment)
 *   cid    INT (FK -> asu_loan_colleges.cid)
 *   name   VARCHAR(255)
 *   status TINYINT(1)  — 1 = active
 */
class LoanCollegeProgramList extends ControllerBase {

  protected $database;
  protected $logger;

  public function __construct(Connection $database, LoggerChannelFactoryInterface $logger_factory) {
    $this->database = $database;
    $this->logger = $logger_factory->get('asu_cost_comparison_tool');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('logger.factory')
    );
  }

  /**
   * GET /api/cost-comparison-tool/colleges
   *
   * Returns all active colleges as JSON.
   *
   * Response shape:
   *   { "colleges": [ { "value": "1", "label": "College Name" }, ... ] }
   */
  /**
   * Returns academic programs filtered by campus and career as JSON.
   *
   * @param string|null $campus
   *   Campus code from the route, or NULL.
   * @param string|null $acad_career
   *   Academic career code from the route, or NULL.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing grouped program options.
   */
  public function getCollegeList($campus = NULL, $acad_career = NULL) {
    
    try {
      $result_ap = $this->database->query(
        "SELECT DISTINCT ac.acad_career, c.campus, ap.acad_prog, ap.descr
        FROM {asu_tuition_acad_career} AS ac
        JOIN {asu_tuition_acad_prog} AS ap
          ON ap.display = '1'
          AND ap.acad_career = ac.acad_career_group
        JOIN {asu_tuition_acad_prog_campus} AS apc
          ON ac.display = '1'
          AND apc.acad_prog = ap.acad_prog
        JOIN {asu_tuition_campus} AS c
          ON c.display = '1'
          AND c.campus = apc.campus
        WHERE (c.campus = :campus AND ap.acad_career = :acad_career)
        ORDER BY ac.acad_career, c.campus, ap.descr",
        [':campus' => $campus, ':acad_career' => $acad_career]
      );

     
      foreach ($result_ap->fetchAll() as $row) {
        $acad_prog[$row->acad_prog] = $row->descr;
      }
      asort($acad_prog); // Sort options alphabetically by description, keeping "Select One" at the top.
      $acad_prog = ['' => $this->t('Select One')] + $acad_prog;
      return new JsonResponse(['programs' => $acad_prog, 'counter' => count($acad_prog) - 1]);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch program options: @msg', ['@msg' => $e->getMessage()]);
      return new JsonResponse(['status' => 'error', 'message' => 'Failed to fetch program options'], 500);
    }
  }


  /**
  
   * Returns program fee options filtered by campus, academic career, and
   * academic program (college). All three parameters are required.
   *
   * Response shape:
   *   { "programs": { "": "None/Not Listed", "<fee_code>": "<descr>", ... } }
   *
   * @param string|null $campus
   *   Campus code from the route.
   * @param string|null $acad_career
   *   Academic career code from the route.
   * @param string|null $college
   *   Academic program code from the route.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing program fee options.
   */
  public function getProgramOptions($resident = NULL, $campus = NULL, $acad_career = NULL, $acad_prog = NULL, $acad_year = NULL) {
   
    if (empty($resident) || empty($campus) || empty($acad_career) || empty($acad_prog) || empty($acad_year)) {
      return new JsonResponse(
        ['status' => 'error', 'message' => 'All parameters are required.'],
        400
      );
    }

    try {
      $result_pf = $this->database->query(
        "SELECT DISTINCT ay.acad_year, r.residency, ac.acad_career, c.campus,
          apc.acad_prog, fc.fee_code, fc.descr
        FROM {asu_tuition_acad_year} AS ay
        JOIN {asu_tuition_residency} AS r
          ON ay.display = '1'
          AND r.display = '1'
        JOIN {asu_tuition_acad_career} AS ac
          ON ac.display = '1'
        JOIN {asu_tuition_campus} AS c
          ON c.display = '1'
        JOIN {asu_tuition_acad_prog_campus} AS apc
          ON apc.campus = c.campus
        JOIN {asu_tuition_acad_prog} AS ap
          ON ap.acad_prog = apc.acad_prog
          AND ap.acad_career = ac.acad_career
        JOIN {asu_tuition_fee_code} AS fc
          ON fc.acad_career = ac.acad_career
          AND fc.campus = c.campus
          AND fc.acad_prog = apc.acad_prog
          AND (fc.residency = r.residency OR fc.residency = '')
        JOIN {asu_tuition_rate_type} AS rt
          ON rt.rate_type = fc.fee_type
          AND rt.program_fee_dropdown = 1
        WHERE EXISTS (
          SELECT 'X' FROM {asu_tuition_fee_rate} AS fr
          WHERE fr.fee_code = fc.fee_code
          AND fr.acad_year = ay.acad_year
        )
          AND c.campus = :campus
          AND ac.acad_career = :acad_career
          AND apc.acad_prog = :college
          AND r.residency = :resident
          AND ay.acad_year = :acad_year
        ORDER BY ay.acad_year, r.residency, ac.acad_career, c.campus, apc.acad_prog, fc.descr",
        [':resident' => $resident, ':campus' => $campus, ':acad_career' => $acad_career, ':college' => $acad_prog, ':acad_year' => $acad_year]
      );

      foreach ($result_pf->fetchAll() as $row_pf) {
        $program_fee[$row_pf->fee_code] = $row_pf->descr;
      } 
     
     
     // unset($program_fee['UP4004']); // Remove "Duplicate" program from options.
    //  unset($program_fee['UP3007']); // Remove "Duplicate" program from options.
      asort($program_fee); // Sort options alphabetically by description, keeping "None/Not Listed" at the top.
      $program_fee = ['' => $this->t('Select One')] + $program_fee;
      return new JsonResponse(['programs' => $program_fee]);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch program options: @msg', ['@msg' => $e->getMessage()]);
      return new JsonResponse(['status' => 'error', 'message' => 'Failed to fetch program options'], 500);
    }
  }
}
