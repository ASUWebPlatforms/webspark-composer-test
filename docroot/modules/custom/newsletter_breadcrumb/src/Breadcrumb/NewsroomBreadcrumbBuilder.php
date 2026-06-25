<?php

namespace Drupal\newsletter_breadcrumb\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

class NewsroomBreadcrumbBuilder implements BreadcrumbBuilderInterface
{

    /**
     * {@inheritdoc}
     */
    public function applies(RouteMatchInterface $attributes)
    {
        $parameters = $attributes->getParameters()->all();
        $route_name = $attributes->getRouteName();
        if ($route_name == 'entity.node.revision' || $route_name == 'node.revision_revert_confirm' || $route_name == 'node.revision_revert_translation_confirm' || $route_name == 'node.revision_delete_confirm') {
            return false;
        }
        if (!empty($parameters['node'])) {
            return $parameters['node']->getType() == 'newsroom';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(RouteMatchInterface $route_match)
    {
        $breadcrumb = new Breadcrumb();
        $node = $route_match->getParameter('node');
        // By setting a "cache context" to the "url", each requested URL gets it's own cache.
        // This way a single breadcrumb isn't cached for all pages on the site.
        $breadcrumb->addCacheContexts(['url']);
        // By adding "cache tags" for this specific node, the cache is invalidated when the node is edited.
        $breadcrumb->addCacheTags(["node:{$node->nid->value}"]);
        //First Breadcrumb value
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        //Get landing page field value
        $landing_page_field = $node->get('field_landing_page')->getValue();
        //If the landing page field value is not checked, then show the landing page breadcrumb
        if ($landing_page_field[0]['value'] != 1) {
            $query = \Drupal::entityQuery('node')
                ->accessCheck(FALSE)
                ->condition('status', 1) //published or not
                ->condition('type', 'newsroom'); //content type
            $nids = $query->execute();
            foreach ($nids as $nid) {
                $node = \Drupal\node\Entity\Node::load($nid);
                $landing_page_val = $node->get('field_landing_page')->getValue();
                if ($landing_page_val[0]['value'] == 1) {
                    $landing_page = $nid;
                    break;
                }
            }
            // $nid = $route_match->getParameter('node')->id();
            $breadcrumb->addLink(Link::createFromRoute('Newsroom', 'entity.node.canonical', ['node' => $landing_page]));
        }
        return $breadcrumb;
    }
}
