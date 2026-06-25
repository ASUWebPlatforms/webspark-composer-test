<?php

namespace Drupal\aas2bas_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with a taxonomy dropdown.
 *
 * @Block(
 *   id = "aas2bas_dropdown_block",
 *   admin_label = @Translation("AAS2BAS Dropdown Block"),
 * )
 */
class AAS2BASDropdownBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        // Return the form as the block content.
        return \Drupal::formBuilder()->getForm('Drupal\aas2bas_search\Form\TaxonomyDropdownForm');
    }
}