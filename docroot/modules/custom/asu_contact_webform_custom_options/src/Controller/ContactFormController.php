<?php

namespace Drupal\asu_contact_webform_custom_options\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\file\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Component\Serialization\Json;

/**
 * Provides route responses for the Example module.
 */
class ContactFormController extends ControllerBase
{
	private $node_id;
	private $content;
	private $confirmation = null;
	
	/**Returns information of the reps info in json format which is then called by ajax function in contact_form.js file
    * 
    * @param $state_selected (State value) passed by the jQuery code in contact_form.js file
	* @return JsonResponse
    **/
    public function rep_info_page($state_selected = null)
    {
      
		$rep_query = \Drupal::database();
	
		$hs_value = '';
		$rep_fields = $rep_query->select('node_field_data', 'nfv')->fields('nfv', array('nid'));
		$rep_fields->innerJoin('node__field_specialist_state_freshman', 'fstate', 'fstate.entity_id = nfv.nid');
		$rep_fields->innerJoin('node__field_specialist_category', 'fsc','fsc.entity_id = nfv.nid' );
		$rep_fields->leftJoin("node__field_specialist_hs_names",'fshs', 'fshs.entity_id = nfv.nid');
		$rep_fields->condition('fstate.field_specialist_state_freshman_value', $state_selected);
		$rep_fields->condition('fsc.field_specialist_category_target_id',1);
		$results = $rep_fields->execute()->fetchAll();

		
		
		foreach($results as $rep_id){
			    $node_id[] = $rep_id->nid;
		}
		
		//$content = specialist_node_load($node_id, $results);$confirmation = null
		$content = $this->specialist_node_load($node_id, $state_selected);
		return new JsonResponse(
				[
				 'repInfo' => $content, 
				]

		);

	}
	
