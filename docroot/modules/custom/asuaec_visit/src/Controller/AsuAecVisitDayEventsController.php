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
class AsuAecVisitDayEventsController extends ControllerBase {


    /**
     * @var Renderer
     */
    protected $renderer;

    public function __construct() {
    }


    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('renderer')
        );
    }

//    public function process(Request $request) {
    public function process($date = null, $campus = null, $persontype = null, $category = null) {
        \Drupal::service('page_cache_kill_switch')->trigger();




        // View 1: visitd9_day_agenda
        // get renderable array for view
        if (empty($date)) {
            $view = "";
        } else {
            $view = views_embed_view('visitd9_day_agenda', 'block_1', $date, $campus, $persontype, $category);
        }
//        $view = views_embed_view('visitd9_day_agenda', 'block_1', '20221025', '14');




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