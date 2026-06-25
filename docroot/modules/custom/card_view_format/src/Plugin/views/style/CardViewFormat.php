<?php

namespace Drupal\card_view_format\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin for the cards view format. Creates four-column image cards.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "card_view_format",
 *   title = @Translation("Cards Style"),
 *   help = @Translation("Displays content in four-column image cards."),
 *   theme = "views_view_card_view_format",
 *   display_types = {"normal"}
 * )
 */
class CardViewFormat extends StylePluginBase {
  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;
  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // Options for columns.
    $options = [
      0 => '2 columns',
      1 => '3 columns',
      2 => '4 columns'
    ];

    $form['columns'] = [
      '#type' => 'radios',
      '#title' => t('Columns'),
      '#options' => $options,
      '#default_value' => (isset($this->options['columns'])) ? $this->options['columns'] : 1,
      '#description' => t('Choose how many cards to show per row.')
    ];
  }

}
