<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Component\Utility\Html;

/**
 * Provides a Visit replacement block - Title text for Sendoff Webform.
 *
 * @Block(
 *   id = "visit_replacement_block_titletext",
 *   admin_label = @Translation("Title text for Sendoff Webform - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockTitletext extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {
    $request = \Drupal::request();
    $formTitle = $request->query->get('formtitle') ?? '';

    return [
      '#type' => 'html_tag',
      '#tag' => 'h1',
      '#value' => Html::escape($formTitle),
      '#cache' => [
        'contexts' => ['url.query_args:formtitle'],
      ],
    ];
  }

}
