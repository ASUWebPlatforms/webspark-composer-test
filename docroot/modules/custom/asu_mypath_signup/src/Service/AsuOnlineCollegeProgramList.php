<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * This service is called in mMyPathFormASU.php file to get online programs list.
 */
class AsuOnlineCollegeProgramList {

  /**
   *
   *
   * @return array of programs list
   */
  public function getOnlineCollegeProgramList($program = NULL, $collgeUrl = NULL) {

    // $interest = str_replace('+',' ',$interest_var);
    $client = \Drupal::httpClient();
    $url = "https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?category=$program";
    $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded']]);
    $code = $request->getStatusCode();
    $content = $request->getBody()->getContents();
    $xml = simplexml_load_string($content);
    // ksm($xml->program);.
    foreach ($xml->program as $programs) {
      $college_value = (string) $programs->collegename;
      $college_code = $programs->progcode;
      $string_title = (string) $programs->title;
      $interest_value = (string) $programs->interestareas->value;
      $format_prog_title = str_replace('â', '', $string_title);
      $progTitle_code = $programs->code . '*' . $college_code;
      $progarmCodeArray = explode('-', $programs->code);
      $programCode = $progarmCodeArray[1];
      $college = $programs->collegename;
      // $progTitle_code = (string) $programCode.'*'.$college_code;
      // $online_programDetails = "<p><Strong>Program details</strong></p><p><strong>Program:</strong> $format_prog_title<br /><strong>College:</strong> $college</p>";
      $online_programDetails = "<p><strong>$format_prog_title</strong><br />$interest_value</p>";
      // $all_prog_data[$progTitle_code] = $format_prog_title;
      $all_prog_data[$progTitle_code] = $online_programDetails;
    }
    foreach ($all_prog_data as $key => $value) {
      if (str_contains($key, $collgeUrl)) {
        $key_explode = explode('*', $key);
        $prog_val = str_replace('â', '', $value);
        $progListData[$key_explode[0]] = $prog_val;
      }

    }
    /*foreach($progListData as $pkey => $pvalue){
    $just_plancode = explode('*',$pkey);
    $progList[$just_plancode[0]] = $pvalue;
    }*/
    asort($progListData);
    // ksm($progList);
    // $progList = array_merge(array('0' => 'Select...'), $progList);
    // return json_encode($progList);
    return $progListData;

  }

}
