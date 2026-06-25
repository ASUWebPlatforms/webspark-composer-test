<?php

namespace Drupal\asumex_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "asumex_mailchimp",
 *   admin_label = @Translation("Mailchimp Subcribe Form")
 * )
 */
class ASUMEXCustomMailchimpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#theme' => 'asumex_mailchimp',
      '#attached' => [
        'library' => [
          'asumex_custom/mailchimp',
        ],
      ],
    ];

  }

}
