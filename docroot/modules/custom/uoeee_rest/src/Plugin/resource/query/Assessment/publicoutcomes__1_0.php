<?php

/**
 * @file
 * Contains Drupal\restful_rsc\Plugin\resource\query\Assessment
 */

 namespace Drupal\restful_rsc\Plugin\resource\query\Assessment;

 use Drupal\restful\Plugin\resource\ResourceDbQuery;
 use Drupal\restful\Plugin\resource\ResourceInterface;
 
 /**
  * Class publicoutcomes__1_0
  * @package Drupal\restful_rsc\Plugin\resource
  *
  * @Resource(
  *   name = "publicoutcomes:1.0",
  *   resource = "publicoutcomes",
  *   label = "Public Outcomes from Program Assessment Plans",
  *   description = "Exposes public outcomes from program assessment plans.",
  *   dataProvider = {
  *     "tableName": "pa_assessmentplans_public",
  *     "idColumn": "ID",
  *     "primary": "ID",
  *     "idField": "ID",
  *     "range": 500,
  *   },
  *   authenticationTypes = TRUE,
  *   authenticationOptional = TRUE,
  *   renderCache = {
  *     "render": true
  *   },
  *   formatter="json",
  *   majorVersion = 1,
  *   minorVersion = 0
  * )
  */

 class publicoutcomes__1_0 extends ResourceDbQuery implements ResourceInterface  {

    /**
   * {@inheritdoc}
   */
  protected function publicFields() {

    $public_fields['ID'] = array('property' => 'ID');
    $public_fields['acadplan'] = array('property' => 'acadplan');
    $public_fields['element'] = array('property' => 'element');
    $public_fields['outcome'] = array('property' => 'outcome');
    $public_fields['description'] = array('property' => 'description');
    
    return $public_fields;
  }

}