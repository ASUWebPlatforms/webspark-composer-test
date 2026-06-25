jQuery(document).ready(function ($) {
/*(function ($, Drupal) {
  Drupal.behaviors.customBehavior = {
    attach: function (context, settings) {*/
	var state = '';
	var hs = '';
	var type = '';
	var rep_email = '';
	var inst = '';
	var tr_state= '';
	let timeout = null;
	
	/** code for form visibility **/
	$('#edit-ftf-form').hide();
	$('.contact-form-button').hide();
	
	/* Add svg red required button for conditionla fields, for some reason, it's been removed */
	$('.custom-required > label').each(function () {
    	if (!$(this).data('processed')) {
      		$(this).data('processed', true);
      		$(this).prepend('<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-k3wspwNYf4XN" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-k3wspwNYf4XN">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>');
    	}
  	});
	//$('.custom-required > label', context).once().prepend('<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-k3wspwNYf4XN" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-k3wspwNYf4XN">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>');
	
	/** Add +1 to phone number field **/
	var ini_phone = $('#edit-phone').val();
	if(ini_phone == ''){
		$('#edit-phone').val('+1');
	}
	
	
	/** International fields **/
	//hide international regions initially
	$('.js-form-item-international-select-your-region').addClass('hide');
	$('.form-item-middleeast-are-you-a-sponsored-student-or-non-sponsored-student-').hide();
	$('.js-form-item-select-custom-international-country').hide();
	//initial interntaional question value
	var ini_intl = $('input[name="do_you_attend_an_u_s_high_school_"]:checked').val();
	if(typeof ini_intl === "undefined"){
		$('.js-form-item-international-select-your-region').addClass('hide');
	}
	else{
		$('.js-form-item-international-select-your-region').removeClass('hide');
	}
	
	
	//initial page load international region selected
	var ini_internation_region = $('#edit-international-select-your-region option:selected').val();
	//console.log(ini_internation_region);
	if(ini_internation_region != ''){
		$('.js-form-item-international-select-your-region').removeClass('hide');
		$('#edit-international-select-your-region').trigger('change');
	}
	else{
		$('.js-form-item-international-select-your-region').addClass('hide');
	}
	
	$('#edit-china-taiwan-reps').hide();
	if($('.international_country_class option:selected').val() == ''){
		$('#edit-international-rep-info').hide();
		$('#edit-international-rep-info').html('');
	}
	if($('.international_country_class option:selected').val() == 'CN'){
		$('#edit-china-taiwan-reps').show();
		$('#edit-international-rep-info').show();
		
	}
	else{
		$('#edit-china-taiwan-reps').hide();
		$('#edit-china-taiwan-reps').val('');
	}
	
	
	//add "Accepts 255 Characters" text next character count in text in the forms
	//$('.text-count-message', context).once().prepend('<span>Accepts 255 Characters.&nbsp;</span>');
	$('.text-count-message').each(function () {
    	if (!$(this).data('processed')) {
      		$(this).data('processed', true);
      		$(this).prepend('<span>Accepts 255 Characters.&nbsp;</span>');
    	}
  	});
	
	/** initial page load variables **/
	//freshman options
	var ini_state = $('select[name="freshman_state"]').val();
	var init_hs = $('input[name="high_school_autocomplete"]').val();
	
	
	if((ini_state.length > 0)){
		if(ini_state != "Arizona"){
			freshman_rep_ajax(ini_state);
			$('#edit-ftf-form').show();
			$('.contact-form-button').show();
		}
		
		if(ini_state == "Arizona"){
			if(init_hs.length > 0){
				freshman_az_rep_ajax(init_hs);
				$('#edit-ftf-form').show();
				$('.contact-form-button').show();
			}
		}
		
	}
	
	//transfer options
	 var ini_tr_state = $('select[name="transfer_state"]').val();
	 var international_transfer = $('input[name="transfer_do_you_attend_a_u_s_institution_"]:checked').val()
	 if((international_transfer == "yes") || (international_transfer == "no")){
		$('#edit-ftf-form').show();
		$('.contact-form-button').show();
	 }
	
	 if(ini_tr_state.length > 0){
			if((ini_tr_state != "AZ") && (ini_tr_state != "CA")){
				   tr_other_states_rep(ini_tr_state);
					$('#edit-ftf-form').show();
					$('.contact-form-button').show();
			}
	 }
	
	 var tr_az_inst = $('input[name="select_arizona_college_or_university"]').val();
	 if(tr_az_inst != ''){ //run code only if insitution field is not empty
			tr_az_inst_rep(tr_az_inst);
		 	$('#edit-ftf-form').show();
			$('.contact-form-button').show();
	 }
	
	 var tr_ca_inst = $('input[name="enter_california_college_or_university"]').val();
	 if(tr_ca_inst != ""){
			tr_cali_inst_rep_ajax(tr_ca_inst);
		    $('#edit-ftf-form').show();
			$('.contact-form-button').show();
	 }
	
	
	
	//military field
	var ini_military_value = $('input[name="are_you_applying_to_asu_as_a_"]:checked').val();
	if(ini_military_value == "military"){
		$('#edit-ftf-form').addClass('militaryClassForm');
		$('.contact-form-button').addClass('militaryClassForm');
	}
	if(ini_military_value != "military"){
		$('#edit-ftf-form').removeClass('militaryClassForm');
		$('.contact-form-button').removeClass('militaryClassForm');
	}
	
	
	/** code to scrool to next element upon selection  **/
	
	$('.cf-field').on('change',function(){
		// var element_position = $(this).position().top;
		var next_element_position = $(this).closest('.cf-field').offset().top;
		
		$(document).scrollTop(next_element_position);
		
	})
	
	
	/*** Freshman form code section *****/
	
	/*$(document).on('change','input[name="are_you_applying_to_asu_as_a_"]', function(){
		$('#edit-ftf-form').hide();
	});*/
	$('input[name="are_you_applying_to_asu_as_a_"]').on('change',function(){
		var areYouValue = $(this).val();
		if(areYouValue == "military"){
			//$('input[name="form_trigger_element"]').val('showform').trigger('change');
			$('#edit-ftf-form').show();
			$('.contact-form-button').show();
			$('#edit-ftf-form').addClass('militaryClassForm');
			$('.contact-form-button').addClass('militaryClassForm');
			$('.js-form-item-international-select-your-region').addClass('hide');
			$('input[name="specialist_email"]').val('asujoewarhol@asu.edu');
			$('input[name="specialist_nid"]').val('1931');
			
		}
		else{
			$('#edit-ftf-form').hide();
			$('.contact-form-button').hide();
			$('#edit-ftf-form').removeClass('militaryClassForm');
			$('.contact-form-button').removeClass('militaryClassForm');
			$('.js-form-item-international-select-your-region').addClass('hide');
		}
		$('#edit-international-rep-info').html('');
	});
	
	$('input[name="form_trigger_element"]').on('change', function() {
		
		if($(this).val() == "showform"){
			$('#edit-ftf-form').show();
			$('.contact-form-button').show();
		}
		else{
			$('#edit-ftf-form').hide();
			$('.contact-form-button').hide();
		}
		
	})
	
	$('input[name="do_you_attend_an_u_s_high_school_"]').on('change', function(e){
		//console.log($(this).val());
		if($(this).val() == 'no'){
			$('#domestic-hs-rep-wrapper').html('');
			$('#internation-advisor-info').html('');
			$('#edit-ftf-form').hide();
			$('.contact-form-button').hide();
			$('select[name="freshman_state"]').val('');
			$('input[name="high_school_autocomplete"]').val('');
			$('.js-form-item-international-select-your-region').removeClass('hide');
			$('#edit-international-rep-info').show();
			
		}
		if($(this).val() == 'yes'){
			$('.js-form-item-international-select-your-region').addClass('hide');
			$('select[name="international_select_your_region"]').val('');
			$('select[name="select_custom_international_country"]').val(''); //code added for international change
			$('select[name="select_custom_international_country"]').hide();  //code added for international change
			$('#edit-international-rep-info').hide();
			$('.js-form-item-select-custom-international-country').hide();
			
		}
		if($(this).val() == 'homeschool'){
			$('.js-form-item-international-select-your-region').addClass('hide');
			$('select[name="international_select_your_region"]').val('');
			$('select[name="select_custom_international_country"]').val(''); //code added for international change
			$('select[name="select_custom_international_country"]').hide();  //code added for international change
			$('#edit-international-rep-info').hide();
			$('.js-form-item-select-custom-international-country').hide();
			$('#edit-ftf-form').show();
			$('.contact-form-button').show();
			$('input[name="specialist_email"]').val('ASURecruitment@asu.edu');
			$('input[name="specialist_nid"]').val('');
		}
		
	});
	
	/* change event for Freshman state field */
	 $('select[name="freshman_state"]').on('change',function(e){
		e.preventDefault();
		state = $(this).val();
		$('.contact-form-button').hide();
		$('#domestic-hs-rep-wrapper').html('');
		hs = $('input[name="high_school_autocomplete"]').val(); 
		if((state == "Arizona")){
			if(hs == ''){
				 $('#domestic-hs-rep-wrapper').html('');
				 $('#edit-ftf-form').hide();
			}
			
			
		}
		else{
			if(state != ''){ //run code only if state field is not empty
			    $('#domestic-hs-rep-wrapper').html('');
				freshman_rep_ajax(state);
				
			}
			else{
				$('input[name="form_trigger_element"]').val('').trigger('change');
			}
		 }
		
	});
	
	
	
	//add search button to autocomeplet field
	if($('.custom-searchicon-hs').length == 0){
      		$('#edit-high-school-autocomplete').after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-hs">Find my rep</span>');
	}
	//$('#edit-high-school-autocomplete', context).once().after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-hs">Find my rep</span>');
	/*$('#edit-high-school-autocomplete').change(function () {
    	//if (!$(this).data('processed')) {
      		//$(this).data('processed', true);
		if($('.custom-searchicon-hs').length == 0){
      		$(this).prepend('<span class="highschool_reps_button btn-maroon btn custom-searchicon-hs">Find my rep</span>');
		}
     //	}
  	});*/
	if($('.custom-searchicon-tr-cal').length == 0){
		$('#edit-enter-california-college-or-university').after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-cal">Find my rep</span>');
	}
	//$('#edit-enter-california-college-or-university', context).once().after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-cal">Find my rep</span>');
	/*$('#edit-enter-california-college-or-university').each(function () {
    	//if (!$(this).data('processed')) {
      	//	$(this).data('processed', true);
		if($('.custom-searchicon-tr-cal').lenth == 0){
      		$(this).prepend('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-cal">Find my rep</span>');
		}
    	//}
  	});*/
	if($('.custom-searchicon-tr-az').length == 0){
		$('#edit-select-arizona-college-or-university').after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-az">Find my rep</span>');
	}
	//$('#edit-select-arizona-college-or-university', context).once().after('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-az">Find my rep</span>');
	/*$('#edit-select-arizona-college-or-university').each(function () {
    	//if (!$(this).data('processed')) {
      		//$(this).data('processed', true);
		if($('.custom-searchicon-tr-az').lenth == 0){
      		$(this).prepend('<span class="highschool_reps_button btn-maroon btn custom-searchicon-tr-az">Find my rep</span>');
		}
    	//}
  	});*/
	
	
	$('input[name="high_school_autocomplete"]').on('keypress',function(e) {
		 if (e.keyCode == 13) {
				//$(this).next().focus();
				e.preventDefault();
			    //$('.highschool_reps_button').trigger('click');
				return false;
		 }
		
	});
	
	$('.custom-searchicon-hs').on('click',function(e){
		e.preventDefault();
	    hs = $('input[name="high_school_autocomplete"]').val();
		//console.log('hs value',hs);
		$('input[name="high_school_autocomplete"]').trigger('change');
		var state_selected = $('#edit-freshman-state').val();
		$('#domestic-hs-rep-wrapper').html('');
		$('#transfer-advisor-info').html('');
		/** ajax call to pull reps data**/
		if(hs!= ''){ //run code only if highschool field is not empty
			freshman_az_rep_ajax(hs);
			
		}
		else{
			$('input[name="form_trigger_element"]').val('').trigger('change');
		}
		
		/*When using tab key, we do not want to press enter after enerting high school value, 
		*as it is submitting the form after pressing enter key. So retun false when enter is pressed using below funtion.*/
		disableEnter('input[name="high_school_autocomplete"]');
		
	 })
	
	/*** End of Freshman form code section **/
	
	
	
	/*** Transfer form reps data code section *****/
	
	
	/* show form on change effect of transfer_do_you_attend_a_u_s_institution_ field */
	$('input[name="transfer_do_you_attend_a_u_s_institution_"]').on('input',function(){
		var trn_international = $(this).val();
		if(trn_international == "yes"){
			$('input[name="specialist_email"]').val('ASUGradyFoster@asu.edu');
			$('input[name="specialist_nid"]').val('3717');
		}
		if(trn_international == "no"){
			$('input[name="specialist_email"]').val('ASUNayaraDixon@asu.edu');
			$('input[name="specialist_nid"]').val('3718');
		}
		//if(trn_international == "yes")
		$('#edit-ftf-form').show();
		$('.contact-form-button').show();
	});
	
	
	/* change event for Transfer state field */
	 $('select[name="transfer_state"]').on('change',function(){
		tr_state = $(this).val();
		$('#transfer-advisor-info').html('');
		$('#edit-ftf-form').hide();
		$('.contact-form-button').hide(); 
		if((tr_state != "AZ") && (tr_state != "CA")){
			if(tr_state != ''){ //run code only if state field is not empty
				tr_other_states_rep(tr_state);
				
			}
			
        }
		 else{
			// $('input[name="form_trigger_element"]').val('').trigger('change');
		 }
	 });
	
	
	/*Arizona institution field "select_arizona_college_or_university" change code for Transfer form
	Code will pull rep data using ajax function by passing Ariona and institution values from the form selection*/
	//disable enter key
	$('input[name="select_arizona_college_or_university"]').on('keypress',function(e){
		 if (e.keyCode == 13) {
				//$(this).next().focus();
				e.preventDefault();
				return false;
		 }
	});
	$('.custom-searchicon-tr-az').click(function(){
		az_inst = $('input[name="select_arizona_college_or_university"]').val();
		$('#transfer-advisor-info').html('');
		/** ajax call to pull reps data**/
		if(az_inst != ''){ //run code only if insitution field is not empty
			tr_az_inst_rep(az_inst);
			
		}
		else{
			$('input[name="form_trigger_element"]').val('').trigger('change');
		}
		disableEnter('input[name="select_arizona_college_or_university"]');
	});
	
	
	/**Transfer institution field "select_arizona_college_or_university" change code for Transfer form
	Code will pull rep data using ajax function by passing CA and its institution values from the form selection*/
	//disable enter key
	$('input[name="enter_california_college_or_university"]').on('keypress',function(e){
		 if (e.keyCode == 13) {
				//$(this).next().focus();
				e.preventDefault();
				return false;
		 }
	});
	$('.custom-searchicon-tr-cal').click(function(){
		cal_inst = $('input[name="enter_california_college_or_university"]').val();
		var state_selected = $('#edit-freshman-state').val();
		
		/** ajac call to pull reps data**/
		if(cal_inst != ""){
			tr_cali_inst_rep_ajax(cal_inst);
			
	  	}
		else{
			$('input[name="form_trigger_element"]').val('').trigger('change');
		}
		disableEnter('input[name="form_trigger_element"]');
	});
	
	
	/*** End of Transfer form reps data code section ****/
	
	/** function to work with tab key for accessibility **/
	function disableEnter(element){
		$(element).bind("keypress", function(e) {
			//console.log(e.keyCode);
            if (e.keyCode == 13) {
				$(this).next().focus();
				e.preventDefault();
			}
        });
	}
	/**********/
	
	
	
	/****** International section reps code based on the dropdown selections *****/
	
	/**International country change field for International section of the form
	Code will pull rep data using ajax function by passing region and country from the form selection*/
	
	 $('select[name="select_custom_international_country"]').children().remove("optgroup");
	
	$('select[name="international_select_your_region"]').on('change',function(){
		var region = $(this).val();
		//console.log(region);
		if(region == "Australia"){
			$('select[name="select_custom_international_country"]').val('AU');
			$('select[name="select_custom_international_country"]').trigger('change');
		}
		if(region == "Antarctica"){
			$('select[name="select_custom_international_country"]').val('AQ');
			$('select[name="select_custom_international_country"]').trigger('change');
		}
		var ini_inter_country = $('#edit-select-country').val();
		//console.log('ini_coon', ini_inter_country);
		if(ini_inter_country != ''){
			if(ini_inter_country == 'CN'){
				$('select[name="select_custom_international_country"]').val('CN')
			}
		}
		if($('select[name="select_custom_international_country"] option:selected').val() == ''){
			$('#edit-international-rep-info').hide();
			$('#edit-international-rep-info').html('');
		}
		$('#edit-international-rep-info').hide();
		$('#edit-international-rep-info').html('');
		$('#edit-ftf-form').hide();
		$('#contact-form-button').hide();
		
	});
	
	$(document).on('change','select[name="select_custom_international_country"]', function(e){
	//$('select[name="select_custom_international_country"]').on('change', function(e){
		e.preventDefault();
	    var intl_country = $(this).val();
		var country_name = $('select[name="select_custom_international_country"] option:selected').text();
		var region = $('#edit-international-select-your-region').val();
		var combined_value = country_name+'-'+intl_country;
		$('#domestic-hs-rep-wrapper').html('');
		$('#transfer-advisor-info').html('');
		/** ajax call to pull reps data**/
		if(region != "Middle East"){
			if(intl_country != ''){ //run code only if highschool field is not empty
				//if(intl_country != "CN"){
					international_rep_ajax(region,combined_value);
				//}
				$('#edit-select-country').val(intl_country);
				$('#edit-international-rep-info').show();
			}
			else{
				$('input[name="form_trigger_element"]').val('').trigger('change');
				$('#edit-select-country').val('');
				$('#edit-international-rep-info').hide();
				$('#edit-international-rep-info').html('');

			}
		}
		
		
		
	})
	
	
	
	
	/* ajax call for freshman reps for non AZ  */
	
	function freshman_rep_ajax(state){
		$.ajax({
				  url: "/get-rep-data/"+state,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,
				  success: function(data) {
					  var json = $.parseJSON(data);
					  var jsonString = json.repInfo['body'];
					  $('#domestic-hs-rep-wrapper').html(jsonString);
					  $('input[name="form_trigger_element"]').val('showform').trigger('change');
					  $('input[name="specialist_email"]').val( json.repInfo['email']);
					  $('input[name="specialist_nid"]').val(json.repInfo['rep_nid']);
					},
					error: function () {
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}
				}); 
	}
	
	
	/* ajax call form AZ freshman reps **/
	function freshman_az_rep_ajax(hs){
			$.ajax({
				  url: "/get-rep-data/Arizona/"+hs,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,
				  success: function(data) {
					  var jsonHs = $.parseJSON(data);
					  var jsonHsData = jsonHs.rephsInfo['body']
					  $('#domestic-hs-rep-wrapper').html(jsonHsData);
					  $('input[name="form_trigger_element"]').val('showform').trigger('change');
					  $('input[name="specialist_email"]').val(jsonHs.rephsInfo['email']);
					  $('input[name="specialist_nid"]').val(jsonHs.rephsInfo['rep_nid']);
				   },
					error: function () {
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}          
			 }); 
	}
	
	/** ajax call for CA transfer reps data **/
	
	function tr_cali_inst_rep_ajax(cal_inst){
			$.ajax({
				  url: "/get-tr-ca-az-rep-data/CA/"+cal_inst,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,
				  success: function(data) {
					  var jsonCi = $.parseJSON(data);
					  var jsonCiData = jsonCi.reptrInfo['body']
					  $('#transfer-advisor-info').html(jsonCiData);
					  $('input[name="form_trigger_element"]').val('showform').trigger('change');
					  $('input[name="specialist_email"]').val(jsonCi.reptrInfo['email']);
					  $('input[name="specialist_nid"]').val(jsonCi.reptrInfo['rep_nid']);
				   },
					error: function () {
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}          
			 }); 
	}
	
	/** ajax call for transfer AZ institution rep data **/
	function tr_az_inst_rep(az_inst){
			$.ajax({
					  url: "/get-tr-ca-az-rep-data/AZ/"+az_inst,
					  dataType: "text",
					  contentType: 'application/json',	
					  cache: false,
					  async: false,
					  success: function(data) {
						  //console.log(data);
						  var jsonAi = $.parseJSON(data);
						  var jsonAiData = jsonAi.reptrInfo['body'];
						  $('#transfer-advisor-info').html(jsonAiData);
						  $('input[name="form_trigger_element"]').val('showform').trigger('change');
						  $('input[name="specialist_email"]').val(jsonAi.reptrInfo['email']);
						  $('input[name="specialist_nid"]').val(jsonAi.reptrInfo['rep_nid']);
					   },
						error: function () {
							$('input[name="form_trigger_element"]').val('').trigger('change');
						}          
			}); 
	}
	
	/** ajax call for transfr non AZ, non CA reps data **/
	function tr_other_states_rep(tr_state){
				$.ajax({
				  url: "/get-tr-rep-data/"+tr_state,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,		  
				  success: function(data) {
					  var jsonTr = $.parseJSON(data);
					  var jsonTrString = jsonTr.reptrInfo['body'];
					  $('#transfer-advisor-info').html(jsonTrString);
					  $('input[name="form_trigger_element"]').val('showform').trigger('change');
					  $('input[name="specialist_email"]').val(jsonTr.reptrInfo['email']);
					  $('input[name="specialist_nid"]').val(jsonTr.reptrInfo['rep_nid']);
					},
					error: function () {
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}
				}); 
	}
	
	
	/** ajax call for international rep data **/
	function international_rep_ajax(region, country){
		$.ajax({
				  url: "/get-intl-rep-data/"+region+"/"+country,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,		  
				  success: function(data) {
					  //console.log(data);
					  
					  var jsonIntl = $.parseJSON(data);
					  var jsonIntlString = jsonIntl.repIntlInfo['body'];
					  if(data.length > 20){
					    $('#edit-international-rep-info').html(jsonIntlString);
					  	$('input[name="form_trigger_element"]').val('showform').trigger('change');
					    $('input[name="specialist_email"]').val(jsonIntl.repIntlInfo['email']);
					    $('input[name="specialist_nid"]').val(jsonIntl.repIntlInfo['rep_nid']);
					  }
					  else{
						  $('#edit-international-rep-info').html('');
						  $('input[name="form_trigger_element"]').val('').trigger('change');
					  }
				  },
					error: function () {
						$('#edit-international-rep-info').html('');
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}
				}); 
		
	}
	
	/** ajax call for china reps **/
	function international_china_rep_ajax(nodeid){
		$.ajax({
				  url: "/get-intl-china-rep-data/"+nodeid,
				  dataType: "text",
				  contentType: 'application/json',	
				  cache: false,
				  async: false,		  
				  success: function(data) {
					  var jsonIntlCh = $.parseJSON(data);
					  var jsonIntlChString = jsonIntlCh.repIntlChInfo['body'];
					  $('#edit-international-rep-info').html(jsonIntlChString);
					  $('input[name="form_trigger_element"]').val('showform').trigger('change');
					  $('input[name="specialist_email"]').val(jsonIntlCh.repIntlChInfo['email']);
					  $('input[name="specialist_nid"]').val(jsonIntlCh.repIntlChInfo['rep_nid']);
					},
					error: function () {
						$('#edit-international-rep-info').html('');
						$('input[name="form_trigger_element"]').val('').trigger('change');
					}
				}); 
	}
	
	
   
})
	/*	}
  };
})(jQuery, Drupal);*/