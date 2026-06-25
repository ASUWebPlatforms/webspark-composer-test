<?php

/**
 * @file
 * Contains \Drupal\edge\Plugin\Block\Affiliates.
 *
 * Provides the Affiliates block.
 */

namespace Drupal\edge\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Affiliates' block.
 *
 * @Block(
 *   id = "affiliates",
 *   admin_label = @Translation("Affiliates"),
 *   category = @Translation("Edge"),
 * )
 */
class Affiliates extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The entity storage for nodes.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The entity storage for taxonomy terms.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NodeStorageInterface $node_storage,
    TermStorageInterface $term_storage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $node_storage;
    $this->termStorage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get("entity_type.manager")->getStorage("node"),
      $container->get("entity_type.manager")->getStorage("taxonomy_term")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Load the taxonomy terms from the vocabulary.
    $vid = "affiliates";
    $terms = $this->termStorage->loadTree($vid);

    // Create an array of term options.
    $term_options = ["" => $this->t("- Select a term -")];
    foreach ($terms as $term) {
      $term_options[$term->tid] = $term->name;
    }

    $form["field_type"] = [
      "#type" => "select",
      "#title" => $this->t("Type"),
      "#description" => $this->t(
        "Select the term to filter the Affiliates block."
      ),
      "#options" => $term_options,
      "#default_value" => $this->configuration["field_type"] ?? "",
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration["field_type"] = $form_state->getValue("field_type");
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the selected term ID from the block configuration.
    $selected_term_id = $this->configuration["field_type"] ?? null;

    // Build the query to retrieve the affiliated nodes.
    $query = $this->nodeStorage
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'affiliates')
      ->condition('status', 1);

    // Add a condition to filter by the selected term ID, if any.
    if ($selected_term_id) {
      $query->condition("field_type.target_id", $selected_term_id);
    }

    // Execute the query and load the nodes.
    $nids = $query->execute();
    $nodes = $this->nodeStorage->loadMultiple($nids);

    // Build the renderable array for the block.
    $build = [
      "#theme" => "affiliates",
      "#nodes" => $nodes,
      "#attached" => [
        "library" => ["edge/affiliates"],
      ],
      "#attributes" => [
        "class" => ["block--affiliates"],
      ],
    ];

    return $build;
  }
}
