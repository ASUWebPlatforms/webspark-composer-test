<?php

namespace Drupal\asuaec_json\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for generating JSON pages.
 */
class JsonController extends ControllerBase {

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * Handler for JSON request.
     * Build JSON page to list campuses.
     * Ugrad - List campuses based on Visit bucket selected.
     * Grad - List campuses based on degrees in degrees table in DB.
     * ie:
     * /admin/asuaec_json/json/campus/ugrad/{interest}
     * /admin/asuaec_json/json/campus/grad/{interest}
     */
    public function generateJsonCampusList(Request $request) {

        // Get interest
        $requestUri = $request->getRequestUri();
//        \Drupal::logger('asuaec_visit')->notice('Ajax URL1: ' . $requestUri);
        $uriSegment_array = explode('/',$requestUri);
//        ksm($uriSegment_array, "uriSegment_array in generateJsonCampusList()");
            //0 => string (0) ""
            //⇄1 => string (5) "admin"
            //⇄2 => string (17) "asuaec_json"
            //⇄3 => string (4) "json"
            //⇄4 => string (6) "campus"
            //⇄5 => string (5) "ugrad"
            //⇄6 => string (1) "0"
        $grad_ugrad = isset($uriSegment_array[5]) ? $uriSegment_array[5] : '';
//        $interest = isset($uriSegment_array[6]) ? $uriSegment_array[6] : ''; // Added urldecode on 1/10/2024.
        $interest = urldecode(isset($uriSegment_array[6]) ? $uriSegment_array[6] : '');
        // NOTES: For "Other", we get "ugrad" and "0".

        // DB connection

        $database = \Drupal::database();
        $table = '';
        $field = '';
        switch ($grad_ugrad) {
            case 'ugrad':
                $table = 'asu_visit_buckets';
                $condition_field = 'bucket_tid';
                $field = 'campus';
                break;

            case 'grad':
                $table = 'asu_grad_interest_category_degrees';
                $condition_field = 'categoryname';
                $field = 'categorycampuscode';
                break;
        }

//        $input = Xss::filter($input);

        $query = $database->select($table, 't');
        $query->fields('t', [$field]);
        if($grad_ugrad == 'ugrad') {
            if ($interest == '0') {
                // No condition when it is "0" which happens when "Other" is selected. Display all campuses.
            } else {
                $query->condition($condition_field, $interest, '='); // For Ugrad $interest contains tid.
            }

        } else {
            $query->condition($condition_field, '%' . Database::getConnection()->escapeLike($interest) . '%', 'LIKE');
        }
//        $query->condition('transferAgreementType', $type, '=');
        $query->orderBy($field, 'ASC');
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        $options = array();
        foreach ($result as $record) {
            $campuses = explode(",", $record->$field);
            $campuses_trimmed = array();
            foreach($campuses as $camp) {
                array_push($campuses_trimmed, trim($camp) );
            }

            // Compare with what is in $options array. If the campus is not already in the array, add the campus.
            foreach($campuses_trimmed as $camp2){
                if(!in_array($camp2, $options)){
                    $desc[$camp2] = '';
                    if($interest != 0) { // 0 happens when "Other" person type is selected. There is no "i" for "Other" person type.
                        /*** Archana's code get camps tid from database query ***/
                        $tooltip_begin = '<div class="custom-campus-tooltip"><div class="custom-tooltip-container"><button tabindex="0" class="uds-tooltip uds-tooltip-campus-button uds-tooltip-dark" aria-describedby="tooltip-desc-1"><span class="fa-stack"><svg class="campus-svg svg-inline--fa fa-circle fa-w-16 fa-stack-2x" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="#8c1d40" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg><svg class="svg-inline--fa fa-info fa-w-6 fa-stack-1x" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512" data-fa-i2svg=""><path fill="#ffffff" d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path></svg></span>description and majors</button><div role="tooltip" class="custom-tooltip-campus-description" id="tooltip-desc-1"><button style="color:#fff;" type="button" class="btn btn-default popup-close-btn"><svg class="svg-inline--fa fa-window-close fa-w-16" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="window-close" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"></path></svg></button><div class="formatted-text">';
                        $tooltip_end = '</div></div></div></div>';
                        $coltable = 'taxonomy_term_field_data';
                        $field_array = array('tid');
                        $collegequery = $database->select($coltable, 't');
                        $collegequery->fields('t', $field_array);
                        $collegequery->condition('description__value', $camp2, '=');
                        $collresult = $collegequery->distinct()->execute();
                        foreach ($collresult as $collrecord) {
                            $value_of_campus = $collrecord->tid;
                            if($grad_ugrad == 'ugrad') {
                                //get campus description views results for campus description info
                                $aview = \Drupal\views\Views::getView('campus_description');
                                $aview->setDisplay('page_1');
                                $exposed_filters = ['field_campus_tax_target_id' => $value_of_campus, 'field_visit_bucket_target_id' => $interest];
                                $aview->setExposedInput($exposed_filters);
                                $aview->execute();
                                //$view_render = $aview->render();
                                //$result = \Drupal::service('renderer')->renderRoot($view_render);
                                //$renderer = \Drupal::service('renderer');
                                //$result = $renderer->render($aview->render(), FALSE);
                                foreach ($aview->result as $key => $arow) {
                                    //ksm($key);
                                    $node = $arow->_entity;
                                    // Get the full description value.
                                    $description_val = $node->get('field_description')->value;
                                    $desc[$camp2] = $tooltip_begin . $description_val . $tooltip_end;

                                }
                            }
                        }
                    } // END OF if($interest != 0)

					$campus_var = $camp2;
					/** End of Archana's code **/


                    switch ($camp2) {
//          case "AWC":
//            $camp2_val = "ASU@Yuma";
//            break;
                        case "CALHC":
                            $camp2 = "Havasu";
                            $camp2_val = "<div class='campus-val'>ASU at Lake Havasu</div>".$desc['CALHC'];
							              $campus_desc = $camp2_val.$desc['CALHC'];
                            break;
                        case "DTPHX":
                            $camp2 = "Downtown Phoenix";
                            $camp2_val = "<div class='campus-val'>Downtown Phoenix campus</div>".$desc['DTPHX'];
							              $campus_desc =  $camp2_val.$desc['DTPHX'];
                            break;
//          case "EAC":
//            $camp2_val = "ASU@TheGilaValley";
//            break;
//          case "ONLNE":
//            $camp2 = "ONLNE";
//            $camp2_val = "ASU Online";
//            break;
                        case "POLY":
                            $camp2 = "Polytechnic";
                            $camp2_val = "<div class='campus-val'>Polytechnic campus</div>".$desc['POLY'];
							              $campus_desc =  $camp2_val.$desc['POLY'];
                            break;
                        case "TEMPE":
                            $camp2 = "Tempe";
                            $camp2_val = "<div class='campus-val'>Tempe campus</div>".$desc['TEMPE'];
							              $campus_desc =  $camp2_val.$desc['TEMPE'];
                            break;
//          case "TUCSN":
//            $camp2_val = "ASU@Tucson";
//            break;
                        case "WEST":
                            $camp2 = "West";
                            $camp2_val = "<div class='campus-val'>West Valley campus</div>".$desc['WEST'];
							              $campus_desc =  $camp2_val.$desc['WEST'];
                            break;

//          case "CAC":
//            $camp2_val = "ASU@Pinal";
//            break;
//          case "TBIRD":
//            $camp2_val = "Thunderbird campus";
//            break;

                        case "LOSAN": // Added on 10/20/2023.
//                            $camp2 = "Los Angeles"; // Changed on 4/12/2024
                            $camp2 = "ASU California Center in downtown L.A.";
                            $camp2_val = "<div class='campus-val'>ASU California Center in downtown L.A.</div>".$desc['LOSAN'];
                            $campus_desc = $camp2_val.$desc['LOSAN'];
                            break;

                        // When we start using the Visit site managed event for California, use the following code.
//                        // California Center (1/29/2024)
//                        case "LOSAN": // Changed on 1/10/2024.
//                            $camp2 = "ASU California Center";
//                            $camp2_val = "<div class='campus-val'>ASU California Center</div>".$desc['LOSAN'];
//                            $campus_desc = $camp2_val.$desc['LOSAN'];
//                            break;

                        default:
                            continue 2;
                    }
                    $options[$camp2] = $camp2_val;
					//$options[$camp2] = $campus_desc; //$campus_desc varaible added by Archana
                } // END OF if(!in_array($camp2, $options))
            }
        }
        asort($options);

        // Bring Lake Havasu campus at the bottom.
        if(array_key_exists('Havasu', $options) ) { // Lake Havasu City
            $v = $options['Havasu'];
            unset($options['Havasu']);
            $options['Havasu'] = $v;
        }
        return new JsonResponse($options);
    } // END OF generateJsonCampusList()


