<?php

namespace Drupal\asu_custom_schema\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Controls access to the Schema tab.
 *
 * Grants access when:
 *  1. User has 'manage node schema' or 'administer nodes' permission.
 *  2. Schema is enabled for the node's content type (from config).
 */
class NodeSchemaAccess {

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
  ) {}

  public function access(AccountInterface $account, NodeInterface $node): AccessResultInterface {
    $has_permission = $account->hasPermission('manage asu node schema')
      || $account->hasPermission('administer nodes');

    if (!$has_permission) {
      return AccessResult::forbidden('Requires manage asu node schema or administer nodes permission.')
        ->cachePerPermissions();
    }

    $bundle_enabled = $this->configFactory
      ->get('asu_custom_schema.bundle.' . $node->bundle())
      ->get('enabled') ?? FALSE;

    return $bundle_enabled
      ? AccessResult::allowed()->addCacheableDependency($node)->cachePerPermissions()
      : AccessResult::forbidden('Schema is not enabled for this content type.')
          ->addCacheableDependency($node)->cachePerPermissions();
  }

}
