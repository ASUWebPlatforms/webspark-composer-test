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
use Drupal\Core\Entity\EntityInterface;


/**
 * Provides route responses for the custom module.
 */
class SurveyAjaxController extends ControllerBase
{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function survey_ajax_page($class_node_id = NULL)
    {
       
        if(empty($class_node_id)){
			$nid = \Drupal::request()->query->get('nid');
		}
		else{
			$nid = $class_node_id;
		}
		
		if(!empty($nid)){
			
			$node = \Drupal\node\Entity\Node::load($nid);
			
			$builder = \Drupal::entityTypeManager()->getViewBuilder('node'); 
            $build = $builder->view($node, 'full');
			$output = \Drupal::service('renderer')->render($build);
			
			$body = $output;
        }
		else{
			$body = '';
		}
		
		$results['body'] = \Drupal\Core\Render\Markup::create($body);
		
		//ksm($results);
		return new JsonResponse(
				[
				 //'resultsData' => \Drupal\Core\Render\Markup::create($body), 
					'resultsData' => $results, 
				]

		);
	}
		

}