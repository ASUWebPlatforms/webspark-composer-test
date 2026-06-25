jQuery(document).ready(function ($) {
	//hide program of interest field until area of interest or college vaue is changed
	var initial_degree_value = $('#edit-degree-level').val();
	//console.log(initial_degree_value);
	var initial_question_value = $('input[name="who_do_you_have_questions_for_"]').val();
	var militray = $('input[name="which_one_of_these_apply_to_you_"]').val();
	
	
	$('.grad-contact-form-button').hide();
	//$('select[name="select_program_of_interest"]').hide();
	$('.js-form-item-select-program-of-interest').hide();
	//$('select[name="start_term"]').hide();
	$('.js-form-item-start-term').hide();
	$('.js-form-item-select-area-of-interest').hide();
	$('#edit-or-text').hide();
	$('.interest-text').hide();
	$('.js-form-item-select-a-school-or-college').hide();
	
	
	
	$('.custom-required > label').each(function () {
    	if (!$(this).data('processed')) {
      		$(this).data('processed', true);
      		$(this).prepend('<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-k3wspwNYf4XN" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-k3wspwNYf4XN">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>');
    	}
  	});
	
	
	//add "Accepts 255 Characters" text next character count in text in the forms
	//$('.text-count-message').once().prepend('<span>Accepts 255 Characters.&nbsp;</span>');
	$('.text-count-message').each(function () {
    	if (!$(this).data('processed')) {
      		$(this).data('processed', true);
      		$(this).prepend('<span>Accepts 255 Characters.&nbsp;</span>');
    	}
  	}); 
	
	if(initial_degree_value != 0){
		$('#edit-degree-level').val(initial_degree_value);
		$('#edit-degree-level').trigger('change');
		$('.interest-text').show();
		$('.js-form-item-select-area-of-interest').show();
		$('.js-form-item-select-a-school-or-college').show();
		$('#edit-or-text').show();
		$('select[name="select_a_school_or_college"]').val(0);
		$('select[name="select_area_of_interest"]').val(0);
	}
	
	
	
	if(($('select[name="select_area_of_interest"]').val() != 0) || ($('select[name="select_a_school_or_college"]').val() != 0)){
		//$('select[name="select_program_of_interest"]').show();
		$('.js-form-item-select-program-of-interest').show();
		$('#edit-or-text').show();
	}
	
	if($('select[name="select_program_of_interest"]').val() != 0){
		//$('select[name="start_term"]').show();
		$('.js-form-item-start-term').show();
	}
	
	if(initial_degree_value == 0){
		//$('select[name="select_area_of_interest"]').hide();
		$('.js-form-item-select-area-of-interest').hide();
		//$('select[name="select_a_school_or_college"]').hide();
		$('.js-form-item-select-a-school-or-college').hide();
		$('select[name="select_area_of_interest"]').val(0);
		$('select[name="select_a_school_or_college"]').val(0);
		$('#edit-or-text').hide();
	}
	
	$('#edit-degree-level').on('change',function(){
		//console.log($(this).val());
		if($(this).val() != 0){
			$('.interest-text').show();
			//$('select[name="select_area_of_interest"]').show();
			$('.js-form-item-select-area-of-interest').show();
			//$('select[name="select_a_school_or_college"]').show();
			$('.js-form-item-select-a-school-or-college').show();
			$('#edit-or-text').show();
		}
	})
	
	if($('.invalid-feedback').is(':visible')){
		$('select[name="select_area_of_interest"]').val(0);
		//$('select[name="select_program_of_interest"]').hide();
		$('.js-form-item-select-program-of-interest').hide();
		if(($('select[name="select_area_of_interest"]').val() == 0) && ($('select[name="select_a_school_or_college"]').val() == 0)){
			$('.req-field').addClass('required error is-invalid');
			
		}
		else{
			$('.req-field').remove('required error is-invalid');
		
		}
	}
	/*//RFI form code
	
	$('#edit-graduate-form').hide();
	
	if($('input[name="which_one_of_these_apply_to_you_"]').is(':checked')){
		$('#edit-graduate-form').show();
		$('.grad-contact-form-button').show();
	}
	
	if($('input[name="who_do_you_have_questions_for_"]').is(':checked')){
		$('#edit-graduate-form').show();
		$('.grad-contact-form-button').show();
	}

	$('input[name="who_do_you_have_questions_for_"]').on('input',function(){
		$('#edit-graduate-form').show();
		$('.grad-contact-form-button').show();
	})
	
	$('input[name="which_one_of_these_apply_to_you_"]').on('input',function(){
		$('#edit-graduate-form').hide();
		var studentType = $('input[name="are_you_applying_to_asu_as_a_"]').val();
		console.log('st', studentType);
		if(studentType == 'international'){
			$('#edit-graduate-form').hide();
			$('.grad-contact-form-button').hide();
		}
		if($(this).val() == "Military"){
			$('#edit-graduate-form').show();
			$('.grad-contact-form-button').show();
		}
		if($(this).val() == "Online"){
			$('#edit-graduate-form').hide();
			$('.grad-contact-form-button').hide();
		}
	})
	
	$('#edit-select-country').on('change',function(){
		$('#edit-graduate-form').show();
		$('.grad-contact-form-button').show();
	})*/
	
	// Reset are of interest value if college field is changed
	$('select[name="select_a_school_or_college"]').on('change',function(){
		console.log('area',$('select[name="select_area_of_interest"]').val());
		$('select[name="select_area_of_interest"]').val(0).change();
		// localStorage.setItem('collegeInput', $(this).val());
	})
	
	//Reset college of interest if Area of interest is selected
	$('select[name="select_area_of_interest"]').change(function(){
		console.log('coll',$('select[name="select_a_school_or_college"]').val());
		$('select[name="select_a_school_or_college"]').val(0);
	})
	
	
	$('.cf-field').on('change',function(){
		var next_element_position = $(this).closest('.cf-field').offset().top;
		$(document).scrollTop(next_element_position);
		//$('html,body').animate({ scrollTop:next_element_position}, 'slow');
	
	})
	
	
});