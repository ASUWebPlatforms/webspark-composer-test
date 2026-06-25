<?php
namespace Drupal\asu_opt_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 @file
 * Contains \Drupal\asu_opt_calculator\Plugin\Block\advising_appointment_block
 */


/**
 * Provides a block that contains Advising appointment button based on day and time logic built in the build() function.
 *
 * @Block(
 *   id = "advising_appointment_block",
 *   admin_label = @Translation("Advising appointment button"),
 *  
 * )
 */
class advising_appointment_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    //return $account->hasPermission('search content');
      //if ( AccessResult::allowedIfHasPermission($account, 'access content') ) {
       return AccessResult::allowedIfHasPermission($account, 'access content');
      //}
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

		$day = date('D');
		$time = strtotime(date('H:i'));
		$sec = date('i');
		$initial_time = strtotime(date('H:i',strtotime("3 PM")));
		$final_time = strtotime(date('H:i',strtotime("5 PM")));
		$wed_start_time = strtotime(date('H:i',strtotime("11 AM")));
		$wed_end_time = strtotime(date('H:i',strtotime("3:30 PM")));
		$meridian = date('a');
	    $data = '';
		if(($day == "Tue") || ($day == "Thu")){
		  if(($time >= $initial_time) && ($time <= $final_time)){
			   $data = '<p><strong>Click on the link below to meet with our express advisor. You will enter a zoom waiting room with instructions on what to do next.</strong></p><p><a href="https://asu.zoom.us/j/9250366374" class="btn btn-primary">Join Express Advising</a></p>';
			 
			}
		  else{
			  	$data = '';
		  }
		}

		elseif(($day == "Wed")){
		  if(($time >= $wed_start_time) && ($time <= $wed_end_time)){
			   $data = '<p><strong>Click on the link below to meet with our express advisor. You will enter a zoom waiting room with instructions on what to do next.</strong></p><p><a href="https://asu.zoom.us/j/9250366374" class="btn btn-primary">Join Express Advising</a></p>';
			
			}
		}
	  else{
		  $data = '';
	  }
	  //ksm($data);
     $rendering_in_block = $data ;
    
      return array(
          '#markup' => \Drupal\Core\Render\Markup::create($rendering_in_block),
          '#cache' => array(
              'max-age' => 0,
          ),
         
      );
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}