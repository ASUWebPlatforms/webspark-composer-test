<?php

namespace Drupal\asuaec_visit_revamp\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\asuaec_visit_revamp\Controller\WebformConfirmationPageRevamp;


/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "visitConfirmationBlockRevamp",
 *   admin_label = @Translation("Visit confirmation (Revamp)")
 * )
 */
class visitConfirmationBlock extends BlockBase {

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
        $sid_val = \Drupal::request()->query->get('sid');
        if(empty($sid_val)) {
            $sid = 72; // When there is no URL params, use $sid = 72. So, the web page doesn't become white screen of death.
        }
        else{
            $sid = $sid_val;
        }
        $guests =\Drupal::request()->query->get('guests');
//        $campus_option = \Drupal::request()->query->get('campus_option');
//        $grad_ugrad =\Drupal::request()->query->get('grad_ugrad');
//        $plancode =\Drupal::request()->query->get('plancode'); // Becomes null if it is empty in URL param.
//        $student_type = \Drupal::request()->query->get('student_type');
//        $interest = \Drupal::request()->query->get('interest');

//        $sid = isset($sid) ? $sid:'5';

        $controller_variable = new WebformConfirmationPageRevamp();
        $rendering_in_block = $controller_variable->process($sid, $guests);
        return $rendering_in_block;
    }

}
