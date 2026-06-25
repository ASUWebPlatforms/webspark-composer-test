<?php

namespace Drupal\asu_mypath_signup\Service;

use Drupal\Core\Render\Markup;

/**
 * This service is called in mMyPathFormASU.php file to get programs list.
 */
class AsuProgramList {

  /**
   * Function to pull oncampus porgrams from weservices.
   *
   * @return array of programs
   */
  public function getProgramList($campus = NULL, $programVal = NULL) {
    // ksm('$programVal',$programVal);.
    if (($campus == "GROUND")) {
      $client = \Drupal::httpClient();
      $url = "https://degrees.apps.asu.edu/t5/service?method=findAllDegrees&program=undergrad&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,DegreeDescrshort,DegreeDescrformal,Descr100,AcadPlan,AsuCritTrackUrl,DegreeEducationLvl,graduateAllApplyDates,AsuCustomText,AsuNactvAppOvrd";

      // $interest = !empty($url_interest)?urldecode($url_interest):'';
      $request = $client->get($url);
      $code = $request->getStatusCode();
      $content = $request->getBody()->getContents();
      $reportData = [];
      // ksm($content);
      $file_contents = json_decode($content);
      foreach ($file_contents->programs as $key => $programs) {

        $campus_array_val = $programs->CampusStringArray;
        // dpm($campus_array_val);
        $campus_data = campusDataFormat($campus_array_val);
        $campus_included = 'yes';
        if (sizeof($campus_array_val) == 1 && (($campus_data == "ONLNE") || ($campus_data == "LOSAN"))) {
          $campus_included = "no";
        }
        if (sizeof($campus_array_val) == 2 && (($campus_array_val[0] == "ONLNE") && ($campus_array_val[1] == "LOSAN"))) {
          $campus_included = "no";
        }

        if (empty($programVal)) {

          if ($campus_included == "yes") {
            $each_college = $programs->DiplomaDescr;
            // $degreeShort = $programs->DegreeDescrshort ? $programs->DegreeDescrshort : '';
            $degreeShort = isset($programs->DegreeDescrshort) && $programs->DegreeDescrshort ? $programs->DegreeDescrshort : '';
            if ((str_contains($programs->AcadPlan, 'HIFMPBFA')) || (str_contains($programs->AcadPlan, 'HIFMPSBFA')) || (str_contains($programs->AcadPlan, 'HIFSHBA')) || (str_contains($programs->AcadPlan, 'HIFSHDBA')) || (str_contains($programs->AcadPlan, 'HIFSHMBA')) || (str_contains($programs->AcadPlan, 'HIFSHATDBA'))) {
              $campus_data = str_replace('ASU Local', 'Los Angeles', $campus_data);
            }
            $showProgData = "<strong>" . $programs->Descr100 . ", " . $degreeShort . "</strong><br />$each_college<br /><strong>Location: </strong>" . $campus_data;
            // $planodename[$programs->AcadPlan] = \Drupal\Core\Render\Markup::create($programs->Descr100.", ".$programs->DegreeDescrshort."<br />$each_college<br />$campus_data");
            $planodename[$programs->AcadPlan] = Markup::create($showProgData);

            asort($planodename);
            $progList = $planodename;
            asort($progList);

            $returnData = $progList;
          }

        }
        else {
          // ksm('adada',$programs->AcadPlan);
          // ksm($programVal);
          if ($programs->AcadPlan == $programVal) {
            $program_name = $programs->Descr100;
            // $area_of_interest = $programs->Descr100;
            $college = $programs->DiplomaDescr;

            if (sizeof($programs->planCatDescr) > 1) {
              foreach ($programs->planCatDescr as $key => $avalue) {
                $all_interest[$key] = $avalue;
              }
              $area_of_interest = implode(', ', $all_interest);
            }
            else {
              $area_of_interest = $programs->planCatDescr[0];
            }

            $college_code = $programs->CollegeAcadOrg;
            if ((str_contains($programs->AcadPlan, 'HIFMPBFA')) || (str_contains($programs->AcadPlan, 'HIFMPSBFA')) || (str_contains($programs->AcadPlan, 'HIFSHBA')) || (str_contains($programs->AcadPlan, 'HIFSHDBA')) || (str_contains($programs->AcadPlan, 'HIFSHMBA')) || (str_contains($programs->AcadPlan, 'HIFSHATDBA'))) {
              $campus_data = str_replace('ASU Local', 'Los Angeles', $campus_data);
            }
            $programDetails = "<p><Strong>Program details</strong></p><p><strong>Program:</strong> $program_name<br /><strong>Area of interest:</strong> $area_of_interest<br /><strong>College: </strong>$college<br /><strong>Location: </strong>$campus_data</p>";

            // $programDetails = "<strong>".$programs->Descr100.", ".$programs->DegreeDescrshort."</strong><br />$each_college<br /><strong>Location: </strong>" .$campus_data;
            $reportData['progDetails'] = $programDetails;
            $reportData['collegeCode'] = $college_code;
            $reportData['degreeCode'] = $programs->DegreeDescrshort;
            $reportData['programName'] = $programs->Descr100;
            $reportData['collegeName'] = $college;
           // dpm($reportData);
            $returnData = $reportData;
          }

        }

      }

      // Return $returnData;.
    }
    // ksm($programVal);
    if (($campus == "ONLNE")) {
      $onlineData = onlineData($programVal);
      $returnData = $onlineData;
    }

    if ($campus == "LOCAL") {
      $localData = onlineData($programVal);
      unset($localData['LSORGLBA']);
      unset($localData['LSMILSTAA']);
      $returnData = $localData;

    }
    // ksm($returnData);
    unset($returnData['HIATDAA']);
    unset($returnData['HIFSHAA']);
    unset($returnData['HIMERCHAA']);
    // \Drupal::logger('$returnData')->info('<pre>' . print_r($returnData, TRUE) . '</pre>');
    return $returnData;

  }

}

