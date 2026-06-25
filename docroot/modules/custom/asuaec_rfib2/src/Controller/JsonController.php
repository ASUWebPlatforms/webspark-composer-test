<?php

namespace Drupal\asuaec_rfib2\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for generating JSON pages.
 */
class JsonController extends ControllerBase {
  /**
   * Handler for JSON request.
   * Build JSON page.
   * ie: /admin/asuaec_transferoption/autocomplete/keywords?q=bio
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonCat(Request $request) {

      // Get ground/online and Ugrad/Grad
      $requestUri = $request->getRequestUri();
      $uriSegment_array = explode('/',$requestUri);
      $lastUriSegment = end($uriSegment_array);
      $grad_ugrad = $lastUriSegment;
      $ground_online = $uriSegment_array[count($uriSegment_array)-2];

      $results = [];
      // Get the grad/ugrad string from the URL, if it exists.
      if (!$grad_ugrad || !$ground_online) {
          return new JsonResponse($results);
      }

      $database = \Drupal::database();
      $table = '';
      $field = '';

      switch ($ground_online) {
          case 'ground':
              $field = 'categoryname';
              if($grad_ugrad == 'ugrad') {
                  $table = 'asu_ugrad_interest_category_degrees';
              } else if($grad_ugrad == 'grad') {
                  $table = 'asu_grad_interest_category_degrees';
              }
              break;

          case 'online':
              $field = 'onlineinterestarea';
              if($grad_ugrad == 'ugrad') {
                  $table = 'asu_online_degrees';
              } else if($grad_ugrad == 'grad') {
                  $table = 'asu_online_degrees';
              }
      }

//        $input = Xss::filter($input);

      $query = $database->select($table, 't');
      $query->fields('t', [$field]);
      $query->orderBy($field, 'ASC');
      $result = $query->distinct()->execute();
      $catOptions = array();
      foreach ($result as $record) {
          $catName = $record->$field;
          if($ground_online == 'ground') { // Ground
              $catOptions[$catName] = $catName;
          }
          else if($ground_online == 'online') { // Online
              $catOptionsTemp = explode('|', $catName);
              foreach($catOptionsTemp as $catName) {
                  if(!in_array($catName, $catOptions)) {
                      $catOptions[$catName] = $catName;
                  }
              }
          }
      }
      asort($catOptions);
      return new JsonResponse($catOptions);
  }


  /**
   * Handler for JSON request.
   * Build JSON page.
   * ie: /admin/asuaec_rfib2/json/degrees/online/ugrad/Art and design
   * ie: /admin/asuaec_rfib2/json/degrees/ground/ugrad/Business
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonDegree(Request $request) {

      // Get ground/online and Ugrad/Grad
      $requestUri = $request->getRequestUri();
      $uriSegment_array = explode('/',$requestUri);
      $lastUriSegment = end($uriSegment_array); //<-- Interest
      $interest = urldecode($lastUriSegment);
      $grad_ugrad = $uriSegment_array[count($uriSegment_array)-2];
      $ground_online = $uriSegment_array[count($uriSegment_array)-3];

      $results = [];
      // Get the grad/ugrad string from the URL, if it exists.
      if (!$grad_ugrad || !$ground_online) {
          return new JsonResponse($results);
      }

      $database = \Drupal::database();
      $table = '';
      $fields_array = array();
      $degree_options = array();

      switch ($ground_online) {
          case 'ground':
              $field = array('categorymajorcode', 'categorymajorname', 'categorydegree', 'categoryprogramname' );
              if($grad_ugrad == 'ugrad') {
                  $table = 'asu_ugrad_interest_category_degrees';
              } else if($grad_ugrad == 'grad') {
                  $table = 'asu_grad_interest_category_degrees';
              }
              $degree_options = $this->getDegreesGround($database, $table, $fields_array, $grad_ugrad, $interest);
              break;

          case 'online':
              $table = 'asu_online_degrees';
              $fields_array = array('onlinecode', 'onlinetitle');
              $degree_options = $this->getDegreesOnline($database, $table, $fields_array, $grad_ugrad, $interest);
      }
      asort($degree_options);
      return new JsonResponse($degree_options);
  }


  /**
   * @param $database
   * @param $table
   * @param $fields_array
   * @param $grad_ugrad
   * @param $interest
   * @return array
   */
  public function getDegreesGround($database, $table, $fields_array, $grad_ugrad, $interest) {
      $query = $database->select($table, 't');
      $query->fields('t', $fields_array);
      $query->condition('categoryname', '%' . Database::getConnection()->escapeLike($interest) . '%', 'LIKE');
      $query->orderBy('categorymajorname', 'ASC');
      $result = $query->distinct()->execute();
      $degreeOptions = array();
      foreach ($result as $record) {
        $degreeName = $record->categorymajorname;
        $degreeCode = $record->categorymajorcode;
        $programName = $record->categoryprogramname;
        if($grad_ugrad == 'ugrad') {
          $degree = $record->categorydegree;
          if($degree == '') {
//                    $degreeOptions[$degreeCode] = $degreeName . ' (' . $programName . ')';
            $degreeOptions[$degreeCode] = $degreeName;
          } else {
//                    $degreeOptions[$degreeCode] = $degreeName . '(' . $degree . ')' . ' (' . $programName . ')';
            $degreeOptions[$degreeCode] = $degreeName . '(' . $degree . ')';
          }
        }
        if($grad_ugrad == 'grad') {
          $degreeOptions[$degreeCode] = $degreeName;
        }
      }
      return $degreeOptions;
  }


