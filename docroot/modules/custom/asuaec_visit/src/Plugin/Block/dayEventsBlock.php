<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\asuaec_visit\Controller\AsuAecVisitDayEventsController;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;


//use Drupal\generator\Form\ProjektForm;
/**
 * @file
 * Contains \Drupal\asuaec_visit\Plugin\Block\dayEventsBlock
 */
/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "asuaec_visit",
 *   admin_label = @Translation("Visit Day events")
 * )
 */
class dayEventsBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account) {
        if ( AccessResult::allowedIfHasPermission($account, 'access content') ) {
            return AccessResult::allowedIfHasPermission($account, 'access content');
        }
    }



    /**
     * {@inheritdoc}
     */
    public function build() {
        // Get URL params
        $date = !empty(\Drupal::request()->query->get('date')) ? \Drupal::request()->query->get('date') : '';
        $campus = !empty(\Drupal::request()->query->get('campus')) ? \Drupal::request()->query->get('campus') : '';
        $persontype = !empty(\Drupal::request()->query->get('persontype')) ? \Drupal::request()->query->get('persontype') : '';
        $category = !empty(\Drupal::request()->query->get('cat')) ? \Drupal::request()->query->get('cat') : '';

//        $sid = isset($sid) ? $sid:'5';
        $controller_variable = new AsuAecVisitDayEventsController;
//        $rendering_in_block = $controller_variable->process(\Drupal::request(), $date, $category);
//        $rendering_in_block = $controller_variable->process($date, '14');
        $rendering_in_block = $controller_variable->process($date, $campus, $persontype, $category);
//        return $rendering_in_block;

        return array(
            '#markup' => \Drupal\Core\Render\Markup::create($rendering_in_block),
            '#cache' => array(
                'max-age' => 0,
            ),
        );


    }

    public function getCacheMaxAge() {
        return 0;
    }

}





