<?php

namespace Drupal\asu_format_advising\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a 'element_summary' form element.
 *
 * @FormElement("element_summary")
 */
class ElementSummary extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processElementSummary'],
      ],
      '#pre_render' => [
        [$class, 'preRenderElementSummary'],
      ],
      '#theme' => 'element_summary',
      '#theme_wrappers' => ['form_element'],
      '#data' => NULL,
    ];
  }

  public static function processElementSummary(&$element, FormStateInterface $form_state, &$complete_form) {
    // Additional processing if needed.
    return $element;
  }

  public static function preRenderElementSummary($element) {
    $element['#attributes']['type'] = 'element_summary';
    return $element;
  }

}
