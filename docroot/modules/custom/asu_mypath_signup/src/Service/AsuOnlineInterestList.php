<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */
class AsuOnlineInterestList {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function getOnlineInetrestList($catdata = NULL) {

    $client = \Drupal::httpClient();
    $url = 'https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs?method=findAllDegrees&program=undergrad&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,Descr100,AcadPlan,AsuCritTrackUrl,Degree,AsuCustomText,AsuNactvAppOvrd';
    // $request = $client->get($url, array('headers' => array('Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded')));
    $request = $client->get($url, ['headers' => ['Accept' => 'text/xml', 'Content-Type' => 'application/json']]);
    $code = $request->getStatusCode();
    $content = $request->getBody()->getContents();
    // ksm($content);
    $json_data = Json::decode($request->getBody());
    // ksm($json_data);
    // $xml = simplexml_load_string(utf8_encode($content));
    $xml = simplexml_load_string($content);

    foreach ($xml->program as $key => $programs) {
      // ksm($programs);
      $catPorg[] = (string) $programs->interestareas->value;
      /*$string_title = (string) $programs->title;
      $format_prog_title = str_replace('â', '',$string_title);
      $progTitle_code = $format_prog_title.'*'.(string) $programs->code;
      $all_prog_data[$progTitle_code] = $format_prog_title;*/
    }

    $uniqueCatList = array_unique($catPorg);
    // ksm($uniqueCatList);
    // ksm(string($uniqueCatList[0]);
    foreach ($uniqueCatList as $key => $pvalue) {
      $interestList[$pvalue] = $pvalue;
    }
    unset($interestList['']);
    // ksm($progList);
    return $interestList;
  }

}
