(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.visitjs = {
    attach: function (context, settings) {

      // Redirect to the /schedule page for people who came direct to the /scheduling-your-tour page
      if(sessionStorage.getItem("persontype") === null && sessionStorage.getItem("interest") === null && sessionStorage.getItem("month") === null) {
        window.location.href = "/schedule";
      }


      /**
       * Reset - Turn off all toggle switches.
       */
      function resetToggleSwitch() {
        $('#switch-selfguided > .switch > input').prop( "checked", false );
        $('#switch-inperson > .switch > input').prop( "checked", false );
        $('#switch-inperson-academic > .switch > input').prop( "checked", false );
        $('#switch-barrett > .switch > input').prop( "checked", false );
        $('#switch-facility > .switch > input').prop( "checked", false );
        $('#switch-generic > .switch > input').prop( "checked", false );
      }

      // Reset at the beginning
      resetToggleSwitch();
		
	  //hide toggles initially
	  $('#toggles-block').hide();	

      //---- For Cancel form - Add Attendee id and Event id to URL and pass it through to the final form. ----//
      // Get URL param - ?aid={attendee_id}&eventid={event_id}
      let urlParams = new URLSearchParams(window.location.search);
      let cancel_attendee_id = urlParams.get('c-aid');
      let cancel_eventid = urlParams.get('c-eid');
      let cancel_sid = urlParams.get('c-sid');
      
      let cancel_urlParam = "&c-aid=" + cancel_attendee_id + "&c-eid=" + cancel_eventid + "&c-sid=" + cancel_sid;

      // Global variables
      evolutionFormPath = '/registration-form';
      evolutionOtherFormPath = '/registration-form-0';

      toggleSwitchColorSelfguided = '#FF7F32';
      toggleSwitchClassSelfguided = 'selfguided';
      toggleSwitchColorInperson = '#78BE20';
      toggleSwitchClassInperson = 'inperson';
      toggleSwitchColorInpersonWithAcademic = '#00A3E0';
      toggleSwitchClassInpersonWithAcademic = 'inperson-academic';
      toggleSwitchColorBarrett = '#8C1D40';
      toggleSwitchClassBarrett = 'barrett';
      toggleSwitchColorFacility = '#E74973'; // ASU Pink
      toggleSwitchClassFacility = 'facility';
      toggleSwitchColorGeneric = '#FFC627'; // ASU Gold
      toggleSwitchClassGeneric = 'generic';

      theInterest = '';
      thePersonType = '';
      theMonth = '';

      // Get domain
      let thedomain = document.location.origin; // https://visitd9cs2-ddev2.ddev.site


      // // In order to prevent from being executed many times, wrap it with the following: -- Added on 9/8/2023. Removed on 9/9 because persontype lost value.
      // $('main', context).once('visitjs').each(function () {


      // Toggles has to come before calendar in mobile
      $(window).on('resize', function(){
        var win = $(this); //this = window
        if (win.width() < 758) {
          // Move toggles to above calendar
          $('#toggles-block').insertBefore($('#campuses-block'));
        } else {
          $('#toggles-block').insertBefore($('#dayagenda-block'));
        }
      });


      // Clear bottom part on top dropdown list's change event
      $('#persontype').on('change', function() {
        // Clear bottom part
        $('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
        $('#campuses').html('');
        $('.day-agenda-result').html('');
        $('.views-field-description__value').hide();
		$('#toggles-block').hide();	  
        resetToggleSwitch();
      });
      $('#interest-ugrad').on('change', function() {
        // Clear bottom part
        $('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
        $('#campuses').html('');
        $('.day-agenda-result').html('');
        $('.views-field-description__value').hide();
		$('#toggles-block').hide();	  
        resetToggleSwitch();
      });
      $('#interest-grad').on('change', function() {
        // Clear bottom part
        $('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
        $('#campuses').html('');
        $('.day-agenda-result').html('');
        $('.views-field-description__value').hide();
		$('#toggles-block').hide();	  
        resetToggleSwitch();
      });
      $('#month').on('change', function() {
        // Clear bottom part
        $('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
        $('#campuses').html('');
        $('.day-agenda-result').html('');
        $('.views-field-description__value').hide();
		$('#toggles-block').hide();	  
        resetToggleSwitch();
        // }
      });


        //------------------------------------------------------------------------------------------------
        /*** Archana's code to update interest bucket content by trggering interest bucket viw filter change based on interest selection **/

        //show interest description on initial page load if session variables exist
        if(( sessionStorage.getItem("interest") !== null) && (sessionStorage.getItem("persontype") !== null) &&  (sessionStorage.getItem("month") != null)) {
          interestCallFunction(sessionStorage.getItem("persontype"), sessionStorage.getItem("interest"),sessionStorage.getItem("month"));
          if((sessionStorage.getItem("persontype") == "High school freshman") || (sessionStorage.getItem("persontype") == "High school sophomore") || (sessionStorage.getItem("persontype") == "High school junior") || (sessionStorage.getItem("persontype") == "High school senior")){
            $('.views-field-description__value').show();
          }
          else{
            $('.views-field-description__value').hide();
          }
          $('#search').trigger('click');
          // Changed (1/13/2026)
          // $(document).ready(function(){
          //   $("#edit-tid").change();
          // })
        }
        else{
          $('.views-field-description__value').hide();
        }

        $('#search').on('click',function(){
          var stype = $("select[name='persontype']").val();
          var interest_val = $('#interest-ugrad').val();
		  var mon_sel = $('#month').val();	
          interestCallFunction(stype, interest_val,mon_sel);
        })

        function interestCallFunction(stype, interest_val, mon_sel){
			//console.log('interest_val',interest_val);
          if((stype == "High school freshman") || (stype == "High school sophomore") || (stype == "High school junior") || (stype == "High school senior")){
			  if(interest_val != 0){
          // Changed (1/13/2026)
          // $("select[name='tid']").val(interest_val).change();
          // BEGIN FIX: prevent infinite reload + ensure correct description via URL tid
          var tidForm = document.getElementById("views-exposed-form-interest-bucket-description-block-1");
          if (tidForm) {
            var url = new URL(window.location.href);
            var currentTid = url.searchParams.get('tid') || '';
            var nextTid = String(interest_val || '');

            // Only navigate if the URL tid is different
            if (nextTid && currentTid !== nextTid) {
              url.searchParams.set('tid', nextTid);
              window.location.assign(url.toString());
              return;
            }
          }
          // END FIX

			  	  $('.views-field-description__value').show();
				  $('#message-based-on-interest').html('');
			  }
			  else{
				  $('.views-field-description__value').hide();
				  $('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
			  }
          }
          else if(stype == 0){
		  	$('#message-based-on-interest').html('<p><strong>Please select from the options above.</strong></p>');
		  }
		  else{
            $('.views-field-description__value').hide();
          }
        }
        /******* end of Archana's code *******/


        // Populate Month dropdown
        populateMonthDropdown();


        // // Detect mobile
        // // https://stackoverflow.com/questions/11381673/detecting-a-mobile-browser/13819253
        // window.mobilecheck = function() {
        //   var check = false;
        //   (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        //   return check;
        // };


        // Clear error classes on change
        $("select[name='interest']").on('change', function(){
          $("select[name='interest']").removeClass("error is-invalid");
        });
        $("select[name='interest-ugrad']").on('change', function(){
          $("select[name='interest-ugrad']").removeClass("error is-invalid");
        });
        $("select[name='interest-grad']").on('change', function(){
          $("select[name='interest-grad']").removeClass("error is-invalid");
        });
        $("select[name='month']").on('change', function(){
          $("select[name='month']").removeClass("error is-invalid");
        });

        // console.log("thePersonType:", thePersonType);
        // When there is already data (person type, interest and month) stored in sessionStorage
        if(sessionStorage.getItem("persontype") !== null && sessionStorage.getItem("interest") !== null && sessionStorage.getItem("month") !== null) {

          // Select the values that are in sessionStorage and show the dropdowns
          thePersonType = sessionStorage.getItem("persontype");
          theInterest = sessionStorage.getItem("interest");
          theMonth = sessionStorage.getItem("month");

          // When came to Scheduling Your Tour page from the front page

          $("select[name='persontype']").val(thePersonType);
          $('#wrapper-dropdowns .sec1').show(); // Person type

          if(thePersonType == 'Graduate student') {
            $("select[name='interest-grad']").val(theInterest);
            $('#wrapper-dropdowns .sec2').hide(); // Interest - empty
            $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
            $('#wrapper-dropdowns .sec4').show(); // Interest - Grad

          } else if(thePersonType == 'Other') {
            //document.getElementById("interest").disabled = true; // Gray-out Interest dropdown for Other
            // Just hide Interest dropdown for "Other".
            $('#wrapper-dropdowns .sec2').hide(); // Interest - empty
            $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
            $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad

          } else {
            //document.getElementById("interest").disabled = false; // Activate Interest dropdown for Other
            $("select[name='interest-ugrad']").val(theInterest);
            $('#wrapper-dropdowns .sec2').hide(); // Interest - empty
            $('#wrapper-dropdowns .sec3').show(); // Interest - Ugrad
            $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad
          }

          $("select[name='month']").val(theMonth);
          $('#wrapper-dropdowns .sec5').show(); // Month


          // Add binding for select[name='persontype'].
//          $("select[name='persontype']", context).once('visitjs').on('change', function(){
          $(once('visitjs', "select[name='persontype']", context)).each(function () { // D10 change
              $(this).on('change', function(){
                persontypeChanged();
              });
          });

          let searchBtnClicked = true;
          //$("#search", context).once('visitjs').on('click', function(){
          $(once('visitjs', "#search", context)).each(function () { // D10 change
            $(this).on('click', function(){
              searchClicked(searchBtnClicked);
            });
          });

          // searchClicked(false);
          //$(window, context).once('visitjs').on('load' , function(){
          // Can not use `window` or `document` directly. 
          if (once('off-canvas', 'html').length) { // D10 change
            $(window).on('load', function() { 
              searchClicked(false);
            });
          }

        } else { // When there isn't already data (person type, interest and month) stored in sessionStorage

          // Clear dropdown selection
          $("select[name='persontype']").val(0);
          $("select[name='interest-ugrad']").val(0);
          $("select[name='interest-grad']").val(0);

          // Month
          if(sessionStorage.getItem("month") !== null) {
            theMonth = sessionStorage.getItem("month");
            $("select[name='month']").val(theMonth);
          } else {
            // Select current month
            //$("select[name='month']").val(createStringYearMonth(new Date()));
          }

          // Show dropdowns
          $('#wrapper-dropdowns .sec1').show(); // Person type
          $('#wrapper-dropdowns .sec2').show(); // Interest - empty
          $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
          $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad
          $('#wrapper-dropdowns .sec5').show(); // Month


          // Add binding for select[name='persontype'].
          // When user select Grad, show Grad interest list.
          //$("select[name='persontype']", context).once('visitjs').on('change', function(){
          $(once('visitjs', "select[name='persontype']", context)).each(function () { // D10 change              
            $(this).on("change", function(){
              persontypeChanged();
            });
          });  
          
          // If Interest dropdown is clicked before Person type is set, alert.
          //$("select[name='interest']", context).once('visitjs').on('click', function(){
          $(once('visitjs', "select[name='interest']", context)).each(function () { // D10 change  
            $(this).on('click', function(){
              if($("select#persontype").val() == '0') {
                alert("Please select from 'I am a...' dropdown list first.");
                $("#persontype").addClass("error is-invalid");
              }
            });
          });

          let searchBtnClicked = true;
          //$("#search", context).once('visitjs').on('click', function(){
          $(once('visitjs', "#search", context)).each(function () { // D10 change
            $(this).on('click', function(){
              searchClicked(searchBtnClicked);
            });
          });          

        }　// END OF else of if(sessionStorage.getItem("persontype") !== null && sessionStorage.getItem("interest") !== null && sessionStorage.getItem("month") !== null)



        /**
         * persontypeChanged -- Exact same function exists in asuaec_schedulepage.js at line 73.
         */
        function persontypeChanged() {
          // console.log("persontypeChanged fired");
          // console.log("select#persontype val:", $("select#persontype").val());

          $("#persontype").removeClass("error is-invalid");

          // When user select Group Visit, user will be redirected to /groupvisit page.
          if($("select#persontype").val() == "/groupvisit"){
            window.location.href = "/groupvisit";
          }

          // Clear selection
          $("select[name='interest-ugrad']").val(0);
          $("select[name='interest-grad']").val(0);

          if($("select#persontype").val() == '0'){
            // console.log("inside if for 0");
            $('#wrapper-dropdowns .sec2').show(); // Interest - empty
            $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
            $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad

          } else if($("select#persontype").val() == "Graduate student"){
            // console.log("inside if for Grad");
            $('#wrapper-dropdowns .sec2').hide();
            $('#wrapper-dropdowns .sec3').hide();
            $('#wrapper-dropdowns .sec4').show();

          }else if($("select#persontype").val() == "Other") {
            // console.log("inside if for Other");
            // Just hide Interest dropdown for "Other".
            $('#wrapper-dropdowns .sec2').hide(); // Interest - empty
            $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
            $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad

          } else {
            // console.log("inside if for else");
            // Just hide Interest dropdown for "Other".
            $('#wrapper-dropdowns .sec2').hide();
            $('#wrapper-dropdowns .sec3').show();
            $('#wrapper-dropdowns .sec4').hide();

          } // END OF if($("select#persontype").val() == "Graduate student")

        } // END OF function persontypeChanged()



        /**
         * searchClicked -- Almost same function exists in asuaec_schedulepage.js at line 91. Only the ending part is different.
         */
        function searchClicked(searchBtnClicked) {

          // $('.interest-custom').html('<span class="pleasewait"><i><strong>Retrieving information. Please wait...</strong></i></span>');
          // $("body").css("cursor", "progress");

          
          // Clear errors
          $("#persontype").removeClass("error is-invalid");
          $("#interest-grad").removeClass("error is-invalid");
          $("#interest-ugrad").removeClass("error is-invalid");
          $("#month").removeClass("error is-invalid");

          // Clear what is there first.
          $("#campuses").html('');
          // Clear agenda area
          $(".day-agenda-result").html('');
          $("#message-based-on-interest").html('');


          // Validation
          if($('#persontype').val() == '0') {
            alert ("Please tell us a little about yourself.");
            $("#persontype").addClass("error is-invalid");
            return false;

          } else if($('#persontype').val() == 'Graduate student' && $('#interest-grad').val() == '0') {
            alert ("Please tell us what you want to study.");
            $("#interest-grad").addClass("error is-invalid");
			$('.views-field-description__value').hide();
            return false;

          } else if ($('#persontype').val() != 'Graduate student' && $('#persontype').val() != 'Other' && $('#interest-ugrad').val() == '0') {
            alert ("Please tell us what you want to study.");
            $("#interest-ugrad").addClass("error is-invalid");
			$('.views-field-description__value').hide();  
            return false;

          } else if($('#month').val() == ''){
            alert ("Please select a month.");
            $("#month").addClass("error is-invalid");
            return false;

          } else {
          }

          //--------------------------------------------------------------------------------------------------------------------
          // Collect 3 inputs (Person type, Interest and Month)

          // Refresh global variables
          thePersonType = '';
          theInterest = '';
          theMonth = '';

          // Get inputs from the dropdown
          if(searchBtnClicked == true) {

            thePersonType = $('select#persontype').val().length ? $('select#persontype').val() : '';
            if(thePersonType == 'Graduate student') {
              theInterest = $('select#interest-grad').val().length ? $('select#interest-grad').val() : '';
            } else {
              theInterest = $('select#interest-ugrad').val().length ? $('select#interest-ugrad').val() : '';
            }
            theMonth = $('select#month').val().length ? $('select#month').val() : '';

          } else { // In other words, person came to Scheduling Your Tour page  from the /schedule page.

            // Get inputs from sessionStorage
            thePersonType = sessionStorage.getItem("persontype") != '' ? sessionStorage.getItem("persontype") : $('select#persontype').val();
            if(thePersonType == 'Graduate student') {
              theInterest = sessionStorage.getItem("interest") != '' ? sessionStorage.getItem("interest") : $('select#interest-grad').val();
            } else if(thePersonType == 'Other'){
              theInterest = '';
            } else {
              theInterest = sessionStorage.getItem("interest") != '' ? sessionStorage.getItem("interest") : $('select#interest-ugrad').val();
            }
            theMonth = sessionStorage.getItem("month") != '' ? sessionStorage.getItem("month") : $('select#month').val();
          }

          // console.log("thePersonType: " + thePersonType);
          // console.log("theInterest: " + theInterest);
          // console.log("theMonth: " + theMonth);


          // Place the values in sessionStorage
          sessionStorage.setItem("persontype", thePersonType);
          sessionStorage.setItem("interest", theInterest);
          sessionStorage.setItem("month", theMonth);
          // Update createdtime in JS Session Storage also.
          let createdTime = Math.floor((new Date()).getTime() / 1000);
          sessionStorage.setItem("createdtime", createdTime);


          //--------------------------------------------------------------------------------------------------------------------
          // Ajax call to get campuses based on interest selected.

          // Display available visits except for the ones already in sessionStorage.
          let grad_ugrad = '';
          let jsonPath = '';
          if(thePersonType == 'Graduate student') {
            grad_ugrad = 'grad';
          } else {
            grad_ugrad = 'ugrad';
          }
          jsonPath = '/admin/asuaec_json/json/campus';
          if(theInterest == '') {
            theInterest = '0';
          }

          $.ajax({ // Ajax#1 -- Display available campuses
            type: "POST",
            url: jsonPath + "/" + grad_ugrad + "/" + theInterest,
            data: "{}",
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            async: true,
            success: OnSuccess,
            error: OnError
          });
          function OnSuccess(data) {

            $("#campuses").html(''); // Clear what is there first.

            $.each(data, function (key, value){
//              $("#campuses").append("<div class='campus-wrapper mb-2 clearfix'><div class='campus-info clearfix'><input type='radio' class='campus campus-input' name='campus' value='" + key + "'><strong>" + value + "</strong></div><div id='minicalendar-" + key.replace(' ', '-').toLowerCase() + "'></div>"); //updated by Archana to add tooltip for campus // Changed on 1/29/2024.
//              $("#campuses").append("<div class='campus-wrapper mb-2 clearfix'><div class='campus-info clearfix'><input type='radio' class='campus campus-input' name='campus' value='" + key + "'><strong>" + value + "</strong></div><div id='minicalendar-" + key.replace(new RegExp(' ', 'g'), '-').toLowerCase() + "'></div>"); //updated by Archana to add tooltip for campus
              $("#campuses").append("<div class='campus-wrapper mb-2 clearfix'><div class='campus-info clearfix'><input type='radio' class='campus campus-input' name='campus' value='" + key + "'><strong>" + value + "</strong></div><div id='minicalendar-" + key.replace(new RegExp(' ', 'g'), '-').replace(new RegExp('L.A.', 'g'), 'la').toLowerCase() + "'></div>"); //updated by Archana to add tooltip for campus

            }); // END OF $.each(data, function (key, value)

            // $("input[name='campus']").bind("change", function(){ campusChanged(); });
            $("input[name='campus']").on('change', function(){
              campusChanged();
            });

            /******* Archana's code starts here *********/
            //code to hide show campus description info
			 showHideCampusDescription(); 
            /*$(document).ready(function(){
				$('.custom-tooltip-campus-description').each(function(){
				  $(this).hide();
				});
				$('.uds-tooltip-campus-button').on('click',function(){
				  //console.log('clicked');
				  $(this).siblings('.custom-tooltip-campus-description').toggle();
				});
				$('.popup-close-btn').on('click',function(){
				  $(this).parent('.custom-tooltip-campus-description').hide();
				});
            })*/
            /** End of Arcahna's code **/



                // if(window.mobilecheck()) { // Mobile

            // Scroll down after clicking on search button
            var offset = $("#message-based-on-interest").offset();
            offset.top -= 200;
            $("html, body").stop();
            $('html, body').animate({
              scrollTop: offset.top
            }, 500, 'linear');
            $(window).bind("mousewheel", function() {
              $("html, body").stop(true, false);
            });


            // }
            $("body").css("cursor", "default");



          } // END OF function OnSuccess(data)
          function OnError(data) {
            console.log("Error occured.");
          }

        } // END OF searchClicked(searchBtnClicked)


		/***Code added by Archana for campus description and visit type icons toogle ****/
		showHideCampusDescription();
		
		function showHideCampusDescription(){
			//code to hide show campus description info
           $(document).ready(function(){
            $('.custom-tooltip-campus-description').each(function(){
              $(this).hide();
            });
            $('.uds-tooltip-campus-button').on('click',function(){
              $(this).siblings('.custom-tooltip-campus-description').show();
			  $(this).parents('.campus-wrapper').siblings().find('.custom-tooltip-campus-description').hide(); 	
            });
            $('.popup-close-btn').on('click',function(){
              $(this).parent('.custom-tooltip-campus-description').hide();
            });
           })
		}
		/*** end of Arcahna's code ***/
		

        function campusChanged() {

          let theCampus = $("input[name='campus']:checked").val();

          // If Havasu is selected, redirect to Havasu site.
          if(theCampus == 'Havasu'){
            window.location.href = "https://havasu.asu.edu/schedule-tour";
          }
			
		  // Comment out redirect for California Center when it goes live (4/12/2024)
//          if(theCampus == 'ASU California Center in downtown L.A.'){
//			$('#minicalendar-asu-california-center-in-downtown-la').html('');
////            window.location.href = "https://asufidm.asu.edu/fidm-transition#events"; // Changed on 2/16/2024.
//            window.location.href = "https://visit.asu.edu/californiacenter";
//          }
			
		  // Keep redirect only in Live site (7/9/2024) -> Removed redirect on 7/16/2024.
//      	  let thedomain = document.location.origin; // https://visitd9cs2-ddev2.ddev.site
//		  if(thedomain == 'https://visit.asu.edu') {
//			  // Comment out redirect for California Center when it goes live (4/12/2024)
//			  if(theCampus == 'ASU California Center in downtown L.A.'){
//				$('#minicalendar-asu-california-center-in-downtown-la').html('');
//	//            window.location.href = "https://asufidm.asu.edu/fidm-transition#events"; // Changed on 2/16/2024.
//				window.location.href = "https://visit.asu.edu/californiacenter";
//			  }
//			  
//		  } else {
//			  	// Removed redirect for Dev sites 
//	//          if(theCampus == 'ASU California Center in downtown L.A.'){
//	//			$('#minicalendar-asu-california-center-in-downtown-la').html('');
//	////            window.location.href = "https://asufidm.asu.edu/fidm-transition#events"; // Changed on 2/16/2024.
//	//            window.location.href = "https://visit.asu.edu/californiacenter";
//	//          }
//		  }

          // Clear mini calendars
          $('#minicalendar-asu-california-center-in-downtown-la').html(''); // Added on 1/10/2024.
          $('#minicalendar-tempe').html('');
          $('#minicalendar-downtown-phoenix').html('');
          $('#minicalendar-polytechnic').html('');
          $('#minicalendar-west').html('');

          // Clear Place holder area for Calendar events
          $('.day-agenda-result').html('');

			
          // Reset toggles
          resetToggleSwitch();

          //--------------------------------------------------------------------------------------------------------------------
          // Display mini calendar

//          if(theCampus != 'Havasu' && theCampus != 'Los Angeles' ){ // When Havasu is selected, don't display the mini-calendar. // Changed on 1/29/2024
//          if(theCampus != 'Havasu' && theCampus != 'ASU California Center in downtown L.A.' ){ // When Havasu or California Center is selected, don't display the mini-calendar. // Changed on 4/15/2024
          if(theCampus != 'Havasu' ){ // When Havasu is selected, don't display the mini-calendar. // TODO: Switch to this when start using Site Managed event for California Center (1/31/2024)

            // Disable campus radio buttons in order to prevent double calendar issue - Added on 1/30/2024
            $('input.campus.campus-input').attr('disabled', true);
//            $('html,body').css('cursor', 'wait');
            $('html').addClass('waiting');
//            $('#minicalendar-' + theCampus.replace(' ', '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>'); // Changed on 1/29/2024
//            $('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
            $('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').replace(new RegExp('L.A.', 'g'), 'la').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
            openMiniCalClicked(theCampus);
//            $('html,body').css('cursor', 'default');
            $('html').removeClass('waiting');

          } // END OF if(theCampus != 'Havasu')
			
		  // Keep redirect only in Live site (7/9/2024)	--> Removed redirect in prod on 7/16/2024.
//		  if(thedomain == 'https://visit.asu.edu') {
//			  if(theCampus != 'Havasu' && theCampus != 'ASU California Center in downtown L.A.' ){ // When Havasu or California Center is selected, don't display the mini-calendar. // Changed on 4/15/2024
//
//				// Disable campus radio buttons in order to prevent double calendar issue - Added on 1/30/2024
//				$('input.campus.campus-input').attr('disabled', true);
//	//            $('html,body').css('cursor', 'wait');
//				$('html').addClass('waiting');
//	//            $('#minicalendar-' + theCampus.replace(' ', '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>'); // Changed on 1/29/2024
//	//            $('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
//				$('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').replace(new RegExp('L.A.', 'g'), 'la').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
//				openMiniCalClicked(theCampus);
//	//            $('html,body').css('cursor', 'default');
//				$('html').removeClass('waiting');
//
//			  } // END OF if(theCampus != 'Havasu')
//			  
//		  } else {
//			  if(theCampus != 'Havasu' ){ // When Havasu is selected, don't display the mini-calendar. // TODO: Switch to this when start using Site Managed event for California Center (1/31/2024)
//
//				// Disable campus radio buttons in order to prevent double calendar issue - Added on 1/30/2024
//				$('input.campus.campus-input').attr('disabled', true);
//	//            $('html,body').css('cursor', 'wait');
//				$('html').addClass('waiting');
//	//            $('#minicalendar-' + theCampus.replace(' ', '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>'); // Changed on 1/29/2024
//	//            $('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
//				$('#minicalendar-' + theCampus.replace(new RegExp(' ', 'g'), '-').replace(new RegExp('L.A.', 'g'), 'la').toLowerCase()).html('<span class="pleasewait d-block"><i><strong>Retrieving information. Please wait...</strong></i></span>');
//				openMiniCalClicked(theCampus);
//	//            $('html,body').css('cursor', 'default');
//				$('html').removeClass('waiting');
//
//			  } // END OF if(theCampus != 'Havasu')
//		  }			
			
			
			

        } // END OF function campusChanged()



        /**
         * createStringYearMonth -- Exact same function exists in asuaec_schedulepage.js at line 150.
         *
         * Build YYYYMM string
         */
        function createStringYearMonth(theDate) {

          let currentYear = theDate.getFullYear();
          let currentMonth = theDate.getMonth() + 1;
          let currentMonth_leadingzero = '';
          if(currentMonth < 10) {
            currentMonth_leadingzero = '0' + currentMonth.toString();
          } else {
            currentMonth_leadingzero = currentMonth;
          }
          return currentYear + '-' + currentMonth_leadingzero;

        } // END OF function createStringYearMonth(theDate)


        /**
         * populateMonthDropdown -- Exact same function exists in asuaec_schedulepage.js at line 169.
         */
        function populateMonthDropdown() {

          // TODO: Make the End date pluggable. So, Liz can change it.

          let startDate = new Date(); // Start date(month) of the dropdown list
          //let startDate = new Date(2018, 11, 1); // This is for testing. Rememeber that JavaScript counts months from 0 to 11. January is 0. December is 11.
          let startMonth = startDate.getMonth();
          let new_month_options = [];
          let endDate = new Date(2026, 4, 15) ; // <-- Up to which month to display. Change month here and also in asuaec_schedulepage.js at Line 272. JavaScript counts months from 0 to 11. January is 0. December is 11.
          let endMonth = endDate.getMonth();

          // Display from this month to endMonth
          let howManyMonth = 4; // Display 4 months by default
          if(endMonth >= startMonth) {
            howManyMonth = endMonth - startMonth + 1;
          } else if (startMonth > endMonth) {
            howManyMonth = (12 - startMonth) + endMonth + 1;
          }
          //console.log("howManyMonth", howManyMonth);

          for (i = 0; i < howManyMonth; i++) {
            if(i == 0) {
              startDate.setMonth(startDate.getMonth());
            } else {
              // If it is 31st of March, May, August, October
              if(startDate.getDate() == 31 && (startDate.getMonth() == 2 || startDate.getMonth() == 4 || startDate.getMonth() == 7 || startDate.getMonth() == 9)) {
                startDate.setDate(startDate.getDate() + 30);
              }
              // If it is 31st of January
              else if(startDate.getDate() == 31 && startDate.getMonth() == 0) {
                startDate.setDate(startDate.getDate() + 28);
              }
              // If it is Jan 29-31
              else if((startDate.getDate() == 29 && startDate.getMonth() == 0) || (startDate.getDate() == 30 && startDate.getMonth() == 0) || (startDate.getDate() == 31 && startDate.getMonth() == 0)) {
                startDate.setDate(startDate.getDate() + 25);
              }
              // The rest
              else {
                startDate.setDate(startDate.getDate() + 31);
              }
            }
            //console.log(startDate.toLocaleDateString());
            new_month_options[i] = createStringYearMonth(startDate);

          } // END OF for (i = 0; i < howManyMonth; i++)
          //console.log("new month options", new_month_options);

          let options = '<option selected="selected" value="">I want to visit in...</option>';
          $.each(new_month_options, function( index, value ){
            //console.log( "index", index, "value", value );
            let myArray = value.split('-');
            // Prepare month name
            monthName = '';
            switch(myArray[1]) {
              case "01":
                monthName = "January";
                break;
              case "02":
                monthName = "February";
                break;
              case "03":
                monthName = "March";
                break;
              case "04":
                monthName = "April";
                break;
              case "05":
                monthName = "May";
                break;
              case "06":
                monthName = "June";
                break;
              case "07":
                monthName = "July";
                break;
              case "08":
                monthName = "August";
                break;
              case "09":
                monthName = "September";
                break;
              case "10":
                monthName = "October";
                break;
              case "11":
                monthName = "November";
                break;
              case "12":
                monthName = "December";
            }
            let valuewithouthypen = value.replace('-','');
            options += '<option value="' + valuewithouthypen + '">' + monthName + ' ' + myArray[0] + '</option>';
          });  // END OF $.each(new_month_options, function( index, value )
          // Remove all options from the select list
          $("select[name='month']").empty();
          // Add new options
          $("select[name='month']").html(options);

          // Select current month by default
          //$("select[name='month']").val(createStringYearMonth(new Date()));

        } // END OF function populateMonthDropdown()



        // function openMiniCalClicked(campus = '', month = '') {
        function openMiniCalClicked(campus = '', month = '', strforurlparam = '') {
          // console.log("campus:", campus);
		  
          let grad_ugrad = $("input[name='persontype']:checked").val() == "Graduate student" ? 'grad' : 'ugrad';

          if(month == '') {
            month = $("select#month > option:selected").val(); // 202211
          }
          if(campus == '') {
            campus = $("select#campus > option:selected").val();
          }
		  if(campus == 'ASU California Center in downtown L.A.') {
			campus = 'ASU California Center in downtown LA';
		  }
          let persontype = $("select#persontype > option:selected").val();

          let bucket2 = '';
          if(grad_ugrad == 'ugrad') {
            bucket2 = $("select#interest-ugrad > option:selected").val(); // 7
          } else {
            bucket2 = $("select#interest-grad > option:selected").val();
          }

          // Build strforurlparam for Month dropdown to work.
          let theYear = month.substring(0,4); // 2023
          let theMonth = month.substring(4, 6); // 09

          // Timezone issue - Resolved on 9/18/2023.
          // let newDate = new Date( parseInt(theYear), parseInt(theMonth)-1); //<---- in their location (OLD)
          jsMonth = parseInt(theMonth);
          if(jsMonth < 10) {
            theJsMonth = '0' + jsMonth; // Make it 2 digit month for single digit month.
          } else {
            theJsMonth = jsMonth;
          }
          let newDate = new Date( theYear + '-' + theJsMonth + '-01T00:00:00.000-07:00'); //<---- Make it AZ time.
          // console.log("newDate:", newDate); // newDate: Sun Oct 01 2023 00:00:00 GMT-0700 (Pacific Daylight Time)
          // console.log(newDate.getTime()); // Timestamp in milliseconds
          let theTimestamp = newDate.getTime()/1000;
          // console.log("theTimestamp: ", theTimestamp); // Timestamp in seconds
          strforurlparam = "?calendar_timestamp=" + theTimestamp + "&current=" + theTimestamp + "&date_format=custom&date_pattern=Ym-F&use_previous_next=0&display_reset=0&pager_type=calendar_month";
		  
          $('html').addClass('waiting');			
          $.ajax({ // Ajax#2
            // url: thedomain + '/minicalendar/' + month + '/' + campus + '/' + persontype + '/' + bucket2,
            url: thedomain + '/minicalendar/' + month + '/' + campus + '/' + persontype + '/' + bucket2 + strforurlparam,

            // type: 'GET',　　 //post or get
            cache: false,        //cacheを使うか使わないかを設定
            dataType: 'text',     //data type script・xmlDocument・jsonなど
            // data: "{}",           //アクセスするときに必要なデータを記載
            async: true,
            contentType: 'application/json',
            
            complete: function () {
              // Enable campus radio buttons. Added on 1/30/2024.
              $('input.campus.campus-input').attr('disabled', false);
              $('html').removeClass("waiting");
            },
            success: function (mydata) {
              // console.log("mydata", mydata);
              // console.log("month", month);

              let jsonnidData = $.parseJSON(mydata);
              let jsonnidString = jsonnidData.resultsData;

              // $("#minicalendar-" + campus.replace(' ', '-').toLowerCase()).html("<p><div id='next-" + campus.replace(' ', '-').toLowerCase() + "'>Next</div></p>" + jsonnidString['body']); //<--- DELETE later just in case
//              $("#minicalendar-" + campus.replace(' ', '-').toLowerCase()).html(jsonnidString['body']); // Changed on 1/29/2024
//              $("#minicalendar-" + campus.replace(new RegExp(' ', 'g'), '-').toLowerCase()).html(jsonnidString['body']);
              $("#minicalendar-" + campus.replace(new RegExp(' ', 'g'), '-').replace(new RegExp('L.A.', 'g'), 'la').toLowerCase()).html(jsonnidString['body']);

              // The following need to be under "Success". Otherwise, they don't work.
              $(".thedate").on('click', function() {
                let date = $(this).attr('date');
                dateClicked(date, campus, bucket2);
              });

              // Styling for caption
              $('table.calendar-view-month > caption').wrapInner( "<h2><span class='highlight-gold'></span></h2>");


              //---------------------------------------------------------
              //--- Next/Prev code by Archana ---/
			
			  $('#toggles-block').show();	
				
              //code to change month header
              $('thead').html('<tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Thu</th><th>Fri</th><th>Sa</th></tr>');

              //code to extract date and Month of Next link
              var next_link = $('.pager__next').find('a').text();
              var monNext = next_link.split("-");

              var nextHtml = '<svg class="calendar-next-arrow calendar-arrow svg-inline--fa fa-chevron-circle-right" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-circle-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm113.9 231L234.4 103.5c-9.4-9.4-24.6-9.4-33.9 0l-17 17c-9.4 9.4-9.4 24.6 0 33.9L285.1 256 183.5 357.6c-9.4 9.4-9.4 24.6 0 33.9l17 17c9.4 9.4 24.6 9.4 33.9 0L369.9 273c9.4-9.4 9.4-24.6 0-34z"></path></svg>';

              var prevHtml = '<svg class="calendar-prev-arrow calendar-arrow svg-inline--fa fa-chevron-circle-left" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-circle-left" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 504C119 504 8 393 8 256S119 8 256 8s248 111 248 248-111 248-248 248zM142.1 273l135.5 135.5c9.4 9.4 24.6 9.4 33.9 0l17-17c9.4-9.4 9.4-24.6 0-33.9L226.9 256l101.6-101.6c9.4-9.4 9.4-24.6 0-33.9l-17-17c-9.4-9.4-24.6-9.4-33.9 0L142.1 239c-9.4 9.4-9.4 24.6 0 34z"></path></svg>';


              // $('.pager__next').find('a').after("<span class='next-btn'>"+monNext[1]+"</span>");
              $('.pager__next').find('a').after("<span class='next-btn'>"+nextHtml+"</span>");

              //code to extract date and Month of Previous link
              var prev_link = $('.pager__previous').find('a').text();
              var monPrev = prev_link.split("-");
              //$('.pager__previous').find('a').after("<span class='prev-btn'>"+monPrev[1]+"</span>");
              $('.pager__previous').find('a').after("<span class='prev-btn'>"+prevHtml+"</span>");

              //code to extract date and Month of current link
              /*var current_link = $('.pager__current').find('div').text();
              var monCur = current_link.split("-");
              $('.pager__current').find('div').after("<span class='current-btn'>"+monCur[1]+"</span>");*/

              //Hide originla text
              $('.pager__previous').find('a').hide();
              $('.pager__next').find('a').hide();
              $('.pager__current').find('div').hide();

              //move pager to bottom of the calendar view
              var pagerHtml = $('.pager__items').html();
              $('.view-content').after('<ul class="js-pager__items calendar-view-pager">'+pagerHtml+'</ul>');
              $('.pager__items').hide();

              $('.pager__next').click(function(){
                var mon = $(this).find('a').text();
                var next_link = $(this).find('a').attr('href');
                next_previous_link(mon, next_link);
              });

              $('.pager__previous').click(function(){
                var monP = $(this).find('a').text();
                var pre_link = $(this).find('a').attr('href');

                next_previous_link(monP, pre_link);
              });

              function next_previous_link(mon, link){

                var monArray = mon.split("-");
                var wholemon = monArray[0];
                $('#month').val(wholemon);
                openMiniCalClicked(campus = campus, month = wholemon, link);

              }

              // END OF Next/Prev code by Archana
              //---------------------------------------------------------





              /////////////////////////////////////////
              // Toggle switch

              // Reset at the beginning
              resetToggleSwitch();

              //-----------------------------
              // Self-guided

              // Check if there is barrett class exists in the mini-calendar page
              if ($(".calendar-view-day > .selfguided")[0]){
                // Flip switch
                $('#switch-selfguided > .switch > input').prop("checked", true);
                // Add a dot
                addADotToMiniCal(toggleSwitchColorSelfguided, toggleSwitchClassSelfguided);
              } else {
                // Do something if class does not exist
              }

              //--------------------
              // In person

              // Check if there is inperson class exists in the mini-calendar page
              if ($(".calendar-view-day > .inperson")[0]){
                // Flip switch
                $('#switch-inperson > .switch > input').prop( "checked", true );
                // Add a dot
                addADotToMiniCal(toggleSwitchColorInperson, toggleSwitchClassInperson);
              } else {
                // Do something if class does not exist
              }

              //-------------------------------
              // In person with academic

              // Check if there is academic class exists in the mini-calendar page
              if ($(".calendar-view-day > .inperson-academic")[0]){
                // Flip switch
                $('#switch-inperson-academic > .switch > input').prop( "checked", true );
                // Add a dot
                addADotToMiniCal(toggleSwitchColorInpersonWithAcademic, toggleSwitchClassInpersonWithAcademic);
              } else {
                // Do something if class does not exist
              }

              //-----------------------------
              // Barrett

              // Check if there is barrett class exists in the mini-calendar page
              if ($(".calendar-view-day > .barrett")[0]){
                // Flip switch
                $('#switch-barrett > .switch > input').prop( "checked", true );
                // Add a dot
                addADotToMiniCal(toggleSwitchColorBarrett, toggleSwitchClassBarrett);
              } else {
                // Do something if class does not exist
              }

              //-----------------------------
              // Facility

              // Check if there is facility class exists in the mini-calendar page
              if ($(".calendar-view-day > .facility")[0]){
                // Flip switch
                $('#switch-facility > .switch > input').prop( "checked", true );
                // Add a dot
                addADotToMiniCal(toggleSwitchColorFacility, toggleSwitchClassFacility);
              } else {
                // Do something if class does not exist
              }

              //-----------------------------
              // Generic

              // Check if there is barrett class exists in the mini-calendar page
              if ($(".calendar-view-day > .generic")[0]){
                // Flip switch
                $('#switch-generic > .switch > input').prop( "checked", true );
                // Add a dot
                addADotToMiniCal(toggleSwitchColorGeneric, toggleSwitchClassGeneric);
              } else {
                // Do something if class does not exist
              }

              ////// END OF Toggle switch ////////

//              // Enable campus radio buttons. Added on 1/30/2024.
//              $('input.campus.campus-input').attr('disabled', false);

            }, // END OF success: function (mydata)
            error: function(){
              $(".minicalendar-" + campus.replace(' ', '-').toLowerCase()).html('');
//              // Enable campus radio buttons. Added on 1/30/2024.
//              $('input.campus.campus-input').attr('disabled', false);
            }

          });

        } // END OF function openMiniCalClicked(month)



        function dateClicked(date, campus = '', bucket2 = '') {
          // Clear Place holder area for Calendar events
          $('.day-agenda-result').html('<div class="pleasewait mt-4"><i><strong>Retrieving information. Please wait...</strong></i></div>');


          if(campus == '') {
            let campus = $("select#campus > option:selected").val();
          }
          let persontype = $("select#persontype > option:selected").val();
          if(bucket2 == '') {
            bucket2 = $("select#interest-ugrad > option:selected").val(); // 7
          }

          $.ajax({
            url: thedomain + '/dayevents/' + date + '/' + campus + '/' + persontype + '/' + bucket2,
            // type: 'GET',　　 //post or get
            cache: false,        //cacheを使うか使わないかを設定
            dataType: 'text',     //data type script・xmlDocument・jsonなど
            // data: "{}",           //アクセスするときに必要なデータを記載
            async: true,
            contentType: 'application/json',

            success: function (mydata) {
              // console.log("mydata", mydata);

              let jsonnidData = $.parseJSON(mydata);
              let jsonnidString = jsonnidData.resultsData;

              // $(".day-agenda-result").html('');
              $(".day-agenda-result").html(jsonnidString['body']);


              // The following need to be under "Success". Otherwise, they don't work.

              // Save selection to JS Session variables
              $("#continue").on('click', function() {
                continueClicked();
              });

              // Scroll down after clicking on a date in the mini calendar
              var offset = $(".day-agenda-result").offset();
              offset.top -= 200;
              $("html, body").stop();
              $('html, body').animate({
                scrollTop: offset.top
              }, 500, 'linear');



              /////////////////////////////////////
              // Toggle switch

              //--------------------------
              // Self-guided

              // Check if there is selfguided class exists in day agenda
              if ($(".views-row-outer.selfguided")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorSelfguided, toggleSwitchClassSelfguided);
              } else {
                // Do something if class does not exist
              }

              //---------------------
              // In person

              // Check if there is inperson class exists in day agenda
              if ($(".views-row-outer.inperson")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorInperson, toggleSwitchClassInperson);
              } else {
                // Do something if class does not exist
              }

              //--------------------------------
              // In person with academic

              // Check if there is inperson-academic class exists in day agenda
              if ($(".views-row-outer.inperson-academic")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorInpersonWithAcademic, toggleSwitchClassInpersonWithAcademic);
              } else {
                // Do something if class does not exist
              }

              //--------------------------
              // Barrett

              // Check if there is barrett class exists in day agenda
              if ($(".views-row-outer.barrett")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorBarrett, toggleSwitchClassBarrett);
              } else {
                // Do something if class does not exist
              }

              //--------------------------
              // Facility

              // Check if there is barrett class exists in day agenda
              if ($(".views-row-outer.facility")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorFacility, toggleSwitchClassFacility);
              } else {
                // Do something if class does not exist
              }

              //--------------------------
              // Generic

              // Check if there is barrett class exists in day agenda
              if ($(".views-row-outer.generic")[0]){
                // Add a dot to day agenda
                addADotToDayAgenda(toggleSwitchColorGeneric, toggleSwitchClassGeneric);
              } else {
                // Do something if class does not exist
              }

              ///////// END OF Toggle switch //////////////

              // Remove destination from Edit link
              removeDestinationFromEditLink();

              /******* Archana's code starts here *********/
              //code to hide show academic sessions description info
              $(document).ready(function(){
                $('.custom-tooltip-description').each(function(){
                  $(this).hide();
                });
                $('.uds-tooltip-button').on('click',function(){
                  // console.log('clicked');
                  $(this).siblings('.custom-tooltip-description').toggle();
                });
                $('.popup-close-btn').on('click',function(){
                  $(this).parent('.custom-tooltip-description').hide();
                });
              })


              /************* Show/hide Additional tour and Barrett under Exp ASU *************/
              // When select a different Experience ASU radio button
              $("input[name='event']").bind("click", function(){ differentEventClicked(this); });
              // init
              clearAllEventSelection();


              // Add indentation
              $("div.views-field-field-placeholder-for-addtour2").addClass('ml-2 clearfix mb-2');
              $("div.views-field-field-placeholder-for-barrett").addClass('ml-2 clearfix mb-2');


              /******* Add spacing after date/time **********/
              $("div.views-field-date__value").addClass('mb-2');




            },
            error: function(){
              $(".day-agenda-result").html('');
            }

          });

        } // END OF function dateClicked(date)



        function continueClicked() {

          // console.log("theInterest beginning of continueClicked():", theInterest);
          // console.log("thePersonType beginning of continueClicked():", thePersonType);
          // console.log("theMonth beginning of continueClicked():", theMonth);

          // Prevent double-clicking
          $('#continue').prop("disabled", true);
          setTimeout(function(){ // Timeout for 0.3 sec.
            $('#continue').prop("disabled", false);
          }, 300);

          // Validation - Need to select at least one top-level event
          if(!$('input[name="event"]').is(":checked")) {
            alert("Please select an event.");
            return;
          }

          if(theMonth      === undefined) { theMonth      = $("select#month > option:selected").val(); }
          if(thePersonType === undefined) { thePersonType = $("select#persontype > option:selected").val(); }
          if(theInterest   === undefined) {
            if (thePersonType == 'Graduate student') {
              theInterest = sessionStorage.getItem("interest") != '' ? sessionStorage.getItem("interest") : $('select#interest-grad').val();
            } else if (thePersonType == 'Other') {
              theInterest = '';
            } else {
              theInterest = sessionStorage.getItem("interest") != '' ? sessionStorage.getItem("interest") : $('select#interest-ugrad').val();
            }
          }
          // console.log("theInterest:", theInterest);
          // console.log("thePersonType:", thePersonType);
          // console.log("theMonth:", theMonth);

          sessionStorage.setItem("persontype", thePersonType);
          sessionStorage.setItem("interest", theInterest);
          sessionStorage.setItem("month", theMonth);

          // Prepare for Json array/object for selected tour

          // Get values from dropdowns:
          let campus = $("input[name='campus']:checked").val();
          let interest = theInterest;

          // Get values from input's value - Receive values from views-view-unformatted--visitd9-day-agenda--block-1.html.twig at line 76.
          let eventseriesid = $('input[name="event"]:checked').val();

          let eventinstanceid = $('input[name="eventinstanceid-' + eventseriesid + '"]').val();
          let eventid = $('input[name="eventid-' + eventseriesid + '"]').val();
          let vdate = $('input[name="vdate-' + eventseriesid + '"]').val(); // 2023-06-20
          let timestamp = $('input[name="timestamp-' + eventseriesid + '"]').val();
          let timestamp2 = $('input[name="timestamp2-' + eventseriesid + '"]').val();
          let from = $('input[name="from-' + eventseriesid + '"]').val();
          let to = $('input[name="to-' + eventseriesid + '"]').val();
          let datetime = $('input[name="datetime-' + eventseriesid + '"]').val(); // Ex: 2023/06/20 10:00:00
          let eventtype = $('input[name="eventtype-' + eventseriesid + '"]').val(); //For Barrett, "Barrett tour"
          let eventdisplaytitle = $('input[name="eventdisplaytitle-' + eventseriesid + '"]').val();

          // TODO - Need to change for Barrett
          // eventname is Barrett+Tour, then, assign Barrett in tourtype
          let tourtype = "Regular";
          if(eventtype == 'Barrett tour'){
            let tourtype = "Barrett";
          }

          // console.log("vdate test: ", vdate);

          // Additional tours selected
          let addToursArray = [];
          $('input[name="add-tour"]').each(function () {
            if(this.checked){
              // console.log("addtour checked:" + $(this).val()) // Ok. 1-477-1692633600|1692633600|1692637200|Aviation tour test
              addToursArray.push($(this).val()); // Value contains Paragraph Entity ID
            }
          });
          // console.log("add tours array:", addToursArray); // OK. "1"

          // Barrett additional tours selected
          let addToursBarrettArray = [];
          let barrettUnderExpASUListArray = [];
          $('input[name=event-barrett-under-expasu]').each(function () {
            if(this.checked){
              // console.log("addtour Barrett checked:" + $(this).val()) // 2-1692986400|1692986400|1692987300|Barrett, The Honors College information session and tour - Barrett tour 1
              let tempArray = $(this).val().split('|');
              // console.log( "Barrett tour event id:", tempArray[0] ); // 2-1683730800

              // addToursBarrettArray array
              addToursBarrettArray.push($(this).val());
              // addToursBarrettArray.push(tempArray[0]); // Value contains $barrett_entity_id . '-' . $start_datetime_timestamp (original)



              // barrettUnderExpASUList array
              // Let's grab actual start timestamp
              barrettUnderExpASUListArray.push($(this).val());

            }
          });
          // console.log("add tours Barrett array:", addToursBarrettArray); // Ok. "2-1683730800"
          // console.log("barrettUnderExpASUListArray:", barrettUnderExpASUListArray);

          // Create Json
          let myJsonDataObj = {
            "campus":campus,
            "eventid":eventid,
            "eventseriesid": eventseriesid,
            "eventinstanceid": eventinstanceid,
            "eventtype": eventtype,
            "vdate":vdate,
            "timestamp":timestamp,
            "timestamp2":timestamp2,
            "from":from,
            "to":to,
            "interest":interest,
            "tourtype":tourtype,
            "addtour":addToursArray, // Additional tours -- It will contain Additional tour Paragraph id|start timestamp|end timestamp
            "addtour_barrett":addToursBarrettArray, // Barrett as additional tour -- It will contain: Event series id|start timestamp|end timestamp|Display title
            "eventdisplaytitle":eventdisplaytitle
          };

          let visitsArray = [];
          visitsArray.push(myJsonDataObj);

          // Save the Json to sessionStorage
          sessionStorage.setItem('visits', JSON.stringify(visitsArray));

          // What the following code is doing is: If there is already the campus, don't overwrite it. We are not doing
          // multi-campus, so, we can just save and overwrite.

          // if(sessionStorage.getItem("visits") != null) {
          //   visitsArray = JSON.parse( sessionStorage.getItem("visits"));
          // }
          //
          // // Check if the campus is already in visitsArray
          // let alreadyHasSameCampus = false;
          // for (i = 0; i < visitsArray.length; i++) {
          //   for (let key in visitsArray[i]) {
          //
          //     if(key == 'campus') {
          //       if(campus == visitsArray[i]['campus']) {
          //         alreadyHasSameCampus = true;
          //       }
          //     }
          //
          //   } // END OF for (let key in visitsArray[i])
          // } // END OF for (i = 0; i < visitsArray.length; i++)
          //
          // if(alreadyHasSameCampus == false) {
          //   visitsArray.push(myJsonDataObj);
          //
          //   // Save the Json to sessionStorage
          //   sessionStorage.setItem('visits', JSON.stringify(visitsArray));
          // }


          // NEW
          // Save another json to Session storage about Barrett under Exp ASU list. - Added on 6/29/2023
          sessionStorage.setItem('barrett_under_expasu_list', JSON.stringify(barrettUnderExpASUListArray));
          // // Also save another json for Additional tour. -----ROLLBACK 8/3/2023
          sessionStorage.setItem('addtour_list', JSON.stringify(addToursArray));




          // Redirect to Visit form
          console.log("cancel_urlParam:", cancel_urlParam);

          // If person type is other -> Go to Other form.
          // let urlParam = '?eventid=' + eventid + '&eventseriesid=' + eventseriesid + '&eventtype=' + eventtype + '&eventdate=' + displaydate + '&from=' + from + '&to=' + to + '&timestamp=' + timestamp + '&timestamp2=' + timestamp2 + '&date=' + vdate;
          let urlParam = '?eid=' + eventid + '&seriesid=' + eventseriesid + '&etype=' + eventtype + '&dt=' + datetime + '&from=' + from + '&to=' + to + '&ts=' + timestamp + '&ts2=' + timestamp2 + '&date=' + vdate + '&ptype=' + thePersonType + '&instid=' + eventinstanceid + '&campus=' + campus + cancel_urlParam;

          if(thePersonType == "Other"){
            window.location.href = evolutionOtherFormPath + urlParam;
          } else {
            window.location.href = evolutionFormPath + urlParam; // Redirect: Commented out for testing
          }


        } // END OF function continueClicked()


        /**
         * Clear Additional tour and Barrett tour selection.
         * Then, hide all Additional tour and Barrett tour selection.
         * Finally, show only the one that the parent was clicked.
         *
         * @param obj
         */
        function differentEventClicked(obj) {
          // Step 1: Clear Additional tours selection
          $("input[name='add-tour']").each(function() {
            $(this).prop('checked', false);
          });
          $("input[name='event-barrett-under-expasu']").each(function() {
            $(this).prop('checked', false);
          });

          // Show/hide Additional Tours

          // Step 2: Hide all
          $("div.views-field-field-placeholder-for-addtour2").each(function() {
            $(this).hide();
          });
          $("div.views-field-field-placeholder-for-barrett").each(function() {
            $(this).hide();
          });
          $("div.views-field-field-placeholder-for-descr-html").each(function() {
            $(this).hide();
          });

          // Step 3 Show only the one that need to be shown.
          eventNid = $(obj).val();
          // console.log("eventNid:", eventNid);

          // var children = $(obj).parent().children();
          var parent = $(obj).parent(); // parent is .views-row
          var addtour = parent.find('div.views-field.views-field-field-placeholder-for-addtour2').show();
          var barrett_underexpasu = parent.find('div.views-field-field-placeholder-for-barrett').show();
          var desc = parent.find('div.views-field-field-placeholder-for-descr-html').show();

        } // END OF function differentEventClicked()


        /**
         * Clear all top-level event selection and hide additional tours/Barrett under Exp ASU.
         */
        function clearAllEventSelection() {
          // Clear top-level event selection
          $("input[name='event']").each(function() {
            $(this).prop('checked', false);
          });
          // Clear Additional tours selection
          $("input[name='add-tour']").each(function() {
            $(this).prop('checked', false);
          });
          $("input[name='event-barrett-under-expasu']").each(function() {
            $(this).prop('checked', false);
          });

          // Hide Additional Tours
          $("div.views-field-field-placeholder-for-addtour2").each(function() {
            $(this).hide();
          });
          $("div.views-field-field-placeholder-for-barrett").each(function() {
            $(this).hide();
          });
          $("div.views-field-field-placeholder-for-descr-html").each(function() {
            $(this).hide();
          });
        } // END OF function clearAllEventSelection()



        ////////////////////////////////////
        // Toggle switch change event

        //------------------------
        // Self-guided
        $('#switch-selfguided > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassSelfguided);
        });

        //------------------------
        // In person
        $('#switch-inperson > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassInperson);
        });

        //------------------------
        // In person with academic
        $('#switch-inperson-academic > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassInpersonWithAcademic);
        });

        //------------------------
        // Barrett
        $('#switch-barrett > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassBarrett);
        });

        //------------------------
        // Facility
        $('#switch-facility > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassFacility);
        });

        //------------------------
        // Generic
        $('#switch-generic > .switch > input').change(function() {
          toggleSwitchChangeEvent(this, toggleSwitchClassGeneric);
        });

        //////////// END OF Toggle switch change event ////////////////



        /**
         * Reset - Turn off all toggle switches.
         */
        function resetToggleSwitch() {
          $('#switch-selfguided > .switch > input').prop( "checked", false );
          $('#switch-inperson > .switch > input').prop( "checked", false );
          $('#switch-inperson-academic > .switch > input').prop( "checked", false );
          $('#switch-barrett > .switch > input').prop( "checked", false );
          $('#switch-facility > .switch > input').prop( "checked", false );
          $('#switch-generic > .switch > input').prop( "checked", false );
        }

        function addADotToDayAgenda(color, classname) {
          // Add a dot to day agenda
          // $(".views-row-outer." + classname).each(function( index ) {
          //   $(this).find(".dots").html('<div class="dot dot-' + classname + '" style="width:10px;"><svg style="fill: ' + color + ';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512z"/></svg></div>');
          // });
          // $(".views-row-outer." + classname).each(function( index ) {
          //   $(this).find(".dots").append('<div class="dot dot-' + classname + '" style="width:10px;"><svg style="fill: ' + color + ';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512z"/></svg></div>');
          // });
          // Removed Font awesome. We will use just CSS to display dots using background and color.
          $(".views-row-outer." + classname).each(function( index ) {
            $(this).find(".dots").append('<div class="dot dot-' + classname + '" style="width:10px;"></div>');
          });



        } // END OF function addADotToDayAgenda(color, classname)

        function addADotToMiniCal(color, classname) {

          // Add a dot to mini calendar
          $(".calendar-view-day > ." + classname).each(function( index ) {
            $('<div class="dot dot-' + classname + '" style="width:10px;"><svg style="fill: ' + color + ';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512z"/></svg></div>').appendTo($(this).find(".dots"));
          });
        } // END OF function addADotToMiniCal(color, classname)

        function toggleSwitchChangeEvent(theThis, classname) {
          // console.log("theThis:", theThis);
          // console.log("classname: ", classname);

          if(theThis.checked) {
            // Show inperson dot
            $(".dot-" + classname).each(function( index ) {
              $(this).show();
            });
            $(".views-row-outer." + classname).each(function( index ) {
              $(this).show();
            });

          } else {
            // Hide inperson dot
            $(".dot-" + classname).each(function( index ) {
              $(this).hide();
            });
            $(".views-row-outer." + classname).each(function( index ) {
              $(this).hide();
            });
          }

        } // END OF function toggleSwitchChangeEvent()

        /**
         * Remove ?destination=xxxxx from Edit links
         */
        function removeDestinationFromEditLink() {
          $('.views-field-edit-eventinstance > .field-content > a').each(function( index ) {
            let oldUrl = $(this).attr("href"); // Get current url
            let newUrl = oldUrl.split('?')[0]; // Create new url
            $(this).attr("href", newUrl); // Set herf value
          });
          $('.views-field-edit-eventseries > .field-content > a').each(function( index ) {
            let oldUrl = $(this).attr("href"); // Get current url
            let newUrl = oldUrl.split('?')[0]; // Create new url
            $(this).attr("href", newUrl); // Set herf value
          });
        }







      // }); // END OF $('main', context).once('visitjs').each(function ()


    }
  };
})(jQuery, Drupal, drupalSettings);