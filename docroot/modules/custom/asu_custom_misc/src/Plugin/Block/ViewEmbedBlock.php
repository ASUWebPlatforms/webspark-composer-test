<?php

namespace Drupal\asu_custom_misc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block to embed a View.
 *
 * @Block(
 *   id = "virtual_session_node_page_view_embed_block",
 *   admin_label = @Translation("Virtual session node page View embed block"),
 *   category = @Translation("Custom")
 * )
 */
class ViewEmbedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current request and retrieve the 'nid' parameter from the URL.
    $current_request = \Drupal::request();
    $nid = $current_request->query->get('nid');
    // Validate the 'nid' parameter to ensure it's numeric.
    if (!is_numeric($nid)) {
      return [
        '#markup' => $this->t('Invalid or missing node ID (nid) parameter.'),
        '#cache' => [
          'max-age' => 0, // Disable caching for this block.
        ],
      ];
    }    
    
    // Define the View and display to embed.
    $view_id = 'virtual_session_node_page';
    $display_id = 'block_1';
    $args = [$nid]; // Use the dynamic 'nid' value as an argument for the View.

    // Load and render the View.
    $view = \Drupal\views\Views::getView($view_id);
    if ($view) {
      $view->setDisplay($display_id);
      $view->setArguments($args);
      $view->preExecute();
      $view->execute();

//      return $view->buildRenderable($display_id);
      $render_array = $view->buildRenderable($display_id);
      // Disable caching for the View render array.
      $render_array['#cache'] = [
        'max-age' => 0, // Disable caching for this block.
      ];

      return $render_array;      
    }

    // Return an empty render array if the View is not found.
    return [
      '#markup' => $this->t('The View could not be loaded.'),
      '#cache' => [
            'max-age' => 0, // Disable caching for this block.
      ],      
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return parent::blockForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
  }
}