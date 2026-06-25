<?php

namespace Drupal\asu_digital_book\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime;


/**
 @file
 * Contains \Drupal\asu_digital_book\Plugin\Block\react_digital_book__block
 */






/**
 * Provides a Camera block.
 *
 * @Block(
 *   id = "react_digital_book_block",
 *   admin_label = @Translation("React ASU Digital View Book block"),
 *   category = @Translation("React ASU Digital View Book block"),
 * )
 */
class react_digital_book_block extends BlockBase {

   /**
   * {@inheritdoc}
   */
  public function build() {
   $build = [];
  
   $build['react_digital_book_block'] = [
     '#markup' => '<div id="digital-viewbook-div"></div>',
     '#attached' => [
       'library' => 'asu_digital_book/digital-lib',
     ],
   ];
   return $build;
	  
 }
}