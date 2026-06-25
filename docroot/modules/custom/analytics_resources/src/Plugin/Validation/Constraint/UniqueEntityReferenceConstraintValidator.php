<?php

namespace Drupal\analytics_resources\Plugin\Validation\Constraint;

use Drupal;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the UniqueEntityReference constraint.
 */
class UniqueEntityReferenceConstraintValidator extends ConstraintValidator
{

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void
  {
    $resource_service = Drupal::service('analytics_resources.resource_service');

    /** @var ContentEntityInterface $entity */
    $entity = $this->context->getRoot()->getValue();
    $entity_id = $entity->id();
    $type = $entity->bundle();

    foreach ($value as $item) {
      $nid = $item->entity->id();
      $metadata_id = $resource_service->loadMetadataIdByResourceId($nid);

      if ($metadata_id !== null && $metadata_id !== $entity_id) {
        $this->context->addViolation($constraint->notUniqueMessage, ['%content_type' => $type]);
      }
    }
  }
}
