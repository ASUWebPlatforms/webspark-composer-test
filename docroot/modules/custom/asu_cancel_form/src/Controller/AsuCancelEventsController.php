<?php

namespace Drupal\asu_cancel_form\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\views\Controller\ViewAjaxController;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\NodeInterface;
use Drupal\Core\Link;
use Drupal\node\NodeViewBuilder;
use Drupal\node\Entity\Node;

/**
 * Class AsuCancelEventsController
 */
class AsuCancelEventsController extends ControllerBase {
	 /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function cancelForm()
	{
		$current_path = \Drupal::service('path.current')->getPath();
        $path_args = explode('/', $current_path);
        // ksm($path_args);
		$aid = $path_args[2];
		$eventid = $path_args[3];
		// ksm($eventid);
        $body = 'hi';
		$database = \Drupal::database();
		$query = $database->select('node__field_student_event_id', 'et');
        $query->fields('et', ['entity_id']);
		$query->leftJoin('node__field_attendee_id', 'ai', 'ai.entity_id = et.entity_id');
		$query->condition('et.field_student_event_id_value', $eventid,'=');
		$query->condition('ai.field_attendee_id_value', $aid,'=');
        $result = $query->execute();
		foreach($result as $data){
			$sub_id = $data->entity_id;
		}
		// ksm($sub_id);
		$node_data = Node::load($sub_id);
		//ksm($node_data);
		$cancel_val = $node_data->get('field_canceled_registration')->getValue();
		// ksm($cancel_val);
		if(empty($cancel_val)){
			$node_data->set('field_canceled_registration', 'Yes');
			$node_data->save();
		}
		 return array(
            '#markup' => \Drupal\Core\Render\Markup::create($body),
            '#cache' => array(
                'max-age' => 0,
            ),

        );
    }
}