    /**
     * @param Request $request
     * @return JsonResponse
     *
     * Grad - List colleges based on multiple campuses and an interest.
     * It will be always for grad since we are not collecting degree data from Ugrad.
     * ie:
     * /admin/asuaec_json/json/get_colleges_by_multi_campuses_and_interest/TEMPE/Arts/grad
     */
    public function getCollegesByMulticampusesAndInterest(Request $request) {

        // Get campuses, interest and grad/ugrad
        $requestUri = $request->getRequestUri();
//        \Drupal::logger('asuaec_visit')->notice('Ajax URL2: ' . $requestUri);
        $uriSegment_array = explode('/',$requestUri);
//        ksm($uriSegment_array, "uriSegment_array in getCollegesByMulticampusesAndInterest()");
        //0 => string (0) ""
        //⇄1 => string (5) "admin"
        //⇄2 => string (11) "asuaec_json"
        //⇄3 => string (4) "json"
        //⇄4 => string (43) "get_colleges_by_multi_campuses_and_interest"
        //⇄5 => string (5) "Tempe"
        //⇄6 => string (4) "Arts"
        //⇄7 => string (4) "grad"
        $grad_ugrad = isset($uriSegment_array[7]) ? $uriSegment_array[7] : '';
//        $interest = isset($uriSegment_array[6]) ? $uriSegment_array[6] : ''; // Added urldecode on 1/10/2024.
        $interest = urldecode(isset($uriSegment_array[6]) ? $uriSegment_array[6] : '');
        $campuses = isset($uriSegment_array[5]) ? $uriSegment_array[5] : '';
//        ksm($campuses, "campuses");
        $campusesArray = explode('|', $campuses);

// NOTES from D7 Visit site
//        // Build Campus condition string inside ()
//        // IN didn't work for this case. Need to use OR with LIKE.
//        $campus_condition_string = '';
//        $i = 0;
//        foreach($campusesArray as $eachcampus) {
//            if($eachcampus != ''){
//                if($i == 0) {
//                    $campus_condition_string = "categorycampuscode LIKE '%" . $eachcampus . "%'";
//                } else {
//                    $campus_condition_string .= " OR categorycampuscode LIKE '%" . $eachcampus . "%'";
//                }
//                $i++;
//            }
//        } // END OF foreach($campusesArray as $eachcampus)


        // DB connection

        $database = \Drupal::database();
        $table = '';
        $fields = [];
        switch ($grad_ugrad) {
            case 'grad': // It will be always grad for Visit site because for Ugrad, we don't ask degree question.
                $table = 'asu_grad_interest_category_degrees';
                $condition_field = 'categoryname';
                $condition_field2 = 'categorycampuscode';
                $fields = ['categoryprogramcode', 'categoryprogramDesrc100', 'categoryprogramname'];
                break;
        }
        $query = $database->select($table, 't');
        $query->fields('t', $fields);
        if($grad_ugrad == 'grad') { // Always grad for this case for Visit site because for Ugrad, we don't ask degree question.
            $query->condition($condition_field, '%' . Database::getConnection()->escapeLike($interest) . '%', 'LIKE');
            $query->condition('categoryprogramcode', 'GRND', '<>');
            $query->condition('categoryprogramcode', 'UGNFA', '<>');
            $query->condition('categoryprogramcode', '', '<>');

//            $query->condition($condition_field, $campusesArray, 'IN'); //<--- IN doesn't work in this case for campuses.
            $or = $query->orConditionGroup();
            foreach($campusesArray as $campus) {
                $campus = urldecode($campus); // Added on 1/12/2024.
                if($campus == 'ASU California Center') { // Added on 1/12/2024.
                  $campus = 'LOSAN';
                }
                $or->condition($condition_field2, '%' . Database::getConnection()->escapeLike($campus) . '%', 'LIKE');
            }
            $query->condition($or);
        }
        $result = $query->distinct()->execute();
        $options = [];
        foreach ($result as $record) {
            $programcode = $record->categoryprogramcode; // GRHI
            $programDesrc100 = $record->categoryprogramDesrc100; // Design and the Arts, Herberger Institute for
            $programDesrc100_userFriendly = $this->getUserFriendlyCollegeName($programDesrc100);
            $categoryprogramname = $record->categoryprogramname; // Herberger Institute for Design and the Arts
        //    $options[$programcode] = $programDesrc100; // Changed on 1/12/2024.
            $options[$programcode] = $programDesrc100_userFriendly; // Changed on 12/11/2024
            // $options[$programcode] = $categoryprogramname;
        }
        asort($options);
//        ksm($options, "colleges_array - before return in getCollegesByMulticampusesAndInterest()");
        return new JsonResponse($options);
    } // END OF getCollegesByMulticampusesAndInterest()


