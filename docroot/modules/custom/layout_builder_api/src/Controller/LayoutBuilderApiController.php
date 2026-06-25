<?php

namespace Drupal\layout_builder_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\node\Entity\Node;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for Layout Builder API endpoints.
 */
class LayoutBuilderApiController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * LayoutBuilderApiController constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    SectionStorageManagerInterface $section_storage_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->sectionStorageManager = $section_storage_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('plugin.manager.layout_builder.section_storage')
    );
  }

  /**
   * Get layout data for a specific node.
   */
  public function getNodeLayout(Node $node, Request $request) {
    if (!$node->access('view')) {
      throw new NotFoundHttpException();
    }

    $include_content = $request->query->get('include_content', 'true') === 'true';
    $include_rendered = $request->query->get('include_rendered', 'false') === 'true';

    $layout_data = $this->extractLayoutData($node, $include_content, $include_rendered);

    return new JsonResponse([
      'node_id' => $node->id(),
      'node_title' => $node->getTitle(),
      'node_type' => $node->bundle(),
      'node_status' => $node->isPublished(),
      'layout_enabled' => $layout_data['layout_enabled'],
      'sections' => $layout_data['sections'],
      'metadata' => [
        'sections_count' => count($layout_data['sections']),
        'components_count' => $layout_data['components_count'],
        'generated_at' => date('c'),
      ],
    ]);
  }

  /**
   * Get rendered layout for a specific node.
   */
  public function getNodeLayoutRendered(Node $node) {
    if (!$node->access('view')) {
      throw new NotFoundHttpException();
    }

    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $build = $view_builder->view($node, 'full');
    $rendered = $this->renderer->renderPlain($build);

    return new JsonResponse([
      'node_id' => $node->id(),
      'node_title' => $node->getTitle(),
      'rendered_content' => $rendered,
      'generated_at' => date('c'),
    ]);
  }

  /**
   * Get all nodes that use Layout Builder.
   */
  public function getNodesWithLayouts(Request $request) {
    $content_type = $request->query->get('type');
    $limit = min((int) $request->query->get('limit', 50), 100);
    $offset = (int) $request->query->get('offset', 0);

    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('status', 1);
    $query->range($offset, $limit);
    $query->sort('changed', 'DESC');

    if ($content_type) {
      $query->condition('type', $content_type);
    }

    $node_ids = $query->execute();
    $nodes = Node::loadMultiple($node_ids);

    $layout_nodes = [];
    foreach ($nodes as $node) {
      if ($this->nodeUsesLayoutBuilder($node)) {
        $layout_data = $this->extractLayoutData($node, FALSE, FALSE);
        $layout_nodes[] = [
          'node_id' => $node->id(),
          'node_title' => $node->getTitle(),
          'node_type' => $node->bundle(),
          'node_status' => $node->isPublished(),
          'changed' => $node->getChangedTime(),
          'sections_count' => count($layout_data['sections']),
          'components_count' => $layout_data['components_count'],
        ];
      }
    }

    return new JsonResponse([
      'nodes' => $layout_nodes,
      'total' => count($layout_nodes),
      'offset' => $offset,
      'limit' => $limit,
    ]);
  }

  /**
   * Get available layout templates.
   */
  public function getLayoutTemplates() {
    $layout_manager = \Drupal::service('plugin.manager.core.layout');
    $layouts = $layout_manager->getDefinitions();

    $templates = [];
    foreach ($layouts as $layout_id => $layout) {
      $templates[] = [
        'id' => $layout_id,
        'label' => $layout->getLabel(),
        'category' => $layout->getCategory(),
        'regions' => $layout->getRegionNames(),
        'icon' => $layout->getIcon(),
        'template' => $layout->getTemplate(),
      ];
    }

    return new JsonResponse(['templates' => $templates]);
  }

  /**
   * Extract layout data from a node.
   */
  protected function extractLayoutData(Node $node, $include_content = TRUE, $include_rendered = FALSE) {
    $layout_enabled = $this->nodeUsesLayoutBuilder($node);
    $sections = [];
    $components_count = 0;

    if ($layout_enabled) {
      $entity_view_display = $this->entityTypeManager
        ->getStorage('entity_view_display')
        ->load('node.' . $node->bundle() . '.default');

      if ($entity_view_display && $entity_view_display instanceof LayoutBuilderEntityViewDisplay) {
        $section_storage = $this->sectionStorageManager
          ->load('overrides', ['entity' => $node, 'view_mode' => 'default']);

        if ($section_storage) {
          foreach ($section_storage->getSections() as $delta => $section) {
            $section_data = [
              'delta' => $delta,
              'layout_id' => $section->getLayoutId(),
              'layout_settings' => $section->getLayoutSettings(),
              'components' => [],
            ];

            foreach ($section->getComponents() as $component) {
              $component_data = [
                'uuid' => $component->getUuid(),
                'region' => $component->getRegion(),
                'weight' => $component->getWeight(),
                'configuration' => $component->getConfiguration(),
                'plugin_id' => $component->getPluginId(),
              ];

              // Add content if requested
              if ($include_content) {
                $component_data['content'] = $this->getComponentContent($component);
              }

              // Add rendered content if requested
              if ($include_rendered) {
                $component_data['rendered'] = $this->getComponentRendered($component);
              }

              $section_data['components'][] = $component_data;
              $components_count++;
            }

            // Sort components by weight
            usort($section_data['components'], function($a, $b) {
              return $a['weight'] <=> $b['weight'];
            });

            $sections[] = $section_data;
          }
        }
      }
    }

    return [
      'layout_enabled' => $layout_enabled,
      'sections' => $sections,
      'components_count' => $components_count,
    ];
  }

  /**
   * Check if a node uses Layout Builder.
   */
  protected function nodeUsesLayoutBuilder(Node $node) {
    $entity_view_display = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load('node.' . $node->bundle() . '.default');

    return $entity_view_display && $entity_view_display instanceof LayoutBuilderEntityViewDisplay;
  }

  /**
   * Get content for a component.
   */
  protected function getComponentContent($component) {
    $configuration = $component->getConfiguration();
    $plugin_id = $component->getPluginId();

    $content = [
      'plugin_id' => $plugin_id,
      'label' => $configuration['label'] ?? '',
      'provider' => $configuration['provider'] ?? '',
    ];

    // Handle block content entities
    if (strpos($plugin_id, 'block_content:') === 0) {
      $uuid = str_replace('block_content:', '', $plugin_id);
      $block_content = $this->entityTypeManager
        ->getStorage('block_content')
        ->loadByProperties(['uuid' => $uuid]);

      if ($block_content) {
        $block = reset($block_content);
        $content['block_content'] = [
          'id' => $block->id(),
          'uuid' => $block->uuid(),
          'bundle' => $block->bundle(),
          'info' => $block->info->value,
          'fields' => $this->getEntityFields($block),
        ];
      }
    }

    // Handle inline blocks
    if (strpos($plugin_id, 'inline_block:') === 0) {
      $block_revision_id = $configuration['block_revision_id'] ?? NULL;
      if ($block_revision_id) {
        $block_content = $this->entityTypeManager
          ->getStorage('block_content')
          ->loadRevision($block_revision_id);

        if ($block_content) {
          $content['inline_block'] = [
            'id' => $block_content->id(),
            'uuid' => $block_content->uuid(),
            'bundle' => $block_content->bundle(),
            'revision_id' => $block_content->getRevisionId(),
            'info' => $block_content->info->value,
            'fields' => $this->getEntityFields($block_content),
          ];
        }
      }
    }

    return $content;
  }

  /**
   * Get rendered content for a component.
   */
  protected function getComponentRendered($component) {
    try {
      $plugin_manager = \Drupal::service('plugin.manager.block');
      $block_plugin = $plugin_manager->createInstance($component->getPluginId(), $component->getConfiguration());
      
      $build = $block_plugin->build();
      $rendered = $this->renderer->renderPlain($build);
      
      return $rendered;
    } catch (\Exception $e) {
      return 'Error rendering component: ' . $e->getMessage();
    }
  }

  /**
   * Get field values for an entity.
   */
  protected function getEntityFields($entity) {
    $fields = [];
    $field_definitions = $entity->getFieldDefinitions();

    foreach ($field_definitions as $field_name => $field_definition) {
      if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
        continue;
      }

      $field_type = $field_definition->getType();
      $field_value = $entity->get($field_name)->getValue();

      // Process different field types
      switch ($field_type) {
        case 'text':
        case 'text_long':
        case 'text_with_summary':
          $fields[$field_name] = [
            'type' => $field_type,
            'value' => $field_value[0]['value'] ?? '',
            'format' => $field_value[0]['format'] ?? NULL,
            'summary' => $field_value[0]['summary'] ?? NULL,
          ];
          break;

        case 'image':
          $fields[$field_name] = [
            'type' => $field_type,
            'target_id' => $field_value[0]['target_id'] ?? NULL,
            'alt' => $field_value[0]['alt'] ?? '',
            'title' => $field_value[0]['title'] ?? '',
            'width' => $field_value[0]['width'] ?? NULL,
            'height' => $field_value[0]['height'] ?? NULL,
          ];
          break;

        case 'entity_reference':
          $fields[$field_name] = [
            'type' => $field_type,
            'target_id' => $field_value[0]['target_id'] ?? NULL,
            'target_type' => $field_definition->getSetting('target_type'),
          ];
          break;

        default:
          $fields[$field_name] = [
            'type' => $field_type,
            'value' => $field_value,
          ];
      }
    }

    return $fields;
  }

}
