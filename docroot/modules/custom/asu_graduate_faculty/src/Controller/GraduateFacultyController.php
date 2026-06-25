<?php

namespace Drupal\asu_graduate_faculty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\asu_graduate_faculty\GraduateQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GraduateFacultyController extends ControllerBase {

  protected $graduateQueryService;

  public function __construct(GraduateQueryService $graduateQueryService) {
    $this->graduateQueryService = $graduateQueryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asu_graduate_faculty.graduate_query_service')
    );
  }

  public function person($person) {

    $rows = [];
    $heads = [];

    $terms = explode(' ', $person);
    $results = $this->graduateQueryService->getFacultyByPerson($terms);
    if(!empty($results)){
      $rows =  $this->massageRows($results, 'person');
      $heads = array_keys($rows[0]);
    }

    $count = count($rows) > 0 ? count($rows) : 'Your search did not return any ';

    return [
      '#theme' => 'asu_graduate_faculty_table',
      '#rows' => $rows,
      '#heads' => $heads,
      '#title' => 'ASU Graduate Faculty',
      '#searchSummary' => $count . ' results for <strong>' . $person . '</strong>.  <a href="/graduate-faculty">Try another search.</a>'
    ];
  }

  public function degree($degree) {

    $results = $this->graduateQueryService->getFacultyByPlan($degree);
    $rows =  $this->massageRows($results);
    $heads = array_keys($rows[0]);
    $title = $this->graduateQueryService->getDegreeFromPlan($degree);

    return [
      '#theme' => 'asu_graduate_faculty_table',
      '#rows' => $rows,
      '#heads' => $heads,
      '#title' => $title,
      '#searchSummary' => count($rows) . ' results for <strong>' . $title . '</strong>.  <a href="/graduate-faculty">Try another search.</a>'
    ];
  }

  public function category($category) {

    $results = $this->graduateQueryService->getPlanDataByCategory($category);

    $title = $results[0]->category;

    return [
      '#theme' => 'asu_graduate_faculty_list',
      '#rows' => $results,
      '#title' => $title,
      '#searchSummary' => '<a href="/graduate-faculty">Try another search.</a><br>The following degrees are in the <strong>' . $title . '</strong>:'
    ];
  }

  private function massageRows($results, $type = null){
    $output = [];
    foreach($results as $row){
      $name = $row->last_name . ', ' . $row->first_name;
      $endorsement = (isset($type) && $type == 'person') ? 'View' : $row->highest_lvl_approval;
      $output[] = [
        'name' => $row->employee_flag == 'Y' ? ['name' => $name, 'link' => $row->eid] : $name,
        'title' => $row->job_title,
        'endorsement' => [$row->eid, $endorsement],
        'email address' => $row->email_addr,
        'phone' => $row->phone,
        //'relevance' => $row->relevance // Debug only
      ];
    }
    return $output;
  }
}
