<?php
namespace Drupal\asu_survey\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 @file
 * Contains \Drupal\asu_survey\Plugin\Block\survey_confirmation_next_page_block
 */






/**
 * Provides a survey block.
 *
 * @Block(
 *   id = "survey_confirmation_next_page_block",
 *   admin_label = @Translation("Survey next page GI bill block"),
 *  
 * )
 */
class survey_confirmation_next_page_block extends BlockBase {

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
   
      $sid_val = \Drupal::request()->query->get('sid');
     
      $sid = isset($sid_val) ? $sid_val:'5';
      if(!empty(($sid))){
			$webform = \Drupal\webform\Entity\Webform::load('veterans_survey');
			if ($webform->hasSubmissions()) {
				$query = \Drupal::entityQuery('webform_submission')
					->condition('webform_id', 'veterans_survey')
					->condition('sid', intval($sid))
					->accessCheck(FALSE);
				$result = $query->execute();
				$submission_data = [];
				foreach ($result as $item) {
					$submission = \Drupal\webform\Entity\WebformSubmission::load($item);
					$submission_data = $submission->getData();

				}
				
			}
			
			$undecided_decided1 = $submission_data['i_feel_prepared_for_success_in_a_collegiate_setting'];
			$undecided_decided2 = $submission_data['i_m_seeking_a_degree_with_the_mindset_that_an_earned_degree_will'];
			if((($undecided_decided1 == "Strongly disagree") || ($undecided_decided1 == "Disagree") || ($undecided_decided1 == "Neutral") ) && (($undecided_decided2 == "Strongly disagree") || ($undecided_decided2 == "Disagree") || ($undecided_decided2 == "Neutral") )){
				//$next_nids_array = array(51,52,53,56);
				$next_page = 52;
			}
			else if((($undecided_decided1 == "Strongly agree") || ($undecided_decided1 == "Agree")) && (($undecided_decided2 == "Strongly agree") || ($undecided_decided2 == "Agree"))){
				//$next_nids_array = array(51,54,55,56);
				$next_page = 54;
			}
		  	else{
			  $next_page = 52;
		  	}
	  }
	  $block = '<div class="container"><div class="row"><div class="layout__region layout__region--first col-md-2"><span class="survey_result_next_page_button 50 left-float"><strong>&lt; Back</strong></span></div><div class="layout__region layout__region--first col-md-8"><div class="bar-div" style="width: 100%; border: 1px solid #D0D0D0"><div class="survey_bar" style="width: 34%; background: #FFc627;  height:10px;">&nbsp;</div></div></div><div class="layout__region layout__region--second col-md-2"><span class="survey_result_next_page_button '.$next_page.' right-float"><strong>Next &gt;</strong></span></div></div></div>';
	  
     return array(
            '#markup' => \Drupal\Core\Render\Markup::create($block),
            '#cache' => array(
                'max-age' => 0,
            ),
		 );
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}