/**
 * Function concatinate all campuses by "comma".
 */
function campusDataFormat($campus_array_val) {
  // ksm($campus_array_val);
  unset($campus_array_val['CALHC']);
  unset($campus_array_val['COCHS']);
  unset($campus_array_val['PIMA']);
  unset($campus_array_val['CAC']);
  unset($campus_array_val['EAC']);
  // ksm($campus_array_val);
  // \Drupal::logger('campus_array_val')->notice('fields: <pre>@fields</pre>', ['@fields' => print_r($campus_array_val, TRUE)]);.
  if (sizeof($campus_array_val) > 1) {
    foreach ($campus_array_val as $key => $value) {
      if ($value == "POLY") {
        $campus_array_val[$key] = "Polytechnic";
      }
      if ($value == "TEMPE") {
        $campus_array_val[$key] = "Tempe";
      }
      if ($value == "WEST") {
        $campus_array_val[$key] = "West Valley";
      }
      if ($value == "CALHC") {
        $campus_array_val[$key] = "";
      }
      if ($value == "DTPHX") {
        $campus_array_val[$key] = "Downtown Phoenix";
      }
      if ($value == "ONLNE") {
        $campus_array_val[$key] = "Online";
      }
      if ($value == "AWC") {
        $campus_array_val[$key] = "";
      }
      if ($value == "LOSAN") {
        $campus_array_val[$key] = "ASU Local";
      }
      if ($value == "YAVAP") {
        $campus_array_val[$key] = "";
      }
      if ($value == "CALHC") {
        $campus_array_val[$key] = "";
      }
      if ($value == "COCHS") {
        $campus_array_val[$key] = "";
      }
      if ($value == "PIMA") {
        $campus_array_val[$key] = "";
      }
      if ($value == "CAC") {
        $campus_array_val[$key] = "";
      }
      if ($value == "EAC") {
        $campus_array_val[$key] = "";
      }
    }
    // ksm($campus_array_val);
    // array_filter($campus_array_val);
    $filteredArray = array_filter($campus_array_val, function ($value) {
      return $value !== '' && $value !== NULL;
    });

    $campus_data = implode(", ", $filteredArray);
  }
  else {
    $single_campus_value = $campus_array_val[0];

    if ($single_campus_value == "POLY") {
      $single_campus_value = "Polytechnic";
    }
    if ($single_campus_value == "TEMPE") {
      $single_campus_value = "Tempe";
    }
    if ($single_campus_value == "WEST") {
      $single_campus_value = "West Valley";
    }
    if ($single_campus_value == "CALHC") {
      $single_campus_value = "";
    }
    if ($single_campus_value == "DTPHX") {
      $single_campus_value = "Downtown Phoenix";
    }
    if ($single_campus_value == "LOSAN") {
      $single_campus_value = "ASU Local";
    }
    if ($single_campus_value == "YAVAP") {
      $single_campus_value = "";
    }
    if ($single_campus_value == "ONLNE") {
      $single_campus_value = "Online";
    }
    $campus_data = $single_campus_value;
  }
  // ksm($campus_data);
  return $campus_data;
}

