<?php

namespace Drupal\asu_digital_book\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class DigitalBookJsVariablesSettingsPage extends ControllerBase {
  public function getSettings() {
    $config = \Drupal::config('asu_digital_book.admin_settings');
	$view_book_settings = $config->get('passion_intro');
	$learning_book_settings = $config->get('learning'); 
	$customize_book_settings = $config->get('customize'); 
	$invest_book_settings = $config->get('invest');   
	$rfi_settings = $config->get('rfi');  
	$all_settings = $view_book_settings + $learning_book_settings + $customize_book_settings + $invest_book_settings + $rfi_settings;
	//$all_settings = $view_book_settings  + $customize_book_settings + $invest_book_settings + $rfi_settings;
	foreach($all_settings as $key => $value){
		$data[$key] = $value;
	}  
	unset($data['submit']);
	unset($data['form_build_id']);
	unset($data['form_token']);
	unset($data['form_id']);
	unset($data['op']); 
	foreach($data as $dataKey => $content){
		//ksm($content);
		$viewSettings[$dataKey] = isset($content['value'])?$content['value']:$content; 
	}
	return new JsonResponse($viewSettings);
  }
	
  //function to return terms  
  function getTerms(){
            $today_month = date('n');
            $today_year = date('Y');
            $options = array();
            $semester = array('Spring' => '1', 'Summer' => '4', 'Fall' => '7');
            $next_semester = "";
            $next_semester_year = "";
    
    
            $i = 0;
            $num_of_options = sizeof($options);
    
            while($num_of_options < 8) {
    
                if($i == 0) { // First iteration.
    
                    $current_semester_year = $today_year;
    
                    if(($today_month <= 5)) { // Jan - May
                        // Start from Summer semester
                        $current_semester = "Spring";
    
                        $sem = $semester[$current_semester];
                        $three_digit_year = substr ($current_semester_year, 0, 1) . substr ($current_semester_year, 2, 3);
                        $semester_code = $three_digit_year . $sem;
                        $next_semester = "Fall"; // Removed Summer
                        $next_semester_year = $current_semester_year;
                    }
    
    
                    if(($today_month >= 6)) { // Removed summer
                        // Start from Spring semester
                        $current_semester = "Fall";
    
                        $sem = $semester[$current_semester];
                        $three_digit_year = substr ($current_semester_year, 0, 1) . substr ($current_semester_year, 2, 3);
                        $semester_code = $three_digit_year . $sem;
    
                        $next_semester = "Spring";
                        $next_semester_year = $current_semester_year + 1;
    
                    }
    
    
                } // END of if($i == 0)
                else { // Not first iteration
                    $current_semester_year = $next_semester_year;
                    $current_semester = $next_semester;
    
                    $sem = $semester[$current_semester];
                    $three_digit_year = substr ($current_semester_year, 0, 1) . substr ($current_semester_year, 2, 3);
                    $semester_code = $three_digit_year . $sem;
                    // Insert option to $options array.
                    $options[$semester_code] = $current_semester_year . " " . $current_semester;
    
                    // Set next semester for the next iteration.
                    if($current_semester == "Spring") {
                     // $next_semester = "Summer"; // Removed Summer
                        $next_semester = "Fall";
                        $next_semester_year = $current_semester_year;
                    } elseif ($current_semester == "Summer") {
                        $next_semester = "Fall";
                        $next_semester_year = $current_semester_year;
                    } elseif ($current_semester == "Fall") {
                        $next_semester = "Spring";
                        $next_semester_year = $current_semester_year + 1;
                    }
    
                }
    
                $num_of_options = sizeof($options);
                $i++;
            } // END of while
            return new JsonResponse($options);
    } 
}

/**
 * Helping function
 * Convert Entry term code to human readable text.
 *
 * @param $termCode
 * @return string
 */
function getHumanReadableTerm($termCode) {
    $threeDigitYear = substr($termCode, 0, 3);
    $year = substr($threeDigitYear, 0, 1) . '0' . substr($threeDigitYear, 1, 2);
    $semesterCode = substr($termCode, 3, 1);
    $semester = '';
    switch($semesterCode) {
        case '1':
            $semester = 'Spring';
            break;
        case '4':
            $semester = 'Summer';
            break;
        case '7':
            $semester = 'Fall';
            break;
    }
    return $year . ' ' . $semester;
} // END OF function getHumanReadableTerm($termCode)
