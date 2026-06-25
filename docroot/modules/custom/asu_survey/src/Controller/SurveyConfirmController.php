<?php

namespace Drupal\asu_survey\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityViewBuilder;


/**
 * Provides route responses for the Example module.
 */
class SurveyConfirmController extends ControllerBase
{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function survey_confirm_page($sid_val = NULL)
    {
       \Drupal::service('page_cache_kill_switch')->trigger();
		$config_data = \Drupal::config('asu_survey.admin_settings');	
	    $nid = $config_data->get('survey_confirm_0');
		if(empty($sid_val)){
			//$sid = \Drupal::routeMatch()->getParameter('sid');
			$sid = \Drupal::request()->query->get('sid');
		}
		else{
			$sid = $sid_val;
		}
		//ksm($sid);
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
			//ksm($submission_data);
			$rfi = $submission_data['i_want_to_get_more_information_about_asu'];
			/*$undecided_decided1 = $submission_data['i_feel_prepared_for_success_in_a_collegiate_setting'];
			
			$undecided_decided2 = $submission_data['i_m_seeking_a_degree_with_the_mindset_that_an_earned_degree_will'];
			if((($undecided_decided1 == "Strongly disagree") || ($undecided_decided1 == "Disagree") || ($undecided_decided1 == "Neutral") ) && (($undecided_decided2 == "Strongly disagree") || ($undecided_decided2 == "Disagree") || ($undecided_decided2 == "Neutral") )){
				$next_nids_array = array(49,50,51,52,53,56 );
				$_SESSION['decided'] = "undecided";
			}
			else if((($undecided_decided1 == "Strongly agree") || ($undecided_decided1 == "Agree")) && (($undecided_decided2 == "Strongly agree") || ($undecided_decided2 == "Agree"))){
				$next_nids_array = array(49,50,51,54,55,56 );
				$_SESSION['decided'] = "decided";
			}
			else{
				$_SESSION['decided'] = "undecided";
			}*/
			$_SESSION['rfi_data'] = $rfi; 
			
			if(!empty($nid )){
				/*if($nid == 56){
					 $block = \Drupal\block\Entity\Block::load('asu_degree_rfi_rfi_block');
					 $block_content = \Drupal::entityManager()
						  ->getViewBuilder('block')
						  ->view($block);
					ksm($block);

				}*/
				
				$node = \Drupal\node\Entity\Node::load($nid);
				$builder = \Drupal::entityTypeManager()->getViewBuilder('node'); 
				$build = $builder->view($node, 'full');
				$output = "<div id='topAnchorDiv'><span>&nbsp;</span></div><div id='survey-confirm-node'>";
				$output .= \Drupal::service('renderer')->render($build);
				$output .= "</div>";
				$body = $output;
			}
			else{
				$body = '';
			}
		}
		else{
			$body = '';
		}
			
		return $body;
		/*return array(
            '#markup' => \Drupal\Core\Render\Markup::create($body),
            '#cache' => array(
                'max-age' => 0,
            ),
			'#attached' => [
                'library' => [
                    'asu_survey/SurveyLib',
                ],
            ],
           
        );*/

    }
}