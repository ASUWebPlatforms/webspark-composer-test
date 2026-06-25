<?php

namespace Drupal\aventri_event_embed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Aventri Event block.
 *
 * @Block(
 *   id = "aventri_event_embed",
 *   admin_label = @Translation("Aventri Event Embed"),
 *   category = @Translation("Aventri Event Embed")
 * )
 */
class AventriEventBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {


    $config = $this->getConfiguration();

    $widget_id = isset($config['widget_id']) ? $config['widget_id'] : '';

    $build['content'] = [
      '#theme' => 'aventri_embed',
      '#widget_id' => $widget_id,
    ];
    return $build;
  }

    /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    // $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget ID'),
      '#default_value' => isset($config['widget_id']) ? $config['widget_id'] : '',
      '#size' => 60,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('widget_id', $form_state->getValue('widget_id'));
    parent::blockSubmit($form, $form_state);
  }

}
