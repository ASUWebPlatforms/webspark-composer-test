<?php
namespace Drupal\asu_quiz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 @file
 * Contains \Drupal\asu_quiz\Plugin\Block\quiz_confirmation_block
 */


/**
 * Provides a Persona quiz landing page block.
 *
 * @Block(
 *   id = "quiz_landingpage_block",
 *   admin_label = @Translation("Quiz landing page block"),
 *  
 * )
 */
class quiz_landingpage_block extends BlockBase {

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

    $urlfname = \Drupal::request()->query->get('fname');
    $urllname = \Drupal::request()->query->get('lname');
    $urlemail = \Drupal::request()->query->get('email');
    $urlstype = \Drupal::request()->query->get('stype');
    $urlzipcode = \Drupal::request()->query->get('zipcode');
    $urlcountry = \Drupal::request()->query->get('country');
    $urlsterm = \Drupal::request()->query->get('sterm');
    $urlrfisid = \Drupal::request()->query->get('rfi_sid');
    $urlrfinid = \Drupal::request()->query->get('rfi_nid');
    //$urlvarsarray[] = '';
      $urlvarsarray['fname'] = isset($urlfname)?$urlfname:'';
      $urlvarsarray['lname'] = isset($urllname)?$urllname:'';
      $urlvarsarray['email'] = isset($urlemail)?$urlemail:'';
      $urlvarsarray['stype'] = isset($urlstype)?$urlstype:'';
      $urlvarsarray['zipcode'] = isset($urlzipcode)?$urlzipcode:'';
      $urlvarsarray['country'] = isset($urlcountry)?$urlcountry:'';
      $urlvarsarray['sterm'] = isset($urlsterm)?$urlsterm:'';
      $urlvarsarray['rfi_sid'] = isset($urlrfisid)?$urlrfisid:'';
      $urlvarsarray['rfi_nid'] = isset($urlrfinid)?$urlrfinid:'';
	  if(!empty($urlvarsarray['email'])){
		  $url_variables = array('fname' => $urlvarsarray['fname'], 'lname' => $urlvarsarray['lname'], 'email' => $urlvarsarray['email'], 'stype' => $urlvarsarray['stype'], 'sterm' => $urlvarsarray['sterm'], 'zipcode' => $urlvarsarray['zipcode'], 'country' => $urlvarsarray['country']);
		   $_SESSION['quiz_email_url_variables'] = $url_variables;
	  }
	  else{
		  unset( $_SESSION['quiz_email_url_variables']);
	  }
     

	  foreach($urlvarsarray as $key=>$values){
		  $coded_values[$key] = base64_encode($values);
	  }

      $coded_values['r'] = 1; //add a r variable to the url to perform check on it and avoid redirect loop
      $r_variable = \Drupal::request()->query->get('r');
      if(isset($r_variable )){

      }
      else { /** @var  $redirect_url to redirect the node */
          $redirect_url = new RedirectResponse(\Drupal\Core\Url::fromRoute('<current>', array($coded_values))->toString());
          $redirect_url->send();
      }

      $fname = isset($urlfname)?$urlfname:'';
      $lname = isset($urllname)?$urllname:'';
      $email = isset($urlemail)?$urlemail:'';
      $stype = isset($urlstype)?$urlstype:'';
      $zipcode = isset($urlzipcode)?$urlzipcode:'';
      $country = isset($urlcountry)?$urlcountry:'';
      $sterm = isset($urlsterm)?$urlsterm:'';
      $rfi_sid = isset($urlrfisid)?$urlrfisid:'';
      $rfi_nid = isset($urlrfinid)?$urlrfinid:'';


    $block_content = "<div id='persona_landing_content'><div id='persona_quiz_landing'><div class='persona_quiz_inner'><div class='persona_quiz_content'><p><strong>Which type of student are you most like?</strong></p>";
    if(!empty($fname)){
        $button_content = "<p><a class='btn btn-maroon' href='/quiz/experience_form?fname=$fname&lname=$lname&email=$email&zipcode=$zipcode&country=$country&stype=$stype&sterm=$sterm&rfi_sid=$rfi_sid&rfi_nid=$rfi_nid&r=1'>Start quiz</a></p>";
                }
    else{
        $button_content = "<p><a href='/quiz/experience_form'  class='btn btn-maroon'>Start quiz</a></p>";
    }
      $block_content .= $button_content;
      $block_content .= "<p>Are you trying to decide which college will help you thrive? Remember, there isn’t one right way to do college — there are many paths to success. See which of the five types of student you’re most like. After this quick quiz, you can read about your thrive factors, find characteristics to look for in a college and even receive personalized information about Arizona State University.</p> </div></div></div><div id='persona_quiz_image'>&nbsp;</div><img class='mobile_only' style='height:100%; margin-top:-15px; width: 100%;' src='https://admission.asu.edu/sites/default/files/quizimages3full.jpg'></div>";

    $rendering_in_block = $block_content ;
    //ksm($block_content);
    // return $rendering_in_block;
      return array(
          '#markup' => \Drupal\Core\Render\Markup::create($rendering_in_block),
          '#cache' => array(
              'max-age' => 0,
          ),
          '#attached' => [
              'library' => [
                  'asu_quiz/Quizconfirmationjs',
              ],
          ],
      );
  }
}