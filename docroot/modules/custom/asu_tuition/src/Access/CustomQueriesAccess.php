<?php

namespace Drupal\asu_tuition\Access;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 *
 */
class CustomQueriesAccess {

  /**
   *
   */
  public static function access(AccountInterface $account) {
    // Allow only user with UID 5.
    /*  return ($account->id() == 7)
    ? AccessResult::allowed()
    : AccessResult::forbidden(); */
    $allowed_roles = ['estimator_data_monitor'];
    return AccessResult::allowedIf(
          (bool) array_intersect($allowed_roles, $account->getRoles())
      );
  }

}
