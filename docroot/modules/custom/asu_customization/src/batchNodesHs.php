<?php

/**
 *@file
 *contains \Drupal\asu_customization\Form\courseEmailSettingsForm
 **/

 namespace Drupal\asu_customization;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;

 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class batchNodesHs{
    /**
     *{ @inheritdoc}
     */
   public static function updateCourseHs($nid, &$context){
	    $connection = \Drupal::database();
	    $course_node = \Drupal\node\Entity\Node::load($nid);
	    //$course_node->set('field_deferred',rtrim($deferred_value));
	    $course_node->field_course_code_0->target_id = 112; 
	    $course_node->save();
	    //ksm($nid);
	   /* $course_node = \Drupal\node\Entity\Node::load($nid);
		$school_name = $school;
	    $course_node->set('field_high_school_names',$school_name);
		$course_node->save();
	    $date_submitted = date('Y-m-d',$date);
	   //ksm($date_submitted);
	    $connection = \Drupal::database();
	    $data = $connection->insert('node__field_date_submitted')
				  ->fields(['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta','field_date_submitted_value'])
				  ->values([
					'bundle' => 'course_competency_new',
					'deleted' => 0,
					'entity_id' => $nid,
					'revision_id' => $nid,
					'langcode' => 'en',
					'delta' => 0,  
					'field_date_submitted_value' => $date_submitted   

				  ])
				  ->execute();
	   */
		
   }
	 
  function updateCourseHsFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One node processed.', '@count nodes processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }

 }