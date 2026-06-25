<?php

namespace Drupal\custom_book_block;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Defines a book manager which extends the core BookManager class.
 */
class CustomBookBlockServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('book.manager');
    $definition->setClass('Drupal\custom_book_block\expandBookManager');
  }
}
