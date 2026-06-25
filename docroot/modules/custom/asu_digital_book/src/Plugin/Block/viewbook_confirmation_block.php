<?php

namespace Drupal\asu_digital_book\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime;


/**
 @file
 * Contains \Drupal\asu_digital_book\Plugin\Block\viewbook_confirmation_block
 */






/**
 * Provides a Camera block.
 *
 * @Block(
 *   id = "viewbook_confirmation_block",
 *   admin_label = @Translation("Confirmation block for ASU Digital View Book block"),
 *   category = @Translation("Confirmation block for ASU Digital View Book block"),
 * )
 */
class viewbook_confirmation_block extends BlockBase {
	
	/**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    //return $account->hasPermission('search content');
      if ( AccessResult::allowedIfHasPermission($account, 'access content') ) {
                return AccessResult::allowedIfHasPermission($account, 'access content');
   }
  }


   /**
   * {@inheritdoc}
   */
  public function build() {
  $sid_val = !empty(\Drupal::request()->query->get('sid'))?\Drupal::request()->query->get('sid'):'';
	  if(!empty($sid_val)){
		  $body = "Thank you for your submission";
	  }
	  else{
		  $body =" Thank you for your submission";
	  }
   return array(
            '#markup' => \Drupal\Core\Render\Markup::create($body),
            '#cache' => array(
                'max-age' => 0,
            ),
			
        );
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}