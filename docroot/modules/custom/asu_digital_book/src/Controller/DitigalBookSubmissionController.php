<?php

namespace Drupal\asu_digital_book\Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Http\ClientFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;



/**
 * Defines a route controller for generating JSON pages.
 */
class DitigalBookSubmissionController extends ControllerBase {

    /**
     *
     *
     * @param Request $request
     * @return JsonResponse
     */

     public function CreateWebformSubmission(Request $request){
		// Get data from the request sent by the React app

        $values = json_decode($request->getContent(), TRUE);
		//  ksm($values);

		 $webform_id = 'viewbook';
		 // Load the Webform.
    	 /*$webform = Webform::load($webform_id);
    	 if (!$webform) {
			return new JsonResponse(['error' => 'Webform not found'], 404);
    	 }*/
		 /* if(!empty($values['whatsWhyField'])){
			 foreach($values['whatsWhyField'] as $whatsField){
				 $whatswhyfield = implode(', ',$whatsField);
			 }
	 	  }
		 else{
			 $whatswhyfield = '';
		 } */

		 if(!empty($values['entryTerm'])){
			 $termType = gettype($values['entryTerm']);
			 if($termType == "string"){
				 $termValue = $values['entryTerm'];
			 }
			 else{
				$termValue =  $values['entryTerm']['value'];
			 }
		 }
		  // Define the submission data.

		 $submission_values = [
  			'webform_id' => $webform_id,
			'data' => [
			  'firstname' => $values['firstName'],
			  'emailaddress' => $values['email'],
			  'lastname' => $values['lastName'],
			  'zip' => $values['zipCode'],
			  'country' => $values['Country'],
			  //'entryterm' => $values['entryTerm']['value']? $values['entryTerm']['value']:$values['entryTerm'],
			  'entryterm' => $termValue,
			  'militarystatus' => $values['militaryStatus'],
		      'phone' => $values['phone'],
			  //'interest1' => isset($values['Interest1']['value'])?$values['Interest1']['value']:$values['Interest1'],
			  'interest1' => isset($values['Interest1']) ? (isset($values['Interest1']['value']) ? $values['Interest1']['value'] : $values['Interest1']) : '',

			  'URL' => $values['URL'],
		      'campus' => $values['Campus'],
		      'studenttype'	=> !empty($values['StudentType'])?$values['StudentType']:'',
			  'career' => $values['Career'],
			  'citizenshipcountry' => $values['CitizenshipCountry'],
			  'knowvalue' => !empty($values['knowValue'])?$values['knowValue']:'',
			  'aspiring_profession' => !empty($values['aspiringProfession'])?$values['aspiringProfession']:'' ,
			  'whats_your_why' => !empty($values['whatsWhyField'])?$values['whatsWhyField']:'',
			  'whats_next_to_study' => !empty($values['knowStudyField'])?$values['knowStudyField']:'',
			  'state' => !empty($values['state'])?$values['state']:'',
			  'utm_campaign' => $values['utm_campaign'],
      		  'utm_content' => $values['utm_content'],
      		  'utm_medium' => $values['utm_medium'],
      		  'utm_source' => $values['utm_source'],
      		  'utm_term' => $values['utm_term']
			]
		];

            //ksm($submission_values);
			// Create a new Webform submission.
			$submission = WebformSubmission::create($submission_values);
		 	$submission->save();
		    //ksm($submission);
		    $sid = $submission->id();
		    // ksm($sid);
			// Save the submission.
			 /*try {
			  $submission->save();
			  return new JsonResponse(['status' => 'Submission successful'], 200);
			 }
			 catch (\Exception $e) {
			  return new JsonResponse(['error' => $e->getMessage()], 500);
			 }*/

			$adminConfig = \Drupal::config('asu_digital_book.admin_settings');
			$domain = 'https://' . $_SERVER['HTTP_HOST'];
			$confirm_nid = intval($adminConfig->get('confirmation_page_nid'));
			$custom_url = $domain.'/node/'.$confirm_nid.'?sid='.$sid;
			$redirect_url['redirect_url'] = $custom_url;

			return new JsonResponse($redirect_url);

		}

}



