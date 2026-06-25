<?php

namespace Drupal\asuaec_rfi\Controller;

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

//        \Drupal::logger('asuaec_rfi')->notice("ground_online:<pre>" . $ground_online . "</pre>");

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
//        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
//        $query->condition('transferAgreementType', $type, '=');
        $query->orderBy($field, 'ASC');
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        $catOptions = array();
        foreach ($result as $record) {
            $catName = $record->$field;
            if($ground_online == 'ground') { // Ground
//                $catOptions[] = array('key'=>$catName, 'value'=>$catName);
                $catOptions[$catName] = $catName;

            }
            else if($ground_online == 'online') { // Online
//                $catOptionsTemp[] = explode('|', $catName);
                $catOptionsTemp = explode('|', $catName);
//                \Drupal::logger('asuaec_rfi')->notice("catOptionsTemp:<pre>" . print_r($catOptionsTemp, true) . "</pre>");
//                foreach($catOptionsTemp as $the_interest_area_array) {
                foreach($catOptionsTemp as $catName) {
                    if(!in_array($catName, $catOptions)) {
                        $catOptions[$catName] = $catName;
                    }
                }
            }


        }
        asort($catOptions);
//        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
        return new JsonResponse($catOptions);
    }


    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_rfi/json/degrees/online/ugrad/Art and design
     * ie: /admin/asuaec_rfi/json/degrees/ground/ugrad/Business
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
//        \Drupal::logger('asuaec_rfi')->notice("interest:<pre>" . $interest . "</pre>");
        $grad_ugrad = $uriSegment_array[count($uriSegment_array)-2];
        $ground_online = $uriSegment_array[count($uriSegment_array)-3];

//        \Drupal::logger('asuaec_rfi')->notice("ground_online:<pre>" . $ground_online . "</pre>");

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
//        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
        return new JsonResponse($degree_options);
    }

    public function getDegreesGround($database, $table, $fields_array, $grad_ugrad, $interest) {
        $query = $database->select($table, 't');
        $query->fields('t', $fields_array);
//        $query->condition('categoryname', $interest, '=');
        $query->condition('categoryname', '%' . Database::getConnection()->escapeLike($interest) . '%', 'LIKE');
        $query->orderBy('categorymajorname', 'ASC');
//        $query->range(0, 10);
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
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        $degreeOptions = array();
        foreach ($result as $record) {
            $degreeName = $record->onlinetitle;
            $degreeCode = $record->onlinecode;
//            $degreeOptions[] = array('key' => $degreeCode, 'value' => $degreeName);
            $degreeOptions[$degreeCode] = $degreeName;
        }

        return $degreeOptions;
    }


    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_rfi/json/term/grad/PPAPDTMSW
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
//        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
        $query->condition('categorymajorcode', $plancode, '=');
        $query->orderBy($field, 'ASC');
//        $query->range(0, 10);
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
     * ie: /admin/asuaec_rfi/json/gradtype/PPAPDTMSW
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
//        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
        $query->condition('categorymajorcode', $plancode, '=');
//        $query->orderBy($field, 'ASC');
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        foreach ($result as $record) {
            $gradtype = $record->$field;
            $options[$gradtype] = $gradtype;
        }
//        asort($options); //<-- gradtype will be just 1.
//        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
        return new JsonResponse($options);
    }


//    /**
//     * Handler for JSON request.
//     * Build JSON page.
//     * ie: /admin/asuaec_rfi/json/degreedata/GRAD/PPAPDTMSW
//     *
//     * @param Request $request
//     * @return JsonResponse
//     */
//    function generateJsonDegreeData($grad_ugrad = null, $plancode = null) {
////        \Drupal::logger('asuaec_rfi')->notice("grad_ugrad:<pre>" . $grad_ugrad . "</pre>");
////        \Drupal::logger('asuaec_rfi')->notice("plancode:<pre>" . $plancode . "</pre>");
//
//        $options = [];
//        if (is_null($plancode)) {
//            return new JsonResponse($options);
//        }
//
//        $database = \Drupal::database();
//        if($grad_ugrad == 'GRAD') {
//            $table = 'asu_grad_interest_category_degrees';
//        }
//        else if ('UGRAD') {
//            $table = 'asu_ugrad_interest_category_degrees';
//        }
//
//        $fields_array = array('categorymajorname', 'categorycampuscode'); // descr100
//
//        $query = $database->select($table, 't');
//        $query->fields('t', $fields_array);
////        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
//        $query->condition('categorymajorcode', $plancode, '=');
////        $query->orderBy($field, 'ASC');
////        $query->range(0, 10);
//        $result = $query->distinct()->execute();
//        foreach ($result as $record) {
//            $options['descr100'] = $record->categorymajorname;
//            $options['campus_codes'] = $record->categorycampuscode;
//        }
////        asort($options); //<-- gradtype will be just 1.
////        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
//        return new JsonResponse($options);
//    }







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




