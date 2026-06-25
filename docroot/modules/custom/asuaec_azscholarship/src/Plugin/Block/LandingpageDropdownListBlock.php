<?php

namespace Drupal\asuaec_azscholarship\Plugin\Block;

use Drupal\asuaec_azscholarship\Controller\LandingpageDropdownListBlockContentController;
use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;


/**
 * Provides a 'Example: configurable text string' block.
 *
 * Drupal\Core\Block\BlockBase gives us a very useful set of basic functionality
 * for this configurable block. We can just fill in a few of the blanks with
 * defaultConfiguration(), blockForm(), blockSubmit(), and build().
 *
 * @Block(
 *   id = "asuaec_landingpage_block",
 *   admin_label = @Translation("AZ scholarship landing page dropdown list block")
 * )
 */
class LandingpageDropdownListBlock extends BlockBase {

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
        $controller_variable = new LandingpageDropdownListBlockContentController();
        $rendering_in_block = $controller_variable->process(\Drupal::request());
        return $rendering_in_block;
    }

}
