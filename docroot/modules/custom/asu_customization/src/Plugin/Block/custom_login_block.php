<?php
namespace Drupal\asu_customization\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 @file
 * Contains \Drupal\asu_customization\Plugin\Block\custom_login_block
 */



/**
 * Provides a Nav block for user login.
 *
 * @Block(
 *   id = "custom_login_block",
 *   admin_label = @Translation("Custom User login block"),
 *  
 * )
 */
class custom_login_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  
	protected function blockAccess(AccountInterface $account) {
    //return $account->hasPermission('search content');
      if ( AccessResult::allowedIfHasPermission($account, 'access content') ) {
                return AccessResult::allowedIfHasPermission($account, 'access content');
       }
	  return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
	 $auth = \Drupal::currentUser()->isAuthenticated();
	 $account = \Drupal::currentUser();
	 //ksm($account);
	 
     if (\Drupal::currentUser()->isAuthenticated()) {
		 $roles = \Drupal::currentUser()->getRoles();
		 //ksm($roles);
         if(in_array('employee', $roles)) {
		   $cas_login = "<span class='log-link'><a href='/caslogout'>Sign out</a></span>";
		 }
		 else{
		   $cas_login = "<span class='log-link'><a href='/user/logout'>Logout</a></span>";
		 }
		 $local_login = '';
	 }
	 else{
		 $local_login = "<span class='log-link'><a href='/user/login'>Local Login</a></span>";
		 $cas_login = "<span class='log-link'><a href='/caslogin'>Web administrator login</a></span>";
	 } 
	  $login_links = '<div class="header-top"><div class="universal-nav-custom" data-elastic-exclude="data-elastic-exclude" data-testid="universal-navbar"><div class="container-xl"><div><nav aria-label="ASU" class="nav"><div class="links-container"><div class="nav-link login-status">'.$local_login.'&nbsp;&nbsp;'.$cas_login.'</div></div></nav></div></div></div></div>';

	  
	 return [
     '#markup' => $login_links,
     ];
	 
    
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}