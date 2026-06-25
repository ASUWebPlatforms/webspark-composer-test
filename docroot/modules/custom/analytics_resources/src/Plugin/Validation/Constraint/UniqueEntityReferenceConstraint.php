<?php

namespace Drupal\analytics_resources\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Custom validation constraint for ensuring unique entity references.
 * @Constraint(
 *   id = "UniqueEntityReference",
 *   label = @Translation("Unique entity reference", context = "Validation"),
 *   type = "entity"
 * )
 */
class UniqueEntityReferenceConstraint extends Constraint
{
  public string $notUniqueMessage = 'The referenced entity is already referenced by another %content_type record.';
}
