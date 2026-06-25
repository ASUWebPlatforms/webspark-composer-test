<?php

namespace Drupal\weba11y9_migration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Section Heading block for product page layout sections.
 *
 * @Block(
 *   id = "weba11y9_section_heading",
 *   admin_label = @Translation("Section Heading"),
 *   category = @Translation("WEB A11Y9")
 * )
 */
class SectionHeadingBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'heading_text' => '',
      'heading_level' => 'h2',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['heading_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading text'),
      '#default_value' => $this->configuration['heading_text'],
      '#required' => TRUE,
    ];
    $form['heading_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Heading level'),
      '#options' => [
        'h2' => 'H2',
        'h3' => 'H3',
        'h4' => 'H4',
      ],
      '#default_value' => $this->configuration['heading_level'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['heading_text'] = $form_state->getValue('heading_text');
    $this->configuration['heading_level'] = $form_state->getValue('heading_level');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'html_tag',
      '#tag' => $this->configuration['heading_level'],
      '#value' => $this->configuration['heading_text'],
      '#attributes' => [
        'class' => ['product-section-heading'],
      ],
    ];
  }

}
