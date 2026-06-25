jQuery(document).ready(function ($) {
/*(function($, Drupal) {
Drupal.behaviors.optModule = {
 attach: function (context) {*/

     $('.form-item-opt-type').hide();
     var id;
     var button_type = $("input[type=radio][name='opt_type']:checked").val()
	 var remianing_Days_left = 0;
     if (button_type == undefined) {
         $('#complete_opt_form').hide();
     }

     var hash = window.location.hash.substr(1);
     if (hash == "preopt") {
         id = "pre_opt_yes";
         show_calculator(id);
     }

     if (hash == "preoptno") {
         id = "pre_opt_no";
         show_calculator(id);
     }

     $('.opt_button').click(function () {
         id = $(this).attr('id');
         show_calculator(id);


     })

     function show_calculator(id) {
         $('#complete_opt_form').show();
         //console.log('idnew',id);
         if (id == "pre_opt_yes") {
             $("#" + id + "").addClass('active_button');
             $('#pre_opt_no').removeClass('active_button');

             $("#" + id + "").closest("form")[0].reset();
             // $('input[name="opt_type"]:checked').val('yes');
             $('input[name="opt_type"][value="yes"]').attr('checked', true);
             // $('input[name="opt_type"][value="no"]').attr('checked',false);
             $('#edit-opt-fieldset').show();
             $('#edit-opt-part-fieldset1').show();
         }
         if (id == "pre_opt_no") {
             $("#" + id + "").addClass('active_button');
             $('#pre_opt_yes').removeClass('active_button');
             $("#" + id + "").closest("form")[0].reset();

             $('input[name="opt_type"][value="no"]').attr('checked', true);
             $('#edit-opt-fieldset').hide();
             $('#edit-opt-part-fieldset1').hide();

         }
         $('#total_period').html('');
         $('#grace_period').html('');
         $('#remaining_days').html('');
     }


     var fullScope = {};

     var partScope = {};

     var day_count;

     var end_period;


     //var datesarray = Drupal.settings.optModule;
     var datesarray = drupalSettings.optModule;
     // console.log(datesarray);
     var date1;
     var date2;
     var total;
     var id = '';
     var type;
     var days;

     $('.pre_dates_div').find('input').each(function () {
         $(this).change(function () {

             $('#edit-begin-full-employment').val('');

             $('#total_period').html('');
         })

     })

     $('input[name="datetime[date]"]').click(function () {
         var degreetype = $('#edit-opt-degree-type').val();

         if (degreetype == "Bachelor's") {

             // $('#ui-datepicker-div').css({'display':'none !important'});
             $('#ui-datepicker-div').addClass('date_pick_div');

         }
         if (degreetype != "Bachelor's") {

             $('#ui-datepicker-div').removeClass('date_pick_div');
         }
     })


     //Full time calculations
     $('#edit-full-start-d1').change(function () {
         var i = 1;
         type = "full";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val() / 2) + parseInt($('input[name="part_text_d1"]').val() / 2) + parseInt($('input[name="part_text_d3"]').val() / 2);
         if (total > 0) {
             show_message(total);
         }

     });

     $('#edit-full-end-d1').change(function () {
         var j = 1;
         type = "full";
         calculate_date(j, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val() / 2) + parseInt($('input[name="part_text_d1"]').val() / 2) + parseInt($('input[name="part_text_d3"]').val() / 2);
         show_message(total);
     });

     $('#edit-full-start-d2').change(function () {
         var i = 2;
         type = "full";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         if (total > 0) {
             show_message(total);
         }
     });

     $('#edit-full-end-d2').change(function () {
         var j = 2;
         type = "full";
         calculate_date(j, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         show_message(total);
     });

     $('#edit-full-start-d3').change(function () {
         var i = 3;
         type = "full";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         if (total > 0) {
             show_message(total);
         }
     });

     $('#edit-full-end-d3').change(function () {
         var j = 3;
         type = "full";
         calculate_date(j, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         show_message(total);
     });

     //Part time calculations
     $('#edit-part-start-d1').change(function () {
         var i = 1;

         type = "part";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         if (total > 0) {
             show_message(total);
         }
     });

     $('#edit-part-end-d1').change(function () {
         var j = 1;
         type = "part";
         //part_calculate_date(j);
         calculate_date(j, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         show_message(total);
     });

     $('#edit-part-start-d2').change(function () {
         var i = 2;
         //part_calculate_date(i);
         type = "part";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         if (total > 0) {
             show_message(total);
         }
     });

     $('#edit-part-end-d2').change(function () {
         var j = 2;
         //part_calculate_date(j);
         type = "part";
         calculate_date(j, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());

         show_message(total);
     });

     $('#edit-part-start-d3').change(function () {
         var i = 3;
         //part_calculate_date(i);
         type = "part";
         calculate_date(i, type);
         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         if (total > 0) {
             show_message(total);
         }
     });

     $('#edit-part-end-d3').change(function () {
         var j = 3;

         type = "part";
         calculate_date(j, type);

         total = parseInt($('input[name="full_text_d2"]').val()) + parseInt($('input[name="full_text_d1"]').val()) + parseInt($('input[name="full_text_d3"]').val()) + parseInt($('input[name="part_text_d2"]').val()) + parseInt($('input[name="part_text_d1"]').val()) + parseInt($('input[name="part_text_d3"]').val());
         show_message(total);
     });

     //Full opt calculator
     function calculate_date(i, type) {

         if (type == "full") {
             fullScope["start_date" + i] = $("#edit-full-start-d" + i).val();

             fullScope["s" + i] = new Date(fullScope["start_date" + i]);
             fullScope["end_date" + i] = $("#edit-full-end-d" + i).val();
             fullScope["e" + i] = new Date(fullScope["end_date" + i]);
             fullScope["input" + i] = $('input[name="full_text_d' + i + '"]').attr('id');
             //console.log(fullScope["input"+i]);
             var difference = fullScope["s" + i] < fullScope["e" + i];

             if (((fullScope["start_date" + i]).length > 0) && ((fullScope["end_date" + i]).length > 0)) {

                 if (difference == true) {
                     var diff_value = parseInt((fullScope["e" + i] - fullScope["s" + i]) / (1000 * 60 * 60 * 24)) + 1;
                     days = parseInt(diff_value);

                     $("#" + fullScope['input' + i]).val(days)
                 } else {
                     //$("#"+fullScope['input'+i]).val(days)
                     // $("#"+fullScope['input'+i]).val('Please enter correct date range.');
                 }

             }

             //console.log($("#"+fullScope['input'+i]).val());
         }

         if (type == "part") {
             partScope["start_date" + i] = $("#edit-part-start-d" + i).val();
             partScope["s" + i] = new Date(partScope["start_date" + i]);
             partScope["end_date" + i] = $("#edit-part-end-d" + i).val();
             partScope["e" + i] = new Date(partScope["end_date" + i]);
             partScope["input" + i] = $('input[name="part_text_d' + i + '"]').attr('id');
             var part_difference = partScope["s" + i] < partScope["e" + i];

             if (((partScope["start_date" + i]).length > 0) && ((partScope["end_date" + i]).length > 0)) {

                 if (part_difference == true) {
                     /*var part_diff_value = parseInt((partScope["e"+i]  - partScope["s"+i] ) / (1000 * 60 * 60 * 24));
                     var part_days = parseInt(part_diff_value)  ;
                     part_day_count =  Math.round(parseInt(part_days)/2) ;*/
                     var part_diff_value = (partScope["e" + i] - partScope["s" + i]) / (1000 * 60 * 60 * 24);
                     var part_days = part_diff_value + 1;
					 console.log('part_days',part_days);
                     //console.log(part_days/2);
                     part_day_count = Math.ceil(part_days / 2);
					 //part_day_count = Math.ceil(part_days);
                     //console.log(part_day_count);
                     if (Math.floor(part_day_count)) {
                         part_day_count_value = part_day_count;
                     } else {
                         part_day_count_value = part_day_count + 1;
                     }
                     $("#" + partScope['input' + i]).val(part_day_count_value);
                 } else {
                     $("#" + partScope['input' + i]).val('Please enter correct date range.');
                 }
             } else {
                 $("#" + partScope['input' + i]).val('');
             }
             //console.log($("#"+partScope['input'+i]).val())
         }
     }


    // $('#edit-opt-year', context).once('optModule').on('change', function () {
    $('#edit-opt-year').change(function(){

         $('#grace_period').hide();
         var degree_type = $('#edit-opt-degree-type').val();
         var year = $('#edit-opt-year').val();
         var term = $('#edit-opt-term').val();
         var currentTime = new Date()
         var current_year = currentTime.getFullYear();
         var current_month = currentTime.getMonth() + 1;
         $('#edit-opt-calculated-year').val(year - current_year);

         $('#edit-begin-full-employment').val('');
         $('#total_period').html('');
         var back_year = current_year - 5;
         var future_year = parseInt(year) + 2;

         if (year.length > 0) {
             if (current_year == year) {

                 if (current_month > 7) {
                     $("#edit-opt-term option[value='Spring']").remove();
                 } else {
                     if ($("#edit-opt-term option[value='Spring']").length == 0) {
                         $("#edit-opt-term").append("<option value='Spring'>Spring</option>");
                     }

                 }

                 if (current_month > 9) {
                     $("#edit-opt-term option[value='Summer']").remove();
                 } else {
                     if ($("#edit-opt-term option[value='Summer']").length == 0) {
                         $("#edit-opt-term").append("<option value='Summer'>Summer</option>");
                     }
                 }
             } else if (current_year > year) {
                 $("#edit-opt-term option[value='Spring']").remove();
                 $("#edit-opt-term option[value='Summer']").remove();
             } else if (current_year < year) {
                 $("#edit-opt-term option[value='Fall']").remove();
                 if ($("#edit-opt-term option[value='Spring']").length == 0) {
                     $("#edit-opt-term").append("<option value='Spring'>Spring</option>");
                 }
                 if ($("#edit-opt-term option[value='Summer']").length == 0) {
                     $("#edit-opt-term").append("<option value='Summer'>Summer</option>");
                 }
                 if ($("#edit-opt-term option[value='Fall']").length == 0) {
                     $("#edit-opt-term").append("<option value='Fall'>Fall</option>");
                 }
             } else {
                 if ($("#edit-opt-term option[value='Spring']").length == 0) {
                     $("#edit-opt-term").append("<option value='Spring'>Spring</option>");
                 }
                 if ($("#edit-opt-term option[value='Summer']").length == 0) {
                     $("#edit-opt-term").append("<option value='Summer'>Summer</option>");
                 }
                 if ($("#edit-opt-term option[value='Fall']").length == 0) {
                     $("#edit-opt-term").append("<option value='Fall'>Fall</option>");
                 }

             }

             if (degree_type.length == 0) {
                 if ($('.degree_alert').length == 0) {
                     $('#end_period').before('<div class="alert alert-block alert-error degree_alert"><a class="close" data-dismiss="alert" href="#">x</a><h2 class="element-invisible">Error message</h2><strong>Which type of Degree will you earn?</strong> field is required.</div>');

                 }
             }


             if (degree_type == "Bachelor's") {
                 var session_data = $('#edit-opt-session-question').val();  //check for session field. If yes, pull session A date
                 //if (degree_type.length > 0) {
                 if (term == 'Fall') {
                     var sterm = 'fall';
                 }
                 if (term == 'Spring') {
                     var sterm = 'spring';
                 }
                 if (term == 'Summer') {
                     var sterm = 'summer';
                 }

                 if (session_data == "Yes") {
                     var fulldate = sterm + 'sessiondate' + year;
                 } else {
                     var fulldate = sterm + 'date' + year;
                 }

                 end_period = datesarray[fulldate];

                 if ((end_period == "12/31/1969") || (end_period === "undefined")) {
                     $('#edit-datetime').val('Error occured');
                 } else {
                     $('#edit-datetime').val(end_period);
                 }

             } else {
                 $('#edit-datetime').val('');
                 $('input[name="datetime[date]"]').prop('readonly', false);
             }
         }

     });

     $('#edit-opt-session-question').change(function () {
         var session_data = $(this).val();
         var term = $('#edit-opt-term').val();
         var year = $('#edit-opt-year').val();
         if (term == 'Fall') {
             var sterm = 'fall';
         }
         if (term == 'Spring') {
             var sterm = 'spring';
         }
         if (term == 'Summer') {
             var sterm = 'summer';
         }

         if (session_data == "Yes") {
             var fulldate = sterm + 'sessiondate' + year;
         } else {
             var fulldate = sterm + 'date' + year;
         }

         end_period = datesarray[fulldate];
		 if ((end_period == "12/31/1969") || (end_period === "undefined")) {
             $('#edit-datetime').val('Error occured');
         } else {
             $('#edit-datetime').val(end_period);
         }
         $('#grace_period').hide();

     })


     $('#edit-opt-degree-type').change(function () {

         var degree_type = $('#edit-opt-degree-type').val();
         var year = $('#edit-opt-year').val();
         var term = $('#edit-opt-term').val();
         var program_date = $('edit-otherdatetime-datepicker-popup-0').val();
         $('#edit-begin-full-employment').val('');
         $('#total_period').html('');
         //$('#edit-datetime').prop("readonly",false);
         if (degree_type == "Bachelor's") {
             $('input[name="datetime[date]"]').prop('readonly', true);
             $("#edit-datetime").keypress(function (e) {
                 e.preventDefault();
             });

             // $('#edit-datetime').datepicker("hide");
         }
         if (degree_type != "Bachelor's") {
             $('#grace_period').hide();
             $('#edit-datetime').prop('readonly', false);
         }

         if ((term.length > 0) && (year.length > 0)) {

             if (degree_type == "Bachelor's") {
                 var session_data = $('#edit-opt-session-question').val(); //check for session field. If yes, pull session A date
                 if (term == 'Fall') {
                     var sterm = 'fall';
                 }
                 if (term == 'Spring') {
                     var sterm = 'spring';
                 }
                 if (term == 'Summer') {
                     var sterm = 'summer';
                 }

                 if (session_data == "Yes") {
                     var fulldate = sterm + 'sessiondate' + year;
                 } else {
                     var fulldate = sterm + 'date' + year;
                 }

                 end_period = datesarray[fulldate];
				 if ((end_period == "12/31/1969") || (end_period === "undefined")) {
                     $('#edit-datetime').val('Error occured');
                 } else {
                     $('#edit-datetime').val(end_period);
                 }
             }

         } else {
             $('#edit-datetime').val('');
         }

         if ($('.degree_alert').length > 0) {
             if (term.length > 0) {
                 $('div').remove('.term_alert');
             }
             $('div').remove('.degree_alert');
         } else {
             $("#edit-opt-year option").prop("selected", false);
             $("#edit-opt-term option").prop("selected", false);
             $('#edit-datetime').val('');
         }


     });

     $('#edit-opt-term').change(function () {


         //$('#grace_period').hide();
         var degree_type = $('#edit-opt-degree-type').val();
         var year = $('#edit-opt-year').val();
         var term = $('#edit-opt-term').val();
         var currentTime = new Date()
         var current_year = currentTime.getFullYear();
         var current_month = currentTime.getMonth() + 1;

         if (degree_type.length == 0) {
             if ($('.degree_alert').length == 0) {
                 $('#end_period').before('<div class="alert alert-block alert-error degree_alert"><a class="close" data-dismiss="alert" href="#">x</a><h2 class="element-invisible">Error message</h2><strong>Which type of Degree will you earn?</strong> field is required.</div>');
             }
         } else {
             $('div').remove('.degree_alert');
         }
         if (term.length == 0) {
             if ($('.term_alert').length == 0) {
                 $('#end_period').before('<div class="alert alert-block alert-error term_alert"><a class="close" data-dismiss="alert" href="#">x</a><h2 class="element-invisible">Error message</h2><strong>Term</strong> field is required.</div>');
             }
         } else {
             $('div').remove('.term_alert');

         }

         $('#edit-begin-full-employment').val('');
         $('#total_period').html('');

         if ((degree_type.length > 0) && (year.length > 0)) {
             if (degree_type == "Bachelor's") {
                 var session_data = $('#edit-opt-session-question').val(); //check for session field. If yes, pull session A date
                 if (term == 'Fall') {
                     var sterm = 'fall';
                 }
                 if (term == 'Spring') {
                     var sterm = 'spring';
                 }
                 if (term == 'Summer') {
                     var sterm = 'summer';
                 }
                 var fulldate = sterm + 'date' + year;
                 if (session_data == "Yes") {
                     var fulldate = sterm + 'sessiondate' + year;
                 } else {
                     var fulldate = sterm + 'date' + year;
                 }

                 end_period = datesarray[fulldate];

                 if ((end_period == "12/31/1969") || (end_period === "undefined")) {
                     $('#edit-datetime').val('Error occured');
                 } else {
                     $('#edit-datetime').val(end_period);
                 }
             }

             /*if (current_year == year) {

                     if (current_month > 7) {
                         $("#edit-opt-term option[value='Spring']").remove();
                     }
                     else{
                         $("#edit-opt-term").append("<option value='Spring'>option6</option>");
                     }

                     if (current_month > 9) {
                         $("#edit-opt-term option[value='Summer']").remove();
                     }
                     else{
                         $("#edit-opt-term").append("<option value='Summer'>option6</option>");
                     }


             }*/

         } else {
             $('#edit-datetime').val('');
         }
         if (degree_type.length > 0) {
             // $("#edit-opt-year option").prop("selected", false);
             $('div').remove('.degree_alert');
             $('div').remove('.term_alert');
         }


     });

     $('#edit-opt-degree-type').change(function () {
         $('div').remove('.degree_alert');
     })
     $('#edit-opt-term').change(function () {
         $('div').remove('.term_alert');
     })


     function show_message(total) {
         //console.log(total);
         var year = $('#edit-opt-year').val();
         var tdays = isLeap(year);
         //console.log(tdays);
         var remaining = tdays - total;
		 remianing_Days_left = remaining;
         if (total >= tdays) {
             var message = "<hr /><p style='color:red;'><strong>You are not eligible for OPT.</strong></p><hr />";
             $('.bottom_terms_container').hide();
         } else {
             var message = "<hr /><p><strong>Total OPT days used: " + total + " days.<br />Days remaining: " + remaining + " days.</strong></p><hr />";
             $('.bottom_terms_container').show();
         }
         var div_message = $('#remaining_days').html(message);
     }

     function isLeap(year) {
         //if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)){
         if (((year % 4) == 0) && (((year % 100) != 0) || ((year % 400) == 0))) {

             leap_days = 365;
         } else {
             leap_days = 364;
         }
         return leap_days;
     }
	
	
	 $('#edit-total-days-submit').on('click', function () {
		var remaining_days_value = remianing_Days_left;
		console.log('remaining_days_value:', remaining_days_value);

		var subDateValue = $('#edit-begin-full-employment').val(); // This should be in YYYY-MM-DD format
		console.log('Selected Date:', subDateValue);

		if (subDateValue) {
			var newDate = addDaysToDate(subDateValue, remaining_days_value);
			console.log('New Date:', newDate);
		} else {
			console.error('Please select a valid start date.');
		}
	 });

	function addDaysToDate(startDate, daysToAdd) {
		console.log('Start Date:', startDate, 'Days to Add:', daysToAdd);

		var date = new Date(startDate); // Directly create a Date object

		// Check if date is valid
		if (isNaN(date.getTime())) {
			console.error('Invalid start date');
			return null;
		}

		// Add days
		date.setDate(date.getDate() + daysToAdd);

		// Format date as MM-DD-YYYY
		var month = (date.getMonth() + 1).toString().padStart(2, '0');
		var day = date.getDate().toString().padStart(2, '0');
		var year = date.getFullYear();
		var updatedValue = month + '-' + day + '-' + year
		console.log('updatedValue',updatedValue);
		$(document).ajaxComplete(function (event, xhr, settings) {
		 $('.valueFromJquery').text(updatedValue);
		});
		return updatedValue;

	} 
	
	 /*$('.rfi_button').click(function(){
		 console.log(total);
	 })*/

     /*
      }
     }
     }(jQuery, Drupal, drupalSettings));*/
 });