  /**
   * Get Online degrees from database
   *
   * @param $database
   * @param $table
   * @param $fields_array
   * @param $grad_ugrad
   * @param $interest
   * @return array
   */
  public function getDegreesOnline($database, $table, $fields_array, $grad_ugrad, $interest) {
      $onlinecategory = '';
      switch($grad_ugrad) {
          case 'ugrad':
              $onlinecategory = 'Undergraduate';
              break;
          case 'grad':
              $onlinecategory = 'Graduate';
              break;
          case 'cert':
              $onlinecategory = 'Certificates';
      }
      $query = $database->select($table, 't');
      $query->fields('t', $fields_array);
      $query->condition('onlinecategory', $onlinecategory, '=');
      $query->condition('onlineinterestarea', '%' . Database::getConnection()->escapeLike($interest) . '%', 'LIKE');
      $query->orderBy('onlinetitle', 'ASC');
      $result = $query->distinct()->execute();
      $degreeOptions = array();
      foreach ($result as $record) {
          $degreeName = $record->onlinetitle;
          $degreeCode = $record->onlinecode;
          $degreeOptions[$degreeCode] = $degreeName;
      }
      return $degreeOptions;
  }


  /**
   * Handler for JSON request.
   * Build JSON page.
   * ie: /admin/asuaec_rfib2/json/term/grad/PPAPDTMSW
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonTerm (Request $request) {

      // Get ground/online and Ugrad/Grad
      $requestUri = $request->getRequestUri();
      $uriSegment_array = explode('/',$requestUri);
      $lastUriSegment = end($uriSegment_array);
      $plancode = $lastUriSegment;

      $results = [];
      // Get the plancode string from the URL, if it exists.
      if (!$plancode) {
          return new JsonResponse($results);
      }

      $database = \Drupal::database();
      $table = 'asu_grad_interest_category_degrees';
      $field = 'categorygraduateAllApplyDates';

      $query = $database->select($table, 't');
      $query->fields('t', [$field]);
      $query->condition('categorymajorcode', $plancode, '=');
      $query->orderBy($field, 'ASC');
      $result = $query->distinct()->execute();
      $options = array();
      foreach ($result as $record) {
          $data = $record->$field;
          if(trim($data) != '') {
              $termdata_array = explode(',',$data);
              foreach($termdata_array as $td) {
                  $temp_array = explode(':',$td);
                  if(!array_key_exists($temp_array[2], $options)) {
                      $options[$temp_array[2]] = $temp_array[2]; // Ignoring campuses and just looking at term code
                      $options[$temp_array[2]] = getHumanReadableTerm($temp_array[2]);
                  }
              }
          }
      }
      asort($options);
      return new JsonResponse($options);
  }


  /**
   * Handler for JSON request.
   * Build JSON page.
   * ie: /admin/asuaec_rfib2/json/gradtype/PPAPDTMSW
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonGradtype(Request $request) {
      // Get plancode
      $requestUri = $request->getRequestUri();
      $uriSegment_array = explode('/',$requestUri);
      $lastUriSegment = end($uriSegment_array);
      $plancode = $lastUriSegment;

      $options = [];
      if (!$plancode) {
          return new JsonResponse($options);
      }

      $database = \Drupal::database();
      $table = 'asu_grad_interest_category_degrees';
      $field = 'categorygradtype';

      $query = $database->select($table, 't');
      $query->fields('t', [$field]);
      $query->condition('categorymajorcode', $plancode, '=');
      $result = $query->distinct()->execute();
      foreach ($result as $record) {
          $gradtype = $record->$field;
          $options[$gradtype] = $gradtype;
      }
//        asort($options); //<-- gradtype will be just 1.
      return new JsonResponse($options);
  }


  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonWsdirectTerm (Request $request) {

    // Get ground/online and Ugrad/Grad
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array);
    $plancode = $lastUriSegment;

    $results = [];
    // Get the plancode string from the URL, if it exists.
    if (!$plancode) {
      return new JsonResponse($results);
    }

    //-----------------------------------
    // DB version
//    $database = \Drupal::database();
//    $table = 'asu_grad_interest_category_degrees';
//    $field = 'categorygraduateAllApplyDates';
//
//    $query = $database->select($table, 't');
//    $query->fields('t', [$field]);
////        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
//    $query->condition('categorymajorcode', $plancode, '=');
//    $query->orderBy($field, 'ASC');
////        $query->range(0, 10);
//    $result = $query->distinct()->execute();
//    $options = array();
//    foreach ($result as $record) {
//      $data = $record->$field;
//      if(trim($data) != '') {
//        $termdata_array = explode(',',$data);
//        foreach($termdata_array as $td) {
//          $temp_array = explode(':',$td);
//          if(!array_key_exists($temp_array[2], $options)) {
//            $options[$temp_array[2]] = $temp_array[2]; // Ignoring campuses and just looking at term code
//            $options[$temp_array[2]] = getHumanReadableTerm($temp_array[2]);
//          }
//        }
//      }
//    }

    //-----------------------------------
    // Web service direct version

    $grad_ugrad = 'graduate'; // Used only for Grad
    // Use cached ground degrees if available
    $cached_items = \Drupal::cache()->get('asuaec_ground_degrees_' . $grad_ugrad);
    if($cached_items) {
      $file_contents = $cached_items->data;
    } else {
      $file_contents = $this->_get_degrees_from_webservice($grad_ugrad, 'false');
      // Cache degrees for later use
      \Drupal::cache()->set('asuaec_ground_degrees_' . $grad_ugrad, $file_contents);
    }

    $options = array();
    // Keep only the degree that we are looking at
    foreach($file_contents as $programs => $thearray) {
      foreach($thearray as $key2 => $theobj) {

        $data = array();
        $data['AcadPlan'] = $theobj->AcadPlan; //<--- This is text.

        if($data['AcadPlan'] == $plancode) {
          $data['graduateAllApplyDates'] = $theobj->graduateAllApplyDates; //<--- This is array.
          foreach($data['graduateAllApplyDates'] as $key => $value) {
            //$key: ONLNE:REG:2257
            $temp_array = explode(':',$key);
            $options[$temp_array[2]] = $temp_array[2]; // Ignoring campuses and just looking at term code
            $options[$temp_array[2]] = getHumanReadableTerm($temp_array[2]);
          }
        }

      } // END OF foreach($thearray as $key2 => $theobj)
    } // END OF foreach($file_contents as $programs => $thearray)
    asort($options);
    return new JsonResponse($options);
  }


  /**
   * Handler for JSON request. - Webservice direct
   * Build JSON page.
   * ie: /admin/asuaec_rfib2/json/wsdirect/categories/ground/ugrad
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonWsdirectCat(Request $request) {
    // Get ground/online and Ugrad/Grad
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array);
    $grad_ugrad = $lastUriSegment;
    if($grad_ugrad == 'grad') {
      $grad_ugrad = 'graduate';
    } else if ($grad_ugrad == 'ugrad') {
      $grad_ugrad = 'undergrad';
    }
    $ground_online = $uriSegment_array[count($uriSegment_array)-2];

    $catOptions = [];

    // Get the grad/ugrad string from the URL, if it exists.
    if (!$grad_ugrad || !$ground_online) {
      return new JsonResponse($catOptions);
    }

    // Direct Web service
    switch ($ground_online) {
      case 'ground':
        // Added caching mechanism
        //$catOptions = $this->_get_all_categories_from_webservice_ground($grad_ugrad);

        // Use cached ground cats if available
        $cached_items = \Drupal::cache()->get('asuaec_ground_cats_' . $grad_ugrad);
        // \Drupal::logger('asuaec_rfib2')->notice("cached ground cats: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          // \Drupal::logger('asuaec_rfib2')->notice("There is cached cats.(ground) - cstest");
          $catOptions = $cached_items->data;
        } else {
          $catOptions = $this->_get_all_categories_from_webservice_ground($grad_ugrad);
          // \Drupal::cache()->set('asuaec_ground_cats_' . $grad_ugrad, $catOptions);
        }
        break;

      case 'online':

        //---------------------
        // DB version
//          $database = \Drupal::database();
//          $field = 'onlineinterestarea';
//          $table = 'asu_online_degrees';
//
//          $query = $database->select($table, 't');
//          $query->fields('t', [$field]);
//          $query->orderBy($field, 'ASC');
//          $result = $query->distinct()->execute();
//          $catOptions = array();
//          foreach ($result as $record) {
//            $catName = $record->$field;
//            $catOptionsTemp = explode('|', $catName);
//  //                \Drupal::logger('asuaec_rfib2')->notice("catOptionsTemp:<pre>" . print_r($catOptionsTemp, true) . "</pre>");
//  //                foreach($catOptionsTemp as $the_interest_area_array) {
//            foreach($catOptionsTemp as $catName) {
//              if(!in_array($catName, $catOptions)) {
//                $catOptions[$catName] = $catName;
//              }
//            }
//          }

        //---------------------
        // Web service direct version

        // Use cached online cats if available
        $cached_items = \Drupal::cache()->get('asuaec_online_cats_' . $grad_ugrad);
        // \Drupal::logger('asuaec_rfib2')->notice("cached online cats: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          // \Drupal::logger('asuaec_rfib2')->notice("There is cached cats.(online) - cstest");
          $catOptions = $cached_items->data;
        } else {

          // Get online degrees first

          // Added caching mechanism
//        $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);

          // Use cached online degrees if available
          $cached_items2 = \Drupal::cache()->get('asuaec_online_degrees_' . $grad_ugrad);
          if($cached_items2) {
            $degrees = $cached_items2->data;
          } else {
            $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);
          }
          foreach($degrees as $key => $value) {
            $catOptions[$key] = $key;
          }
          // Cache the catOptions
          \Drupal::cache()->set('asuaec_online_cats_' . $grad_ugrad, $catOptions);
        }
        break;
    }
    asort($catOptions);
    return new JsonResponse($catOptions);
  }


  /**
   * @param $grad_ugrad // undergrad/graduate
   * @return array
   * Modified a function from asupesc_degree_webservice.module at line 247.
   */
  protected function _get_all_categories_from_webservice_ground($grad_ugrad) {
    $webservice_xml_url = 'https://degrees.apps.asu.edu/XmlRpcServer';
    // Get all categories list
    $all_categories = array();
    $categories = xmlrpc($webservice_xml_url, array('eAdvisorDSFind.listCategoriesMap'  => array($grad_ugrad)));
    foreach($categories as $cat_id => $cat_name){
      $all_categories[$cat_name] = $cat_name;
    }
    return $all_categories;
  }


