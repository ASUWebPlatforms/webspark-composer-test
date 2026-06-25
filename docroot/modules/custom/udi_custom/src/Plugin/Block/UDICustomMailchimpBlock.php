<?php

namespace Drupal\udi_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "udi_mailchimp",
 *   admin_label = @Translation("Mailchimp Subcribe Form")
 * )
 */
class UDICustomMailchimpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

  return [
      '#theme' => 'udi_mailchimp',
      '#attached' => [
        'library' => [
          'udi_custom/mailchimp',
        ],
      ],
    ];

  }

}