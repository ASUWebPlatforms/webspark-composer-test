<?php

namespace Drupal\asu_tuition\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * @file
 * Contains \Drupal\asu_quiz\Plugin\Block\quiz_confirmation_block.
 */






/**
 * Provides a Persona quiz block.
 *
 * @Block(
 *   id = "asu_tuition_block",
 *   admin_label = @Translation("ASU tuition search calculator block"),
 *
 * )
 */
class asu_tuition_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Return $account->hasPermission('search content');.
    if (AccessResult::allowedIfHasPermission($account, 'access content')) {
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\asu_tuition\Form\AsuTuitionSearchPage');

    return $form;
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
