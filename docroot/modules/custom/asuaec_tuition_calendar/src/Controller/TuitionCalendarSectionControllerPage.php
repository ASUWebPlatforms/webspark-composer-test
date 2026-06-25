<?php

namespace Drupal\asuaec_tuition_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;

/**
 * Controller routines for Tuition Calendars Section
 */
class TuitionCalendarSectionControllerPage extends ControllerBase {

    /**
     * @var Renderer
     */
    protected $renderer;

    public function __construct(Renderer $renderer) {
        $this->renderer = $renderer;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('renderer')
        );
    }

    public function tuitionBillingCalendarView() {
        $content = [];
        // get renderable array for view
        $view = views_embed_view('tuition_and_billing_calendar', 'block_1', '2224');
        // render view
        $content['#markup'] = $this->renderer->render($view);

        return $view;
    }

    public function displayView($term) {
        $content = [];
        // get renderable array for view
        $view = views_embed_view('tuition_and_billing_calendar', 'block_1', $term);
        // render view
        $content['#markup'] = $this->renderer->render($view);

        return $view;
    }



}