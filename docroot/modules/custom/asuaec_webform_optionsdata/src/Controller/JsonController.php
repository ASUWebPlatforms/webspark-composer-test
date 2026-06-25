<?php

namespace Drupal\asuaec_webform_optionsdata\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
//use Drupal\Core\Entity\Element\EntityAutocomplete;

/**
 * Defines a route controller for generating JSON pages.
 */
class JsonController extends ControllerBase {

    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_transferoption/autocomplete/keywords?q=bio
     */
    public function generateJsonCity(Request $request) {

        // Get state
        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
        $lastUriSegment = end($uriSegment_array);
        $state = $lastUriSegment;
        $hs_inst = $uriSegment_array[count($uriSegment_array)-2];

//        \Drupal::logger('asuaec_webform_optionsdata')->notice("state:<pre>" . $state . "</pre>");

        $results = [];

        // Get the state string from the URL, if it exists.
        if (!$state) {
            return new JsonResponse($results);
        }

        $database = \Drupal::database();
        if($hs_inst == 'hs') {
            $table = 'asu_webform_highschool_data';
            $field_array = array('hs_city');
            $field_state = 'hs_state_code';
            $field_city = 'hs_city';

        } else if ($hs_inst == 'inst') {
            $table = 'asu_webform_institution_data';
            $field_array = array('inst_city');
            $field_state = 'inst_state_code';
            $field_city = 'inst_city';
        }

        $query = $database->select($table, 't');
        $query->fields('t', $field_array);
        $query->condition($field_state, $state, '=');
        $query->orderBy($field_city, 'ASC');
        $result = $query->distinct()->execute();
        $options = array();
        foreach ($result as $record) {
            $cityName = $record->$field_city;
            $options[$cityName] = $cityName;
        }
        asort($options);
//        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
        return new JsonResponse($options);
    }

    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_transferoption/autocomplete/keywords?q=bio
     */
    public function generateJsonName(Request $request) {

        // Get state
        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
        $lastUriSegment = end($uriSegment_array);
        $city = urldecode($lastUriSegment);
        $state =  $uriSegment_array[count($uriSegment_array)-2];
        $hs_inst =  $uriSegment_array[count($uriSegment_array)-3];
//        \Drupal::logger('asuaec_webform_optionsdata')->notice("state:<pre>" . $state . "</pre>");
//        \Drupal::logger('asuaec_webform_optionsdata')->notice("city:<pre>" . $city . "</pre>");
//        \Drupal::logger('asuaec_webform_optionsdata')->notice("hs_inst:<pre>" . $hs_inst . "</pre>");

        $results = [];

        // Get the state string from the URL, if it exists.
        if (!$state || !$city) {
            return new JsonResponse($results);
        }

        $database = \Drupal::database();
        if($hs_inst == 'hs') {
            $table = 'asu_webform_highschool_data';
            $field_array = array('hs_name', 'hs_id');
            $field_state = 'hs_state_code';
            $field_city = 'hs_city';
            $field_name = 'hs_name';
            $field_id = 'hs_id';

        } else if ($hs_inst == 'inst') {
            $table = 'asu_webform_institution_data';
            $field_array = array('inst_name', 'inst_id');
            $field_state = 'inst_state_code';
            $field_city = 'inst_city';
            $field_name = 'inst_name';
            $field_id = 'inst_id';
        }
        $query = $database->select($table, 't');
        $query->fields('t', $field_array);
        $query->condition($field_state, $state, '=');
        $query->condition($field_city, $city, '=');
        //$query->orderBy($field_city, 'ASC'); // Changed on 9/8/2025
        $query->orderBy($field_name, 'ASC');
        $result = $query->distinct()->execute();
        $options = array();
        foreach ($result as $record) {
            $name = $record->$field_name;
            $id = $record->$field_id;
            $options[$id] = $name;
        }
        asort($options);
//        \Drupal::logger('asuaec_rfi')->notice("catOptions before return:<pre>" . print_r($catOptions, true) . "</pre>");
        return new JsonResponse($options);
    }



}

