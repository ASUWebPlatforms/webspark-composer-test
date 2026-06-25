<?php

namespace Drupal\asu_mypath_signup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Http\ClientFactory;

/**
 * Defines a route controller for generating JSON pages.
 */
class myPathJsonController extends ControllerBase {

    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_transferoption/autocomplete/keywords?q=bio
     *
     * @param Request $request
     * @return JsonResponse
     */

     public function generateCommunityCollegeList(){
       
        $url = 'https://api.myasuplat-dpl.asu.edu/api/codeset/external-organizations?countryCode=USA';

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
          
        }
        foreach($data as $commData){
            $newDescription = $commData['description'];
            $comm[$commData['ficeCode']] = $newDescription;
        }
		unset($comm['N00217']);
        unset($comm['']);
		unset($comm['001338']); 
        unset($comm['001081']); //unset Arizona state university
        unset($comm['001070']); //remove Thunderbird School of Global Management from the dropdown
        unset($comm['011112']); //remove Fashion Institute of Design & Merchandising from the dropdown
		asort($comm);
		foreach($comm as $key => $value){
			if(($value == "West Valley Mission Community College Dist (N)") || ($value == "West Valley Mission Community College Dist")){
				$newValue = "West Valley";
			}
            if(str_contains($value,'*USE 1220331944')){
                $newValue = str_replace("*USE 1220331944 ",'',$value);
            }
            else{
                $newValue = $value;
            }
			if($value == "Chattahoochee Technical College *post 7/1/09"){
                $newValue = "CHATTAHOOCHEE TECHNICAL COLLEGE (AFTER 7/1/09)";
            }
            if($value == "Moorpark College (N)"){
                $newValue = "Moorpark College";
            }
			if(str_contains($value,' (N)')){
				$newValue = str_replace(" (N)",'',$value);
            }
			//below code will update key for moorpark college as JsonResponse is sorting the array and placing it above all options. Thsi can be avoided by updating the key here and replacing it with originla value in react during form submission process
			if($key == "107115"){
				$newKey = "000111";
			}
			else{
				$newKey = $key;
			}
            $updatedCommList[$newKey] = $newValue;
        }
		$updatedCommList['000008'] = "Mission College"; 
		$updatedCommList['001338'] = "West Valley";
		$updatedCommList['029301'] = "Alexandria Technical & Community College";
        $updatedCommList['001284'] = "Santa Ana College";
        $updatedCommList['000004'] = "Santiago Community College";
        asort($updatedCommList);
		
