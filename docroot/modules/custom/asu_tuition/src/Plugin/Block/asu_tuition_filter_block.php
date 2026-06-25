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
 * Provides a Filter for asu_tuition .
 *
 * @Block(
 *   id = "asu_tuition_filter_block",
 *   admin_label = @Translation("ASU tuition filter block"),
 *
 * )
 */
class asu_tuition_filter_block extends BlockBase {

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
    $form = \Drupal::formBuilder()->getForm('Drupal\asu_tuition\Form\AsuTuitionAdminFilterForm');
    return [
      '#markup' => $form,

    ];
    // Return $form;.
  }

  /**
   *
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['asu_tuition_filter_settings'] = $form_state->getValue('asu_tuition_filter_settings');
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
