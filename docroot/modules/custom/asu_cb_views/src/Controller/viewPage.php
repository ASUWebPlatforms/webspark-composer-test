<?php
/**
 * @file
 * Contains \Drupal\first_module\Controller\initialLogin.
 */

namespace Drupal\asu_cb_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Drupal\node\NodeInterface;
use Drupal\node\Entity;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeViewBuilder;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\views\Plugin\views\row\RssPluginBase;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\views\Views;
use Drupal\views\ViewExecutable;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\FilterProcessResult;
use Drupal\views\Render;
use Drupal\views\Entity\View;
use Drupal\views\Plugin\views\filter\NumericFilter;
use Drupal\views\Plugin\views\filter\StringFilter;
use Drupal\views\Plugin\views\filter;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\Core\Render\BareHtmlPageRenderer;


//header('Content-type: text/xml');

define('MYASU_CB_VIEWS_VIEW', 'my_asu_cb_view');
define('MYASU_CB_VIEWS_DISPLAY_ID', 'feed_1');
//define('MYASU_CB_VIEWS_DISPLAY_ID_PAGE', 'page_3');
/*define('MYASU_CB_VIEWS_FILTER_ADMIT', 'field_myasu_cb_admitterm_value_many_to_one');
define('MYASU_CB_VIEWS_FILTER_ADMIT_TYPE', 'field_myasu_cb_admit_type_value_many_to_one');
define('MYASU_CB_VIEWS_FILTER_CAMPUS', 'field_myasu_cb_campus_value_many_to_one');
define('MYASU_CB_VIEWS_FILTER_LEVEL', 'field_myasu_cb_acadlevel_value');
define('MYASU_CB_VIEWS_FILTER_MILESTONE', 'field_myasu_cb_milestone_value_many_to_one');
define('MYASU_CB_VIEWS_FILTER_SG', 'field_myasu_cb_sg_value');
define('MYASU_CB_VIEWS_FIELD_IMAGE', 'field_myasu_cb_image');
define('MYASU_CB_VIEWS_FIELD_IMAGE_URL', 'field_myasu_cb_image_url');
define('MYASU_CB_VIEWS_FIELD_TRACKING', 'field_myasu_cb_tracking');
define('MYASU_CB_VIEWS_FIELD_FOOTER', 'field_myasu_cb_footer');
define('MYASU_CB_VIEWS_FIELD_DEFAULT_MESSAGE', 'field_myasu_cb_default_message');
//define('MYASU_CB_VIEWS_FIELD_VIDEO', 'field_myasu_cb_video');
define('MYASU_CB_VIEWS_VIEW_TABS_BY_GID', 'myasu_cb_tabs_by_gid');*/

class viewPage extends ControllerBase {

