<?php

namespace Drupal\ggl_mailchimp_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Mailchimp subscription form block.
 *
 * @Block(
 *   id = "ggl_mailchimp_subscription_block",
 *   admin_label = @Translation("GGL Mailchimp Subscription Form"),
 *   category = @Translation("Great Game Lab")
 * )
 */
class MailchimpSubscriptionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ggl_mailchimp_subscription_block',
      '#attached' => [
        'library' => [
          'ggl_mailchimp_forms/subscription-form',
        ],
      ],
    ];
  }

}