    /**
     * @param Request $request
     * @return JsonResponse
     *
     *  Grad - List degrees based on multiple campuses and a college.
     * It will be always for grad since we are not collecting degree data from Ugrad.
     * ei:
     * /admin/asuaec_json/json/get_majors_by_multi_campuses_and_college/TEMPE/GRES/grad
     */
    public function getMajorsByMulticampusesAndCollege(Request $request) {

        // Get campuses, college and grad/ugrad
        $requestUri = $request->getRequestUri();
//        \Drupal::logger('asuaec_visit')->notice('Ajax URL3: ' . $requestUri);
        $uriSegment_array = explode('/',$requestUri);
//        ksm($uriSegment_array, "uriSegment_array in getCollegesByMulticampusesAndInterest()");
        //0 => string (0) ""
        //⇄1 => string (5) "admin"
        //⇄2 => string (11) "asuaec_json"
        //⇄3 => string (4) "json"
        //⇄4 => string (43) "get_colleges_by_multi_campuses_and_interest"
        //⇄5 => string (5) "Tempe"
        //⇄6 => string (4) "GRES"
        //⇄7 => string (4) "grad"
        $grad_ugrad = isset($uriSegment_array[7]) ? $uriSegment_array[7] : '';
        $college = isset($uriSegment_array[6]) ? $uriSegment_array[6] : ''; // GRHI
        $campuses = isset($uriSegment_array[5]) ? $uriSegment_array[5] : '';
//        ksm($campuses, "campuses");
        $campusesArray = explode('|', $campuses);




//        if($op4 == "grad") {
//
//            //$major_query = db_query("SELECT DISTINCT categorymajorcode, categorymajorname FROM {asu_grad_interest_category_degrees} WHERE categoryprogramcode = :prog_code AND categorycampuscode IN (:campus)", array(':prog_code' => $college, ':campus' => $campusesArray));
//
//            // IN didn't work for this case. Fixed the SQL on Aug 13, 2018 - Chizuko.
//            $major_query = db_query("SELECT DISTINCT categorymajorcode, categorymajorname FROM {asu_grad_interest_category_degrees} WHERE categoryprogramcode = :prog_code AND categoryDegreeDescShort <> 'Certificate' AND (" . $campus_condition_string . ")", array(':prog_code' => $college));
//
//            //watchdog('calendar_evolution', "query:<pre>" . print_r($major_query, true) . "</pre>", NULL, WATCHDOG_DEBUG, NULL);
//
//
//        }


        // DB connection

        $database = \Drupal::database();
        $table = '';
        $fields = [];
        switch ($grad_ugrad) {
            case 'grad': // It will be always grad for Visit site because for Ugrad, we don't ask degree question.
                $table = 'asu_grad_interest_category_degrees';
                $condition_field = 'categoryprogramcode';
                $condition_field2 = 'categorycampuscode';
                $fields = ['categorymajorcode', 'categorymajorname'];
                break;
        }
        $query = $database->select($table, 't');
        $query->fields('t', $fields);
        if($grad_ugrad == 'grad') { // Always grad for this case for Visit site because for Ugrad, we don't ask degree question.
            $query->condition($condition_field, $college, '=');
            $query->condition('categoryDegreeDescShort', 'Certificate', '<>');

//            $query->condition($condition_field, $campusesArray, 'IN'); //<--- IN doesn't work in this case for campuses.
            $or = $query->orConditionGroup();
            foreach($campusesArray as $campus) {
                $campus = urldecode($campus); // Added on 1/12/2024.
                if($campus == 'ASU California Center') { // Added on 1/12/2024.
                  $campus = 'LOSAN';
                }
                $or->condition($condition_field2, '%' . Database::getConnection()->escapeLike($campus) . '%', 'LIKE');
            }
            $query->condition($or);
        }
        $result = $query->distinct()->execute();
        $options = [];
        foreach ($result as $record) {
            $majorcode = $record->categorymajorcode; // CSNEMMA
            $majorname = $record->categorymajorname; // GRHI
            $options[$majorcode] = $majorname;
        }
        asort($options);
        return new JsonResponse($options);
    }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * For example:
   * https://visit-asu-csdev40-2.ddev.site/admin/asuaec_json/json/get_ptypes/234
   */
    public function getPtypes(Request $request) {

      // Get campuses, interest and grad/ugrad
      $requestUri = $request->getRequestUri();
  //        \Drupal::logger('asuaec_visit')->notice('Ajax URL2: ' . $requestUri);
      $uriSegment_array = explode('/',$requestUri);
//          ksm($uriSegment_array, "uriSegment_array in getCollegesByMulticampusesAndInterest()");
//⇄0 => string (0) ""
//⇄1 => string (5) "admin"
//⇄2 => string (11) "asuaec_json"
//⇄3 => string (4) "json"
//⇄4 => string (10) "get_ptypes"
//⇄5 => string (3) "234"
      $eventseries_id = $uriSegment_array[5];

      // Get list of person types based off the series id
      // Load the series entity
      $entity_type_eventseries = 'eventseries';
      $entity_id_eventseries = $eventseries_id;
      $entity_eventseries  = \Drupal::entityTypeManager()->getStorage($entity_type_eventseries)->load($entity_id_eventseries);
      $ptypes = $entity_eventseries->get('field_visitor_type')->getValue();

      foreach($ptypes as $ptype) {
        if($ptype['value'] != 'Other') {
          $options [$ptype['value']] = $ptype['value'];
        }
      }
      //asort($options);
      return new JsonResponse($options);
    } // END OF getPtypes()

