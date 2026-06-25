<?php
namespace Drupal\asu_contact_webform_custom_options\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\asu_contact_webform_custom_options\Controller\ContactFormController;

/**
 @file
 * Contains \Drupal\asu_contact_webform_custom_options\Plugin\Block\contact_form_confirmation_block
 */






/**
 * Provides a Persona quiz block.
 *
 * @Block(
 *   id = "contact_form_confirmation_block",
 *   admin_label = @Translation("ASU Contact forms confrimation block"),
 *  
 * )
 */
class contact_form_confirmation_block extends BlockBase {

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
      $spec_nid = \Drupal::request()->query->get('rep_nid');
	  $fstate_value =  \Drupal::request()->query->get('fstate');
	  $tstate_value =  \Drupal::request()->query->get('tstate');
	  $intl_value =  \Drupal::request()->query->get('intl');
	  $intl_country =  \Drupal::request()->query->get('intlC');
	  if(!empty($fstate_value)){
		  $state = $fstate_value;
	  }
	  elseif(!empty($tstate_value)){
		  $state = $tstate_value;
	  }
	  else{
		  $state = '';
	  }
	  $web_sid = \Drupal::request()->query->get('sid');
	  $nid[] = isset($spec_nid) ? $spec_nid:'3303';
	  $confirmation="yes";
	  $controller_variable = new ContactFormController;
	  if($intl_value == "No"){
		  if($intl_country == "CN"){
			  $rendering_in_block = $controller_variable->international_china_specialist_node_load($nid);
		  }
		  else{
			  $rendering_in_block = $controller_variable->international_specialist_node_load($nid);
		  }
		  
		  
	  }
	  else{
		  $rendering_in_block = $controller_variable->specialist_node_load($nid, $state);
	  }
	  if($intl_value == "No"){
		  $left_content = '<div class="col-md-6 custom-col">'.$rendering_in_block['left_col_content'].'</div>';
		  $right_content = '<div class="col-md-6 custom-col confirm-right-side"><div class="rhs-content"><div>'.$rendering_in_block["image"].'</div><div class="specialist-info"><h4>'.$rendering_in_block["fullName"].'</h4><p><i class="fas fa fa-envelope"> </i> <a href="'.$rendering_in_block["email"].'">'.$rendering_in_block["email"].'</a></p><p><i class="fas fa fa-phone"> </i> '.$rendering_in_block["phone"].'</p><p>'.$rendering_in_block["bio_content"].'</p></div></div></div>';

		  $full_content = "<div class='row col-12 contact-confirm-row'>$left_content$right_content</div>";
	  }
      else{
		  //if(!empty($state)){
			  $left_content = '<div class="col-md-6 custom-col">'.$rendering_in_block['left_col_content'].'</div>';
			  $right_content = '<div class="col-md-6 custom-col confirm-right-side"><div class="rhs-content"><div>'.$rendering_in_block["image"].'</div><div class="specialist-info"><h4>'.$rendering_in_block["fullName"].'</h4><p><i class="fas fa fa-envelope"> </i> <a href="'.$rendering_in_block["email"].'">'.$rendering_in_block["email"].'</a></p><p><i class="fas fa fa-phone"> </i> '.$rendering_in_block["phone"].'</p><p>'.$rendering_in_block["bio_content"].'</p></div></div></div>';

			  $full_content = "<div class='row col-12 contact-confirm-row'>$left_content$right_content</div>";
		  /*}
		  else{
			  $full_content = "<div class='row col-12 contact-confirm-row custom-int-col'><p>".$rendering_in_block['body']."</p></div>";
		  }*/
	  }
	  
	  return array(
            '#markup' => \Drupal\Core\Render\Markup::create($full_content),
            '#cache' => array(
                'max-age' => 0,
            ),
		    '#attached' => [
                'library' => [
                    'asu_contact_webform_custom_options/contactFormConfirmationCss',
                ],
            ],
           
        );
      
  }
	
  public function getCacheMaxAge() {
     return 0;
  }
}