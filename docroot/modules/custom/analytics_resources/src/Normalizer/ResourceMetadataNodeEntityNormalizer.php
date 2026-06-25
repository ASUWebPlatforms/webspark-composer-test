<?php

namespace Drupal\analytics_resources\Normalizer;

use ArrayObject;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class ResourceMetadataNodeEntityNormalizer extends ContentEntityNormalizer
{
  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\node\NodeInterface';

  /**
   * {@inheritdoc}
   * TODO: Is this still needed? We are no longer using the "Resource" based content types
   */
  public function supportsNormalization($data, string $format = NULL, array $context = []): bool
  {
    // If we aren't dealing with an object or the format is not supported
    if (!is_object($data) || !$this->checkFormat($format)) {
      return false;
    }

    // This custom normalizer should be supported for "Article" nodes.
    if ($data instanceof NodeInterface && $data->getType() == 'resource_metadata') {
      return true;
    }

    // Otherwise, this normalizer does not support the $data object.
    return false;
  }

  /**
   * {@inheritdoc}
   * @param Node $entity
   */
  public function normalize($entity, $format = null, array $context = []): float|int|bool|ArrayObject|array|string|null
  {
    $attributes = parent::normalize($entity, $format, $context);

    // Re-sort the array after our new addition.
    ksort($attributes);

    return $attributes;
  }
}
