<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for category creation and editing.
 *
 * @Block(
 *   id = "visited_categories_block",
 *   admin_label = @Translation("Visited Categories Block"),
 * )
 */
class VisitedCategoriesBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  protected $database;
  protected $currentUser;
  protected $entityTypeManager;
  protected $routeMatch;

  /**
   * Constructs a VisitedCategoriesBlock object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database,
    AccountProxyInterface $currentUser,
    EntityTypeManagerInterface $entityTypeManager,
    RouteMatchInterface $routeMatch
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();

    $allowed_types = [
      'sp_cq_clients_johns' => 'Clients (Johns)',
      'sp_cq_current_response' => 'Current Response',
      'sp_cq_drugs' => 'Drugs',
      'sp_cq_environment' => 'Environment',
      'sp_cq_pimps' => 'Pimps',
      'sp_cq_police_community_members' => 'Police Community Members',
      'sp_cq_sexual_transactions' => 'Sexual Transactions',
      'sp_cq_street_prostitutes' => 'Street Prostitutes',
    ];

    $categories = [];
    $responses = [];

    foreach ($allowed_types as $type => $label) {
      $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
      $query->condition('type', $type);
      $query->condition('uid', $uid);
      $query->accessCheck(TRUE);
      $nids = $query->execute();

      if (!empty($nids)) {
        $node_id = reset($nids);
        $url = "/node/{$node_id}/edit";
        $button_text = "[Edit]";
        $button_class = "btn cq-secondary-button";

        $node = \Drupal\node\Entity\Node::load($node_id);
        $fields = [];

        foreach ($node->getFields() as $field_name => $field) {
          if (strpos($field_name, 'field_') === 0 && !$field->isEmpty()) {
            $fields[] = [
              'label' => $field->getFieldDefinition()->getLabel(),
              'value' => $field->getString(),
            ];
          }
        }

        usort($fields, function ($a, $b) {
          preg_match('/^\d+/', $a['label'], $matchesA);
          preg_match('/^\d+/', $b['label'], $matchesB);

          $labelA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
          $labelB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;

          return $labelA <=> $labelB;
        });

        if (!empty($fields)) {
          $responses[] = [
            'title' => $label,
            'fields' => $fields,
          ];
        }
      } else {
        $url = "/node/add/{$type}";
        $button_text = "Add";
        $button_class = "btn-primary";
      }

      $categories[] = [
        'title' => $label,
        'url' => $url,
        'button_text' => $button_text,
        'button_class' => $button_class,
      ];
    }

    return [
      '#theme' => 'visited_categories_block',
      '#categories' => $categories,
      '#responses' => $responses,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['node_list'],
      ],
    ];
  }
}
