<?php

namespace Drupal\analytics_designs\TwigExtension;

use Drupal;
use Drupal\group\Entity\Group;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AnalyticsDesignsTwigExtension extends AbstractExtension
{
  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array
  {
    return [
      new TwigFunction('time_ago', [$this, 'getTimeAgo']),
      new TwigFunction('has_group_access', [$this, 'getGroupMembership']),
      new TwigFunction('current_user', [$this, 'getCurrentUser']),
      new TwigFunction('die', [$this, 'die']),
      new TwigFunction('preg_replace', [$this, 'pregReplace']),
      new TwigFunction('group', [$this, 'getGroup']),
    ];
  }

  /**
   * Provides a 'time ago' string for the current node's creation time.
   *
   * @return string A formatted string or a message if no valid node is found.
   */
  public function getTimeAgo(): string
  {
    $currentRouteMatch = Drupal::routeMatch();
    $dateFormatter = Drupal::service('date.formatter');
    $node = $currentRouteMatch->getParameter('node');

    return $node instanceof NodeInterface
      ? $dateFormatter->formatTimeDiffSince($node->getCreatedTime()) . ' ago'
      : 'No valid node found.';
  }

  /**
   * Return the Group.
   *
   * @param int|null $gid
   *
   * @return Group|null
   */
  public function getGroup(int $gid = null): Group|null
  {
    return Group::load($gid);
  }

  /**
   * Determine if the current user is a Group member.
   *
   * @param int|null $gid int Group ID to check
   *
   * @return bool
   */
  public function getGroupMembership(int $gid = null): bool
  {
    if (!$gid) {
      return false;
    }

    $currentUser = Drupal::currentUser();
    $group = Group::load($gid);
    return (bool)$group?->getMember($currentUser);
  }

  /**
   * Get the current Drupal user.
   *
   * @return Drupal\Core\Entity\EntityInterface|Drupal\Core\Entity\EntityBase|User|null
   */
  public function getCurrentUser(): Drupal\Core\Entity\EntityInterface|Drupal\Core\Entity\EntityBase|User|null
  {
    return User::load(Drupal::currentUser()->id());
  }

  /**
   * Kill the page.
   *
   * @return void
   */
  public function die(): void
  {
    die();
  }

  /**
   * Perform a preg_replace.
   *
   * @param string $pattern
   * @param string $replacement
   * @param string $subject
   *
   * @return string
   */
  public function pregReplace(string $pattern, string $replacement, string $subject): string
  {
    return preg_replace($pattern, $replacement, $subject);
  }
}