	/**Returns information of the reps info in json format which is then called by ajax function in contact_form.js file
    * 
    * @param $state_selected (state value) and $hs_selected(high school value) passed by the jQuery code in contact_form.js file
	* @return JsonResponse
    **/
	public function rep_info_hs_page($state_selected = null, $hs_selected = null)
    {
		$state_selected = "Arizona";
		$rep_query = \Drupal::database();
	    $hs_value = $hs_selected;
		$rep_fields = $rep_query->select('node_field_data', 'nfv')->fields('nfv', array('nid'));
		$rep_fields->innerJoin('node__field_specialist_state_freshman', 'fstate', 'fstate.entity_id = nfv.nid');
		$rep_fields->innerJoin('node__field_specialist_category', 'fsc','fsc.entity_id = nfv.nid' );
		$rep_fields->innerJoin("node__field_specialist_hs_names",'fshs', 'fshs.entity_id = nfv.nid');
		$rep_fields->condition('fstate.field_specialist_state_freshman_value', 'Arizona');
		$rep_fields->condition('fshs.field_specialist_hs_names_value', '%' . $rep_query->escapeLike($hs_value) . '%', 'LIKE');
		$rep_fields->condition('fsc.field_specialist_category_target_id','1');
		$rep_fields->condition('nfv.type','specialist');
		$rep_fields->condition('nfv.status','1');
		$results = $rep_fields->execute()->fetchAll();
	
	
		foreach($results as $rep_id){
			    $node_id[] = $rep_id->nid;
		}
		$content = $this->specialist_node_load($node_id, $state_selected);
		return new JsonResponse(
				[
				 'rephsInfo' => $content, 
				]

		);
	
		
	}
	
	
	
	
	/**Returns information of the Transfer CA reps info in json format which is then called by ajax function in contact_form.js file
    * 
    * @param $state_selected (state value) and $int_selected(Institution value) passed by the jQuery code in contact_form.js file
	* @return JsonResponse
    **/
	public function transfer_ca_az_rep_info_page($state_selected = null, $inst_selected = null)
    {
		if($state_selected == "AZ"){
			$vid = "community_college_university";
			$table_name = "node__field_community_college_universi";
			$table_shortcut = "fcommu";
			$field_name = "field_community_college_universi_target_id";
		}
		if($state_selected == "CA"){
			$vid = "california_institutions";
			$table_name = "node__field_california_college_or_univ";
			$table_shortcut = "fcaliu";
			$field_name = "field_california_college_or_univ_target_id";
		}
		$rep_query = \Drupal::database();
	    
		$inst_value = $inst_selected;
		
		
		$termFields = $rep_query->select('taxonomy_term_field_data', 'ttfd')->fields('ttfd', array('tid'));
		$termFields->condition('ttfd.name', '%' . $rep_query->escapeLike($inst_value) . '%', 'LIKE');
		$termFields->condition('ttfd.vid',$vid);
		$termResults = $termFields->execute()->fetchAll();
		foreach($termResults as $term_ids){
			    $tid = $term_ids->tid;
		}
		
		
		$rep_fields = $rep_query->select('node_field_data', 'nfv')->fields('nfv', array('nid'));
		$rep_fields->innerJoin('node__field_specialist_state', 'fstate', 'fstate.entity_id = nfv.nid');
		$rep_fields->innerJoin('node__field_specialist_category', 'fsc','fsc.entity_id = nfv.nid' );
		$rep_fields->innerJoin($table_name,$table_shortcut, $table_shortcut.'.entity_id = nfv.nid');
		$rep_fields->condition('fstate.field_specialist_state_value', $state_selected);
		$rep_fields->condition($table_shortcut.'.'.$field_name, $tid);
		$rep_fields->condition('fsc.field_specialist_category_target_id','4');
		$rep_fields->condition('nfv.type','specialist');
		$rep_fields->condition('nfv.status','1');
		$results = $rep_fields->execute()->fetchAll();	
		
		foreach($results as $rep_id){
			    $node_id[] = $rep_id->nid;
		}
		
		if(!empty($node_id)){
			$content = $this->specialist_node_load($node_id, $state_selected);
		}
		else{
			$content = '';
		}
		return new JsonResponse(
				[
				 'reptrInfo' => $content, 
				]

		);
	}
	
	
	/**Returns information of the Transfer CA reps info in json format which is then called by ajax function in contact_form.js file
    * 
    * @param $state_selected (state value) and $int_selected(Institution value) passed by the jQuery code in contact_form.js file
	* @return JsonResponse
    **/
	public function transfer_other_rep_info_page($state_selected = null)
    {
		$rep_query = \Drupal::database();
		$rep_fields = $rep_query->select('node_field_data', 'nfv')->fields('nfv', array('nid'));
		$rep_fields->innerJoin('node__field_specialist_state', 'fstate', 'fstate.entity_id = nfv.nid');
		$rep_fields->innerJoin('node__field_specialist_category', 'fsc','fsc.entity_id = nfv.nid' );
		$rep_fields->condition('fsc.field_specialist_category_target_id','4');
		$rep_fields->condition('fstate.field_specialist_state_value', $state_selected);
		$rep_fields->condition('nfv.type','specialist');
		$rep_fields->condition('nfv.status','1');
	
		$results = $rep_fields->execute()->fetchAll();
		
		foreach($results as $rep_id){
			    $node_id[] = $rep_id->nid;
		}
		
		$content = $this->specialist_node_load($node_id, $state_selected);
		return new JsonResponse(
				[
				 'reptrInfo' => $content, 
				]

		);
	}
	
	
	/**
   * Returns response for the autocompletion of Aizona high schools.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
	public function highSchoolAutocomplete(Request $request) {
		$results = [];
		$string = $request->query->get('q');
		$hs_query = \Drupal::database();
		$hashsData = $hs_query->select('node__field_specialist_hs_names', 'fs')
				->fields('fs',['field_specialist_hs_names_value'])
				->condition('field_specialist_hs_names_value', '%'.$hs_query->escapeLike($string).'%', 'LIKE')
				->execute()
				->fetchAll();

		foreach($hashsData as $hsdata){
				$hs_name[$hsdata->field_specialist_hs_names_value] = $hsdata->field_specialist_hs_names_value;

		}
		
		ksort($hs_name);
		
		foreach($hs_name as $key => $value){
			$results[] = [
				'key' => $key,
				'value' => $value,
			];
		}
		
		return new JsonResponse($results);
	}
	




/**
   * Returns response for the autocompletion for Arizona institute for Transfer students.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
  */
	public function ArizonaInstituteAutocomplete(Request $request) {
		$results = [];
		$string = $request->query->get('q');
		$hs_query = \Drupal::database();
		$hasaiData = $hs_query->select('taxonomy_term_field_data', 'ttfd')
				->fields('ttfd',['name'])
				->condition('vid','community_college_university')
				->condition('name', '%'.$hs_query->escapeLike($string).'%', 'LIKE')
				->execute()
				->fetchAll();

		foreach($hasaiData as $aidata){
				$ai_name[$aidata->name] = $aidata->name;

		}
		
		ksort($ai_name);
		
		foreach($ai_name as $key => $value){
			$results[] = [
				'key' => $key,
				'value' => $value,
			];
		}
		
		return new JsonResponse($results);
	}
	


/**
   * Returns response for the autocompletion of Transfer california institution fields.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
	public function CaliforniaInstituteAutocomplete(Request $request) {
		$results = [];
		$string = $request->query->get('q');
		$hs_query = \Drupal::database();
		$hasciData = $hs_query->select('taxonomy_term_field_data', 'ttfd')
				->fields('ttfd',['name'])
				->condition('vid','california_institutions')
				->condition('name', '%'.$hs_query->escapeLike($string).'%', 'LIKE')
				->execute()
				->fetchAll();

		foreach($hasciData as $cidata){
				$ci_name[$cidata->name] = $cidata->name;
		}
		
		foreach($ci_name as $key => $value){
			$results[] = [
				'key' =>$key ,
				'value' => $value,
			];
		}
		ksort($ci_name);
		
		return new JsonResponse($results);
	}
	


	
	/** function to load specialist node and format specialist data  */
	public	function specialist_node_load(array $node_id, $state){
		
		if(!empty($node_id)){ //state is not passed for international form options, and there are no nodes for international reps, so skip below code and add custom content
			foreach($node_id as $nid){
				$node = Node::load($nid);
				$fname = isset($node->field_first_name[0]->value)?$node->field_first_name[0]->value:'';
				$last_name = isset($node->field_last_name[0]->value)?$node->field_last_name[0]->value:'';
				$image_id = isset($node->field_specialist_image[0]->target_id)?$node->field_specialist_image[0]->target_id:'';
				$phone = isset($node->field_phone[0]->value)?$node->field_phone[0]->value:'';
				$email = isset($node->field_specialist_email[0]->value)?$node->field_specialist_email[0]->value:'';
				$appointment_link = isset($node->field_phone_or_video_appointment[0]->value)?$node->field_phone_or_video_appointment[0]->value:'';
				if(!empty($image_id)){
					$image_uri = File::load($image_id)->getFileUri();
					$rep_image = ImageStyle::load('medium')->buildUri($image_uri);
					$image_path = explode('://', $image_uri);
					$new_explode = explode('/',$image_path[1]);

					$image_parts = sizeof($new_explode);
					$image_name = $new_explode[$image_parts - 1];
					$rfi_image_path = "http://".$_SERVER['HTTP_HOST']."/sites/default/files/".$image_path[1];
					//$rfi_image_path = "https://admission.asu.edu/sites/default/files/".$image_name;
				}
				else{
					$rfi_image_path = "https://admission.asu.edu/sites/default/files/2022-02/asulogo_0_4_0_0.png";
				}
				$bio = isset($node->body[0]->value)?$node->body[0]->value:'';
				$full_name = $fname.' '.$last_name;
				$content['body'] = '<div class="show-rep-info-div col-md-12"><div class="profile_image"><img class="image-cropper" src="'.$rfi_image_path.'"></div><br />
				<div class="right-col"><p><strong>'.$fname.' '.$last_name.' is your contact</strong></p><p><span class="smalltext"> Please contact '.$fname.' for information about admissions, requirements or any other questions you may have.</p> <i class="fa fa-envelope" aria-hidden="true"> </i><a href="mailto:' . $email . '">' . $email . '</a>&nbsp;&nbsp;  <i class="fa fa-phone" aria-hidden="true"> </i> &nbsp;'.$phone.' </span><br><p>Schedule a time to connect with me: <a href="'.$appointment_link.'">'.$appointment_link.'</a></p></div></div>';
				$content['email'] = $email;
				$content['rep_nid'] = $nid;
				$content['bio'] = $bio;
				$content['fullName'] = $full_name;
				$content['phone'] = $phone;
				$content['image'] = '<img class="image-cropper" src="'.$rfi_image_path.'" alt='.$full_name.'>';
				$content['left_col_content'] = "<h3>We've received your email! Thanks!</h3><p>Your dedicated admission representative is $full_name.</p><p>They will contact you shortly to answer your question(s).</p>";
				if(!empty($bio)){
					$content['bio_content'] = '<div class="button-link btn-dark btn" data-target="#repBio" data-toggle="modal" type="button">View bio</div><div aria-labelledby="repBio" class="modal fade" id="repBio" role="dialog" tabindex="-1" style="display: none;" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><span class="rep_title">'.$full_name.'</span><div aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></div></div>
				 <div class="modal-body"><p>'.$bio.'</p></div></div></div></div>';
				}
				else{
					$content['bio_content'] = '';
				}
			}
		}
		else{
			$content['body'] = "<p>We have received your email! We will contact you shortly.</p>";
		}
			
			return $content;
	}
	
	
	/**Returns information of the Inernational reps info in json format which is then called by ajax function in undergradcontact_form.js file
    * 
    * @param $state_selected (state value) and $int_selected(Institution value) passed by the jQuery code in undergradcontact_form.js file
	* @return JsonResponse
    **/
	public function intl_rep_info_page($region = null,$country = null)
    {
		//ksm($region);
		/*if($region =="Africa"){
			$table_name = "node__field_africa_countries";
			$field = "field_africa_countries_target_id";
		}
		if($region =="Asia"){
			$table_name = "node__field_asian_countries";
			$field = "field_asian_countries_target_id";
		}
		if($region == "Antarctica"){
			$table_name = "node__field_antarctica";
			$field = 'field_antarctica_target_id';
		}
		if($region == "Middle East"){
			$table_name = "node__field_middle_east_countries";
			$field = 'field_middle_east_countries_target_id';
		}
		if($region == "Europe"){
			$table_name = "node__field_europe_countries";
			$field = 'field_europe_countries_target_id';
		}
		if($region == "Central America"){
			$table_name = "node__field_central_america_country";
			$field = 'field_central_america_country_target_id';
		}
		if($region == "South America"){
			$table_name = "node__field_south_america_country";
			$field = 'field_south_america_country_target_id';
		}
		if($region == "Australia"){
			$table_name = "node__field_australia";
			$field = 'field_australia_target_id';
		}
		if($region == "North America"){
			$table_name = "node__field_north_america_countries";
			$field = 'field_north_america_countries_target_id';
		}
		if($region == "Oceania"){
			$table_name = "node__field_oceania_countries";
			$field = 'field_oceania_countries_target_id';
		}
		
		$rep_query = \Drupal::database();
		//ksm($country);
		$country_tid_data = $rep_query->select('taxonomy_term_field_data', 'tax')
			->fields('tax', array('tid'))
			->condition('tax.description__value',$country)
			->execute()
			->fetchAll();
		
		foreach($country_tid_data as $ctid){
			$ctax_id = $ctid->tid;
		}
		
		
		$region_fields = $rep_query->select($table_name, 'nfr')
			->fields('nfr', array('entity_id'))
			->condition("nfr.$field", $ctax_id)
			->execute()
			->fetchAll();
		
		foreach($region_fields as $regiondata){
				$region_nodes[$regiondata->entity_id] = $regiondata->entity_id;
				$node_id[] = $regiondata->entity_id;
		}
		
		$content = $this->international_specialist_node_load($node_id);
		return new JsonResponse(
				[
				 'repIntlInfo' => $content, 
				]

		);*/
		
		$rep_query = \Drupal::database();
		//ksm($country);
		$country_tid_data = $rep_query->select('taxonomy_term_field_data', 'tax')
			->fields('tax', ['tid'])
			->condition('tax.name',$country)
			->execute()
			->fetchAll();
		
		foreach($country_tid_data as $ctid){
			$ctax_id = $ctid->tid;
		}
		
		
		/*$region_fields = $rep_query->select($table_name, 'nfr')
			->fields('nfr', array('entity_id'))
			->condition("nfr.$field", $ctax_id)
			->execute()
			->fetchAll();*/
		$region_fields = $rep_query->select('node__field_select_country', 'nfr')
			->fields('nfr', array('entity_id'))
			->condition("nfr.field_select_country_target_id", $ctax_id)
			->execute()
			->fetchAll();
		
		foreach($region_fields as $regiondata){
				$region_nodes[$regiondata->entity_id] = $regiondata->entity_id;
				$node_id[] = $regiondata->entity_id;
		}
		if(!empty($node_id)){
			$content = $this->international_specialist_node_load($node_id);
		}
		else{
			$content = '';
		}
		//ksm($content);
		return new JsonResponse(
				[
				 'repIntlInfo' => $content, 
				]

		);
	}
	
	
	/** function to load international specialist node and format specialist data  **/
	public	function international_specialist_node_load(array $node_id){
		//ksm($node_id);
		if(!empty($node_id)){ //state is not passed for international form options, and there are no nodes for international reps, so skip below code and add custom content
			foreach($node_id as $key => $nid){
				
				if($nid == "3709"){
					$content['body'] = '<div class="show-rep-info-div col-md-12"><strong>Please contact <a href="mailto:ASUinternational@asu.edu">ASUinternational@asu.edu</a></strong></div>';
					$content['email'] = "ASUinternational@asu.edu";
					$content['rep_nid'] = $nid;
					$content['left_col_content'] = "<h3>We've received your email! Thanks!</h3><p>Your dedicated admission representative will contact you shortly to answer your question(s).</p>";
					$content['phone'] = '';
				}
				else{
					//$size = sizeof($nid);
					$node = Node::load($nid);
					$fname = isset($node->field_intl_first_name[0]->value)?$node->field_intl_first_name[0]->value:'';
					$last_name = isset($node->field_intl_last_name[0]->value)?$node->field_intl_last_name[0]->value:'';
					$image_id = isset($node->field_intl_adv_image[0]->target_id)?$node->field_intl_adv_image[0]->target_id:'';
					$phone = isset($node->field_intl_phone[0]->value)?$node->field_intl_phone[0]->value:'';
					$email = isset($node->field_intl_email[0]->value)?$node->field_intl_email[0]->value:'';
					$appointment_link = isset($node->field_intl_appointment_link[0]->value)?$node->field_intl_appointment_link[0]->value:'';
					if(!empty($image_id)){
						$image_uri = File::load($image_id)->getFileUri();
						$rep_image = ImageStyle::load('medium')->buildUri($image_uri);
						$image_path = explode('://', $image_uri);
						$new_explode = explode('/',$image_path[1]);
						$image_parts = sizeof($new_explode);
						$image_name = $new_explode[$image_parts - 1];
						$rfi_image_path = "http://".$_SERVER['HTTP_HOST']."/sites/default/files/".$image_path[1];
						//$rfi_image_path = "https://admission.asu.edu/sites/default/files/".$image_name;
					}
					else{
						$rfi_image_path = "https://admission.asu.edu/sites/default/files/2022-02/asulogo_0_4_0_0.png";
					}
					$bio = isset($node->body[0]->value)?$node->body[0]->value:'';
					$full_name = $fname.' '.$last_name;
					$content['body'] = '<div class="show-rep-info-div col-md-12"><div class="profile_image"><img class="image-cropper" src="'.$rfi_image_path.'"></div><br />
					<div class="right-col"><p><strong>'.$fname.' '.$last_name.' is your contact</strong></p><p><span class="smalltext"> Please contact '.$fname.' for information about admissions, requirements or any other questions you may have.</p> <i class="fa fa-envelope" aria-hidden="true"> </i>&nbsp;<a href="mailto:' . $email . '">' . $email . '</a>&nbsp;&nbsp;  <i class="fa fa-phone" aria-hidden="true"> </i> &nbsp;'.$phone.' </span><br><p>Schedule a time to connect with me: <a href="'.$appointment_link.'">'.$appointment_link.'</a></p></div></div>';
					$content['email'] = $email;
					$content['rep_nid'] = $nid;
					$content['bio'] = $bio;
					$content['fullName'] = $full_name;
					$content['phone'] = $phone;
					$content['app_link'] = $appointment_link;
					$content['image'] = '<img class="image-cropper" src="'.$rfi_image_path.'" alt='.$full_name.'>';
					$content['left_col_content'] = "<h3>We've received your email! Thanks!</h3><p>Your dedicated admission representative is $full_name.</p><p>They will contact you shortly to answer your question(s).</p>";
					if(!empty($bio)){
						$content['bio_content'] = '<div class="button-link btn-dark btn" data-target="#repBio" data-toggle="modal" type="button">View bio</div><div aria-labelledby="repBio" class="modal fade" id="repBio" role="dialog" tabindex="-1" style="display: none;" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><span class="rep_title">'.$full_name.'</span><div aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></div></div>
					 <div class="modal-body"><p>'.$bio.'</p></div></div></div></div>';
					}
					else{
						$content['bio_content'] = '';
					}
				}
			}
		}
		else{
			$content['body'] = "<p>We have received your email! We will contact you shortly.</p>";
		}
		
			return $content;
	}
	
	
	/** function to load China international specialist node and format specialist data  **/
	public function international_china_specialist_node_load($node_id = null){
		if(!empty($node_id)){ //state is not passed for international form options, and there are no nodes for international reps, so skip below code and add custom content
			
				$node = Node::load($node_id);
				$fname = isset($node->field_intl_first_name[0]->value)?$node->field_intl_first_name[0]->value:'';
				$last_name = isset($node->field_intl_last_name[0]->value)?$node->field_intl_last_name[0]->value:'';
				$image_id = isset($node->field_intl_adv_image[0]->target_id)?$node->field_intl_adv_image[0]->target_id:'';
				$phone = isset($node->field_intl_phone[0]->value)?$node->field_intl_phone[0]->value:'';
				$email = isset($node->field_intl_email[0]->value)?$node->field_intl_email[0]->value:'';
				$appointment_link = isset($node->field_intl_appointment_link[0]->value)?$node->field_intl_appointment_link[0]->value:'';
				if(!empty($image_id)){
					$image_uri = File::load($image_id)->getFileUri();
					$rep_image = ImageStyle::load('medium')->buildUri($image_uri);
					$image_path = explode('://', $image_uri);
					$new_explode = explode('/',$image_path[1]);
					$image_parts = sizeof($new_explode);
					$image_name = $new_explode[$image_parts - 1];
					$rfi_image_path = "http://".$_SERVER['HTTP_HOST']."/sites/default/files/".$image_path[1];
					//$rfi_image_path = "https://admission.asu.edu/sites/default/files/".$image_name;
				}
				else{
					$rfi_image_path = "https://admission.asu.edu/sites/default/files/2022-02/asulogo_0_4_0_0.png";
				}
				$bio = !empty($node->body[0]->value)?$node->body[0]->value:'';
				$full_name = $fname.' '.$last_name;
				$content['body'] = '<div class="show-rep-info-div col-md-12"><div class="profile_image"><img class="image-cropper" src="'.$rfi_image_path.'"></div><br />
				<div class="right-col"><p><strong>'.$fname.' '.$last_name.' is your contact</strong></p><p><span class="smalltext"> Please contact '.$fname.' for information about admissions, requirements or any other questions you may have.</p> <i class="fa fa-envelope" aria-hidden="true"> </i>&nbsp;<a href="mailto:' . $email . '">' . $email . '</a>&nbsp;&nbsp;  <i class="fa fa-phone" aria-hidden="true"> </i> &nbsp;'.$phone.' </span><br><p>Schedule a time to connect with me: <a href="'.$appointment_link.'">'.$appointment_link.'</a></p></div></div>';
				$content['email'] = $email;
				$content['rep_nid'] = $node_id;
				$content['bio'] = $bio;
				$content['fullName'] = $full_name;
				$content['phone'] = $phone;
				$content['image'] = '<img class="image-cropper" src="'.$rfi_image_path.'" alt='.$full_name.'>';
				$content['left_col_content'] = "<h3>We've received your email! Thanks!</h3><p>Your dedicated admission representative is $full_name.</p><p>They will contact you shortly to answer your question(s).</p>";
				if(!empty($bio)){
					$content['bio_content'] = '<div class="button-link btn-dark btn" data-target="#repBio" data-toggle="modal" type="button">View bio</div><div aria-labelledby="repBio" class="modal fade" id="repBio" role="dialog" tabindex="-1" style="display: none;" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><span class="rep_title">'.$full_name.'</span><div aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></div></div>
				 <div class="modal-body"><p>'.$bio.'</p></div></div></div></div>';
				}
				else{
					$content['bio_content'] = '';
				}
			
		}
		else{
			$content['body'] = "<p>We have received your email! We will contact you shortly.</p>";
		}
			return new JsonResponse(
				[
					'repIntlChInfo' => $content,
				]
			);
	}
	
}