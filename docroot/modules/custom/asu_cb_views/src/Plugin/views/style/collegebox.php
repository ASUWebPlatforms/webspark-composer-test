<?php

namespace Drupal\asu_cb_views\Plugin\views\style;

use Drupal\core\form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render a list of years and months
 * in reverse chronological order linked to content.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "collegebox",
 *   title = @Translation("My ASU College Box"),
 *   help = @Translation("Render a list of years and months in reverse chronological order linked to content."),
 *   theme = "views_view_collegebox",
 *   display_types = { "feed" }
 * )
 */
class collegebox extends StylePluginBase {
  
	protected $channel_elements = [];
  
 
	
  public $namespaces = [];

  
  protected $usesRowPlugin = TRUE;

  //protected $channel_elements = [];

  public function attachTo(array &$build, $display_id, Url $feed_url, $title) {
    $url_options = [];
    $input = $this->view->getExposedInput();
    if ($input) {
      $url_options['query'] = $input;
    }
    $url_options['absolute'] = TRUE;

    $url = $feed_url->setOptions($url_options)->toString();
    //dpm($url,'url');
    // Add the RSS icon to the view.
    $this->view->feedIcons[] = [
      '#theme' => 'feed_icon',
      '#url' => $url,
      '#title' => $title,
    ];

    // Attach a link to the RSS feed, which is an alternate representation.
    $build['#attached']['html_head_link'][][] = [
      'rel' => 'alternate',
      'type' => 'application/rss+xml',
      'title' => $title,
      'href' => $url,
    ];
  }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['description'] = ['default' => ''];

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('RSS description'),
      '#default_value' => $this->options['description'],
      '#description' => $this->t('This will appear in the RSS feed itself.'),
      '#maxlength' => 1024,
    ];
  }
	
   /**

   * The channel elements.

   *

   * @var array

   */

  //protected $channel_elements;
	
	 /**

   * The namespaces.

   *

   * @var array

   */

 // protected $namespaces = [];


  
  protected function getChannelElements() {
    return [];
  }

 
  public function getDescription() {
    $description = $this->options['description'];

    // Allow substitutions from the first row.
    $description = $this->tokenizeValue($description, 0);

    return $description;
  }

  public function render() {
    if (empty($this->view->rowPlugin)) {
      trigger_error('Drupal\views\Plugin\views\style\Rss: Missing row plugin', E_WARNING);
      return [];
    }
    $rows = [];

    // This will be filled in by the row plugin and is used later on in the
    // theming output.
    $this->namespaces = ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'];

    // Fetch any additional elements for the channel and merge in their
    // namespaces.
    $this->channel_elements = $this->getChannelElements();
    foreach ($this->channel_elements as $element) {
      if (isset($element['namespace'])) {
        $this->namespaces = array_merge($this->namespaces, $element['namespace']);
      }
    }

    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
    ];
    unset($this->view->row_index);
    return $build;
  }

}