<?php

namespace Drupal\asu_custom_schema\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Utility\Token;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Builds Schema.org JSON-LD for a node.
 */
class SchemaBuilder {

  protected const BUNDLE_TYPE_MAP = [
    'article'      => 'Article',
    'blog_post'    => 'BlogPosting',
    'blog'         => 'BlogPosting',
    'news'         => 'NewsArticle',
    'news_article' => 'NewsArticle',
    'event'        => 'Event',
    'product'      => 'Product',
    'faq'          => 'FAQPage',
    'recipe'       => 'Recipe',
    'person'       => 'Person',
    'profile'      => 'Person',
    'job'          => 'JobPosting',
    'job_posting'  => 'JobPosting',
    'course'       => 'Course',
    'review'       => 'Review',
    'how_to'       => 'HowTo',
    'howto'        => 'HowTo',
    'basic_page'   => 'WebPage',
    'page'         => 'WebPage',
    'landing_page' => 'WebPage',
  ];

  protected const IMAGE_FIELD_CANDIDATES = [
    'field_image',
    'field_hero_image',
    'field_featured_image',
    'field_thumbnail',
    'field_photo',
    'field_media_image',
    'field_media',
  ];

  protected const KEYWORD_FIELD_CANDIDATES = [
    'field_tags',
    'field_tag',
    'field_keywords',
    'field_topics',
    'field_category',
    'field_categories',
  ];

  public function __construct(
    protected Token $token,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Returns the fully merged schema: base field JSON or auto-generated.
   */
  public function buildSchema(NodeInterface $node): array {
    $saved_json = $node->get('asu_schema_json')->value ?? '';

    if (!empty(trim($saved_json))) {
      $resolved = (string) $this->token->replace(
        $saved_json,
        ['node' => $node],
        ['clear' => TRUE, 'sanitize' => FALSE]
      );
      $schema = json_decode($resolved, TRUE);
      if (json_last_error() === JSON_ERROR_NONE && is_array($schema)) {
        return $schema;
      }
      $this->loggerFactory->get('asu_custom_schema')->warning(
        'Could not parse schema JSON for node @nid: @err',
        ['@nid' => $node->id(), '@err' => json_last_error_msg()]
      );
    }

    return $this->buildAutoSchema($node);
  }

  /**
   * Builds the base schema from live node data.
   * Exposed publicly so the Schema tab can render a live preview.
   */
  public function buildAutoSchema(NodeInterface $node): array {
    $schema = [
      '@context'      => 'https://schema.org',
      '@type'         => static::BUNDLE_TYPE_MAP[$node->bundle()] ?? 'WebPage',
      'name'          => $node->getTitle(),
      'url'           => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
      'datePublished' => date('c', $node->getCreatedTime()),
      'dateModified'  => date('c', $node->getChangedTime()),
    ];

    // Language.
    $langcode = $node->language()->getId();
    if ($langcode && $langcode !== 'und') {
      $schema['inLanguage'] = $langcode;
    }

    // Author.
    $owner = $node->getOwner();
    if ($owner && !$owner->isAnonymous()) {
      $schema['author'] = ['@type' => 'Person', 'name' => $owner->getDisplayName()];
    }

    // Publisher.
    $site_name = $this->configFactory->get('system.site')->get('name');
    if (!empty($site_name)) {
      $schema['publisher'] = ['@type' => 'Organization', 'name' => $site_name];
    }

    // Description.
    if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
      $body    = $node->get('body')->first();
      $summary = trim($body->summary ?? '');
      if ($summary === '') {
        $summary = mb_strimwidth(strip_tags($body->value ?? ''), 0, 300, '…');
      }
      if ($summary !== '') {
        $schema['description'] = $summary;
      }
    }

    // Image.
    foreach (static::IMAGE_FIELD_CANDIDATES as $field_name) {
      if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
        continue;
      }
      $entity = $node->get($field_name)->first()?->entity;
      if (!$entity) {
        continue;
      }
      $url = $this->resolveImageUrl($entity);
      if ($url) {
        $schema['image'] = ['@type' => 'ImageObject', 'url' => $url];
        break;
      }
    }

    // Keywords.
    $keywords = [];
    foreach (static::KEYWORD_FIELD_CANDIDATES as $field_name) {
      if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
        continue;
      }
      foreach ($node->get($field_name)->referencedEntities() as $term) {
        if ($term instanceof TermInterface) {
          $keywords[] = $term->getName();
        }
      }
    }
    if (!empty($keywords)) {
      $schema['keywords'] = implode(', ', array_unique($keywords));
    }

    return $schema;
  }

  protected function resolveImageUrl(object $entity): ?string {
    if ($entity instanceof FileInterface) {
      return $this->fileUrlGenerator->generateAbsoluteString($entity->getFileUri());
    }
    if (is_a($entity, '\Drupal\media\MediaInterface')) {
      $source_field = $entity->getSource()->getConfiguration()['source_field'] ?? NULL;
      if ($source_field && $entity->hasField($source_field)) {
        $file = $entity->get($source_field)->entity;
        if ($file instanceof FileInterface) {
          return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
    return NULL;
  }

}