  public function myasu_cb_views_page($groupn) {
      $group_name = "/$groupn";
      //\Drupal::logger('groupn')->warning($groupn);
	  //\Drupal::logger('groupn')->notice('groupn: <pre>@groupn</pre>', ['@fgroupn' => print_r($groupn, TRUE)]);
      // \Drupal::logger('groupname')->warning($group_name);
      $connection = \Drupal::database();
      
      $query = $connection->query("SELECT path FROM {path_alias} WHERE alias = :alias", [':alias' => $group_name]);
      if ($query) {
        while ($row = $query->fetchAssoc()) {
          $source = $row['path'];
        }
      }
     
      $get_node_id = explode('/',$source);
      
      $nid = $get_node_id[2];
      if (!$nid || !is_numeric($nid)) {
        throw new NotFoundHttpException();
      }
      
      // Set a variable we can use in the view display to render the archive link
    global $myasu_cb_archive_gid;
    $myasu_cb_archive_gid = $nid;
	//ksm('$myasu_cb_archive_gid',$myasu_cb_archive_gid);
    $any = array('Any' => 'Any');
    $all = array('All');
    $all_new = array('All');
    $not_enrolled = array('NotEnrolled' => 'NotEnrolled');
	
    //dpm($myasu_cb_archive_gid,'$myasu_cb_archive_gid');
    // Prepare the filter values
    if(!empty($_GET['enrolledTerms'])){
       $enrolled_terms_value = asu_cb_views_parse_enrolled_terms($_GET['enrolledTerms']) ;
       $merged_enrolled = array_merge($all,$enrolled_terms_value);
    }
    else{
      $enrolled_terms_value = '';
      $merged_enrolled = $all;
    }
    
    $not_enrolled_merge = array_merge($all, $not_enrolled);
    if(!empty($enrolled_terms_value)){
      $enrolled_terms =  $merged_enrolled;
    }
    else{
      $enrolled_terms = $not_enrolled_merge;
    }
    //$enrolled_terms =  count($enrolled_terms_value) ? $merged_enrolled : $not_enrolled_merge;
    //dpm($enrolled_terms);
    if(!empty($_GET['studentGroups'])){
	  $student_groups = asu_cb_views_parse_student_groups($_GET['studentGroups']);
	   //\Drupal::logger('sg')->notice(print_r($student_groups, TRUE));
      $student_value = array_merge($all,$student_groups);
		// \Drupal::logger('sg2')->notice(print_r($student_value , TRUE));
	
    }
    else{
      $student_value = $all;
    }
    
    // $student_value = $student_groups + $all;
    //dpm($student_groups);
    if(!empty($_GET['acadLevel'])){
		$acad_levels_value = array(asu_cb_views_parse_acad_level($_GET['acadLevel']));
		//$acad_levels = count($acad_levels_value)? $acad_levels_value + $all;
		$acad_levels = count($acad_levels_value)? array_merge($all, $acad_levels_value): $all;
		$acadvalue = count($acad_levels) ? $acad_levels : '';
    }
    else{
      $acadvalue = ''; 
    }
    
    //$milestones = asu_cb_views_parse_milestones($_GET['completedMilestones']);
    if(!empty($_GET['planData'])){
	  $plan_vars = asu_cb_views_parse_admit_plans($_GET['planData']) ;
	
    }
    else{
       $plan_vars  = '';
    }
    
    //\Drupal::logger('planvars')->warning('<pre><code>' . print_r($plan_vars, TRUE) . '</code></pre>');
    if(!empty($plan_vars['acadCareer'])){
      $ini_career = array($plan_vars['acadCareer']);
      $career =  array_merge($all, $ini_career);
    }
    else{
      $career = $all;
    }
    
    //$career =  $all_new + $ini_career;
    if(!empty($plan_vars['acadPlan'])){
      $ini_plan = array($plan_vars['acadPlan']);
      $acadplan =  array_merge($all, $ini_plan);
		//$acadplan = $ini_plan;
    }
    else{
      $acadplan = $all;
    }
    
    //$acadplan =  $all_new + $ini_plan;
    if(!empty($plan_vars['admitTerm'])){
       $ini_admit = isset($plan_vars['admitTerm'])?array($plan_vars['admitTerm']):'';
       $admitterm =  array_merge($all, $ini_admit);
    }
    else{
      $admitterm = $all;
    }
   
    //$admitterm =  $all_new + $ini_admit;
     if(!empty($plan_vars['admitType'])){
       $ini_type = isset($plan_vars['admitType'])?array($plan_vars['admitType']):'';
       $admitType = array_merge($all, $ini_type);
    }
    else{
       $admitType = $all;
    }
   
   // \Drupal::logger('admintype')->warning($admitType);
    //$admitType = array( 'All', 'RAD');
    //$admitType = $all_new + $ini_type;
    if(!empty($plan_vars['campus'])){
      $ini_campus = isset($plan_vars['campus'])?array($plan_vars['campus']):'';
      $campus = array_merge($all, $ini_campus);
    }
    else{
      $campus = $all;
    }
    
   
    $view_id = MYASU_CB_VIEWS_VIEW;
   
    //$display_id = MYASU_CB_VIEWS_DISPLAY_ID_PAGE;
    $display_id = MYASU_CB_VIEWS_DISPLAY_ID;
    $feedview = Views::getView($view_id);
    $feedview->setDisplay($display_id);
    $feedview->setArguments(array($nid));
    //Get exposed values from url and set them in the view filters
    $filter_input = $feedview->getExposedInput();
	//  \Drupal::logger('filter')->notice(print_r($filter_input, TRUE));
	//\Drupal::logger('feed')->notice(print_r($feedview, TRUE));
    $filter_input['acadLevel'] = $acadvalue;
    $filter_input['enrolledTerms'] = $enrolled_terms;
    
    $filter_input['acadCareer'] = $career ;
    $filter_input['acadPlan'] = $acadplan ;
    $filter_input['admitTerm'] = $admitterm;
    $filter_input['admitType'] = $admitType;
    $filter_input['campus'] = $campus;
    
    $filter_input['gid1'] = $nid;
    
    //$filter_input['studentGroups'] = implode(',', $student_value);
	$filter_input['studentGroups'] = $student_value;
    //dpm($filter_input);
    \Drupal::logger('filter 2')->notice(print_r($filter_input, TRUE));
    $feedview->setExposedInput($filter_input);
    $feedview->execute();
    $view_render = $feedview->render();
	 // \Drupal::logger('feed')->notice(print_r($feedview, TRUE));
	 // \Drupal::logger('feed')->notice(print_r($view_render, TRUE));
    $result = \Drupal::service('renderer')->renderRoot($view_render);
     
    
    
    /* output for xml feed */
   
    
    $response = new Response();
    $response->setContent($result);
    $response->headers->set('Content-Type', 'text/xml;charset=UTF-8');
    return $response; 
  
   
  } 
}




//view filter parse code

function asu_cb_views_parse_admit_plans($string) {

  if (!$string) {
    return array();
  }
  $string = urldecode($string);
  $values = array();

  $plans = explode(',', $string);
  foreach ($plans as $plan) {
    $rows = explode('|', $plan);
    foreach ($rows as $row) {
      $pieces = explode('=', $row);
      $key = $pieces[0];
      $value = $pieces[1];
      if (isset($key) && isset($value)) {
        if (!in_array($key, array('acadPlan', 'admitTerm', 'admitType', 'acadCareer','campus'))) {
          continue;
        }
        if ($value == 'null') {
          continue;
        }
        $values[$key] = $value;
      }
    }
  }
  
  return $values;
}


function asu_cb_views_parse_student_groups($string) {
	
  if (!$string) {
    return array();
  }
  //$groups = explode(',', $string);
  $groups = explode(',', $string);
  return $groups;
}

function asu_cb_views_parse_enrolled_terms($string) {
  if (!$string) {
    return array();
  }
  $string = urldecode($string);
  $terms = explode(',', $string);
  return $terms;
}

function asu_cb_views_parse_acad_level($string) {
  if (!$string) {
    return array();
  }
  $string = urldecode($string);
 
  $levels_raw = explode(',', $string);
 
  $levels = array();
  foreach ($levels_raw as $level) {
    
    $pieces = explode('=', $level);
    $key = $pieces[0];
    $value = $pieces[1];
    
    if (isset($key) && isset($value)) {
      if (!in_array($key, array('UGRD', 'GRAD', 'NOCR', 'LAW'))) {
        continue;
      }
      if ($value == null) {
        continue;
      }
     $levels[] = $value;
       
    }
  }
 
  return $levels;
}