        return new JsonResponse($updatedCommList);
     }


    public function generateJsonInterest(Request $request){
        // Get ground/online and Ugrad/Grad
        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
       //ksm($uriSegment_array);
        $lastUriSegment = end($uriSegment_array);
        $student_status = $lastUriSegment;
        $learning_mode = urlencode($uriSegment_array[count($uriSegment_array)-2]);
        //ksm($student_status);
        //ksm($learning_mode);
        if(($learning_mode == "GROUND") || ($learning_mode == "NOPREF")){
             $catdata = xmlrpc('https://degrees.apps.asu.edu/XmlRpcServer', array('eAdvisorDSFind.listCategoriesMap'  => array('undergrad')));
            foreach($catdata as $key => $data){
                    $cat_options[$data] = $data;
            } 

           
           asort($cat_options);
           $interestList = array_merge(array('0' => 'Select...'), $cat_options);
           $interestList = $cat_options;
        }

        if(($learning_mode == "ONLNE") || ($learning_mode == "LOCAL")){
            $catdata = 'https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=undergraduate';
        
            $client = \Drupal::httpClient();
            $url = 'https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?method=findAllDegrees&program=undergrad&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,Descr100,AcadPlan,AsuCritTrackUrl,Degree,AsuCustomText,AsuNactvAppOvrd';
            $request = $client->get($url, array('headers' => array('Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded')));
            //$request1 = $client->get($url, array('headers' => array('Accept' => 'text/xml', 'Content-Type' => 'application/json')));
            $clientFactory = \Drupal::service('http_client_factory');
            $clientf = $clientFactory->fromOptions();
            $request1 = $clientf->get($url);
            $code = $request->getStatusCode();
            $content = $request->getBody()->getContents();
            $json_data = Json::decode($request1->getBody());
            
            //ksm($content);
            // $xml = simplexml_load_string(utf8_encode($content));
            $xml = simplexml_load_string($content);
            
            foreach($xml->program as $key => $programs){
                $catPorg[] = (string) $programs->interestareas->value;
            }
            
            $uniqueCatList = array_unique($catPorg);
          
            //ksm(string($uniqueCatList[0]);
            foreach($uniqueCatList as $key => $pvalue){
                $interestList[$pvalue] = $pvalue; 
            }
            unset($interestList['']);
            asort($interestList);
        }
       
        $interestJson = json_encode($interestList);
        //return $interestJson;
        return new JsonResponse($interestList);
    } 

	
	//Search program data based on interest
    public function generateJsonDegreeData(Request $request){
        $requestUri = $request->getRequestUri();
        $uriVariables = explode('/',$requestUri);
       
        $lastUriSegment = end($uriVariables);
        $length=sizeof($uriVariables); //calculated array length
        
        //$lastthreeStringsCombined=$uriVariables[$length-3].'/'.$uriVariables[$length-2].'/'.$uriVariables[$length-1];
        $student_status = urldecode($uriVariables[$length-1]);
        $learning_mode = $uriVariables[$length-3];
        $interest = urldecode($uriVariables[$length-2]);
        if(($learning_mode == "GROUND") || ($learning_mode == "NOPREF")){
            $program = "undergrad";
            $program_options = \Drupal::service('getOnCampusProgramList')->getOnCampusProgramList($program,$interest);
        }
        if(($learning_mode == "ONLNE") || ($learning_mode == "LOCAL")){
            $program = 'undergraduate';
            
            $program_options = \Drupal::service('getOnlineProgramList')->getOnlineProgramList($program,$interest);
           
        }
        
       // $programJson = json_encode($program_options);
        //return $interestJson;
        return new JsonResponse($program_options);
    } 

	//Search program data based on college
    public function generateJsonCollegeDegreeData(Request $request){
        $requestUri = $request->getRequestUri();
        $uriVariables = explode('/',$requestUri);
       
        $lastUriSegment = end($uriVariables);
        $length=sizeof($uriVariables); //calculated array length
        
        //$lastthreeStringsCombined=$uriVariables[$length-3].'/'.$uriVariables[$length-2].'/'.$uriVariables[$length-1];
        $student_status = urldecode($uriVariables[$length-1]);
        $learning_mode = $uriVariables[$length-3];
        $college = urldecode($uriVariables[$length-2]);
       if(($learning_mode == "GROUND") || ($learning_mode == "NOPREF")){
            $program = "undergrad";
            $program_options = \Drupal::service('getOnCampusCollegeProgramList')->getOnCampusCollegeProgramList($program,$college);
            
            //ksm($interest_options);
          //  ksm($program_options);
        }
        if(($learning_mode == "ONLNE") || ($learning_mode == "LOCAL")){
            $program = 'undergraduate';
            
            $program_options = \Drupal::service('getOnlineCollegeProgramList')->getOnlineCollegeProgramList($program,$college);
           
        }
        
       // $programJson = json_encode($program_options);
        //return $interestJson;
        return new JsonResponse($program_options);
    }

    //Get all programs list on initial modal load
    public function getProgramsList(Request $request){
        // Get ground/online and Ugrad/Grad
        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
        //ksm($uriSegment_array);
        $lastUriSegment = end($uriSegment_array);
        $student_status = $lastUriSegment;
        $learning_mode = urlencode($uriSegment_array[count($uriSegment_array)-1]);
        //ksm($learning_mode);
        $program = "undergraduate";
       // ksm(str_contains($learning_mode,"ONLNE"));
        if(str_contains($learning_mode,"ONLNE") || str_contains($learning_mode,"LOCAL")){
            $campus = "ONLNE";
            
        }
        else{
            $campus = "GROUND";
        }
       
        if(str_contains($learning_mode,'-')){
            $program_from_url = explode('-',$learning_mode);
            $prog_url = $program_from_url[1];
        }
        else{
            $prog_url = '';
        }
        //ksm($prog_url);
        //ksm($campus);
         $program_options = \Drupal::service('getProgramList')->getProgramList($campus,$prog_url);
        return new JsonResponse($program_options); 
    
    }

    //Get college list
    public function getCollegeList(Request $request){
        // Get ground/online and Ugrad/Grad
        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
        //ksm($uriSegment_array);
        $lastUriSegment = end($uriSegment_array);
        $student_status = $lastUriSegment;
        $learning_mode = urlencode($uriSegment_array[count($uriSegment_array)-1]);
       // ksm($learning_mode);
        $program = "undergraduate";
        //ksm(str_contains($learning_mode,"ONLNE"));
        if(str_contains($learning_mode,"ONLNE")){
            $campus = "ONLNE";
        }
        else{
            $campus = "GROUND";
        }
       
        if(str_contains($learning_mode,'-')){
            $program_from_url = explode('-',$learning_mode);
            $prog_url = $program_from_url[1];
        }
        else{
            $prog_url = '';
        }
        //ksm($prog_url);
        //ksm($campus);
        $program_options = \Drupal::service('getCollegeList')->getCollegeList($campus,$prog_url);
        return new JsonResponse($program_options); 
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




