<?php

/**
 *@file
 *contains \Drupal\asu_masterform_posting\Form\masterFormPosting
 **/

 namespace Drupal\asu_masterform_posting\Form;
 
 

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;
 
 
 /**
  *Defines a form to configure Persoan Quiz confirmation page content settings
  */
 
 class masterFormPosting extends ConfigFormBase{
    /**
     *{ @inheritdoc}
     */
	 
   public function getFormID(){
        return 'asu_masterform_posting_fields_admin_settings';
    }
    
    /*
     **{@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'asu_masterform_posting.fields_admin_settings'
           ];
    }
     /*
     **{@inheritdoc}
     */
     public function buildForm(array $form, FormStateInterface $form_state) {
         $config = $this->config('asu_masterform_posting.fields_admin_settings');
		 $config_data = \Drupal::config('asu_masterform_posting.admin_settings');
		 
		 $webid_data = $config_data->get('webform_form_nid');
		 
		 $webid_values = explode(',',$webid_data);
		 $current_path = \Drupal::service('path.current')->getPath();
		 $path_args = explode('/', $current_path);
		 //ksm($path_args);
		 $webform_name = $path_args[5];
		 //check if the current webform is added to send the submissions to master webform
		 if(!empty($webid_data)){
			// if($webid == $path_args[5]){
			 if(in_array($path_args[5],$webid_values)){
				 $nodeid = $path_args[5];
				 
				 $webform = \Drupal\webform\Entity\Webform::load($webform_name); //replace webform_id with the webform id
				  if ($webform) {
						$view_builder = \Drupal::entityTypeManager()->getViewBuilder('webform');
						$elements = $webform->getElementsDecodedAndFlattened();
						foreach($elements as $key => $values){
							 $formkey[$key] =$key;
						}
				  }
				  
				 $option = "None";
				 array_unshift($formkey,$option); // append "None" to the webform components select option list.
  
 				$text = "<p><strong>Below you can choose the fields you intend to send to Form manager. Choose what ever applies. <br />The webform components are in the drop down list and the titles of the fields are the Form manager fields titles.</strong></p>";
				 
				 // variables declaration to be saved in the 'variables' table with unique form id.
                  
				  $first_name = $nodeid.'-first_name';
				  $middle_name = $nodeid. '-middle_name';
				  $last_name = $nodeid. '-last_name';
				  $address1 = $nodeid. '-street_address';
				  $address2 = $nodeid. '-street_address_2';
				  $city = $nodeid. '-city';
				  $state = $nodeid. '-state';
				  $country = $nodeid. '-country';
				  $zip = $nodeid. '-zipcode';
				  $phone = $nodeid. '-phone';
				  $email = $nodeid. '-email_address';
				  $dob = $nodeid. '-dob';
				  $semester = $nodeid. '-semester_expected_to_attend_asu';
				  $question = $nodeid. '-question';
				  $interest = $nodeid. '-academic_interest';
				  $campus = $nodeid. '-campus';
				  $college = $nodeid. '-college';
				  $major = $nodeid. '-major_program';
				  $high_school = $nodeid. '-high_school';
				  $institution = $nodeid. '-institution';
				  $webform_url = $nodeid. '-webform_url';
				  $diet = $nodeid. '-dietary_restrictions';
				  $food_menu = $nodeid. '-menu_preference_of_first_guest';
				  $food_menu2 = $nodeid. '-menu_preference_of_second_guest';
				  $special_needs = $nodeid. '-specail_needs';
				  $parent_email = $nodeid. '-parent_email';
          $parent_email_2 = $nodeid. '-parent_email_2';
				  $no_of_guests = $nodeid. '-number_of_guests';
				  $form_url = $nodeid. '-form_url';
				  $eventid = $nodeid. '-event_id';
				  $eventname = $nodeid. '-event_name';
				  $parking = $nodeid. '-parking_info';
				  $event_date = $nodeid. '-event_date';
				  $event_category = $nodeid. '-event_type';
				  $asurite = $nodeid. '-asurite';
				  $check_capacity = array('none' => 'None',$nodeid.'-nodesOnly' => 'Nodes',$nodeid.'-formOnly' => 'Just this form');
				  $capacity_var = $nodeid. '-capacity_check';
				  $capacity = $nodeid.'-event_capacity';
				  $capacity_form = $nodeid.'-event_capacity_form';
				  $remaining_spot = $nodeid.'-remaining_spots';
				  $event_node_id = $nodeid.'-event_node_id';
				  $event_start_date = $nodeid.'-event_start_date';
				  $event_start_time = $nodeid.'-event_start_time';
				  $event_end_date = $nodeid.'-event_end_date';
				  $event_end_time = $nodeid.'-event_end_time';
				  $eventdate = $nodeid.'-event_date';
				  $event_location = $nodeid.'-event_location';
				  $opportunity_type = $nodeid.'-opportunityType';
				  $webid = $nodeid.'-nid';
          $service_type = $nodeid.'-service_type'; // Added by Chizuko on 2/20/2025.
				 
				 //create form fields matching master form fields
				  $form['#attributes']= array('id' => 'talisma-div');
  
				  $form['desc'] = array(
					'#markup' => $text,
				  );

				  $form[$webid] = array(
					'#type' => 'textfield',
					'#title' => t('Current Webform name'),   
					'#value' => $nodeid,
					'#default_value' => $config->get($webid,''),
				  );
				 
				 $form[$capacity_var] =  array(
					'#type' => 'radios',
					'#title' => 'Does this webform have the capacity? Is capacity being monitored on form and not by nodes?',
					'#description' => t("Check if capacity is available for this form"),
					'#options' => $check_capacity,
					'#default_value' => $config->get($capacity_var),
					 '#attributes' => [
						// Define a static id so we can easier select it.
						'id' => 'field_capacity_check',
					  ],
                       
         		 );
				 
				 $form[$capacity_form] =  array(
						'#type' => 'textfield',
						'#title' => 'Capacity of the form',
					 	//'#disabled' => true,
						'#maxlength' => 100,
						'#description' => t("Capacity of the form"),
						'#default_value' => $config->get($capacity_form),
					    '#states' => [
							  'visible' => [
								':input[id="field_capacity_check"]' => ['value' => $nodeid.'-formOnly'],
							  ],
						]
					   

				 );
				 
				 $form[$remaining_spot] =  array(
						'#type' => 'textfield',
						'#title' => 'Spots left',
					 	//'#disabled' => true,
						'#maxlength' => 100,
						'#description' => t("Remaining spots left, do not add value, it will be updated programmatically."),
						'#default_value' => $config->get($remaining_spot),
					    '#states' => [
							  'visible' => [
								':input[id="field_capacity_check"]' => ['value' => $nodeid.'-formOnly'],
							  ],
						]
					   

				 );
				 
				 
				 
				  $form[$webform_url] = array(
								'#type' => 'textfield',
								'#title' => t('Webform url'),
								'#description' => t('Enter the webform url'),
								'#default_value' => $config->get($webform_url,''),
						  );
				 
				 $form[$capacity] =  array(
						'#type' => 'select',
						'#title' => 'Capacity',
						'#options' => $formkey,
						'#description' => t("Enter capacity to the form"),
						'#default_value' => $config->get($capacity),
					    '#states' => [
							  'visible' => [
								':input[id="field_capacity_check"]' => ['value' => $nodeid.'-nodesOnly'],
							  ],
						]
					 
				 );
				 
				 $form[$event_node_id] =  array(
						'#type' => 'select',
						'#title' => 'Event node ids',
						'#options' => $formkey,
						'#description' => t("Choose event node id field"),
						'#default_value' => $config->get($event_node_id),
					    '#states' => [
							  'visible' => [
								':input[id="field_capacity_check"]' => ['value' => $nodeid.'-nodesOnly'],
							  ],
						]
					 
				 );

				  $form[$first_name] = array(
						'#type' => 'select',
						'#title' => t('First name'),
						'#options' => $formkey,
						'#default_value' => $config->get($first_name,''),
						  );
				 
				  $form[$event_category] = array(
						'#type' => 'select',
						'#title' => t('Event category'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_category,''),
						  );
				 
				 $form[ $opportunity_type] = array(
						'#type' => 'select',
						'#title' => t('Opportunity type'),
						'#options' => $formkey,
						'#default_value' => $config->get( $opportunity_type,''),
					);
				 
				
				 
				  $form[$middle_name] = array(
						'#type' => 'select',
						'#title' => t('Middle name'),
						'#options' => $formkey,
						'#default_value' => $config->get($middle_name,'NULL'),
						  );
				  $form[$last_name] = array(
						'#type' => 'select',
						'#title' => t('Last name'),
								'#options' => $formkey,
						'#default_value' => $config->get($last_name,'NULL'),
						);
				  $form[$email] = array(
						'#type' => 'select',
						'#title' => t('Email'),
						'#options' => $formkey,
						'#default_value' => $config->get($email,''),
						);

				  $form[$address1] = array(
						'#type' => 'select',
						'#title' => t('Address - Street 1'),
						'#options' => $formkey,
						'#default_value' => $config->get($address1,''),
						);

				  $form[$address2] = array(
						'#type' => 'select',
						'#title' => t('Address - Street 2'),
								'#options' => $formkey,
						'#default_value' => $config->get($address2,''),
						  );
				  $form[$city] = array(
						'#type' => 'select',
						'#title' => t('Address - City'),
								'#options' => $formkey,
						'#default_value' => $config->get($city,''),
						  );
				  $form[$state] = array(
						'#type' => 'select',
						'#title' => t('Address - State'),
								'#options' => $formkey,
						'#default_value' => $config->get($state,''),
						  );

				  $form[$zip] = array(
						'#type' => 'select',
						'#title' => t('Address - zip code'),
								'#options' => $formkey,
						'#default_value' => $config->get($zip,''),
						  );

				  $form[$country] = array(
						'#type' => 'select',
						'#title' => t('Address - Country'),
								'#options' => $formkey,
						'#default_value' => $config->get($country,''),
						  );

				  $form[$phone] = array(
						'#type' => 'select',
						'#title' => t('Phone'),
								'#options' => $formkey,
						'#default_value' => $config->get($phone,''),
						  );
				 
				  $form[$dob] = array(
						'#type' => 'select',
						'#title' => t('Date of birth'),
								'#options' => $formkey,
						'#default_value' => $config->get($dob,''),
						  );

				  $form[$semester] = array(
						'#type' => 'select',
						'#title' => t('Semester'),
								'#options' => $formkey,
						'#default_value' => $config->get($semester,''),
						  );


				  $form[$question] = array(
						'#type' => 'select',
						'#title' => t('Comments'),
								'#options' => $formkey,
						'#default_value' => $config->get($question,''),
						  );

				  $form[$interest] = array(
						'#type' => 'select',
						'#title' => t('Interest - Academic interest'),
								'#options' => $formkey,
						'#default_value' => $config->get($interest,''),
						  );
				  $form[$campus] = array(
						'#type' => 'select',
						'#title' => t('Campus/Location'),
								'#options' => $formkey,
						'#default_value' => $config->get($campus,''),
						  );
				  $form[$high_school] = array(
						'#type' => 'select',
						'#title' => t('Education - High school'),
								'#options' => $formkey,
						'#default_value' => $config->get($high_school,''),
						  );
				  $form[$institution] = array(
						'#type' => 'select',
						'#title' => t('Education - College/University'),
								'#options' => $formkey,
						'#default_value' => $config->get($institution,''),
						  );

				  $form[$major] = array(
						'#type' => 'select',
						'#title' => t('Plan code'),
						'#options' => $formkey,
						'#default_value' => $config->get($major,''),
						  );
				  $form[$college] = array(
						'#type' => 'select',
						'#title' => t('College code'),
						'#options' => $formkey,
						'#default_value' => $config->get($college,''),
						  );
				  $form[$eventid] = array(
						'#type' => 'select',
						'#title' => t('Event ID'),
						'#options' => $formkey,
						'#default_value' => $config->get($eventid,''),
						  );
				 
				  $form[$eventname] = array(
						'#type' => 'select',
						'#title' => t('Event name'),
						'#description' => t('Enter event name'),
					  	'#options' => $formkey,
						'#default_value' => $config->get($eventname,''),
						  );
				  $form[$eventdate] = array(
						'#type' => 'select',
						'#title' => t('Event date'),
						'#description' => t('Enter date'),
					    '#options' => $formkey, 
						'#default_value' => $config->get($eventdate,''),
						  );

				  $form[$event_start_date] = array(
						'#type' => 'select',
						'#title' => t('Event start date'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_start_date,''),
						  );
				 
				 $form[$event_start_time] = array(
						'#type' => 'select',
						'#title' => t('Event start time'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_start_time,''),
						  );

				$form[$event_location] = array(
						'#type' => 'select',
						'#title' => t('Event Location'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_location,''),
				);
				 
				 $form[$event_end_date] = array(
						'#type' => 'select',
						'#title' => t('Event end date'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_end_date,''),
						  );
				 
				 $form[$event_end_time] = array(
						'#type' => 'select',
						'#title' => t('Event end time'),
						'#options' => $formkey,
						'#default_value' => $config->get($event_end_time,''),
						  );

				  $form[$parking] = array(
						'#type' => 'select',
						'#title' => t('Parking'),
						'#options' => $formkey,
						'#default_value' => $config->get($parking,''),
						  );

				 $form[$diet] = array(
						'#type' => 'select',
						'#title' => t('Dietary restrictions'),
								'#options' => $formkey,
						'#default_value' => $config->get($diet,''),
						  );
				 $form[$asurite] = array(
						'#type' => 'select',
						'#title' => t('Asurite'),
						'#options' => $formkey,
						'#default_value' => $config->get($asurite,''),
						  );
				 $form[$food_menu] = array(
						'#type' => 'select',
						'#title' => t('Menu preferences'),
						'#options' => $formkey,
						'#default_value' => $config->get($food_menu,''),
						  );
				  $form[$food_menu2] = array(
						'#type' => 'select',
						'#title' => t('Menu preferences for the guest'),
						'#options' => $formkey,
						'#default_value' => $config->get($food_menu2,''),
						  );
				 $form[$special_needs] = array(
						'#type' => 'select',
						'#title' => t('Special Needs'),
						'#options' => $formkey,
						'#default_value' => $config->get($special_needs,''),
						  );
				  $form[$parent_email] = array(
						'#type' => 'select',
						'#title' => t('Parent email'),
						'#options' => $formkey,
						'#default_value' => $config->get($parent_email,''),
						  );

         	$form[$parent_email_2] = array(
						'#type' => 'select',
						'#title' => t('Parent email 2'),
						'#options' => $formkey,
						'#default_value' => $config->get($parent_email_2,''),
						  );

				  $form[$no_of_guests] = array(
						'#type' => 'select',
						'#title' => t('Number of guests'),
						'#options' => $formkey,
						'#default_value' => $config->get($no_of_guests,''),
						  ); 
         
				  $form[$service_type] = array( // Added by Chizuko on 2/20/2025.
						'#type' => 'select',
						'#title' => t('Phone service type'),
						'#options' => $formkey,
						'#default_value' => $config->get($service_type,''),
						  ); 
         
				 asort($form);
				  return parent::buildForm($form, $form_state);
			 }
			 else{ // if node id not added in the setting page, do not show the field mappings
				  $link = Link::createFromRoute('admin settings page', 'asu_masterform_posting.admin_settings')->toString()->getGeneratedLink();
                  \Drupal::messenger()->addWarning(t("You have not added this node id in the admin settings. If you wish to send the submissions to Salesforce, add the webform name at $link"));
			 }
			
		 }
		  else{ // if node id not added in the setting page, do not show the field mappings
				  $link = Link::createFromRoute('admin settings page', 'asu_masterform_posting.admin_settings')->toString()->getGeneratedLink();
                  \Drupal::messenger()->addWarning(t("You have not added this node id in the admin settings. If you wish to send the submissions to Salesforce, add the webform name at $link"));
			 }
		 
     }
     
 
 
   /*
     **{@inheritdoc}
     */
      public function submitForm(array &$form, FormStateInterface $form_state){
          //ksm($form_state);
           parent::submitForm($form, $form_state);
		 //save the configuration field values
		 $values =  $form_state->getValues();
		 foreach($values as $key => $each_value){
			 $this->config('asu_masterform_posting.fields_admin_settings')
				 ->set($key, $each_value)
				 ->save();
		 } 
		
      }
      
 }