/**
 * Function to return online degress data.
 */
function onlineData($programVal) {
  $client = \Drupal::httpClient();
  $url = "https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=undergraduate";
  $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded']]);
  $code = $request->getStatusCode();
  $content = $request->getBody()->getContents();
  $xml = simplexml_load_string($content);
  foreach ($xml->program as $online_programs) {
    $interest_value = (string) $online_programs->interestareas->value;
    // \Drupal::logger('onlinevalue')->notice('fields: <pre>@fields</pre>', ['@fields' => print_r($online_programs, TRUE)]);
    // ksm($online_programs->collegename);
    // ksm($online_programs->interestareas->value);
    // $catPorg[] = $online_programs->interestareas;
    $string_title = (string) $online_programs->title;
    $format_prog_title = str_replace('â', '-', $string_title);
    // $progTitle_code = (string) $programs->code.'*'.$interest_value;
    $programCode = (string) $online_programs->code;
    // $all_prog_data[(string) $online_programs->plancode] = "<strong>".$format_prog_title."</strong><br />".$interest_value;
    $all_prog_data[$programCode] = "<strong>" . $format_prog_title . "</strong><br />" . $interest_value;

    if (empty($programVal)) {
      foreach ($all_prog_data as $key => $value) {
        $explode_key = explode('-', $key);
        $prog_val = str_replace('â', '-', $value);
        $progList[$key] = $prog_val;
        // $progList[$explode_key[0]] = $explode_key;
      }
      asort($progList);
      // ksm($progList);
      // $progList = array_merge(array('0' => 'Select...'), $progList);
      // return json_encode($progList);
      // \Drupal::logger('proglist')->notice('fields: <pre>@fields</pre>', ['@fields' => print_r($progList, TRUE)]);.
      $returnData = $progList;
    }
    else {
      if ($online_programs->plancode == $programVal) {
        $program_title = $online_programs->title;
        // Get degree code, example: BA, BS etc.
        $part_code = explode('(', $program_title);
        $degree_code_first_part = $part_code[1];
        $degree_code_sec_part = explode(')', $degree_code_first_part);
        $online_degree_code = $degree_code_sec_part[0];
        $on_prog_code = $online_programs->progcode;
        $online_cc = $online_programs->collegecode;
        if ($on_prog_code == "UGBA") {
          $online_college_code = "CBA";
          $online_college_name = "W. P. Carey School of Business";
        }
        if ($on_prog_code == "UGLA") {
          $online_college_code = "CLA";
          $online_college_name = "The College of Liberal Arts and Sciences";
        }
        if ($on_prog_code == "UGTE") {
          $online_college_code = 'CTE';
          $online_college_name = "Mary Lou Fulton Teachers College";
        }
        if ($on_prog_code == "UGLS") {
          $online_college_code = "CLS";
          $online_college_name = "College of Integrative Sciences and Arts";
        }
        if ($on_prog_code == "UGCF") {
          $online_college_code = "CGF";
          $online_college_name = "College of Glocal Futures";
        }
        if ($on_prog_code == "UGHL") {
          $online_college_code = "CHL";
          $online_college_name = "College of Health Solutions";
        }
        if ($on_prog_code == "UGHI") {
          $online_college_code = "CHI";
          $online_college_name = "Herberger Institute for Design and the Arts";
        }
        if ($on_prog_code == "UGES") {
          $online_college_code = "CES";
          $online_college_name = "Ira A. Fulton Schools of Engineering";
        }
        if ($on_prog_code == "UGNU") {
          $online_college_code = "CNU";
          $online_college_name = "Edson College of Nursing and Health Innovation";
        }
        if ($on_prog_code == "UGTB") {
          $online_college_code = "CTB";
          $online_college_name = "Thunderbird School of Global Management";
        }
        if ($on_prog_code == "UGAS") {
          $online_college_code = "CAS";
          $online_college_name = "New College of Interdisciplinary Arts and Sciences";
        }
        if ($on_prog_code == "UGUC") {
          $online_college_code = "CUC";
          $online_college_name = "University College";
        }
        if ($on_prog_code == "UGCS") {
          $online_college_code = "CCS";
          $online_college_name = "Walter Cronkite School of Journalism and Mass Comm";
        }
        if ($on_prog_code == "UGPP") {
          $online_college_code = "CPP";
          $online_college_name = "Watts College of Public Service & Community Solut";
        }

        /*if($online_cc == "w-p-carey-school-of-business"){
        $online_college_code = "CBA";
        }
        if($online_cc == "the-college-of-liberal-arts-and-sciences"){
        $online_college_code = "CLA";
        }
        if($online_cc == "mary-lou-fulton-teachers-college"){
        $online_college_code = "CTE";
        }
        if($online_cc == "college-of-integrative-sciences-and-arts"){
        $online_college_code = "CLS";
        }
        if($online_cc == "college-of-global-futures"){
        $online_college_code = "CGF";
        }
        if($online_cc == "college-of-health-solutions"){
        $online_college_code = "CHL";
        }
        if($online_cc == "herberger-institute-for-design-and-the-arts"){
        $online_college_code = "CHI";
        }
        if($online_cc == "ira-a-fulton-schools-of-engineering"){
        $online_college_code = "CES";
        }
        if($online_cc == "edson-college-of-nursing-and-health-innovation"){
        $online_college_code = "CNU";
        }
        if($online_cc == "thunderbird-school-of-global-management"){
        $online_college_code = "CTB";
        }
        if($online_cc == "new-college-of-interdisciplinary-arts-sciences"){
        $online_college_code = "CAS";
        }
        if($online_cc == "university-college"){
        $online_college_code = "CUC";
        }
        if($online_cc == "walter-cronkite-school-of-journalism-and-mass-communication"){
        $online_college_code = "CCS";
        }
        if($online_cc == "watts-college-of-public-service-and-community-solutions"){
        $online_college_code = "CPP";
        }*/

        // $college = $online_programs->collegename;
        $college = $online_college_name;
        // ksm($college);
        // $interest_value = (string) $online_programs->interestareas->value;.
        $clean_string = (string) $program_title;
        $clean_program = str_replace('â', '-', $clean_string);
        $online_programDetails = "<p><Strong>Program details</strong></p><p><strong>Program:</strong> $clean_program <br /><strong>Area of interest:</strong> $interest_value<br /><strong>College:</strong> $college</p>";
        $campus_data['progDetails'] = $online_programDetails;
        $campus_data['collegeCode'] = $online_college_code;
        $campus_data['degreeCode'] = $online_degree_code;
        $campus_data['programCode'] = $programCode;
        // ksm($campus_data);
        $returnData = $campus_data;
      }
    }
  }
  // ksm($returnData);
  return $returnData;
}
