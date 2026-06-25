<?php

namespace Drupal\asu_graduate_faculty;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;

class GraduateQueryService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new YourService.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function getDegreeOptions(string $empty_option) {
    $options = [];
    $query = $this->database->select('gf_plancodes', 't')
                            ->fields('t', ['plancode', 'plan_descr']);
    $result = $query->execute();

    if($empty_option) $options[''] = $empty_option;

    foreach ($result as $record) {
      $options[$record->plancode] = $record->plan_descr;
    }

    return $options;
  }

  public function getCategoryOptions(string $empty_option) {
    $options = [];
    $query = $this->database->select('gf_categorylist', 't')
                            ->fields('t', ['asu_plan_cat_cd', 'descr100'])
                            ->orderBy('descr100');
    $result = $query->execute();

    if($empty_option) $options[''] = $empty_option;

    foreach ($result as $record) {
      $options[$record->asu_plan_cat_cd] = $record->descr100;
    }

    return $options;
  }

  public function getFacultyByPlan($plancode) {
    $database = $this->database;

    // First part of the union
    $query1 = $database->select('gf_data', 'a');
    $query1->fields('a');
    $query1->addExpression("'100'", 'relevance');
    $query1->condition('plancode', $plancode);
    $query1->condition('employee_flag', 'Y');

    // Second part of the union
    $query2 = $database->select('gf_data', 'a1');
    $query2->fields('a1');
    $query2->addExpression("'50'", 'relevance');
    $query2->condition('plancode', $plancode);
    $query2->condition('employee_flag', 'N');

    // Combine queries with a UNION
    $query = $database->select($query1->union($query2), 'b');

    // Group by eid (employee id)
    $query->addField('b', 'eid');

    // Add GROUP_CONCAT for each field to concatenate multiple values per eid
    $fields = ['emplid', 'last_name', 'first_name', 'email_addr', 'phone', 'oprid',
      'job_title', 'company_name', 'highest_lvl_approval', 'plancode',
      'plan_descr', 'category', 'website', 'employee_flag', 'relevance'
    ];

    foreach ($fields as $field) {
      $query->addExpression("GROUP_CONCAT(DISTINCT b.$field SEPARATOR ', ')", $field);
    }

    $query->groupBy('b.eid');
    $query->orderBy('relevance', 'ASC');
    $query->orderBy('last_name', 'ASC');

    // Execute the query
    $result = $query->execute();
    return $result->fetchAll();
  }

  public function getFacultyByPerson(array $terms = null){
    $database = $this->database;

    $relevances = [
        100 => 'any exact match and employee flag',
        90 => 'any exact match inverted and employee flag',
        80 => 'any exact match',
        70 => 'any exact match inverted',
        60 => 'name or last name match and employee flag',
        50 => 'name or last name match',
        40 => 'partial matches and employee flag',
        30 => 'partial matches'
    ];

    $queries = [];

    foreach($relevances as $number => $relevance){
        $query = $database->select('gf_data', 'a');
        // Explicit fields
        $query->addField('a', 'eid');
        $query->addField('a', 'emplid');
        $query->addField('a', 'first_name');
        $query->addField('a', 'last_name');
        $query->addField('a', 'email_addr');
        $query->addField('a', 'phone');
        $query->addField('a', 'oprid');
        $query->addField('a', 'job_title');
        $query->addField('a', 'company_name');
        $query->addField('a', 'highest_lvl_approval');
        $query->addField('a', 'plancode');
        $query->addField('a', 'plan_descr');
        $query->addField('a', 'category');
        $query->addField('a', 'website');
        $query->addField('a', 'employee_flag');
        $query->addExpression($number, 'relevance');

        $orGroup = $query->orConditionGroup();

        if ($number == 100){
          $query->where("CONCAT(a.first_name, ' ', a.last_name) = :term", [':term' => implode(' ', $terms)]);
          $query->condition('employee_flag', 'Y');
        } elseif ($number == 90){
          $query->where("CONCAT(a.last_name, ' ', a.first_name) = :term", [':term' => implode(' ', $terms)]);
          $query->condition('employee_flag', 'Y');
        } elseif ($number == 80){
          $query->where("CONCAT(a.first_name, ' ', a.last_name) = :term", [':term' => implode(' ', $terms)]);
        } elseif ($number == 70){
          $query->where("CONCAT(a.last_name, ' ', a.first_name) = :term", [':term' => implode(' ', $terms)]);
        }

        foreach ($terms as $index => $term) {
          if ($number == 60){
            $orGroup->condition('first_name', $term)
                    ->condition('last_name', $term);
            $query->condition($orGroup);
            $query->condition('employee_flag', 'Y');
          } elseif ($number == 50){
            $orGroup->condition('first_name', $term)
                    ->condition('last_name', $term);
            $query->condition($orGroup);
          } elseif ($number == 40){
            $orGroup->condition('first_name', '%'.$term.'%', 'LIKE')
                    ->condition('last_name', '%'.$term.'%', 'LIKE');
            $query->condition($orGroup);
            $query->condition('employee_flag', 'Y');
          } elseif ($number == 30){
            $orGroup->condition('first_name', '%'.$term.'%', 'LIKE')
                    ->condition('last_name', '%'.$term.'%', 'LIKE');
            $query->condition($orGroup);
          }
        }

        $queries[] = $query;
    }

    // Combine all queries with UNION
    $finalQuery = null;
    foreach ($queries as $subQuery) {
        if ($finalQuery) {
            $finalQuery->union($subQuery, 'UNION ALL');
        } else {
            $finalQuery = $subQuery;
        }
    }

    if (!$finalQuery) {
        return [];
    }

    // Outer query with aggregation
    $finalQuery = $database->select($finalQuery, 'b');
    $finalQuery->addField('b', 'eid');
    $finalQuery->addExpression('MAX(emplid)', 'emplid');
    $finalQuery->addExpression('MAX(first_name)', 'first_name');
    $finalQuery->addExpression('MAX(last_name)', 'last_name');
    $finalQuery->addExpression('MAX(email_addr)', 'email_addr');
    $finalQuery->addExpression('MAX(phone)', 'phone');
    $finalQuery->addExpression('MAX(oprid)', 'oprid');
    $finalQuery->addExpression('MAX(job_title)', 'job_title');
    $finalQuery->addExpression('MAX(company_name)', 'company_name');
    $finalQuery->addExpression('MAX(highest_lvl_approval)', 'highest_lvl_approval');
    $finalQuery->addExpression('MAX(plancode)', 'plancode');
    $finalQuery->addExpression('GROUP_CONCAT(plan_descr)', 'plans');
    $finalQuery->addExpression('MAX(category)', 'category');
    $finalQuery->addExpression('MAX(website)', 'website');
    $finalQuery->addExpression('MAX(employee_flag)', 'employee_flag');
    $finalQuery->addExpression('MAX(relevance)', 'relevance');

    $finalQuery->groupBy('eid');
    $finalQuery->orderBy('relevance', 'DESC');
    $finalQuery->orderBy('last_name');

    $result = $finalQuery->execute();
    return $result->fetchAll();
  }

  function getPlanDataByCategory($cat) {
    $database = $this->database;
    $query = $database->select('gf_categorylist', 'a');
    $query->distinct();
    $query->join('gf_data', 'b', 'a.descr100 = b.category');
    $query->fields('b', ['plancode', 'plan_descr', 'category']);
    $query->condition('a.asu_plan_cat_cd', $cat);
    $query->orderBy('b.plan_descr');
    $result = $query->execute();
    return $result->fetchAll();
  }

  public function getDegreeFromPlan($plancode){
    $query = $this->database->select('gf_plancodes', 'pc');
    $query->fields('pc', ['plan_descr']);
    $query->condition('plancode', $plancode);

    $result = $query->execute();
    $plan_descr = $result->fetchAssoc();
    return $plan_descr['plan_descr'];
  }

  public function getDegreeFromEid($eid){
    $query = $this->database->select('gf_data', 'a');
    $query->fields('a', ['plancode', 'plan_descr', 'highest_lvl_approval']);
    $query->condition('eid', $eid);
    $query->groupBy('plancode');
    $query->groupBy('plan_descr');
    $query->groupBy('highest_lvl_approval');
    $query->orderBy('highest_lvl_approval');
    $result = $query->execute();
    return $result->fetchAll();
  }

}
