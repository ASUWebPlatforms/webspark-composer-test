<?php
/**
 * @file
 * Contains \Drupal\asu_cb_views\Form\customTaxonomy.
 */

namespace Drupal\asu_allowed_values\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Database;


class customTaxonomy extends ConfigFormBase {

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'terms_admin_settings';
  }
  
  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'customTerms.settings',
    ];
  }
  
  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('customTerms.settings');

    $form['set_taxonomy_terms']['academic_plans'] = array(
        '#type' => 'submit',
        //'#title' => 'Insert Academic plan data table',
        '#value' => 'Insert Academic plan data table',
        '#submit' => array([$this, 'academic_plan_terms']),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#attributes' => array(
          'style' => array('margin: 5%;')
          ),
      );
    
     $form['set_taxonomy_terms']['campus'] = array(
        '#type' => 'submit',
        //'#title' => 'Set Campus taxonomy terms',
        '#value' => 'Insert Campus data table',
        '#submit' => array([$this, 'campus_values']),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#attributes' => array(
          'style' => array('margin: 5%;')
          ),
      );
     
     $form['set_taxonomy_terms']['terms'] = array(
        '#type' => 'submit',
        //'#title' => 'Set Campus taxonomy terms',
        '#value' => 'Insert Terms data table',
        '#submit' => array([$this, 'term_values']),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#attributes' => array(
          'style' => array('margin: 5%;')
          ),
      );

   

    return parent::buildForm($form, $form_state);
  }
  
  
  // Submit handler
  public function academic_plan_terms(array &$form, FormStateInterface $form_state) {
    $plans = array();
    $url = 'https://webapp4.asu.edu/myasu/wsanon/getAllActivePlans';
    $xml = simpleXML_load_file($url);

    if($xml !==  FALSE)
    {
      foreach($xml as $plan) {
        $acadPlan = (string)$plan['acadPlan'];
        $plans[$acadPlan] = $acadPlan." : ".(string)$plan;
      }
    }
    // Sort academic plans by code
    ksort($plans);
    //$all_plans = array_merge(array('All' => '- Any -'), $plans);
    $count_from_url = count($plans);
    $count_from_db = check_data('asu_academic_plans', 'acad_plan_code');
    
    $conn = Database::getConnection();
    if($count_from_db != $count_from_url){
       \Drupal::database()->truncate('asu_academic_plans')->execute();
        foreach ($plans as $key => $category) {
            $conn->insert('asu_academic_plans')->fields(
            array(
              'acad_plan_code' => $key,
              'acad_plan_value' => $category,
             
            )
          )->execute();
          drupal_set_message(t('Inserted Plan data.'), 'status');
        }
    }
    else{
          drupal_set_message(t('Plan Data already exists.'), 'warning');
    }
  }

 // Submit handler
  public function campus_values(array &$form, FormStateInterface $form_state) {
    
    $campuses = array();
    $campus_url = 'https://webapp4.asu.edu/myasu/wsanon/getAllCampuses';
    $campus_xml = simpleXML_load_file($campus_url);
    $categories_vocabulary = 'campus'; // Vocabulary machine name
    if($campus_xml !==  FALSE)
    {
      foreach($campus_xml as $campus) {
        $campusCode = (string)$campus['campusCode'];
        $campuses[$campusCode] = (string)$campus;
      }
    }
    $all_campuses = array('All' => '- Any -') + $campuses;
    $campus_count_from_url = count($all_campuses);
    $conn = Database::getConnection();
    $count = check_data('asu_campus','campus_code');
    
    if($count != $campus_count_from_url){
      \Drupal::database()->truncate('asu_campus')->execute();
      foreach ($all_campuses as $key => $category) {
        $conn->insert('asu_campus')->fields(
          array(
            'campus_code' => $key,
            'campus_value' => $category,
           
          )
        )->execute();
      }
       drupal_set_message(t('Inserted campus data.'), 'status');
    }
    else{
      drupal_set_message(t('Campus Data already exists.'), 'warning');
    }
   
   
    
  }
  
  public function term_values(array &$form, FormStateInterface $form_state){
      $terms = array();
      $admit_terms = array();
      $terms_url = 'https://webapp4.asu.edu/myasu/wsanon/getAllTerms';
    
      // if cache is not empty, return cache.
      // Otherwise retrieve data from web service URL
      $terms_xml = simpleXML_load_file($terms_url);
    
      if($terms_xml !==  FALSE)
        {
          foreach($terms_xml as $term) {
            $strm = (string)$term['strm'];
            $terms[$strm] = (string)$term;
          }
      }
      
      krsort($terms);

      /*$defaults = array(
        'All' => t(' - Any - '),
        'NotEnrolled' => t('- Not Enrolled -'),
      );*/
      /*$defaults = array(
        'All' => ' - Any - ',
        'NotEnrolled' => '- Not Enrolled -',
      );
      //dpm($all_terms);
      $all_terms = $defaults + $terms;*/
      
      //krsort($all_terms);
      //dpm($all_terms);
      $terms_count_from_url = count($terms);
      $conn = Database::getConnection();
      $count = check_data('asu_terms','term_code');
      $count_from_url = count($terms);
      if($count != $count_from_url){
        \Drupal::database()->truncate('asu_terms')->execute();
        foreach ($terms as $key => $category) {
          $conn->insert('asu_terms')->fields(
            array(
              'term_code' => $key,
              'term_value' => $category,
             
            )
          )->execute();
        }
         drupal_set_message(t('Inserted Terms data.'), 'status');
      }
      else{
        drupal_set_message(t('Terms Data already exists.'), 'warning');
      }
      
    
  }
  
}

function check_data($table, $field){
  
  $query = db_query("select * from {$table}");
  foreach($query as $results){
    $value[] = $results->$field;
  }
  
  return count($value);
}