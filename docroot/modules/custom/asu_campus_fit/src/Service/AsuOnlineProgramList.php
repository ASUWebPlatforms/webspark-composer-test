<?php

namespace Drupal\asu_campus_fit\Service;

/**
 * This service is called in mMyPathFormASU.php file to get online programs list.
 */
class AsuOnlineProgramList {

  /**
   *
   *
   * @return array of programs list
   */
  public function getOnlineProgramList($program = NULL, $interest = NULL, $campus = NULL) {
    $client = \Drupal::httpClient();
    if ($program == 'undergrad') {
      $programType = "Undergraduate";
    }
    else {
      $programType = "Graduate";
    }
    $url = "https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=$programType";
    $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded']]);
    $code = $request->getStatusCode();
    $content = $request->getBody()->getContents();
    $xml = simplexml_load_string($content);
    if ($program == "undergrad") {
      $degreeProgram = 'bachelors';
    }
    if ($program == "graduate") {
      $degreeProgram = 'masters-phd';
    }
    // $interest_value = [];
    foreach ($xml->program as $programs) {
      \Drupal::logger('online programs')->info('<pre>' . print_r($programs, TRUE) . '</pre>');
      $catPorg[] = $programs->interestareas;
      $interest_value = (string) $programs->interestareas->value;
      $string_title = (string) $programs->title;
      $planCode = $programs->plancode;
      $format_prog_title = str_replace('â', '', $string_title);

      $encodedDesc = preg_replace('/[\s,(){}]+/', '-', strtolower($format_prog_title));
      $encodedDesc = trim($encodedDesc, '-');
      $pos = strrpos($encodedDesc, '-');
      if ($pos !== FALSE) {
        $trimmedTitle = rtrim(substr($encodedDesc, 0, $pos));
      }
      else {
        // No '-' found, return original.
        $trimmedTitle = rtrim($string);
      }
      // \Drupal::logger('$trimmedTitle')->info('<pre>' . print_r($trimmedTitle, TRUE) . '</pre>');
      // $progTitle_code = (string) $programs->code.'*'.$interest_value;
      // $progarmCodeArray = explode('-',$programs->code);
      // $programCode = $progarmCodeArray[1];
      $progTitle_code = (string) $programs->code . '*' . $interest_value;
      $college = $programs->collegename;
      $degreeUrl = "https://degrees.apps.asu.edu/$degreeProgram/major/ASU00/$planCode/$trimmedTitle";
      $online_programDetails = "<p><strong><a href='$degreeUrl'>$format_prog_title</a></strong><br /></p>";
      // $all_prog_data[$progTitle_code] = $format_prog_title;
      $all_prog_data[$progTitle_code] = $online_programDetails;
    }
    foreach ($all_prog_data as $key => $value) {
      if (str_contains($key, $interest)) {
        $prog_val = str_replace('â', '', $value);
        $progListData[$key] = $prog_val;
      }

    }
    // ksm($progListData);
    foreach ($progListData as $pkey => $pvalue) {
      // ksm($key);
      // if(str_contains($pkey, $interest)){.
      $just_plancode = explode('*', $pkey);
      $progList[$just_plancode[0]] = $pvalue;
      // }
    }
    asort($progList);
    return $progList;

  }

}
