<?php

namespace Drupal\asu_edu_components;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\node\Entity\Node;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Processor service.
 */
class Processor {

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Constructs a Processor object.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\example\ExampleInterface $path_alias_manager
   *   The path_alias.manager service.
   */
  public function __construct(CurrentPathStack $current_path, AliasManagerInterface $path_alias_manager) {
    $this->currentPath = $current_path;
    $this->pathAliasManager = $path_alias_manager;
  }

  /**
   * Gets the page path alias.
   *
   * @return string
   *   The path alias.
   */
  public function getPathAlias(): string {
    $currentPath = $this->currentPath->getPath();

    if (empty($currentPath)) {
      return '';
    }

    try {
      $result = $this->pathAliasManager->getAliasByPath($currentPath);
    }
    catch (\Throwable $th) {
      return '';
    }

    if (empty($result)) {
      return '';
    }

    return $result;
  }

  /**
   * Tests if Lucky Orange functionality should be enabled.
   *
   * @return bool
   *   The test result.
   */
  public function enableLuckyOrange(Node $node): bool {
    return $node->bundle() === 'page';
  }

}
