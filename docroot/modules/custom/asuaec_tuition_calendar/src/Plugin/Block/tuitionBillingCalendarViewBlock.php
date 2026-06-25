<?php

namespace Drupal\asuaec_tuition_calendar\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\asuaec_tuition_calendar\Controller\TuitionCalendarSectionControllerPage;


/**
 * Provides a 'Tuition and Billing Calendar' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "asuaec_tuition_calendar",
 *   admin_label = @Translation("Tuition and Billing Calendar View section")
 * )
 */
class tuitionBillingCalendarViewBlock extends BlockBase {

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
        $term = \Drupal::request()->query->get('term');
//        ksm($term, "term");


//        // This works! Display Views in block directly.
//        $view = views_embed_view('tuition_and_billing_calendar', 'block_1', '2224');
//        $renderer = \Drupal::service('renderer');
//        $result =  array(
//            '#markup' => $renderer->render($view),
//        );
//        return $result;




        $controller_variable = new TuitionCalendarSectionControllerPage(\Drupal::service('renderer'));
        $rendering_in_block = $controller_variable->displayView($term);
        return $rendering_in_block;


    }

}
