<?php

/**
 * @file
 * Token hooks for asu_newcollege_custom_tokens module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 *
 * Registers the [asu_newcollege:*] token group and dynamically exposes
 * every saved CustomToken entity as an individual token.
 */
function asu_newcollege_custom_tokens_token_info(): array {
  // Register the token group.
  $types['asu_newcollege'] = [
    'name' => t('ASU New College'),
    'description' => t('Custom text, HTML and date tokens managed from the admin UI.'),
  ];

  $tokens = [];

  // Load all custom token entities and expose them to the token system.
  $entities = \Drupal::entityTypeManager()
    ->getStorage('asu_newcollege_custom_token')
    ->loadMultiple();

  foreach ($entities as $entity) {
    $type_label = match($entity->get('type')) {
      'date' => t('Date'),
      'html' => t('HTML'),
      default => t('Text'),
    };
    $tokens[$entity->id()] = [
      'name' => $entity->label(),
      'description' => $entity->get('description')
        ? $entity->get('description') . ' (' . $type_label . ')'
        : $type_label,
    ];
  }

  return [
    'types' => $types,
    'tokens' => ['asu_newcollege' => $tokens],
  ];
}

/**
 * Implements hook_tokens().
 *
 * Resolves [asu_newcollege:*] tokens to their stored values.
 */
function asu_newcollege_custom_tokens_tokens(
  string $type,
  array $tokens,
  array $data,
  array $options,
  BubbleableMetadata $bubbleable_metadata,
): array {

  $replacements = [];

  if ($type !== 'asu_newcollege') {
    return $replacements;
  }

  $storage = \Drupal::entityTypeManager()
    ->getStorage('asu_newcollege_custom_token');

  foreach ($tokens as $name => $original) {
    /** @var \Drupal\asu_newcollege_custom_tokens\Entity\CustomToken|null $entity */
    $entity = $storage->load($name);

    if ($entity) {
      // Add cache tag so pages are invalidated when the token is updated.
      $bubbleable_metadata->addCacheTags($entity->getCacheTags());
      $replacements[$original] = $entity->getResolvedValue();
    }
  }

  return $replacements;
}
