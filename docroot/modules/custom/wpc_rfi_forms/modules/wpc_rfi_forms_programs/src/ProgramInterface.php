<?php

namespace Drupal\wpc_rfi_forms_programs;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Program entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup wpc_rfi_forms_programs
 */
interface ProgramInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
