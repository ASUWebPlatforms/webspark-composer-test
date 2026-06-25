<?php

/**
 *@file
 *contains \Drupal\asu_customization\Form\courseEmailSettingsForm
 **/

 namespace Drupal\asu_feeds_customization;

 use Drupal\Core\Form\ConfigFormBase;
 use Drupal\Core\Form\FormStateInterface;


 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */

 class batchUndergradDegrees{

   public static function undergradDegreesData($college,&$context){

	    $connection = \Drupal::database();
	    $url = "https://api.myasuplat-dpl.asu.edu/api/codeset/acad-plans?ownedByCollege=$college&degreeType=UG&include=*";

        // Get JSON data from the URL
        $jsonData = file_get_contents($url);// Decode JSON data into a PHP associative array

        if ($jsonData === false) {
            // Handle error
            echo "Failed to fetch data from the API.";
        }


        $data = json_decode($jsonData, true);
         if ($data === null) {
            // Handle error
            echo "Error decoding JSON data";
        } else {
            // JSON data is successfully retrieved and decoded
            // Process and use the $data array as needed
            //ksm($data); // Display the array

	   foreach($data as $degData){
            $degrees[$degData['acadPlanCode']] = $degData['acadPlanCode'].':'.$degData['acadPlanDescription'];
        }
	  // ksm($degrees);
	    foreach($degrees as $planCode => $data){
			$record = [
			  'acad_plan_code' => $planCode,
			  'acad_plan_value' => $data
			];

			$databaseService = \Drupal::service('asu_feeds_customization.database_service');
			$databaseService->degreeInsertOrUpdate($record);

		}
		 }
   }


  public static function gradDegreesData($college,&$context){

	    $connection = \Drupal::database();
	    $url = "https://api.myasuplat-dpl.asu.edu/api/codeset/acad-plans?ownedByCollege=$college&degreeType=GR&include=*";

        // Get JSON data from the URL
        $jsonData = file_get_contents($url);// Decode JSON data into a PHP associative array

        if ($jsonData === false) {
            // Handle error
            echo "Failed to fetch data from the API.";
        }


        $data = json_decode($jsonData, true);
         if ($data === null) {
            // Handle error
            echo "Error decoding JSON data";
        } else {
            // JSON data is successfully retrieved and decoded
            // Process and use the $data array as needed
           // ksm($data); // Display the array
        }
	   $degrees = array();
	   foreach($data as $degData){
            $degrees[$degData['acadPlanCode']] = $degData['acadPlanCode'].':'.$degData['acadPlanDescription'];
        }

	    foreach($degrees as $planCode => $data){
			$record = [
			  'acad_plan_code' => $planCode,
			  'acad_plan_value' => $data
			];

			$databaseService = \Drupal::service('asu_feeds_customization.database_service');
			$databaseService->degreeInsertOrUpdate($record);

		}
   }


   public static function undergradDegreesFinishedCallback($success, $results, $operations) {
	   // ksm(count($results));
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
         'One node processed.','@count degrees inserted.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
	  // ksm($message);
    \Drupal::messenger()->addMessage($message);
  }

   public static function campusData($url, &$context){


        // Get JSON data from the URL
        $jsonData = file_get_contents($url);// Decode JSON data into a PHP associative array

        if ($jsonData === false) {
            // Handle error
            echo "Failed to fetch data from the API.";
        }


        $data = json_decode($jsonData, true);
         if ($data === null) {
            // Handle error
            echo "Error decoding JSON data";
        } else {
            // JSON data is successfully retrieved and decoded
            // Process and use the $data array as needed
            // Display the array
        }
	    $connection = \Drupal::database();
	   foreach($data as $campusData){
            $campus[$campusData['campusCode']] = $campusData['campusCode'];

			$record = [
			  'campus_code' => $campusData['campusCode'],
			  'campus_value' => $campusData['description']
			];

		   $connection->insert('asu_campus')
				  ->fields($record)
				  ->execute();
	   }
	   // ksm($campus);
	 }

 }
