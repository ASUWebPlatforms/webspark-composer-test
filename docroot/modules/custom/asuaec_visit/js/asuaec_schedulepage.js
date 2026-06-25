(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.searchbutton = {
    attach: function (context, settings) {

      //---- For Cancel form - Add Attendee id and Event id to URL and pass it through to the final form. ----//
      // Get URL param - ?aid={attendee_id}&eventid={event_id}
      let urlParams = new URLSearchParams(window.location.search);
      let cancel_attendee_id = urlParams.get('c-aid');
      let cancel_eventid = urlParams.get('c-eid');
      let cancel_sid = urlParams.get('c-sid'); // Added on 3/18/2025
      // console.log("cancel_attendee_id: ", cancel_attendee_id);
      // let cancel_urlParam = "?c-aid=" + cancel_attendee_id + "&c-eid=" + cancel_eventid;
      let cancel_urlParam = "?c-aid=" + cancel_attendee_id + "&c-eid=" + cancel_eventid + "&c-sid=" + cancel_sid; // Added c-sid on 3/18/2025
      // console.log("cancel_urlParam: ", cancel_urlParam);
      
      // Populate "I am a..."
      let cancel_ptype = urlParams.get('c-ptype');

      // Clear JS Session Variables
      sessionStorage.clear();
      schedulingYourTourPath = '/scheduling-your-tour';
      $('#page-title').hide();

      // Clear dropdown selection
      $("select[name='persontype']").val(0);
      $("select[name='interest-ugrad']").val(0);
      $("select[name='interest-grad']").val(0);
      
      // If there is c-ptype in URL param, select the person type.
      if (cancel_ptype) {
        $("select[name='persontype']").val(cancel_ptype).trigger('change');
        // Ensure the change event is properly handled
        setTimeout(() => {
          $("select[name='persontype']").trigger('change');
        }, 100);
      }


      //------------------------------------------------------------------------
      // Get interest from custom API and select the interest from dropdown
      
      // Get URL parameters
      const cSid = urlParams.get('c-sid');
      const cPtype = urlParams.get('c-ptype');

      if (cSid) {
          const apiUrl = `/custom-api/webform-submission-interest/${cSid}`;

          // Fetch interest data from the API
          $.getJSON(apiUrl, function (data) {
              if (data.interest) {

                  // Determine which dropdown to use
                  let interestDropdown = (cPtype === "Graduate student") 
                      ? $("#interest-grad") 
                      : $("#interest-ugrad");

                  // Delay selection to ensure dropdown is fully loaded
                  setTimeout(function () {
                      // Make sure the option exists before setting the value
                      if (interestDropdown.find(`option[value="${data.interest}"]`).length) {
                          interestDropdown.val(data.interest).trigger("change");
                          console.log("Dropdown updated successfully!");
                      } else {
                          console.warn("Option not found in dropdown:", data.interest);
                      }
                  }, 500); // Small delay to ensure dropdown is ready
              }
          }).fail(function () {
              console.error("Failed to fetch interest data");
          });
      }


      // Populate Month dropdown
      populateMonthDropdown();

      // Show dropdowns
      $('#wrapper-dropdowns .sec1').show(); // Person type
      $('#wrapper-dropdowns .sec2').show(); // Interest - empty
      $('#wrapper-dropdowns .sec3').hide(); // Interest - Ugrad
      $('#wrapper-dropdowns .sec4').hide(); // Interest - Grad
      $('#wrapper-dropdowns .sec5').show(); // Month


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


      // Add binding for select[name='persontype'].
      // When user select Grad, show Grad interest list.
      // $("select[name='persontype']").bind("change", function(){ persontypeChanged(); });
      //$("select[name='persontype']").once().on('change', function(){
//      $(once('visitschedulepagejs', "select[name='persontype']", context)).on('change', function() { // D10 change  
//        persontypeChanged();
//      });
      // ChatGPT way
      $(once('visitschedulepagejs', $("select[name='persontype']", context))).on('change', function() { // D10 change  
        persontypeChanged();
      });



      // If Interest dropdown is clicked before Person type is set, alert.
      //$("select[name='interest']").once().on('click', function(){
//      $(once('visitschedulepagejs', "select[name='interest']", context)).on('click', function() { // D10 change   
//        if($("select#persontype").val() == '0') {
//          alert("Please select from 'I am a...' dropdown list first.");
//          $("#persontype").addClass("error is-invalid");
//        }
//      });
      // ChatGPT way
      $(once('visitschedulepagejs', $("select[name='interest']", context))).on('click', function() { // D10 change   
        if($("select#persontype").val() == '0') {
          alert("Please select from 'I am a...' dropdown list first.");
          $("#persontype").addClass("error is-invalid");
        }
      });

      let searchBtnClicked = true;
      // $("#search").bind("click", function(){ searchClicked(searchBtnClicked); });
      //$("#search").once().on('click', function(){
//      $(once('visitschedulepagejs', "#search", context)).on('click', function() { // D10 change  
//        searchClicked(searchBtnClicked);
//      });
      // ChatGPT way
      $(once('visitschedulepagejs', $("#search", context))).on('click', function() { // D10 change  
        searchClicked(searchBtnClicked);
      });


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


      function searchClicked(searchBtnClicked) {
        // Clear errors
        $("#persontype").removeClass("error is-invalid");
        $("#interest-grad").removeClass("error is-invalid");
        $("#interest-ugrad").removeClass("error is-invalid");
        $("#month").removeClass("error is-invalid");

        // Validation
        if($('#persontype').val() == '0') {
          alert ("Please tell us a little about yourself.");
          $("#persontype").addClass("error is-invalid");
          return false;

        } else if($('#persontype').val() == 'Graduate student' && $('#interest-grad').val() == '0') {
          alert ("Please tell us what you want to study.");
          $("#interest-grad").addClass("error is-invalid");
          return false;

        } else if ($('#persontype').val() != 'Graduate student' && $('#persontype').val() != 'Other' && $('#interest-ugrad').val() == '0') {
          alert ("Please tell us what you want to study.");
          $("#interest-ugrad").addClass("error is-invalid");
          return false;

        } else if($('#month').val() == ''){
          alert ("Please select a month.");
          $("#month").addClass("error is-invalid");
          return false;

        } else {
        }

        let thePersonType = '';
        let theInterest = '';
        let theMonth = '';

        // Get inputs from the dropdown
        thePersonType = $('select#persontype').val().length ? $('select#persontype').val() : '';
        if(thePersonType == 'Graduate student') {
          theInterest = $('select#interest-grad').val().length ? $('select#interest-grad').val() : '';
        } else {
          theInterest = $('select#interest-ugrad').val().length ? $('select#interest-ugrad').val() : '';
        }
        theMonth = $('select#month').val().length ? $('select#month').val() : '';

        // Place the values in sessionStorage
        sessionStorage.setItem("persontype", thePersonType);
        sessionStorage.setItem("interest", theInterest);
        sessionStorage.setItem("month", theMonth);
        // Update createdtime in JS Session Storage also.
        let createdTime = Math.floor((new Date()).getTime() / 1000);
        sessionStorage.setItem("createdtime", createdTime);

        // Then redirect to scheduling-your-tour page
        // console.log("cancel_urlParam 2: ", cancel_urlParam);
        window.location.href = schedulingYourTourPath + cancel_urlParam;

      } // END OF searchClicked(searchBtnClicked)


      // Build YYYYMM string
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


      function populateMonthDropdown() {

        // TODO: Make the End date pluggable. So, Liz can change it.

        let startDate = new Date(); // Start date(month) of the dropdown list
        //let startDate = new Date(2018, 11, 1); // This is for testing. Rememeber that JavaScript counts months from 0 to 11. January is 0. December is 11.
        let startMonth = startDate.getMonth();
        let new_month_options = [];
        let endDate = new Date(2026,4, 15) ; // <-- Up to which month to display. Change month here and also in asuaec_visit.js at Line 712. JavaScript counts months from 0 to 11. January is 0. December is 11.
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
        // console.log("new month options", new_month_options);

        let options = '<option selected="selected" value="">I want to visit in...</option>';
        $.each(new_month_options, function( index, value ){
          //console.log( "index", index, "value", value );
          let myArray = value.split('-');

          // Prepare month name
          let monthName = '';
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



    } // END OF function (context, settings)
  }; // END OF   Drupal.behaviors.searchbutton
})(jQuery, Drupal, drupalSettings);