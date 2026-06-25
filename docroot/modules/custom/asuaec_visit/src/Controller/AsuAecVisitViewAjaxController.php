<?php
namespace Drupal\asuaec_visit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\views\Controller\ViewAjaxController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AsuAecVisitViewAjaxController
 */
class AsuAecVisitViewAjaxController extends ViewAjaxController {

    public function ajaxView(Request $request) {

        $view_name = $request->get('view_id');
        $display_id = $request->get('display_id');
        $dom_id = "{$view_name}__{$display_id}";
        $arg = $request->get('arg');
        $category = $request->get('cat');
        $args = $arg . '/' . $category;

        $request->request->set('view_name', $view_name);
        $request->request->set('view_display_id', $display_id);
//        $request->request->set('view_args', $request->get('args'));

//        if($view_name == "visitd9_day_agenda") {
////            \Drupal::logger('visit')->notice("args:<pre>" .$request->get('args') . "</pre>");
//            $pieces = explode("-", $args);
//            $args = $pieces[0] . '/' . $pieces[1];
//        }

        if(!($request->get('arg') == 'noarg')) {
            $request->request->set('view_args', $args);
        }

        $request->request->set('view_dom_id', $dom_id);

        return parent::ajaxView($request);


    }

}