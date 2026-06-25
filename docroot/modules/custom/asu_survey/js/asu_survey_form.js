//<script type="text/javascript">
 // $(document).ready(function(){
(function($) {
 // let flag = false;
  Drupal.behaviors.asu_survey_form = {
    attach: function () {
		
  /*$('.sgroup').each(function(){
			 $(this).find('.js-form-type-radio').css({'border': '1px solid #D0D0D0','padding':'10px'});
		   });
		   $(this).find('label').before().css({"display":"none"});
		   $(this).find('label').css({'display':'block !important'}); */


		  /*$('input[name="full_name"]').focusout(function(){
			  $('.webform-button--next').click();
		  }); 
			$('input[name="once_you_earn_your_degree_what_kind_of_job_do_you_want_to_get_"]').focusout(function(){
			  $('.webform-button--next').click();
		  });*/ 
  		 
  //uncomment below code
	  $('.webform-button--next').hide();
	  $('.webform-button--previous').hide(); 
	  $('.show-next').siblings('.form-actions').find('.webform-button--next').show();

	  $('input[type="radio"]').on('change',function(){ 
		 $('.webform-button--next').click();

	  });
	  $("input[type='radio']:checked").each(function() { 
		   console.log('checked');
		   $(this).siblings('label').css("background", "gold");
	  });
	  $('input[type="checkbox"]').on('change',function(){
		$(this).css({'background':'gold'});
		if($('.checkbox_next').length == 0){
		  $('.sgroup').find('.js-webform-checkboxes').after('<input type="button" class="checkbox_next btn-gold btn" value="Continue" />');
		} 
	   });
		 
  	   $(document).on('click','.checkbox_next', function(){
		   $('.webform-button--next').click();
	   }); 

	   //code for progress bar

		var iniclassList = $(".sgroup").attr("class"); 
		var iniclassArr = iniclassList.split(/\s+/); 
  
		if(iniclassArr[1]){
		  width_value = iniclassArr[1];
		}
		else{
		  width_value = 100;
		}  

    	$('.inner-bar').css({'width':+width_value+'%','background':'#FFC627'});  
 

	  //code to update programs list dropdown based on student type and area of interest fields change
	   $('select[name="select_your_student_status"]').change(function(){
		console.log('changed val'); 
		var type = $(this).val(); 
		//console.log(type.length);
		if(type.length > 0){
			$(this).removeClass('error is-invalid');
			$('.invalid-feedback').remove();
		}
	  })
		 
  $('select[name="area_of_interest_online"]').change( function(){
	 var interest = $(this).val();
	 var stype = $('select[name="select_your_student_status"] option:selected').val();
	 console.log(stype);
	 console.log(interest);
	 console.log(stype);
	 console.log(stype.length);
	 if(stype.length == 0){
         $('select[name="area_of_interest_online"]').val('');
		 $('#edit-select-your-student-status').addClass('error is-invalid');
		 $('#edit-select-your-student-status').after('<small class="invalid-feedback"><svg title="Alert" class="svg-inline--fa fa-exclamation-triangle fa-w-18" aria-labelledby="svg-inline--fa-title-40PszRnCroxP" data-prefix="fa" data-icon="exclamation-triangle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><title id="svg-inline--fa-title-40PszRnCroxP">Alert</title><path fill="currentColor" d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"></path></svg> Select your student status field is required.</small>');
			   }
		else{
			$('#edit-select-your-student-status').remove('error is-invalid');
			$('#edit-select-your-student-status').after('');
			   }
			var result_url = "/survey/online/degrees/"+stype+"/"+interest;
			/*$.ajax({
					 url: result_url,
					 dataType: "text",
					 contentType: 'application/json',	
					 cache: false,
					 async: false,		  
					 success: function(data) {
                            var jsonData = $.parseJSON(data);
							console.log(jsonData);
						    var newOptions = jsonData['resultsData'];
						 	console.log(newOptions);
						    var $el = $('select[name="program_of_interest"]');
							$el.empty(); // remove old options
							$.each(newOptions, function(key,value) {
							  $el.append($("<option></option>")
								 .attr("value", key).text(value));
							});

					 },
					error: function () {
						
						}
			});*/
	  });
	//});
	}
  }
})(jQuery, Drupal, drupalSettings);