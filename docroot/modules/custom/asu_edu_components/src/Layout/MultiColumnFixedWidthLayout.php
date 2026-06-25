<?php

namespace Drupal\asu_edu_components\Layout;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class MultiColumnFixedWidthLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();

    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#default_value' => $configuration['extra_classes'],
      '#description' => $this->t('Separate classes with space. Each class value will be sanitized to match the CSS identifier format requirements.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state){
    $this->configuration['extra_classes'] = $this->sanitizeClasses($form_state->getValue('extra_classes'));
  }

  /**
   * Sanitizes the CSS class values.
   *
   * @param string $value
   *   Teh submitted class values.
   *
   * @return array
   *   The sanitized CSS class values.
   */
  protected function sanitizeClasses(string $value = ''): array {
    $out = [];

    if (empty($value)) {
      return [];
    }

    $buf = explode(' ', $value);
    $buf = array_filter($buf);
    if (!empty($buf)) {
      foreach ($buf as $v) {
        $sanitized = Html::cleanCssIdentifier($v);
        if (!empty($sanitized)) {
          $out[] = $sanitized;
        }
      }
    }

    return $out;
  }

}
