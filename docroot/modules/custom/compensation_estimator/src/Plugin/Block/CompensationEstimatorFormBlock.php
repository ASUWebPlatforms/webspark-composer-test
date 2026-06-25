<?php
/**
 * @file
 * Contains \Drupal\article\Plugin\Block\ArticleBlock.
 */

namespace Drupal\compensation_estimator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'Compensation Estimator Form' block.
 *
 * @Block(
 *   id = "compensation_estimator_form_block",
 *   admin_label = @Translation("Compensation Estimator Form block"),
 *   category = @Translation("Custom block")
 * )
 */
class CompensationEstimatorFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
    public function build() {
        $form = \Drupal::formBuilder()->getForm('Drupal\compensation_estimator\Form\CompensationEstimatorForm');
        return $form;
    }
}