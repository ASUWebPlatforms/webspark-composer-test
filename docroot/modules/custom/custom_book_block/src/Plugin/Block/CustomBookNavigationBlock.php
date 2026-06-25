<?php

namespace Drupal\custom_book_block\Plugin\Block;

use Drupal\book\BookManagerInterface;
use Drupal\book\Plugin\Block\BookNavigationBlock;
// use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Provides a custom 'Book navigation' block.
 *
 * @Block(
 *   id = "custom_book_navigation",
 *   admin_label = @Translation("Custom book navigation"),
 *   category = @Translation("Menus")
 * )
 */
class CustomBookNavigationBlock extends BookNavigationBlock {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('book.manager'),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_mode' => "all pages",
      'target_book' => "",
      'max_levels' => "",
      'always_expand' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $options = ['' => "Show all"];
    foreach ($this->bookManager->getAllBooks() as $book_id => $book) {
      $options[$book_id] = $book['title'];
    }
    $form['target_book'] = [
      '#type' => 'radios',
      '#title' => $this->t('Book to display'),
      '#options' => $options,
      '#default_value' => $this->configuration['target_book'],
      '#description' => $this->t("If left empty, all books will be shown."),
    ];
    $form['max_levels'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum levels to show'),
      '#min' => 0,
      '#default_value' => $this->configuration['max_levels'],
      '#description' => $this->t("If set to zero, all levels will be shown."),
    ];
    $form['always_expand'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always expand the menu'),
      '#default_value' => $this->configuration['always_expand'],
      '#description' => $this->t("If unchecked, will only be expanded in context."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['target_book'] = $form_state->getValue('target_book');
    $this->configuration['max_levels'] = $form_state->getValue('max_levels');
    $this->configuration['always_expand'] = $form_state->getValue('always_expand');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_bid = 0;

    if ($node = $this->requestStack->getCurrentRequest()->get('node')) {
      $current_bid = empty($node->book['bid']) ? 0 : $node->book['bid'];
    }
    $max_levels = $this->configuration['max_levels'] ?: NULL;
    $target_book = $this->configuration['target_book'];
    $always_expand = $this->configuration['always_expand'];

    if ($this->configuration['block_mode'] == 'all pages') {
      $book_menus = [];
      $pseudo_tree = [0 => ['below' => FALSE]];
      foreach ($this->bookManager->getAllBooks() as $book_id => $book) {
        // If a target book which is not this one, continue.
        if ($target_book && $book_id != $target_book) {
          continue;
        }
        // If only displaying the top node, no need to do additional queries.
        if ($max_levels == 1) {
          $book_node = $this->nodeStorage->load($book_id);
          $book['access'] = $book_node->access('view');
          $pseudo_tree[0]['link'] = $book;
          $book_menus[$book_id] = $this->bookManager->bookTreeOutput($pseudo_tree);
        }
        else {
          // Retrieve the full menu, to the specified depth.
          $data = $this->bookManager->bookTreeAllData($book_id, $book, $max_levels, $always_expand);
          $book_menus[$book_id] = $this->bookManager->bookTreeOutput($data);
        }
        $book_menus[$book_id] += [
          '#book_title' => $book['title'],
        ];
      }
      if ($book_menus) {
        return [
          '#theme' => 'book_all_books_block',
        ] + $book_menus;
      }
    }
    elseif ($current_bid) {
      // If not 'all pages' and a target book which is not this one, return.
      if ($target_book && $current_bid != $target_book) {
        return [];
      }
      // Only display this block when the user is browsing a book and do
      // not show unpublished books.
      $nid = \Drupal::entityQuery('node')
        ->accessCheck(FALSE)
        ->condition('nid', $node->book['bid'], '=')
        ->condition('status', NodeInterface::PUBLISHED)
        ->execute();
      

      // Only show the block if the user has view access for the top-level node.
      if ($nid) {
        $tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book, $max_levels, $always_expand);
        // There should only be one element at the top level.
        $data = array_shift($tree);
        $below = $this->bookManager->bookTreeOutput($data['below']);
        if (!empty($below)) {
          return $below;
        }
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  // public function getCacheContexts() {
  //   return Cache::mergeContexts(parent::getCacheContexts(), ['route.book_navigation']);
  // }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  // public function getCacheMaxAge() {
  //   return 0;
  // }

}
