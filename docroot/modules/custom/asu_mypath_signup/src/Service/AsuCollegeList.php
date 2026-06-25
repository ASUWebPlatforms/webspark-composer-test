<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * This service is called in mMyPathFormASU.php file to get College list.
 */
class AsuCollegeList {

  /**
   * Function to pull oncampus colleges from weservices.
   *
   * @return array of colleges
   */
  public function getCollegeList($campus = NULL, $programVal = NULL) {

    if ($campus == "GROUND") {
      $client = \Drupal::httpClient();
      $url = "https://degrees.apps.asu.edu/t5/service?method=findAllDegrees&program=undergrad&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,DegreeDescrshort,DegreeDescrformal,Descr100,AcadPlan,AsuCritTrackUrl,DegreeEducationLvl,graduateAllApplyDates,AsuCustomText,AsuNactvAppOvrd";

      // $interest = !empty($url_interest)?urldecode($url_interest):'';
      $request = $client->get($url);
      $code = $request->getStatusCode();
      $content = $request->getBody()->getContents();
      // ksm($content);
      $file_contents = json_decode($content);
      // ksm($file_contents);
      // $campus_data = array();
      foreach ($file_contents->programs as $key => $programs) {

        $each_college = $programs->DiplomaDescr;
        $college_code = $programs->AcadProg;
        // ksm($college_code);
        $collegeList[$college_code] = $each_college;
        asort($collegeList);

        $returnData = $collegeList;
      }

      return $collegeList;
    }

    if ($campus == "ONLNE") {
      $client = \Drupal::httpClient();
      $url = "https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=undergraduate";
      $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded']]);
      $code = $request->getStatusCode();
      $content = $request->getBody()->getContents();
      $xml = simplexml_load_string($content);
      foreach ($xml->program as $online_programs) {
        $onlinecollege_code = (string) $online_programs->progcode;
        $on_prog_code = (string) $online_programs->progcode;

        if ($on_prog_code == "UGBA") {
          $online_college_name = "W. P. Carey School of Business";
        }
        if ($on_prog_code == "UGLA") {
          $online_college_name = "The College of Liberal Arts and Sciences";
        }
        if ($on_prog_code == "UGTE") {
          $online_college_name = "Mary Lou Fulton Teachers College";
        }
        if ($on_prog_code == "UGLS") {
          $online_college_name = "College of Integrative Sciences and Arts";
        }
        if ($on_prog_code == "UGCF") {
          $online_college_name = "College of Glocal Futures";
        }

        if ($on_prog_code == "UGHL") {
          $online_college_name = "College of Health Solutions";
        }

        if ($on_prog_code == "UGHI") {
          $online_college_name = "Herberger Institute for Design and the Arts";
        }

        if ($on_prog_code == "UGES") {
          $online_college_name = "Ira A. Fulton Schools of Engineering";
        }

        if ($on_prog_code == "UGNU") {
          $online_college_name = "Edson College of Nursing and Health Innovation";
        }

        if ($on_prog_code == "UGTB") {
          $online_college_name = "Thunderbird School of Global Management";
        }
        if ($on_prog_code == "UGAS") {
          $online_college_name = "New College of Interdisciplinary Arts and Sciences";
        }

        if ($on_prog_code == "UGUC") {
          $online_college_name = "University College";
        }

        if ($on_prog_code == "UGCS") {
          $online_college_name = "Walter Cronkite School of Journalism and Mass Comm";
        }
        if ($on_prog_code == "UGPP") {
          $online_college_name = "Watts College of Public Service & Community Solut";
        }

        // $onlinecollege_name = (string) $online_programs->collegename;
        $onlinecollege_name = $online_college_name;
        $campus_data[$onlinecollege_code] = $onlinecollege_name;
        $returnData = $campus_data;

      }
      return $campus_data;
    }

  }

}
