<?php
namespace Drupal\asuaec_visit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\views\Controller\ViewAjaxController;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use \Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AsuAecVisitDayEventsController
 */
class AsuAecVisitMiniCalendarController extends ControllerBase {


    public function process($yearmonth = null, $campus = null, $persontype = null, $category = null) {
        \Drupal::service('page_cache_kill_switch')->trigger();
		
		$data = $yearmonth.'/'.$campus.'/'.$persontype.'/'.$category;
//		\Drupal::logger('asuaec_visit_cal_ajax')->notice('Ajax Call URL: ' . $data);
		
        // View: visitd9_main_calendar_v2
        // get renderable array for view
        if (empty($yearmonth)) {
            $view = "";
        } else {
            $view = views_embed_view('visitd9_main_calendar_v2', 'block_1', $yearmonth, $campus, $persontype, $category);
//            $view = views_embed_view('visitd9_main_calendar_v2', 'default', $yearmonth, $campus, $persontype, $category);
        }

        // render view
        if(empty($view) || $view == "") {
            $content = '';
        } else {
            $content = \Drupal::service('renderer')->render($view);
//            \Drupal::logger('visit')->notice("content: <pre>" .print_r($content, true) . "</pre>");

        }
        $results['body'] = $content;

        return new JsonResponse(
            [
                'resultsData' => $results,
            ]
        );

    }

}