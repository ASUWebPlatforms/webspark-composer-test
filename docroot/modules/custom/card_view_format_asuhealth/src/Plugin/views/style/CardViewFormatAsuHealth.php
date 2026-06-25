<?php
namespace Drupal\card_view_format_asuhealth\Plugin\views\style;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin for the cards view format. Creates four-column image cards.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "card_view_format_asuhealth",
 *   title = @Translation("Cards Style format ASU Health"),
 *   help = @Translation("Displays content in four-column image cards."),
 *   theme = "views_view_card_view_format_asuhealth",
 *   display_types = {"normal"}
 * )
 */
class CardViewFormatAsuHealth extends StylePluginBase {
  protected $usesOptions = true;
  protected $usesRowPlugin = true;

  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // Options for columns
    $options = array(
      0 => '2 columns',
      1 => '3 columns',
      2 => '4 columns'
    );

    $form['columns'] = array(
      '#type' => 'radios',
      '#title' => t('Columns'),
      '#options' => $options,
      '#default_value' => (isset($this->options['columns'])) ? $this->options['columns'] : 1,
      '#description' => t('Choose how many cards to show per row.')
    );
  }
}
