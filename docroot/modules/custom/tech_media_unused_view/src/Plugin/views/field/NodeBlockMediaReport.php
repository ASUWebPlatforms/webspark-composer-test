<?php

declare(strict_types=1);

namespace Drupal\tech_media_unused_view\Plugin\views\field;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\layout_builder\Section;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reports, per Node row, all assets found via:
 *  - Layout Builder blocks (per-node and default),
 *  - Node fields (Media references + direct File/Image),
 *  - Inline <drupal-entity> media/file embeds and links to /sites/default/files in text fields.
 *
 * Shows Block label, Media/File label, File name, Size, and a Delete link.
 *
 * Options:
 *  - include_node_media (bool)
 *  - cache_max_age (int)
 *  - media_bundles (string[])  // only filters Media, not direct File/Image fields
 *  - sort_nodes_by_max_bytes (bool)
 *  - sort_nodes_direction ('desc'|'asc')
 *
 * @ViewsField("node_block_media_report")
 */
class NodeBlockMediaReport extends FieldPluginBase implements ContainerFactoryPluginInterface
{

  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;

  /** {@inheritdoc} */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityRepository  = $container->get('entity.repository');
    return $instance;
  }

  /** {@inheritdoc} */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL)
  {
    parent::init($view, $display, $options);
    if (!isset($this->options['alter']) || !is_array($this->options['alter'])) {
      $this->options['alter'] = [];
    }
  }

  /** Bypass Views' advanced rendering. */
  public function advancedRender(ResultRow $values)
  {
    return $this->render($values);
  }

  /** {@inheritdoc} */
  protected function defineOptions()
  {
    $o = parent::defineOptions();
    $o['include_node_media']      = ['default' => FALSE];
    $o['cache_max_age']           = ['default' => 0];
    $o['media_bundles']           = ['default' => []];
    $o['sort_nodes_by_max_bytes'] = ['default' => FALSE];
    $o['sort_nodes_direction']    = ['default' => 'desc'];
    return $o;
  }

  /** {@inheritdoc} */
  public function buildOptionsForm(&$form, FormStateInterface $form_state)
  {
    parent::buildOptionsForm($form, $form_state);

    $form['include_node_media'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include node-level assets (Media and direct File/Image fields, plus inline in text fields)'),
      '#default_value' => (bool) $this->options['include_node_media'],
      '#description' => $this->t('Also scan the Node entity for Media references, direct File/Image fields, and inline assets in text fields (e.g., body).'),
    ];

    $form['cache_max_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Cache max-age (seconds)'),
      '#default_value' => (int) $this->options['cache_max_age'],
      '#min' => 0,
      '#description' => $this->t('0 = no cache. Set a positive number (e.g., 3600) to cache the rendered table per node and user permissions.'),
    ];

    // Media bundle filter options (applies only to Media items).
    $bundle_options = [];
    $types = $this->entityTypeManager->getStorage('media_type')->loadMultiple();
    foreach ($types as $machine => $type) {
      $bundle_options[$machine] = $type->label();
    }
    $form['media_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Filter by Media bundle'),
      '#options' => $bundle_options,
      '#default_value' => (array) ($this->options['media_bundles'] ?? []),
      '#description' => $this->t('If none are selected, all media bundles are included. This does not affect direct File/Image fields.'),
    ];

    // Sort whole View by node max bytes (desc/asc).
    $form['sort_nodes_by_max_bytes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sort view rows (nodes) by max asset file size'),
      '#default_value' => (bool) $this->options['sort_nodes_by_max_bytes'],
      '#description' => $this->t('When enabled, nodes containing larger (or smaller) files appear first depending on the direction.'),
    ];
    $form['sort_nodes_direction'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort direction'),
      '#options' => [
        'desc' => $this->t('Descending (largest first)'),
        'asc'  => $this->t('Ascending (smallest first)'),
      ],
      '#default_value' => (string) ($this->options['sort_nodes_direction'] ?? 'desc'),
      '#states' => [
        'visible' => [
          ':input[name="options[sort_nodes_by_max_bytes]"]' => ['checked' => TRUE],
        ],
      ],
    ];
  }

  public function usesGroupBy()
  {
    return FALSE;
  }
  public function query()
  { /* no-op */
  }

  /** {@inheritdoc} */
  public function render(ResultRow $values)
  {
    $entity = $values->_entity ?? NULL;
    if (!$entity instanceof NodeInterface) {
      return '';
    }

    [$rows] = $this->gatherRowsForNode($entity);
    if (!$rows) {
      return $this->t('No blocks or media/files found.');
    }

    $header = [
      $this->t('Block/Context'),
      $this->t('Media/File'),
      $this->t('File name'),
      $this->t('Size'),
      $this->t('Delete'),
    ];
    $max_age = (int) ($this->options['cache_max_age'] ?? 0);

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => [],
      '#attributes' => ['class' => ['tech-media-unused-view__table']],
      '#cache' => [
        'tags' => array_merge($entity->getCacheTags(), ['block_content_list', 'media_list', 'file_list']),
        'contexts' => ['user.permissions'],
        'max-age' => $max_age,
      ],
    ];

    foreach ($rows as $r) {
      $build['#rows'][] = [
        ['data' => ['#markup' => $r['block']]],
        ['data' => ['#markup' => $r['media']]],
        ['data' => ['#markup' => $r['filename']]],
        ['data' => ['#markup' => $r['size']]],
        ['data' => $r['delete_link'] ?: ['#markup' => '—']],
      ];
    }

    return $build;
  }

  /** Public helper so external hooks can compute max bytes for a node. */
  public function computeMaxBytesForNode(NodeInterface $entity): int
  {
    [, $max] = $this->gatherRowsForNode($entity);
    return $max;
  }

  /**
   * Gather rows (sorted by bytes desc) + the max bytes for a given node.
   *
   * @return array{0: array<int,array>, 1: int} [$rows_sorted, $max_bytes]
   */
  protected function gatherRowsForNode(NodeInterface $entity): array
  {
    $rows = [];
    $seen = [];

    foreach ($this->getAllComponentConfigurations($entity) as $configuration) {
      $block = $this->resolveBlockFromComponentConfig($configuration);
      if ($block instanceof BlockContentInterface) {
        $label = $block->label() ?: ('(inline) ' . $block->bundle());
        $this->collectMediaFromEntityFields($block, $label, $rows, $seen);
        $this->collectFilesFromEntityFields($block, $label, $rows, $seen);
      }
    }

    if (!empty($this->options['include_node_media'])) {
      $this->collectMediaFromEntityFields($entity, '— (direct on node)', $rows, $seen);
      $this->collectFilesFromEntityFields($entity, '— (direct on node)', $rows, $seen);
      $this->collectInlineAssetsFromNodeText($entity, '— (inline in text)', $rows, $seen);
    }

    if (!$rows) {
      return [[], 0];
    }

    foreach ($rows as &$r) {
      $r['bytes'] = (int) ($r['bytes'] ?? 0);
    }
    unset($r);

    usort($rows, static function (array $a, array $b): int {
      return ($b['bytes'] ?? 0) <=> ($a['bytes'] ?? 0);
    });

    $max_bytes = (int) ($rows[0]['bytes'] ?? 0);
    return [$rows, $max_bytes];
  }

  protected function getAllComponentConfigurations(NodeInterface $node): array
  {
    $configs = [];

    if ($node->hasField('layout_builder__layout') && !$node->get('layout_builder__layout')->isEmpty()) {
      try {
        $sections = $node->get('layout_builder__layout')->getSections();
        foreach ($sections as $section) {
          foreach ($section->getComponents() as $component) {
            $configuration = $component->get('configuration') ?? [];
            if (is_array($configuration)) {
              $configs[] = $configuration;
            }
          }
        }
      } catch (\Throwable $e) {
      }
    }

    // Default layout if nothing found on node.
    if (!$configs) {
      try {
        $display_id = sprintf('node.%s.default', $node->bundle());
        $display = $this->entityTypeManager->getStorage('entity_view_display')->load($display_id);
        if ($display && (bool) $display->getThirdPartySetting('layout_builder', 'enabled', FALSE)) {
          $sections_data = (array) $display->getThirdPartySetting('layout_builder', 'sections', []);
          foreach ($sections_data as $section_data) {
            if (!is_array($section_data)) {
              continue;
            }
            $section = NULL;
            try {
              if (class_exists(Section::class) && method_exists(Section::class, 'fromArray')) {
                $section = Section::fromArray($section_data);
              }
            } catch (\Throwable $e) {
              $section = NULL;
            }
            if ($section) {
              foreach ($section->getComponents() as $component) {
                $configuration = $component->get('configuration') ?? [];
                if (is_array($configuration)) {
                  $configs[] = $configuration;
                }
              }
            } elseif (!empty($section_data['components']) && is_array($section_data['components'])) {
              foreach ($section_data['components'] as $component_data) {
                if (!empty($component_data['configuration']) && is_array($component_data['configuration'])) {
                  $configs[] = $component_data['configuration'];
                }
              }
            }
          }
        }
      } catch (\Throwable $e) {
      }
    }

    return $configs;
  }

  protected function resolveBlockFromComponentConfig(array $configuration): ?BlockContentInterface
  {
    $block = NULL;
    try {
      $block_storage = $this->entityTypeManager->getStorage('block_content');

      if (!empty($configuration['block_revision_id'])) {
        $revision_id = (int) $configuration['block_revision_id'];
        if ($revision_id) {
          $block = $block_storage->loadRevision($revision_id);
        }
      } elseif (!empty($configuration['id']) && is_string($configuration['id']) && str_starts_with($configuration['id'], 'block_content:')) {
        $uuid = substr($configuration['id'], strlen('block_content:'));
        if ($uuid) {
          $block = $this->entityRepository->loadEntityByUuid('block_content', $uuid);
        }
      }
    } catch (\Throwable $e) {
      $block = NULL;
    }

    return $block instanceof BlockContentInterface ? $block : NULL;
  }

  /** Collect Media refs on an entity. */
  protected function collectMediaFromEntityFields($entity, string $context, array &$rows, array &$seen): void
  {
    foreach ($entity->getFieldDefinitions() as $field_name => $def) {
      if ($def->getType() === 'entity_reference' && $def->getSetting('target_type') === 'media') {
        $items = $entity->get($field_name);
        foreach ($items as $item) {
          $media = $item->entity;
          if ($media instanceof MediaInterface) {
            $selected = array_filter((array) ($this->options['media_bundles'] ?? []));
            if ($selected && !in_array($media->bundle(), $selected, TRUE)) {
              continue;
            }
            $mid = (int) $media->id();
            if ($mid && isset($seen['m:' . $mid])) {
              continue;
            }
            $seen['m:' . $mid] = TRUE;
            $rows[] = $this->buildRowFromMedia($context, $media);
          }
        }
      }
    }
  }

  /** Collect direct File/Image fields on an entity (file, image, entity_reference→file). */
  protected function collectFilesFromEntityFields($entity, string $context, array &$rows, array &$seen): void
  {
    foreach ($entity->getFieldDefinitions() as $field_name => $def) {
      $type = $def->getType();
      $target = $def->getSetting('target_type') ?? NULL;

      $is_file_like = in_array($type, ['image', 'file'], TRUE) || $target === 'file';
      if (!$is_file_like || !$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
        continue;
      }

      $items = $entity->get($field_name);
      foreach ($items as $item) {
        $file = $item->entity ?? NULL;
        if (!$file instanceof FileInterface && isset($item->target_id) && $item->target_id) {
          $file = $this->entityTypeManager->getStorage('file')->load($item->target_id);
        }
        if ($file instanceof FileInterface) {
          $fid = (int) $file->id();
          if ($fid && isset($seen['f:' . $fid])) {
            continue;
          }
          $seen['f:' . $fid] = TRUE;
          $rows[] = $this->buildRowFromFile($context . ' (field)', $file);
        }
      }
    }
  }

  /** Collect inline assets from node text fields (body, etc.). */
  protected function collectInlineAssetsFromNodeText(NodeInterface $node, string $context, array &$rows, array &$seen): void
  {
    foreach ($node->getFieldDefinitions() as $field_name => $def) {
      $type = $def->getType();
      if (!in_array($type, ['text_long', 'text_with_summary'], TRUE)) {
        continue;
      }
      if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
        continue;
      }

      foreach ($node->get($field_name) as $item) {
        $html = (string) ($item->value ?? '');
        if ($html === '') {
          continue;
        }
        $this->parseHtmlForInlineAssets($html, $context . " (field: $field_name)", $rows, $seen);
      }
    }
  }

  protected function parseHtmlForInlineAssets(string $html, string $context, array &$rows, array &$seen): void
  {
    if (trim($html) === '') {
      return;
    }

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $internal = libxml_use_internal_errors(TRUE);
    try {
      $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//*[ @data-entity-type and @data-entity-uuid ]') as $el) {
        /** @var \DOMElement $el */
        $type = strtolower((string) $el->getAttribute('data-entity-type'));
        $uuid = (string) $el->getAttribute('data-entity-uuid');
        if ($uuid === '') {
          continue;
        }

        if ($type === 'media') {
          $media = $this->entityRepository->loadEntityByUuid('media', $uuid);
          if ($media instanceof MediaInterface) {
            $mid = (int) $media->id();
            if (!isset($seen['m:' . $mid])) {
              $seen['m:' . $mid] = TRUE;
              $rows[] = $this->buildRowFromMedia($context . ' (inline)', $media);
            }
          }
        } elseif ($type === 'file') {
          $file = $this->entityRepository->loadEntityByUuid('file', $uuid);
          if ($file instanceof FileInterface) {
            $fid = (int) $file->id();
            if (!isset($seen['f:' . $fid])) {
              $seen['f:' . $fid] = TRUE;
              $rows[] = $this->buildRowFromFile($context . ' (inline)', $file);
            }
          }
        }
      }

      foreach (['href', 'src'] as $attr) {
        foreach ($xpath->query(sprintf('//*[@%s]', $attr)) as $el) {
          /** @var \DOMElement $el */
          $url = (string) $el->getAttribute($attr);
          if (strpos($url, '/sites/default/files/') !== FALSE || strpos($url, 'public://') !== FALSE) {
            $file = $this->fileFromPublicUrl($url);
            if ($file instanceof FileInterface) {
              $fid = (int) $file->id();
              if (!isset($seen['f:' . $fid])) {
                $seen['f:' . $fid] = TRUE;
                $rows[] = $this->buildRowFromFile($context . ' (inline)', $file);
              }
            }
          } elseif (strpos($url, '/system/files/') !== FALSE) {
            $basename = basename(parse_url($url, PHP_URL_PATH) ?? '');
            if ($basename !== '') {
              $candidates = $this->entityTypeManager->getStorage('file')->loadByProperties(['filename' => $basename]);
              $file = $candidates ? reset($candidates) : NULL;
              if ($file instanceof FileInterface) {
                $fid = (int) $file->id();
                if (!isset($seen['f:' . $fid])) {
                  $seen['f:' . $fid] = TRUE;
                  $rows[] = $this->buildRowFromFile($context . ' (inline)', $file);
                }
              }
            }
          }
        }
      }
    } catch (\Throwable $e) {
      // Ignore parse errors.
    } finally {
      libxml_clear_errors();
      libxml_use_internal_errors($internal);
    }
  }

  protected function fileFromPublicUrl(string $url): ?FileInterface
  {
    $uri = NULL;

    if (str_starts_with($url, 'public://')) {
      $uri = $url;
    } elseif (preg_match('@/sites/default/files/styles/[^/]+/public/(.+)$@', $url, $m)) {
      $uri = 'public://' . urldecode($m[1]);
    } elseif (preg_match('@/sites/default/files/(.+)$@', $url, $m)) {
      $uri = 'public://' . urldecode($m[1]);
    }

    if ($uri) {
      $files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uri' => $uri]);
      return $files ? reset($files) : NULL;
    }
    return NULL;
  }

  /** Build a row for a Media item. */
  protected function buildRowFromMedia(string $context, MediaInterface $media): array
  {
    $filename = '—';
    $size_h = '—';
    $bytes = 0;

    $file = $this->getMediaFile($media);
    if ($file instanceof FileInterface) {
      $filename = $file->getFilename() ?: $filename;
      $bytes = $this->resolveFileSizeBytes($file);
      if ($bytes > 0) {
        $size_h = $this->humanSize($bytes);
      }
    }

    $delete_link = NULL;
    $url = Url::fromRoute('entity.media.delete_form', ['media' => $media->id()]);
    if ($url->access()) {
      $delete_link = Link::fromTextAndUrl($this->t('Delete'), $url)->toRenderable();
      $delete_link['#attributes']['class'][] = 'button';
      $delete_link['#attributes']['class'][] = 'button--danger';
    }

    return [
      'block' => $context,
      'media' => $media->label(),
      'filename' => $filename,
      'size' => $size_h,
      'delete_link' => $delete_link,
      'bytes' => $bytes,
    ];
  }

  /** Build a row for a direct File entity. */
  protected function buildRowFromFile(string $context, FileInterface $file): array
  {
    $filename = $file->getFilename() ?: '—';
    $bytes = $this->resolveFileSizeBytes($file);
    $size_h = $bytes > 0 ? $this->humanSize($bytes) : '—';

    $delete_link = NULL;
    $url = Url::fromRoute('entity.file.delete_form', ['file' => $file->id()]);
    if ($url->access()) {
      $delete_link = Link::fromTextAndUrl($this->t('Delete file'), $url)->toRenderable();
      $delete_link['#attributes']['class'][] = 'button';
      $delete_link['#attributes']['class'][] = 'button--danger';
    }

    return [
      'block' => $context,
      'media' => $this->t('(File)'),
      'filename' => $filename,
      'size' => $size_h,
      'delete_link' => $delete_link,
      'bytes' => $bytes,
    ];
  }

  /** Resolve bytes for a File using multiple fallbacks. */
  protected function resolveFileSizeBytes(FileInterface $file): int
  {
    $bytes = (int) ($file->getSize() ?? 0);
    if ($bytes <= 0) {
      $raw = $file->get('filesize')->value ?? NULL;
      if ($raw !== NULL) {
        $bytes = (int) $raw;
      }
    }
    if ($bytes <= 0) {
      try {
        $uri = $file->getFileUri();
        if ($uri) {
          $real = \Drupal::service('file_system')->realpath($uri);
          if ($real && is_file($real)) {
            $fs = @filesize($real);
            if (is_int($fs) && $fs > 0) {
              $bytes = $fs;
            }
          }
        }
      } catch (\Throwable $e) {
      }
    }
    return max(0, (int) $bytes);
  }

  protected function getMediaFile(MediaInterface $media): ?FileInterface
  {
    try {
      $source = $media->getSource();
      $def = $source->getSourceFieldDefinition($media);
      if ($def) {
        $field_name = $def->getName();
        if ($media->hasField($field_name) && !$media->get($field_name)->isEmpty()) {
          $item = $media->get($field_name);
          if (!empty($item->entity) && $item->entity instanceof FileInterface) {
            return $item->entity;
          }
          if (isset($item->target_id) && $item->target_id) {
            $loaded = $this->entityTypeManager->getStorage('file')->load($item->target_id);
            if ($loaded instanceof FileInterface) {
              return $loaded;
            }
          }
        }
      }
    } catch (\Throwable $e) {
    }
    try {
      foreach ($media->getFieldDefinitions() as $fname => $fdef) {
        $type = $fdef->getType();
        $target = $fdef->getSetting('target_type') ?? NULL;
        $looks_file = in_array($type, ['image', 'file'], TRUE) || $target === 'file';
        if (!$looks_file || !$media->hasField($fname) || $media->get($fname)->isEmpty()) {
          continue;
        }
        $item = $media->get($fname);
        if (!empty($item->entity) && $item->entity instanceof FileInterface) {
          return $item->entity;
        }
        if (isset($item->target_id) && $item->target_id) {
          $loaded = $this->entityTypeManager->getStorage('file')->load($item->target_id);
          if ($loaded instanceof FileInterface) {
            return $loaded;
          }
        }
      }
    } catch (\Throwable $e) {
    }

    return NULL;
  }

  protected function humanSize(int $bytes): string
  {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $p = (int) floor(log($bytes, 1024));
    $p = max(0, min($p, count($units) - 1));
    $val = $bytes / (1024 ** $p);
    return number_format($val, $p >= 2 ? 2 : 0) . ' ' . $units[$p];
  }
}
