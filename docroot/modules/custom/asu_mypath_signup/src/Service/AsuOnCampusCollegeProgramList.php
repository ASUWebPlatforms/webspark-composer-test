<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * This service is called in mMyPathFormASU.php file to get programs list.
 */
class AsuOnCampusCollegeProgramList {

  /**
   * Function to pull oncampus porgrams from weservices.
   *
   * @return array of programs
   */
  public function getOnCampusCollegeProgramList($program = NULL, $url_college = NULL) {
    $client = \Drupal::httpClient();
    $url = "https://degrees.apps.asu.edu/t5/service?method=findAllDegrees&program=$program&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,DegreeDescrshort,DegreeDescrformal,Descr100,AcadPlan,AsuCritTrackUrl,DegreeEducationLvl,graduateAllApplyDates,AsuCustomText,AsuNactvAppOvrd";

    $college = !empty($url_college) ? urldecode($url_college) : '';
    $request = $client->get($url);
    $code = $request->getStatusCode();
    $content = $request->getBody()->getContents();
    // ksm($content);
    $file_contents = json_decode($content);
    // ksm($file_contents);
    foreach ($file_contents->programs as $key => $programs) {
      $campus_array_val = $programs->CampusStringArray;
      $campus_data = campusDataFormat($campus_array_val);

      if (sizeof($campus_array_val) == 1 && (($campus_array_val != "ONLNE") || ($campus_array_val != "LOSAN"))) {
        $each_college = $programs->DiplomaDescr;
        if ((str_contains($programs->AcadPlan, 'HIFMPBFA')) || (str_contains($programs->AcadPlan, 'HIFMPSBFA')) || (str_contains($programs->AcadPlan, 'HIFSHBA')) || (str_contains($programs->AcadPlan, 'HIFSHDBA')) || (str_contains($programs->AcadPlan, 'HIFSHMBA')) || (str_contains($programs->AcadPlan, 'HIFSHATDBA'))) {
          $campus_data = str_replace('ASU Local', 'Los Angeles', $campus_data);
        }
        $degreeDdescrshort = !empty($programs->DegreeDescrshort) ? $programs->DegreeDescrshort : '';
        $showProgData = "<strong>" . $programs->Descr100 . ", " . $degreeDdescrshort . "</strong><br />$each_college<br /><strong>Location: </strong>" . $campus_data;
        $prog_desc = !empty($programs->AcadProg) ? $programs->AcadProg : '';
        $plankey = $programs->AcadPlan . '*' . $prog_desc;

        // $planodename[$plankey] = $programs->Descr100." - ".$programs->DegreeDescrshort;
        $planodename[$plankey] = $showProgData;
      }
      else {
        $each_college = $programs->DiplomaDescr;
        if ((str_contains($programs->AcadPlan, 'HIFMPBFA')) || (str_contains($programs->AcadPlan, 'HIFMPSBFA')) || (str_contains($programs->AcadPlan, 'HIFSHBA')) || (str_contains($programs->AcadPlan, 'HIFSHDBA')) || (str_contains($programs->AcadPlan, 'HIFSHMBA')) || (str_contains($programs->AcadPlan, 'HIFSHATDBA'))) {
          $campus_data = str_replace('ASU Local', 'Los Angeles', $campus_data);
        }
        $degreeDdescrshort = !empty($programs->DegreeDescrshort) ? $programs->DegreeDescrshort : '';
        $showProgData = "<strong>" . $programs->Descr100 . ", " . $degreeDdescrshort . "</strong><br />$each_college<br /><strong>Location: </strong>" . $campus_data;
        $prog_desc = !empty($programs->AcadProg) ? $programs->AcadProg : '';
        $plankey = $programs->AcadPlan . '*' . $prog_desc;
        // \Drupal::logger('campusdata1')->info('<pre>' . print_r($campus_data, TRUE) . '</pre>');
        // $planodename[$plankey] = $programs->Descr100." - ".$programs->DegreeDescrshort;
        $planodename[$plankey] = $showProgData;
      }

    }

    if (!empty($planodename)) {
      foreach ($planodename as $key => $value) {
        // ksm($key);
        if (str_contains($key, $college)) {
          $just_plancode = explode('*', $key);
          $progList[$just_plancode[0]] = $value;
        }

      }
      unset($progList['HIATDAA']);
      //unset($progList['HIFSHATDBA']);
      unset($progList['HIFSHAA']);
      unset($progList['HIMERCHAA']);
      // \Drupal::logger('progList')->info('<pre>' . print_r($progList, TRUE) . '</pre>');
      asort($progList);
      return $progList;
    }

  }

}

/**
 *
 */
function campusDataFormat($campus_array_val) {
  // \Drupal::logger('campusarray')->info('<pre>' . print_r($campus_array_val, TRUE) . '</pre>');
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
        $campus_array_val[$key] = "ASU at Lake Havasu";
      }
      if ($value == "DTPHX") {
        $campus_array_val[$key] = "Downtown Phoenix";
      }
      if ($value == "ONLNE") {
        $campus_array_val[$key] = "Online";
      }
      if ($value == "LOSAN") {
        $campus_array_val[$key] = "ASU Local";
      }
    }
    // ksm($campus_array_val);
    $campus_data = implode(", ", $campus_array_val);
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
      $single_campus_value = "ASU at Lake Havasu";
    }
    if ($single_campus_value == "DTPHX") {
      $single_campus_value = "Downtown Phoenix";
    }
    if ($single_campus_value == "LOSAN") {
      $single_campus_value = "ASU Local";
    }
    $campus_data = $single_campus_value;
  }
  return $campus_data;
}
