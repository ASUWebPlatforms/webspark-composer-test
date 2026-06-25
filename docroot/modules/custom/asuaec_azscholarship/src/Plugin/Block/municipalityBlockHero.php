<?php

namespace Drupal\asuaec_azscholarship\Plugin\Block;

use Drupal\asuaec_azscholarship\Controller\MunicipalityBlockContentControllerHero;
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
 *   id = "asuaec_municipality_block_hero",
 *   admin_label = @Translation("AZ scholarship municipality block - Hero")
 * )
 */
class MunicipalityBlockHero extends BlockBase {

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
        $citynid = !is_null(\Drupal::request()->query->get('citynid')) ? \Drupal::request()->query->get('citynid') : '104';
        $controller_variable = new MunicipalityBlockContentControllerHero();
        $rendering_in_block = $controller_variable->process(\Drupal::request(), $citynid);
        return $rendering_in_block;
    }

}
