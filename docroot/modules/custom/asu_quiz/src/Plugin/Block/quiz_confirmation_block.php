<?php
namespace Drupal\asu_quiz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\asu_quiz\Controller\QuizConfirmController;

/**
 @file
 * Contains \Drupal\asu_quiz\Plugin\Block\quiz_confirmation_block
 */






/**
 * Provides a Persona quiz block.
 *
 * @Block(
 *   id = "quiz_confirmation_block",
 *   admin_label = @Translation("Quiz confirmation block"),
 *  
 * )
 */
class quiz_confirmation_block extends BlockBase {

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
    //return \Drupal::formBuilder()->getForm('Drupal\asu_quiz\Controller\QuizConfirmController');
      //$current_path = \Drupal::service('path.current')->getPath();
      //$path_args = explode('/', $current_path);
      $sid_val = \Drupal::request()->query->get('qsid');
      //$sid_val = \Drupal::routeMatch()->getParameter('sid');
      //\Drupal::logger('sid val initial')->notice(print_r($sid_val, TRUE));
      //$sid = isset($sid_val) ? $sid_val:'5';
      //\Drupal::logger('sid val')->notice(print_r($sid, TRUE));
      //\Drupal::logger('path args block')->notice(print_r($path_args, TRUE));
	  if(!empty($sid_val)){
		  $controller_variable = new QuizConfirmController;
     	  $rendering_in_block = $controller_variable->quiz_confirm_page($sid_val);
	  }
	  else{
		  $rendering_in_block = 'Thank you for the submission.';
	  }
     
     return $rendering_in_block; 
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}