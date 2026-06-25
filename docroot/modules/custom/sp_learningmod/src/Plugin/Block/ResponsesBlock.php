<?php

namespace Drupal\sp_learningmod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'Responses Block' Block.
 *
 * @Block(
 *   id = "responses_block",
 *   admin_label = @Translation("Responses Block"),
 * )
 */
class ResponsesBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  protected $database;
  protected $currentUser;

  /**
   * Construct the ResponsesBlock.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, AccountProxyInterface $currentUser)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->currentUser = $currentUser;
  }

  /**
   * Dependency Injection.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * Build the block content.
   */
  public function build()
  {
    $visited_nodes = $this->getVisitedNodes();
    $responses = [];

    foreach ($visited_nodes as $node) {
      $body = $node->get('body')->value ?? '';

      $dom = new \DOMDocument();
      libxml_use_internal_errors(true);
      //$dom->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));
      $dom->loadHTML(htmlspecialchars_decode(htmlentities($body)));
      libxml_clear_errors();

      $xpath = new \DOMXPath($dom);
      $response_divs = $xpath->query("//div[contains(@class, 'responseset')]");

      foreach ($response_divs as $div) {
        $responses[] = $dom->saveHTML($div);
      }
    }

    return [
      '#theme' => 'responses_block',
      '#items' => $responses,
      '#attached' => [
        'library' => ['sp_learningmod/budget_banner'],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Get visited nodes.
   */
  private function getVisitedNodes()
  {
    $nids = $this->database->select('sp_learningmod_visited_nodes', 'v')
      ->fields('v', ['nid'])
      ->condition('uid', $this->currentUser->id())
      ->execute()
      ->fetchCol();

    return Node::loadMultiple($nids);
  }
}
