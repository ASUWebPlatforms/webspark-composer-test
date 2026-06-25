
(function($) {
  let flag = false;
  Drupal.behaviors.asu_survey = {
    attach: function () {
//jQuery(document).ready(function ($) {
     var site_host = document.location.hostname;
	 var full_site = 'https://'+site_host;
	 $('#rfi-container').hide();
     $('#hide-show-progress').hide();	
	//hide RFI form on the confirmation page if it was submitted in the form	
	 $('body').on('click', '.last-rfi-button', function (){
			 //console.log('click');
			 var url = document.location.href;
			 var qs = url.substring(url.indexOf('?') + 1).split('&');
			 
			 for(var i = 0, result = {}; i < qs.length; i++){
				qs[i] = qs[i].split('=');
				result[qs[i][0]] = decodeURIComponent(qs[i][1]);
			 }
			 //console.log(result);
		     var rfi = result['rfi'];
		     console.log(rfi);
		     if(rfi == "No"){
			   $('#rfi-container').show();
			   $('#hide-show-progress').show();	
			   $('.rfi-rhs-button').show(); 	  
		     }
			 if(rfi == "Yes"){
				 $('#rfi-container').hide();
				 $('#hide-show-progress').show();	
				 $('.rfi-rhs-button').hide(); 
			 } 
	 });
		
	 $('body').on('click', '.rfi-back-button', function (){
		  $('#rfi-container').hide();
		  $('#hide-show-progress').hide();	
	 });
		
	 if (flag === false) {
		  flag = true;
		 // $('.surver-confirm-box').find('.survey_result_next_page_button').on('click',function(){
		 // code to insert next node in the same confirmation page using ajax
		 $('body').on('click', '.survey_result_next_page_button', function (){
			    var all_classes = $(this).attr("class");
				var classArr = all_classes.split(/\s+/); 
				var nodeid = classArr[1];
			    var result_url = full_site+"/survey/confirmation/json?nid="+classArr[1];
				$.ajax({
					 url: result_url,
					 dataType: "text",
					 contentType: 'application/json',	
					 cache: false,
					 async: false,		  
					 success: function(data) {
                            var jsonData = $.parseJSON(data);
							var jsonIntlString = jsonData.resultsData;
							if(data.length > 20){
								$('#survey-confirm-node').html(jsonIntlString['body']);
								//$('body').scrollTo('#survey-confirm-node');
								$('html, body').animate({
										scrollTop: parseInt($("#topAnchorDiv").offset().top)
								}, 10);
							}
							else{
								$('#survey-confirm-node').html('');
							}

					 },
					error: function () {
						$('.surver-confirm-box').html('');

						}
					});
			});
		 
		  //code to fill online programs and terms fields
		   $('input[name="area_of_interest_online"]').on('change', function(){
			   var interest = $(this).val();
			   var stype = $('input[name="select_your_student_status"] option:selected'). text();
			   result_url = "https://admission.asu.edu/admin/asuaec_rfi/json/categories/online/ugrad";
			   $.ajax({
					 url: result_url,
					 dataType: "text",
					 contentType: 'application/json',	
					 cache: false,
					 async: false,		  
					 success: function(data) {
                            var jsonData = $.parseJSON(data);
							cosnole.log(jsonData);

					 },
					error: function () {
						
						}
					});
		   })
		    
				     
	  }
    }
  }
 
})(jQuery, Drupal, drupalSettings);