  /**
   * Pull from Web service directly.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonWsdirectDegree(Request $request) {
    // Get ground/online and Ugrad/Grad
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array); //<-- Interest
    $interest = urldecode($lastUriSegment);
    $grad_ugrad = $uriSegment_array[count($uriSegment_array)-2];
    $ground_online = $uriSegment_array[count($uriSegment_array)-3];

    $results = [];
    // Get the grad/ugrad string from the URL, if it exists.
    if (!$grad_ugrad || !$ground_online) {
      return new JsonResponse($results);
    }

    $degree_options = array();
    switch ($ground_online) {
      case 'ground':

        if($grad_ugrad == 'ugrad') {
          $grad_ugrad = 'undergrad';
        } else if ($grad_ugrad == 'grad') {
          $grad_ugrad = 'graduate';
        }
        // Use cached ground degrees if available
        $cached_items = \Drupal::cache()->get('asuaec_ground_degrees_' . $grad_ugrad);
        if($cached_items) {
          $file_contents = $cached_items->data;
        } else {
          $file_contents = $this->_get_degrees_from_webservice($grad_ugrad, 'false');
          // Cache degrees for later use
          \Drupal::cache()->set('asuaec_ground_degrees_' . $grad_ugrad, $file_contents);
        }

        // Keep only the degrees with the interest that was passed
        foreach($file_contents as $programs => $thearray) {
          foreach($thearray as $key2 => $theobj) {
            $data = array();
            // Interest bucket
            $data['planCatDescr'] = $theobj->planCatDescr; //<--- This is array.
//            ksm($data['planCatDescr'], "plan cat desc");

            // if the category matches with the interest that was passed($interest), put the degree in array
            $interestMaches = false;
            foreach($data['planCatDescr'] as $key => $value) {
              if($value == $interest) {
                $interestMaches = true;
                break;
              }
            }
            if($interestMaches) {
              $data['AcadPlan'] = $theobj->AcadPlan;
              $data['Descr100'] = isset($theobj->Descr100) ? $theobj->Descr100 : '';
              if($grad_ugrad == 'undergrad') {
                if($theobj->Degree == '') {
                  $degree_options[$data['AcadPlan']] = $data['Descr100'];
                } else {
                  $degree_options[$data['AcadPlan']] = $data['Descr100'] . '(' . $theobj->Degree . ')' ;
                }
              } else if ($grad_ugrad == 'graduate') {
                $degree_options[$data['AcadPlan']] = $data['Descr100'];
              }
            }
          } // END OF foreach($thearray as $key2 => $theobj)
        } // END OF foreach($file_contents as $programs => $thearray)
        break;

      case 'online':
        // For cache, use "graduate"/"undergrad" instead of "grad"/"ugrad"
        if($grad_ugrad == 'ugrad') {
          $grad_ugrad_caching = 'undergrad';
        } else if ($grad_ugrad == 'grad') {
          $grad_ugrad_caching = 'graduate';
        }

        // Use cached online degrees if available
        $cached_items = \Drupal::cache()->get('asuaec_online_degrees_' . $grad_ugrad_caching);
        // \Drupal::logger('asuaec_rfib2')->notice("cached online degree: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          // \Drupal::logger('asuaec_rfib2')->notice("There is cached degrees.(online) - cstest");
          $degrees = $cached_items->data;
        } else {
          $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);
          //\Drupal::cache()->set('asuaec_online_degrees_' . $grad_ugrad_caching, $degrees); //<-- Cache degrees inside _get_degrees_from_webservice_online()
        }
        foreach($degrees as $key_interest => $value_array) {
          if($key_interest == $interest) {
           foreach($value_array as $key_onlinecode => $value) {
              $degree_options[$key_onlinecode] = $value['onlinetitle'];
            }
          }
        }
        break;
    }
    asort($degree_options);
    return new JsonResponse($degree_options);
  }


  /**
   * Get Ground degrees from web service using new method that returns Json.
   *
   * @param string $grad_ugrad
   *  'graduate' or 'undergrad'
   * @param string $cert
   *  'true' or 'false'
   * @return mixed|string
   *  Returns Json decoded object
   */
  protected function _get_degrees_from_webservice($grad_ugrad, $cert) {
    $client = \Drupal::httpClient();
    $filter = '';
    if($grad_ugrad == 'graduate') {
//      $filter = '?method=findAllDegrees&program=' . $grad_ugrad . '&cert=' . $cert . '&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,DegreeDescrshort,DegreeDescrformal,Descr100,AcadPlan,AsuCritTrackUrl,DegreeEducationLvl,graduateAllApplyDates,AsuCustomText,AsuNactvAppOvrd';
      $filter = '?method=findAllDegrees&program=' . $grad_ugrad . '&cert=' . $cert . '&fields=planCatDescr,CampusStringArray,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,DegreeDescrshort,DegreeDescrformal,Descr100,AcadPlan,DegreeEducationLvl,graduateAllApplyDates';

    } else if ($grad_ugrad == 'undergrad') {
//      $filter = '?method=findAllDegrees&program='. $grad_ugrad . '&cert=false&fields=planCatDescr,CampusStringArray,DiplomaDescr,CollegeUrl,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,Descr100,AcadPlan,AsuCritTrackUrl,Degree,AsuCustomText,AsuNactvAppOvrd';
      $filter = '?method=findAllDegrees&program='. $grad_ugrad . '&cert=false&fields=planCatDescr,CampusStringArray,AcadProg,CollegeDescr100,CollegeAcadOrg,DepartmentCode,Descr100,AcadPlan,Degree';
    }
    $file_contents = [];
    try {
//      $config = \Drupal::config('asupesc_degree_webservice.settings');
//      $webservice_url = $config->get('asupesc_degree_webservice_url');

      // Get degreews from admin config page. - 10/8/2025
      $config_data = \Drupal::config('asuaec_rfib2.customadmin_settings');
      $degreews = $config_data ? $config_data->get('degreews') : NULL;
      if ($degreews === 'devdegreews') {
        $webservice_url = 'https://degrees-qa.apps.asu.edu/t5/service';
      } else {
        $webservice_url = 'https://degrees.apps.asu.edu/t5/service';
      }
      $url = $webservice_url . $filter;
      // \Drupal::logger('asupesc_degree_webservice2')->notice("url: <pre>" . $url . "</pre>");
      $request = $client->get($url);
      $code = $request->getStatusCode();
      if ($code == 200) {
        $content = $request->getBody()->getContents();
        if ($content != null) {
          $file_contents = json_decode($content);
        } else {
          throw new Exception("Web service didn't return anything.");
        }
      } else {
        throw new Exception("Error occured. Error code: " . $code);
      }
    }
    catch (Exception $e) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t($e->getMessage(), []));
      \Drupal::logger('asupesc_degree_webservice')->error($e->getMessage());
    }

    // If campus is only ONLNE, remove it. Added on 3/7/2025.
    $data = $file_contents;
