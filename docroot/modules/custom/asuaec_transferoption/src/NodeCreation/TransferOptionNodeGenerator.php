<?php

namespace Drupal\asuaec_transferoption\NodeCreation;

use Drupal\node\Entity\Node;

class TransferOptionNodeGenerator {

  public function generateTransferOptionNode($plancode, $path) {
    // Connect to DB
    $database = \Drupal::database();
    $query = $database->select('asu_transferoption_main', 'm');
    // Add extra detail to this query object: a condition, fields and a range.
    $query->condition('m.AcadPlan', $plancode, '=');
//    $query->fields('m', ['AcadPlan', 'AcadProg', 'Descr100', 'Degree', 'CollegeDescr100', 'DiplomaDescr', 'CampusStringArray', 'AsuCareerOpp', 'planCatDescr']);
//    $query->fields('m', ['AcadPlan', 'Descr100', 'Degree', 'CollegeDescr100', 'DiplomaDescr', 'CampusStringArray', 'AsuCareerOpp', 'planCatDescr']);
    $query->fields('m', ['Descr100']);
//    $query->range(0, 50);
    $result = $query->execute();
    $data = array();
    foreach ($result as $record) {
//      $data['AcadPlan'] = $record->AcadPlan;
//      $data['AcadProg'] = $record->AcadProg;
      $data['Descr100'] = $record->Descr100;
//      $data['Degree'] = $record->Degree;
//      $data['CollegeDescr100'] = $record->CollegeDescr100;
//      $data['DiplomaDescr'] = $record->DiplomaDescr;
//      $data['CampusStringArray'] = $record->CampusStringArray;
//      $data['AsuCareerOpp'] = $record->AsuCareerOpp;
//      $data['planCatDescr'] = $record->planCatDescr;
    }
//    $data = $result->fetchObject();
//    kint($data);


    // Take advantage of the UTO RFI module for node generation.

//    $path = '/bachelors-degrees/majorinfo/' . $data["AcadPlan"] . '/undergrad/false/1928';
      $node = Node::create(['type' => 'degree_detail_page']);
//      $degree_query = $this->degreeSearchClient->getDegreeByAcadPlan($data['AcadPlan'];
      $title = isset($data['Descr100']) ? $data['Descr100'] : $plancode;
      $node->set('title', $title);
      $node->set('field_degree_detail_acadplancode', $plancode);
      $node->status = 1;
      $node->enforceIsNew();
      $node->set('path', $path);
      $node->save();


//    // Create a new node
//    $node = Node::create(['type' => 'transfer_option']);
//    $node -> set('title', $data['AcadPlan']);
//    $node -> set('field_to_acadplan', $data['AcadPlan']);
//    $node -> set('field_to_acadprog', $data['field_to_acadprog']);
//    $node -> set('body', [
//      'value' => 'test test ..',
//      'format' => 'basic_html',
//    ]);
//    $node -> enforceIsNew();
//    $node -> save();
    return $node;

  }

}