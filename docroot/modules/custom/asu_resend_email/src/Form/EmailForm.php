<?php

/**
 *@file
 *contains \Drupal\asu_resend_email\Form\EmailForm
 **/

 namespace Drupal\asu_resend_email\Form;
 
 
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeViewBuilder;
use Drupal\node\Entity\Node;
use Drupal\Core\Url; 
 
 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class EmailForm extends FormBase {
    /**
     *{ @inheritdoc}
     */
    public function getFormID(){
        return 'email_form';
    }
    
    
    /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
        
		$current_path = \Drupal::service('path.current')->getPath();
		$path_args = explode('/', $current_path);
		$nodeid = $path_args[2];
		$node = Node::load($nodeid);
		$email = $node->field_student_email[0]->value; 
		$add_email = isset($node->field_student_add_email[0])?$node->field_student_add_email[0]->value:'';
		//ksm($add_email); 
		if(!empty($add_email)){
			$email_text = "Email will be sent to $email, $add_email after you click submit button";
			$default_emails = $email.','.$add_email; 
		} 
		else{
			$email_text = "Email will be sent to $email after you click submit button";
			$default_emails = $email;
		}
		
		
        $form['help'] = [
		  '#type' => 'item',
		  '#markup' => t('<h2>Send confirmation email</h2>'),
		  '#prefix' => '<div class="container-xl">',	
		];
        $form['email'] = [
		  '#type' => 'textfield',
		  '#title' => $this->t("$email_text. If you want to send email to any other email address, please add them in the below field. Separate multiple emails by commas."),
		  //'#default_value' => $email,	
		  
		];
		 
		 $form['actions']['submit'] = [
		  '#type' => 'submit',
		  '#value' => $this->t('Submit'),
		];
		 
		 $form['nid'] = [
		  '#type' => 'hidden',
		  '#title' => $this->t('Node id'),
		  '#default_value' => $nodeid,
		  '#sufix' => '</div>',		 
		];
        
        
        return $form;
     }
     
 
 
   /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
          //ksm($form_state);
          $form_emails = $form_state->getValue('email');
		  $nodeid = $form_state->getValue('nid');
          $node = Node::load($nodeid);
		  // ksm($node);
		  $email_body = $node->field_student_full_agenda[0]->value;
		  //ksm($email_body);
		  $email = $node->field_student_email[0]->value;
		  $campus = $node->field_student_campus[0]->value;
		  //ksm($campus);
		  if($campus == "Polytechnic"){
				$from = "visitpoly@asu.edu";
		  }
		  if($campus == "Tempe"){
			   $from = "asuvisit@asu.edu";
		  }
		  if($campus == "West"){
				$from = "visitwest@asu.edu";
		  }
		  if($campus == "Downtown Phoenix"){
				$from = "visitdowntown@asu.edu";
		  }
		  if($campus == "Havasu"){
				$from = "asuvisit@asu.edu";
		  }
		  // $optional_email = !empty($form_emails)?",".$form_emails:'';
		  if(!empty($email)){
			  if(!empty($form_emails)){
		        $optional_email = $email.",".$form_emails;
			  }
			  else{
				$optional_email = $email;
			  }
		  }
		  else{
			  $optional_email = '';
		  }
		  
		  if(!empty($node->field_student_add_email[0])){
			  $all_emails = $node->field_student_add_email[0]->value.','.$optional_email;
		  }
		  else{
			  $all_emails = $optional_email;
		  }
		 
		  $subject = "Confirmation of your ASU Visit";
          
		 $mailManager = \Drupal::service('plugin.manager.mail');
		 $module = 'asu_resend_email';
		 $key = 'send_confirmation_email';
		 $to = $all_emails;
		  
	     $params['message'] = $email_body;
		 $params['subject'] = $subject;
	     $params['from'] = $from;
		 $params['reply-to'] = $from;
		 
		 $langcode = \Drupal::currentUser()->getPreferredLangcode();
		$send = true;
	  	$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
		$dest_url = '/admin/resend-visit-confirmation-email'; 
		$url = Url::fromUri('internal:' . $dest_url);
		  
  		$form_state->setRedirectUrl( $url ); 
		\Drupal::messenger()->addMessage("Emails has been sent to $all_emails", 'status'); 
		
      }
      
 }