<?php

namespace Drupal\asuaec_rfib2\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\asuaec_rfib2\Controller\WebformConfirmationPage;


/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "asuaec_rfi",
 *   admin_label = @Translation("Main RFI confirmation")
 * )
 */
class rfiConfirmationBlock extends BlockBase {

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
        $sid = \Drupal::request()->query->get('sid');
        $fname =\Drupal::request()->query->get('fname');
        $campus_option = \Drupal::request()->query->get('campus_option');
        $grad_ugrad =\Drupal::request()->query->get('grad_ugrad');
        $plancode =\Drupal::request()->query->get('plancode'); // Becomes null if it is empty in URL param.
        $student_type = \Drupal::request()->query->get('student_type');
        $interest = \Drupal::request()->query->get('interest');

//        $sid = isset($sid) ? $sid:'5';

        $controller_variable = new WebformConfirmationPage();
        $rendering_in_block = $controller_variable->process(\Drupal::request(), $sid, $fname, $campus_option, $grad_ugrad, $plancode, $student_type, $interest);
        return $rendering_in_block;
    }

}
