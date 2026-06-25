<?php
namespace Drupal\custom_asu_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\node\Entity\Node;

//use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 @file
 * Contains \Drupal\custom_asu_user\Plugin\Block\sun_award_confirmation_page_block
 */


/**
 * Provides Sun award confirmation page block.
 *
 * @Block(
 *   id = "sun_award_confirmation_page_block from custom_asu_user module",
 *   admin_label = @Translation("Sun Award confirmation page block"),
 *  
 * )
 */
class sun_award_confirmation_page_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $sid = \Drupal::request()->query->get('sid');
    $reason_list = '';
	$reason_list_old = '';
	if(!empty($sid)){
		//Load webform submission data if submission exists
		$webform_submission = WebformSubmission::load($sid);
		if(!empty($webform_submission)){
			$submission = $webform_submission->getData();
			$webform_id = $webform_submission->getWebform()->id();
			if($webform_id == 'professor_of_impact_award'){
				$presented_to = $submission['presented_to'];
				$department = $submission['academic_unit'];
				$course= $submission['course'];
				$for = $submission['presented_for'];
				$presented_by = $submission['presented_by'];
				$date = $submission['date'];

				//$date_val = format_date(strtotime($date), 'custom', 'F d, Y');
				$date_string = strtotime($date);
				$date_val =  date("Y-m-d", $date_string); 
				//ksm($date_val); 
				$st_name = $submission['presented_by'];
				$reason_for = $submission['recognized_for_'];
				if(sizeof($reason_for) > 1){
					  $ul_start = "<ul>";
					  foreach($reason_for as $each_reason){
						 $reason_list .= "<li>$each_reason</li>";
					  }
					  $ul_end = "</ul>";
					  $reason_data = $ul_start.$reason_list.$ul_end;
				}
				else{
				  	$reason_data = $reason_for[0]."<br />";
				}
				//ksm($reason_data);
				$content = "<div><h2>$presented_to</h2></div><h3><strong>$department</strong></h3><h4><strong>$course</strong></h4><p>ASU is pleased to inform you that <strong> $presented_by</strong> recognizes you for:<br />$reason_data</p><p>$for</p><p><strong>Presented on: $date_val</strong></p>";
			}
		}
		//if webform submission does not exist, then query the node__field_sid table or old_site_prof_award to check if sid exists in that table. if exists, then run belowe code.
		else{ 
			$database = \Drupal::database();
			$nid = $database->select("node__field_sid", "nfs")
					->fields('nfs', ['entity_id'])
					->condition('nfs.field_sid_value',$sid,'=')
					->execute()
					->fetchField();
			//\Drupal::logger('nid')->notice('nid: ' . $nid);
			$node = Node::load($nid);

			//foreach($award_query as $award_result){
			if(!empty($node)){
					$presented_to_old = $node->get('field_presented_to')->value;
					$department_old = $node->get('field_academic_unit')->value;
					$course_old = $node->get('field_course')->value;
					$for_old = $node->get('field_presented_for')->value;
					$presented_by_old = $node->get('field_presented_by')->value;
					$date_old = $node->get('field_award_date')->value;
					$st_name_old = $node->get('field_presented_by')->value;
					$reason_val_old = $node->get('field_recognized_for')->value;
				    $reason_for_old = explode(';',$reason_val_old);
				    if(sizeof($reason_for_old) > 1){
						  $ul_start_old = "<ul>";
						  foreach($reason_for_old as $each_reason_old){
							 $reason_list_old .= "<li>$each_reason_old</li>";
						  }
						  $ul_end_old = "</ul>";
						  $reason_data_old = $ul_start_old.$reason_list_old.$ul_end_old;
					}
					else{
					  	  $reason_data_old = $reason_for_old[0]."<br />";
					}
			}
			else{ // if webform submission does not exist and node does not exist, then query old_site_prof_award table
				$award_query = $database->select("old_site_prof_award", "osfa")
					->fields("osfa")
					->condition('osfa.sid',$sid,'=')
					->execute()
					->fetchAll();
				foreach($award_query as $award_result){
						$presented_to_old = $award_result->presented_to;
						$department_old = $award_result->academic_unit;
						$course_old = $award_result->course;
						$for_old = $award_result->presented_for;
						$presented_by_old = $award_result->presented_by;
						$date_old = $award_result->award_date;
						$st_name_old = $award_result->presented_by;
						$reason_val_old = $award_result->recognized_for;
						$reason_for_old = explode(',',$reason_val_old);
						if(sizeof($reason_for_old) > 1){
							  $ul_start_old = "<ul>";
							  foreach($reason_for_old as $each_reason_old){
								 $reason_list_old .= "<li>$each_reason_old</li>";
							  }
							  $ul_end_old = "</ul>";
							  $reason_data_old = $ul_start_old.$reason_list_old.$ul_end_old;
						}
						else{
						  	  $reason_data_old = $reason_for_old[0]."<br />";
						}
				}
			}
			$content = "<div><h2>$presented_to_old</h2></div><h3><strong>$department_old</strong></h3><h4><strong>$course_old</strong></h4><p>ASU is pleased to inform you that <strong> $presented_by_old</strong> recognizes you for:<br />$reason_data_old</p><p>$for_old</p><p><strong>Presented on: $date_old</strong></p>";
		}
		
	}
	  
	else{
		$content = "<p>Thank you for your submission</p>";
	}
	return array(
          	'#markup' => $this->t($content),
          	'#cache' => array(
            'max-age' => 0,
        ),
      
    );
 }
	
  public function getCacheMaxAge() {
     return 0;
  }
}
