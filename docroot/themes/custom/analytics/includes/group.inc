<?php
/**
 * @file
 * Theme and preprocess functions for groups.
 */

use Drupal\group\Entity\Group;

function analytics_preprocess_group(&$variables): void
{
  $group = $variables['group'];
  $variables['isGroupMember'] = getGroupMembership($group->id());
  $variables['isGroupPowerUser'] = isGroupPowerUser($group->id());
  $favorites_link = [
    '#lazy_builder' => [
      'flag.link_builder:build',
      [
        'group',
        $group->id(),
        'favorite_group',
      ]
    ],
    '#create_placeholder' => true,
  ];
  $variables['favorites_link'] = $favorites_link;
}

/**
 * Determine if the current user is a Group member.
 *
 * @param int $gid
 *
 * @return bool
 */
function getGroupMembership(int $gid = null): bool
{
  if (!$gid) {
    return false;
  }

  $currentUser = Drupal::currentUser();
  $group = Group::load($gid);

  return (bool)$group?->getMember($currentUser);
}

/**
 * Checks if the current user has the "content_owner_group_ty-power" role in the specified group.
 *
 * @param int $gid The group ID to check the user's role in.
 *
 * @return bool
 */
function isGroupPowerUser(int $gid): bool
{
  $group = Group::load($gid);
  $currentUser = Drupal::currentUser();
  $member = $group?->getMember($currentUser);

  if ($member) {
    $roles = $member->getRoles();
    return array_key_exists('content_owner_group_ty-power', $roles);
  }

  return false;
}

/**
 * Get the count of published reports.
 *
 * @param int $gid
 *
 * @return int|null
 */
function getReportCount(int $gid): ?int
{
  $group = Group::load($gid);
  $reports = $group?->getRelatedEntities('group_node:report');
  $published_reports = array_filter($reports, function ($report) {
    return $report->isPublished();
  });

  return count($published_reports);
}
