<?php

namespace Drupal\asu_components\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'AsuBlock' block.
 *
 * @Block(
 *  id = "organization_overview_block",
 *  admin_label = @Translation("AsuBlock"),
 *  category = @Translation("Custom")
 * )
 */
class AsuBlock extends BlockBase 
{


    public function build()
    {
        return array(
            '#markup' => 'Hello World',
        );
    }

    public function blockAccess(AccountInterface $account)
    {
        return $account->hasPermission('access content');
    }

}