  public function getInterests(Request $request) {

    // Get campuses, interest and grad/ugrad
    $requestUri = $request->getRequestUri();
    //        \Drupal::logger('asuaec_visit')->notice('Ajax URL2: ' . $requestUri);
    $uriSegment_array = explode('/',$requestUri);
//          ksm($uriSegment_array, "uriSegment_array in getCollegesByMulticampusesAndInterest()");
//⇄0 => string (0) ""
//⇄1 => string (5) "admin"
//⇄2 => string (11) "asuaec_json"
//⇄3 => string (4) "json"
//⇄4 => string (10) "get_ptypes"
//⇄5 => string (3) "234"
    $campus = $uriSegment_array[5];

    // Get interests in LA from DB -- Only for Grad
    // DB connection
    $grad_ugrad = 'grad';
    $database = \Drupal::database();
    $table = '';
    $fields = [];
    switch ($grad_ugrad) {
      case 'grad': // It will be always grad for Visit site because for Ugrad, we don't ask degree question.
        $table = 'asu_grad_interest_category_degrees';
        $condition_field = 'categorycampuscode';
        $fields = ['categoryname'];
        break;
    }
    $query = $database->select($table, 't');
    $query->fields('t', $fields);
    if($grad_ugrad == 'grad') { // Always grad for this case for Visit site because for Ugrad, we don't ask degree question.
      $query->condition($condition_field, '%' . $campus . '%', 'LIKE');
      $query->condition('categoryprogramcode', 'GRND', '<>');
    }
    $result = $query->distinct()->execute();
    $options = [];
    foreach ($result as $record) {
      $categoryname = $record->categoryname; // Arts
      $options[$categoryname] = $categoryname;
    }
    asort($options);

    return new JsonResponse($options);
  } // END OF getInterests()

  public function getUserFriendlyCollegeName($programDesrc100){
    // Split the string into two parts using explode()
    $parts = explode(',', $programDesrc100, 2);
    // Extract the part before the first comma
    $beforeComma = trim($parts[0]);
    // Extract the part after the first comma
    $afterComma = isset($parts[1]) ? trim($parts[1]) : '';
    return $afterComma . ' ' . $beforeComma;
  }

} // END OF class
