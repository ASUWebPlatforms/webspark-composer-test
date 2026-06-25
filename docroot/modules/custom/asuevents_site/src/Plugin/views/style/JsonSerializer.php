<?php

namespace Drupal\asuevents_site\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\views\Annotation\ViewsStyle;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "json_serializer",
 *   title = @Translation("JSON serializer"),
 *   help = @Translation("Serializes views row data using the Serializer
 *   component."), display_types = {"data"}
 * )
 */
class JsonSerializer extends Serializer {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      // add node parent wrapper for each nid
      $rows[] = ['node' => $this->view->rowPlugin->render($row)];
    }
    unset($this->view->row_index);
    
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }
    // add nodes as parent wrapper for the feed
    return $this->serializer->serialize(['nodes' => $rows], $content_type, ['views_style_plugin' => $this]);
  }
}