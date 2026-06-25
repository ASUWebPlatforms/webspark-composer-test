<?php

namespace Drupal\weba11y9_migration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Groups VPAT URL and File fields in a fieldset.
 *
 * Replicates production's nested <fieldset id="product-vpat-container">
 * with legend "Supplier's VPAT or ACR" inside the Product info section.
 *
 * @Block(
 *   id = "weba11y9_vpat_container",
 *   admin_label = @Translation("VPAT Container"),
 *   category = @Translation("WEB A11Y9"),
 *   context_definitions = {
 *     "entity" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class VpatContainerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('entity');
    if (!$node instanceof NodeInterface || $node->bundle() !== 'product') {
      return [];
    }

    $build = [
      '#type' => 'fieldset',
      '#title' => $this->t("Supplier's VPAT or ACR"),
      '#attributes' => [
        'id' => 'product-vpat-container',
        'class' => ['product-vpat-container'],
      ],
    ];

    // Render VPAT URL field.
    if ($node->hasField('field_product_vpat_url') && !$node->get('field_product_vpat_url')->isEmpty()) {
      $build['vpat_url'] = $node->get('field_product_vpat_url')->view([
        'type' => 'link',
        'label' => 'inline',
        'settings' => [
          'trim_length' => 80,
          'url_only' => FALSE,
          'url_plain' => FALSE,
          'rel' => '',
          'target' => '',
        ],
      ]);
    }

    // Render VPAT File field.
    if ($node->hasField('field_product_vpat_file') && !$node->get('field_product_vpat_file')->isEmpty()) {
      $build['vpat_file'] = $node->get('field_product_vpat_file')->view([
        'type' => 'file_default',
        'label' => 'inline',
        'settings' => [
          'use_description_as_link_text' => TRUE,
        ],
      ]);
    }

    // Cache based on the node.
    $build['#cache'] = [
      'tags' => $node->getCacheTags(),
      'contexts' => ['route'],
    ];

    return $build;
  }

}