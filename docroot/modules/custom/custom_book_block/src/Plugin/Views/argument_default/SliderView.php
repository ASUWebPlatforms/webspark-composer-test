<?php

namespace Drupal\custom_book_block\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\node\Entity\Node;
// use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Legacy mail Parameter Selector.
 *
 * @ViewsArgumentDefault(
 *   id = "sliderid",
 *   title = @Translation("Custom Filter for Hero Banner")
 * )
 */
class SliderView extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // Write your logic here and return it
    // when you disable the module make sure to remove this filter in view.

    $node = \Drupal::routeMatch()->getParameter('node');
    if (!empty($node->id())) {
      $node_details = Node::load($node->id());
      // Check to see if the current page has slides of its own.
      if (!empty($node_details->field_slides->getValue())) {
        return $node->id();
      }
      else{
        /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager */
        $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
        $links = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->id()]);

        /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
        $link = array_pop($links);

        /** @var \Drupal\Core\Menu\MenuLinkInterface $parent */
        if ($link->getParent() && $parent = $menu_link_manager->createInstance($link->getParent())) {
          $route = $parent->getUrlObject()->getRouteParameters();
          if (isset($route['node']) && $parent_node = Node::load($route['node'])) {
            // Return the parent's node id here.
            return $parent_node->id();
          }
        }
      }
    }
  }


  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

}
