<?php

namespace Drupal\analytics_adfs_claims_handler\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles SAML user sync events for the Analytics ADFS Claims Handler module.
 *
 * Replaces hook_simplesamlphp_auth_allow_login() and
 * hook_simplesamlphp_auth_user_attributes() from the legacy .module file.
 */
class SamlAuthEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The group membership loader service.
   *
   * @var object
   */
  protected $membershipLoader;

  /**
   * Constructs a SamlAuthEventSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param object $membership_loader
   *   The group membership loader service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, $membership_loader) {
    $this->entityTypeManager = $entity_type_manager;
    $this->membershipLoader = $membership_loader;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SamlauthEvents::USER_SYNC => 'onUserSync',
    ];
  }

  /**
   * Handles the samlauth user sync event.
   *
   * Blocks student accounts, syncs user fields, and assigns group memberships.
   *
   * @param \Drupal\samlauth\Event\SamlauthUserSyncEvent $event
   *   The user sync event.
   *
   * @throws \RuntimeException
   *   When a student account (@sundevils.asu.edu) attempts to log in.
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onUserSync(SamlauthUserSyncEvent $event): void {
    $account = $event->getAccount();
    $attributes = $event->getAttributes();

    // Deny access to student accounts.
    // Replaces hook_simplesamlphp_auth_allow_login().
    $epn = $attributes['eduPersonPrincipalName'][0] ?? '';
    if ($epn && str_contains($epn, '@sundevils.asu.edu')) {
      throw new \RuntimeException('Student accounts are not permitted to log in.');
    }

    // Sync user fields from SAML attributes.
    // Replaces hook_simplesamlphp_auth_user_attributes().
    $saml_mail = $attributes['mail'][0] ?? ($attributes['mail'] ?? '');
    $saml_display_name = $attributes['Display-Name'][0] ?? ($attributes['Display-Name'] ?? '');
    $saml_groups = $attributes['group'] ?? [];

    // Ensure every user belongs to the 'AllAnalyticsUsers' group.
    $saml_groups[] = 'AllAnalyticsUsers';

    if ($saml_mail) {
      $account->set('mail', $saml_mail);
      $event->markAccountChanged();
    }

    if ($saml_display_name) {
      $account->set('field_name', $saml_display_name);
      $event->markAccountChanged();
    }

    // Assign the user to the proper groups.
    $viewer_groups = $this->assignGroupMemberships($account, 'vwr', 'member', $saml_groups);
    $power_groups = $this->assignGroupMemberships($account, 'pwr', 'power', $saml_groups);

    // Remove the user from groups they no longer belong to.
    $this->removeGroupMemberships($account, $viewer_groups, $power_groups);
  }

  /**
   * Assigns a user to groups matching the given SAML group claims.
   *
   * @param \Drupal\user\UserInterface $account
   *   The Drupal user account.
   * @param string $permission
   *   The field suffix used to query groups (e.g. 'vwr' or 'pwr').
   * @param string $role
   *   The group role suffix to assign (e.g. 'member' or 'power').
   * @param array $groups
   *   The SAML group claim values.
   *
   * @return array
   *   The group IDs the user was assigned to.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function assignGroupMemberships(UserInterface $account, string $permission, string $role, array $groups): array {
    $target_groups = $this->entityTypeManager
      ->getStorage('group')
      ->getQuery()
      ->condition("adfs_claims_handler_$permission", $groups, 'IN')
      ->accessCheck(FALSE)
      ->execute();

    foreach ($target_groups as $gid) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      $group = $this->entityTypeManager->getStorage('group')->load($gid);
      if (!$group instanceof GroupInterface) {
        continue;
      }

      $membership = $group->getMember($account);
      $user_role = "content_owner_group_ty-$role";
      $values = ['group_roles' => $user_role];

      if (!$membership) {
        $group->addMember($account, $values);
        $group->save();
      }
      elseif (!array_key_exists($user_role, $membership->getRoles())) {
        $group->removeMember($account);
        $group->save();
        $group->addMember($account, $values);
        $group->save();
      }
    }

    return $target_groups;
  }

  /**
   * Removes the user from groups they no longer belong to.
   *
   * @param \Drupal\user\UserInterface $account
   *   The Drupal user account.
   * @param array $viewer_groups
   *   Group IDs the user is assigned to as a viewer.
   * @param array $power_groups
   *   Group IDs the user is assigned to as a power user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function removeGroupMemberships(UserInterface $account, array $viewer_groups, array $power_groups): void {
    $current_memberships = $this->membershipLoader->loadByUser($account);

    foreach ($current_memberships as $membership) {
      $group = $membership->getGroup();

      if (!in_array($group->id(), $viewer_groups) && !in_array($group->id(), $power_groups)) {
        $group->removeMember($account);
        $group->save();
      }
    }
  }

}
