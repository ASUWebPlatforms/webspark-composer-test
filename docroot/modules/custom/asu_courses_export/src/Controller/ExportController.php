<?php

namespace Drupal\asu_courses_export\Controller;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller to provide download csv funstionality
 */
class ExportController {
  public function downloadCourses($status) {
	 	$data = $_SESSION['export_'.$status.'_csv_data'] ?? [];
	    $state = $_SESSION['state'];
	  	
		if (empty($data)) {
		  return new \Symfony\Component\HttpFoundation\Response('No data to download', 404);
		}
	  
	    if($status == 'approved'){
			  $fields = ['NID', 'Title', 'High School', 'Competency code', 'District', 'City','State', 'Created'];
		  }	
		  else{
			  $fields = ['NID', 'Title', 'High School', 'District', 'City','State', 'Created'];
		  }	
	  	  $response = new StreamedResponse(function () use ($data, $fields) {
		  $handle = fopen('php://output', 'w');
		  
		  //fputcsv($handle, ['NID', 'Title', 'Created']);
		  
			
		  fputcsv($handle, $fields);
		  foreach ($data as $row) {
			fputcsv($handle, $row);
		  }
		  fclose($handle);
		});
	  	$fileName = $status.'-'.$state.'-courses-'. date('Y-m-d-h:ia') . '.csv';
		$response->headers->set('Content-Type', 'text/csv');
	    
		$response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
	    unset($_SESSION['export_'.$status.'_csv_data']); 
	    unset($_SESSION['state']); 
	    return $response;
  }

}
