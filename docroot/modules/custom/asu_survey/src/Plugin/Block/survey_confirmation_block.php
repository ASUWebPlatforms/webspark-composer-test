<?php
namespace Drupal\asu_survey\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\asu_survey\Controller\SurveyConfirmController;

/**
 @file
 * Contains \Drupal\asu_survey\Plugin\Block\survey_confirmation_block
 */






/**
 * Provides a survey block.
 *
 * @Block(
 *   id = "survey_confirmation_block",
 *   admin_label = @Translation("Survey confirmation block"),
 *  
 * )
 */
class survey_confirmation_block extends BlockBase {

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
      $current_path = \Drupal::service('path.current')->getPath();
      $path_args = explode('/', $current_path);
      //$sid_val = $path_args[2];
      $sid_val = \Drupal::request()->query->get('sid');
	  //ksm('sid',$sid_val);
      //\Drupal::logger('sid val initial')->notice(print_r($sid_val, TRUE));
      $sid = isset($sid_val) ? $sid_val:'25';
      //\Drupal::logger('sid val')->notice(print_r($sid, TRUE));
      //\Drupal::logger('path args block')->notice(print_r($path_args, TRUE));
     $controller_variable = new SurveyConfirmController;
     $rendering_in_block = $controller_variable->survey_confirm_page($sid = NULL);
	  
     //return $rendering_in_block; 
	  return array(
            '#markup' => \Drupal\Core\Render\Markup::create($rendering_in_block),
            '#cache' => array(
                'max-age' => 0,
            ),
			'#attached' => [
                'library' => [
                    'asu_survey/SurveyLib',
                ],
            ],
           
        ); 
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}