<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * This service is called in mMyPathFormASU.php file to get online programs list.
 */
class AsuOnlineProgramList {

  /**
   *
   *
   * @return array of programs list
   */
  public function getOnlineProgramList($program = NULL, $interest = NULL) {

    // $interest = str_replace('+',' ',$interest_var);
    $client = \Drupal::httpClient();
    $url = "https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=$program";
    $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded']]);
    $code = $request->getStatusCode();
    $content = $request->getBody()->getContents();
    $xml = simplexml_load_string($content);
    foreach ($xml->program as $programs) {
      $catPorg[] = $programs->interestareas;
      $interest_value = (string) $programs->interestareas->value;
      $string_title = (string) $programs->title;
      $format_prog_title = str_replace('â', '', $string_title);
      // $progTitle_code = (string) $programs->code.'*'.$interest_value;
      // $progarmCodeArray = explode('-',$programs->code);
      // $programCode = $progarmCodeArray[1];
      $progTitle_code = (string) $programs->code . '*' . $interest_value;
      $college = $programs->collegename;
      // $online_programDetails = "<p><Strong>Program details</strong></p><p><strong>Program:</strong> $format_prog_title<br /><strong>Area of interest: </strong>$interest_value<br /><strong>College:</strong> $college</p>";
      $online_programDetails = "<p><strong>$format_prog_title</strong><br />$interest_value</p>";
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
