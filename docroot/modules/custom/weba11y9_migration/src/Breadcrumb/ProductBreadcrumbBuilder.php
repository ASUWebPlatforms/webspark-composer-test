<?php

namespace Drupal\weba11y9_migration\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Breadcrumb builder for Product nodes.
 *
 * Replicates the hardcoded breadcrumb from the Access theme's
 * node--product.html.twig: Home > IT product accessibility tests.
 */
class ProductBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $node = $route_match->getParameter('node');
    if ($node && !is_string($node) && $node->getType() === 'product') {
      // Skip revision routes.
      $route_name = $route_match->getRouteName();
      if (str_contains($route_name, 'revision')) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $node = $route_match->getParameter('node');

    $breadcrumb->addCacheContexts(['url']);
    $breadcrumb->addCacheTags(['node:' . $node->id()]);

    $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
    $breadcrumb->addLink(Link::fromTextAndUrl('IT product accessibility tests', \Drupal\Core\Url::fromUserInput('/products')));

    return $breadcrumb;
  }

}
