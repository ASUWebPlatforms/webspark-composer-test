<?php

namespace Drupal\asu_survey\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Database\Database;


/**
 * Provides route responses for the custom module.
 */
class SurveyOnlineDegreeController extends ControllerBase
{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function survey_online_degrees_page($type = NULL, $aoi = NULL)
    {
       
        $onlinecategory = '';
        switch($type) {
            case 'First Time Freshman':
                $onlinecategory = 'Undergraduate';
                break;
            case 'Transfer':
                $onlinecategory = 'Undergraduate';
                break;
            case 'Readmission':
                $onlinecategory = 'Graduate';
        }
		$interest = urldecode($aoi);
		
		$fields_array = array('onlinecode', 'onlinetitle');
		$database = \Drupal::database();
		$table = "asu_online_degrees";
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

        //return $degreeOptions;
		
		return new JsonResponse(
				[
				 //'resultsData' => \Drupal\Core\Render\Markup::create($body), 
					'resultsData' => $degreeOptions, 
				]

		);
	}
		

}