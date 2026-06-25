<?php

namespace Drupal\asuaec_licensure\Controller;

use Drupal\Core\Controller\ControllerBase;
//use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for generating JSON pages.
 */
class JsonControllerLicensure extends ControllerBase {

    /**
     * Handler for JSON request.
     * Build JSON page.
     * ie: /admin/asuaec_licensure/json/programnodetitles/744
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateJsonProgramNodeTitles(Request $request) {

        $requestUri = $request->getRequestUri();
        $uriSegment_array = explode('/',$requestUri);
        $lastUriSegment = end($uriSegment_array); //<-- College or school
        $college_school_tid = urldecode($lastUriSegment); // For example, 477
//        \Drupal::logger('asuaec_licensure')->notice("college_school tid:<pre>" . $college_school_tid . "</pre>");

        // Get node title from node_field_data table.
        $database = \Drupal::database();
        $table = 'node_field_data';
        $fields_array = array(
            'title',
        );
        $fields_array2 = array(
            'field_program_college_school_target_id',
        );
        $query = $database->select($table, 't');
        $query->join('node__field_program_college_school', 'nf', "nf.entity_id = t.nid");
        $query->fields('t', $fields_array);
        $query->fields('nf', $fields_array2);
        $query->condition('field_program_college_school_target_id', $college_school_tid, '=');
//        $query->condition('nid', $ids, 'IN');
//        $query->orderBy('stateDesc', 'ASC');
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        foreach($result as $id => $node) {
            $value = $node->title;
            $options[$value] = $value;
        }
        if (is_array($options)) {
            asort($options);
        }      
        return new JsonResponse($options);
    }

} // END OF class