//    \Drupal::logger('asuaec_rfib2')->notice("Original data:<pre>" . print_r($data, true) . "</pre>");

    // Initialize an empty array to hold the filtered programs
    $filtered_programs = [];

    // Iterate through each program
    foreach ($data->programs as $program) {
        // Check if CampusStringArray contains only "ONLNE"
        if (!(count($program->CampusStringArray) === 1 && $program->CampusStringArray[0] === 'ONLNE')) {
            // If not, add it to the filtered programs array
            $filtered_programs[] = $program;
        }
    }

    // Maintain the original structure
    $file_contents = new \stdClass();
    $file_contents->programs = $filtered_programs;

    // Log the filtered data
//    \Drupal::logger('asuaec_rfib2')->notice("file_contents before return:<pre>" . print_r($file_contents, true) . "</pre>");
    return $file_contents;
  }


  /**
   * Get Online degrees from web service and cache degrees.
   * Modified function asupesc_degree_webservice_data_insert_online.
   *
   * @param $grad_ugrad
   * @return array
   */
  protected function _get_degrees_from_webservice_online($grad_ugrad) {
    if($grad_ugrad == 'undergrad') {
      $grad_ugrad = 'ugrad';
    } else if ($grad_ugrad == 'graduate') {
      $grad_ugrad = 'grad';
    }

    $asu_online_degrees = array();
    $degrees_array = array();
    $degree_errored_array = array();
    $client = \Drupal::httpClient();
    try {
      // Get degrees from web service
//      $config = \Drupal::config('asupesc_degree_webservice.settings');
//      $webservice_url = $config->get('asupesc_degree_webservice_url_online');
      $webservice_url = 'https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs';

      $filter = '';
      if($grad_ugrad == 'ugrad') {
        $filter = '?category=undergraduate';
      }
      if($grad_ugrad == 'grad') {
        $filter = '?category=graduate';
      }
      $url = $webservice_url . $filter;
      // \Drupal::logger('asupesc_degree_webservice2')->notice("url: <pre>" . $url . "</pre>");

      $request = $client->get($url, array('headers' => array('Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded')));
      // \Drupal::logger('asupesc_degree_webservice')->notice("request: <pre>" . print_r($request, true) . "</pre>");

      $code = $request->getStatusCode();
      if ($code == 200) {
        $content = $request->getBody()->getContents();
        // \Drupal::logger('asupesc_degree_webservice')->notice("content: <pre>" . print_r($content, true) . "</pre>");

        if ($content != null) {
          // $xml = simplexml_load_string(utf8_encode($content));
          // if ($xml === false) {
          //   echo "Failed loading XML: ";
          //   foreach(libxml_get_errors() as $error) {
          //     echo "<br>", $error->message;
          //   }

          // } else {

          // Capture XML errors nicely.
          libxml_use_internal_errors(true);

          // Trim + remove BOM(Byte Order Mark) if present.
          $xml_string = (string) $content;
          $xml_string = preg_replace('/^\xEF\xBB\xBF/', '', $xml_string);

          // Parse XML WITHOUT utf8_encode().
          $xml = simplexml_load_string($xml_string);

          if ($xml === false) {
            \Drupal::logger('asupesc_degree_webservice')->error('XML parse failed: @e', [
              '@e' => implode(' | ', array_map(fn($e) => trim($e->message), libxml_get_errors())),
            ]);
            libxml_clear_errors();

            // Helpful debug: log the first chunk escaped.
            \Drupal::logger('asupesc_degree_webservice')->error('XML (escaped head): <pre>@x</pre>', [
              '@x' => Html::escape(substr($xml_string, 0, 1500)),
            ]);

            return [];
          }

          libxml_clear_errors();

          // \Drupal::logger('asupesc_degree_webservice')->notice("xml: <pre>" . print_r($xml, true) . "</pre>");
          foreach ($xml->program as $degree_obj ) {
            // Interest area
            $interest_areas_array = array();
            $onlineinterestareas_obj = $degree_obj->interestareas;
            foreach($onlineinterestareas_obj->value as $interest_area ) {
              array_push($interest_areas_array, $interest_area);
            }
            $interest_areas_string = implode('|', $interest_areas_array);
            // Sub plan
            $subplans_array = array();
            $onlinesubplancode_obj = $degree_obj->subplancode;
            foreach($onlinesubplancode_obj->value as $subplan ) {
              array_push($subplans_array, $subplan);
            }
            $subplans_string = implode('|', $subplans_array);
            $onlinecode = isset($degree_obj->code) ? $degree_obj->code : '';
//              \Drupal::logger('asuaec_rfib2')->notice("onlinecode: <pre>" . $onlinecode . "</pre>");
            $onlinecategory = isset($degree_obj->category) ? $degree_obj->category : '';
            $onlineprogcode = $degree_obj->progcode;
            $onlineplancode = $degree_obj->plancode;
            $onlineshortdesc = $degree_obj->shortdesc;
            $onlineurl = $degree_obj->detailpage;
            $onlinecrmdestination = $degree_obj->crmdestination;

            foreach($interest_areas_array as $interest_area_obj) {
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecode'] = "{$onlinecode}";

              // Fix mojibake on the field itself.
              $title = isset($degree_obj->title) ? (string) $degree_obj->title : '';
              $title = $this->normalizeText($title);
              $title = str_replace('–', '-', $title);
              // Drop invalid UTF-8 bytes defensively (keeps everything valid UTF-8).
              $title = iconv('UTF-8', 'UTF-8//IGNORE', $title);
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinetitle'] = $title;

              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecategory'] = "{$onlinecategory}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineinterestarea'] = $interest_areas_string;
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineprogcode'] = "{$onlineprogcode}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineplancode'] = "{$onlineplancode}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinesubplancode'] = $subplans_string;
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineshortdesc'] = "{$onlineshortdesc}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineurl'] = "{$onlineurl}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecrmdestination'] = "{$onlinecrmdestination}";
            }
          } // END of foreach ($xml->program as $key => $value )
          // }
        } else {
          throw new Exception("Web service didn't return anything.");
        }
      } else {
        throw new Exception("Error occured. Error code: " . $code);
      }

    }
    catch (Exception $e) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t($e->getMessage(), []));
      \Drupal::logger('asuaec_rfib2')->error($e->getMessage());
    }

    // Cache $degrees_array for later use
    if($grad_ugrad == 'ugrad') {
      $grad_ugrad_cache = 'undergrad';
    } else if ($grad_ugrad == 'grad') {
      $grad_ugrad_cache = 'graduate';
    }
    // \Drupal::cache()->set('asuaec_online_degrees_' . $grad_ugrad_cache, $degrees_array);
    return $degrees_array;
  } // END OF function asupesc_degree_webservice_data_insert_online()

  /**
   * Normalize text coming from the ASU Online XML feed.
   * Fixes mojibake like "â" / "â" by replacing the *byte sequences*.
   */
  protected function normalizeText(string $s): string {
    if ($s === '') {
      return $s;
    }

    // These are UTF-8 bytes for the broken sequences (mojibake).
    // Example: "â" (C3 A2 C2 80 C2 93) should become "–" (E2 80 93).
    $replacements = [
      "\xC3\xA2\xC2\x80\xC2\x93" => "–", // â  en dash
      "\xC3\xA2\xC2\x80\xC2\x94" => "—", // â  em dash
      "\xC3\xA2\xC2\x80\xC2\x99" => "’", // â  right apostrophe
      "\xC3\xA2\xC2\x80\xC2\x98" => "‘", // â  left apostrophe
      "\xC3\xA2\xC2\x80\xC2\x9C" => "“", // â  left quote
      "\xC3\xA2\xC2\x80\xC2\x9D" => "”", // â  right quote
      "\xC3\xA2\xC2\x80\xC2\xA6" => "…", // â¦  ellipsis
      "\xC2\xA0"                 => " ", // non-breaking space -> space
    ];

    $s = strtr($s, $replacements);

    // Safety: ensure valid UTF-8 output
    $s = iconv('UTF-8', 'UTF-8//IGNORE', $s);

    return $s;
  }

} // END OF class


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




