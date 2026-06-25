<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Begin the Module' Block.
 *
 * @Block(
 *   id = "begin_module_block",
 *   admin_label = @Translation("Begin the Module Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class BeginModuleBlock extends BlockBase
{
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    return [
      '#theme' => 'begin_module',
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account)
  {
    $allowed_roles = ['administrator', 'sp_learningmod_user'];
    return AccessResult::allowedIf(!empty(array_intersect($allowed_roles, $account->getRoles())));
  }
}
