<?php

/**
 *@file
 *contains \Drupal\asu_customization\Form\courseEmailSettingsForm
 **/

 namespace Drupal\asu_courses_export;
 
 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;
 use Drupal\node\Entity\Node;
 use Symfony\Component\HttpFoundation\BinaryFileResponse;
 use Symfony\Component\HttpFoundation\ResponseHeaderBag;
 use Drupal\Core\Database\Database;
 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class ExportBatch{
	 
   public static function processChunk(array $nids, string $status, string $state_value, array &$context) {
    // Init result array
	 //  ksm('st',$state_value);
    if (!isset($context['results']['rows'])) {
      $context['results']['rows'] = [];
    }
	   
	
    //ksm(count($nids));
    foreach ($nids as $node_id) {
		$nid =$node_id->nid;
		$node = Node::load($nid);
		//$taxArray = [];
		$term_names = [];
		if ($node) {
			
		  if($status == 'approved'){
			  $competencyCode = self::getComptetencyCode($nid);  
		  }
		  else{
				$competencyCode = ''; 
		  }
		  
		  
		  $context['results']['rows'][] = [
			'nid' => $node->id(),
			'title' => $node->label(),
			'high_school' => $node->get('field_high_school') ? $node->get('field_high_school')->value : '',
			'competency_code' => $competencyCode,
			'district' => $node->get('field_district') ? $node->get('field_district')->value : '',
			'city' => $node->get('field_school_city') ? $node->get('field_school_city')->value : '',
			'state' => $node->get('field_school_state') ? $node->get('field_school_state')->value : '',  
			'created' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'short'),
		  ];
		}
   }
    $context['results']['status'] = $status; 
	$context['results']['state_value'] = $state_value;   
	$context['message'] = t('<div id="export_page_div">Processed @count nodes. <a href="/admin/content/download_courses">Go back to download page</a></div>', ['@count' => count($nids)]);
  }

  public static function finishBatch($success, array $results, array $operations) {
    if (!$success || empty($results['rows'])) {
      \Drupal::messenger()->addError(t('No nodes exported.'));
      return;
    }
	 //\Drupal::logger('result_data')->info('<pre>' . print_r($results, TRUE) . '</pre>');
     $status = $results['status'] ?? 'default';
	 $state = $results['state_value'] ?? ''; 
	  $_SESSION['state'] = $state;		
	  if(!empty($status)) { 
		    if ($success && !empty($results['rows'])) {
			  $_SESSION['export_'.$status.'_csv_data'] = $results['rows'];
		      
				$url = \Drupal\Core\Url::fromRoute('asu_courses_export.export_csv_download', ['status' => $status])->toString();
			    \Drupal::service('page_cache_kill_switch')->trigger(); // Just in case

				$response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
				$response->send();
				
				
			 // \Drupal::messenger()->addMessage(t('Export complete. <a href="@url">Download CSV</a>', ['@url' => $url]));
			}
			else {
				\Drupal::messenger()->addError(t('Export failed or no data found.'));
			}
	  }
	  else{
			// Create CSV
			$filename = $status.'-'.$state.'-courses-'. date('Y-m-d_His') . '.csv';
			$handle = fopen($filename, 'w');
		  	$file_path =  $filename;

			// Header
			fputcsv($handle, ['NID', 'Title', 'High School', 'District', 'City', 'State', 'Created']);

			// Rows
			foreach ($results['rows'] as $row) {
			  fputcsv($handle, [$row['nid'], $row['title'], $row['high_school'], $row['district'], $row['city'], $row['state'], $row['created']]);
			}

			fclose($handle);
		    
			// Generate file URL
			$url = \Drupal::service('file_url_generator')->generateAbsoluteString($filename);
			\Drupal::messenger()->addStatus(t('Export complete. <a href="@url" target="_blank">Download '.$filename.' here</a>', ['@url' => $url]));
		   
	  }
	  
  }
	 
public static function getComptetencyCode($nid){
	    $connection = Database::getConnection();
		$query = $connection->select('node__field_course_code_0', 'ncc');
		$query->leftJoin('taxonomy_term_field_data', 'child_term', 'child_term.tid = ncc.field_course_code_0_target_id');
		$query->leftJoin('taxonomy_term__parent', 'parent_map', 'parent_map.entity_id = child_term.tid');
		$query->leftJoin('taxonomy_term_field_data', 'parent_term', 'parent_term.tid = parent_map.parent_target_id');

		$query->fields('child_term', ['tid', 'name']); // child term name
		$query->fields('parent_term', ['tid', 'name']); // parent term name
		$query->condition('ncc.entity_id', $nid, '=');

		$results = $query->execute()->fetchAll();

		$tax_data = [];
		foreach ($results as $data) {
			$tax_data[] = $data->parent_term_name.':'.$data->name;
		}
	  // \Drupal::logger('taxonomy')->notice('<pre>%data</pre>', ['%data' => print_r($tax_data, TRUE),]);
	  	$new_data = implode(',', $tax_data);
	   return $new_data;
	 }
 }