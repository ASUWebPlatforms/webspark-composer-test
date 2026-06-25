(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.visitform = {
    attach: function (context, settings) {

      // Find the first element with the error class
      var firstErrorElement = $('.is-invalid').first();
      // Check if an element with the error class was found
      if (firstErrorElement.length) {
        // Smoothly scroll to the element with a duration of 500 milliseconds
        $('html, body').animate({
          scrollTop: firstErrorElement.offset().top - 375
        }, 500);
      }

      /////////////////////////////////////////////////////////////
      // sessionStorage JS
      // Plug Json into "Json string" hidden field.
      // Grab persontype and interest and plug them into hidden fields.
      // Also, plug Barrett under Exp ASU info (barrett_under_expasu_list) into "barrett_under_expasu_jsonlist" hidden field
      /////////////////////////////////////////////////////////////

      // Grab URL
      const path = window.location.pathname;
      // console.log("path:", path);

      // Only for Exp ASU form, ensure a campus is present in session before proceeding
      if (path === '/registration-form') {
        if (once('visitform-campus-check', 'body', context).length) {
          let missingCampus = true;
          const rawVisits = sessionStorage.getItem('visits');

          if (rawVisits) {
            try {
              const parsed = JSON.parse(rawVisits);
              if (Array.isArray(parsed) && parsed.length > 0) {
                const first = parsed[0] || {};
                // console.log("first:", first);
                const campus = (first.campus ?? '').toString().trim();
                if (campus !== '') {
                  missingCampus = false;
                }
              }
            } catch (e) {
              // If JSON is bad, treat as missing.
              missingCampus = true;
            }
          }

          if (missingCampus) {
            // Clear visits-related session keys
            sessionStorage.removeItem('visits');
            sessionStorage.removeItem('barrett_under_expasu_list');
            sessionStorage.removeItem('addtour_list');

            // sessionStorage.removeItem('persontype');
            // sessionStorage.removeItem('interest');

            alert('We are sorry, something went wrong. Please try again.');
            window.location.assign('/schedule');
            return; // stop processing this behavior
          }
        } // END OF if (once('visitform-campus-check', 'body', context).length)
      }


      // Get ptypelist from URL param
      let urlParams = new URLSearchParams(window.location.search);
      let ptypelist = urlParams.get('ptypelist');

      let jsonString = sessionStorage.getItem("visits");
      $('input[name="json_string"]').val(jsonString);

      if (ptypelist !== 'y') {
        let personType = sessionStorage.getItem("persontype") || '';
        // $('input[name="visitor_type"]').val(personType);
        $('input[name="visitor_type"]').val(personType).trigger('change');
      }

      let interest = sessionStorage.getItem("interest");
      $('input[name="interest"]').val(interest);
      let barrettJsonString = sessionStorage.getItem("barrett_under_expasu_list");
      $('input[name="barrett_under_expasu_jsonlist"]').val(barrettJsonString);
      let addtourJsonString = sessionStorage.getItem("addtour_list");
      $('input[name="addtour_jsonlist"]').val(addtourJsonString);

      // Added on 9/15/2025
      // let visits = JSON.parse(jsonString);
      // let eventTypes = visits.map(v => v.eventtype);
      // // Check evenetTypes array and if all are "Graduate Student Event" event type, isAllGradFair is true.
      // const isAllGradFair = eventTypes.every(type => type === "Graduate Student Event");
      var isAllGradFair;
      if (jsonString) { // This is only for Visit site managed events. Not for Masterform event.
        try {
          let visits = JSON.parse(jsonString);

          if (Array.isArray(visits)) {
            let eventTypes = visits.map(v => v.eventtype);

            // Check if all are Graduate Student Event
            isAllGradFair = eventTypes.every(
              type => type === "Graduate Student Event"
            );
          }
        } catch (e) {
          console.warn("Invalid visits JSON in sessionStorage:", e);
        }
      }

      /**
       * Add +1 to phone number field to be by default
       */
      var ini_phone = $('input[name="phone"]').val();
      if(ini_phone == ''){
        $('input[name="phone"]').val('+1');
      }

      /////////////////////////////////////////////////////////////
      // Add Dynamic Person type dropdown list when ptypelist=y in URL param
      // Look at the Event Series and populate the Person list.
      /////////////////////////////////////////////////////////////
      let eventseriesId = urlParams.get('seriesid');
      //let intlist = urlParams.get('intlist');
      let campus = urlParams.get('campus');
//      console.log("campus:", campus); // ASU California Center in downtown L.A.
      let campusCode = "";
      switch (campus) {
        case "ASU California Center in downtown L.A.":
          campusCode = "LOSAN";
          break;
        case "Tempe":
          campusCode = "TEMPE";
          break;
        case "Polytechnic":
          campusCode = "POLY";
          break;
        case "West":
          campusCode = "WEST";
          break;
        case "Downtown Phoenix": // TODO: Need to test this.
          campusCode = "DTPHX";
          break;
      }
      if(ptypelist == 'y') {
        // Check if the person type field is empty
        if($('#dynamic-ptype').length == 0) {
          $("input[name='visitor_type']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="ptype-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>I am a ..</label><select id="dynamic-ptype" class="custom-select form-select form-control" name="dynamic-ptype"></select></div>');
        }

        // Fill the ptype options in dropdown list
        let collUrl = "/admin/asuaec_json/json/get_ptypes/" + eventseriesId;
        $("#dynamic-ptype").removeOption(/./);
        $("#dynamic-ptype").addOption({'0': '- Please select -'});
        $("#dynamic-ptype").ajaxAddOption(collUrl , null, false);

        // When comes back from validation, select previously selected value.
        // Previously selected value is kept in JS Session variable.
        $(document).ajaxComplete(function(){
          personType = sessionStorage.getItem("persontype");
          if(personType) {
            //$("#dynamic-ptype option[value='" + personType + "']").attr('selected', 'selected'); // 2/2/2026
            $("#dynamic-ptype").val(personType);
            personTypeChanged();
          }
        });

        if (sessionStorage.getItem('persontype')) {
          personTypeChanged();
        }
        $(once('visitformjs-ptype-change', '#dynamic-ptype', context)).on('change', personTypeChanged);

      } // END OF if(ptypelist == 'y')


      // function for onload - Added on 9/16/2025
      function processForOnload() {

        // Assign Grad to the hidden Webform field
        let personType = sessionStorage.getItem("persontype");
        $('input[name="visitor_type"]').val(personType);

        if(isAllGradFair) { // Added on 9/15/2025
          // Don't display Grad interest dropdown.
          // If it is already there, remove it.

          $( document ).on( "ajaxComplete", function() {
            if ($("select[name='college1']").length) {
              $("select[name='college1']").parent().remove();
            }
            if ($("select[name='degree1']").length) {
              $("select[name='degree1']").parent().remove();
            }
          });

          // If there is Interest dropdown, hide it.
          $("#dynamic-interest").hide();
        }
        else { // isAllGradFair is false

          if(personType == "Graduate student") {  // isAllGradFair is false and Grad

            // HS fields
            $("#edit-high-school-you-are-currently-attending").hide();
            // Parent info
            $("#edit-parentsinformation").hide();

            // Display Interest dropdown
            displayGradInterestDropdown(campusCode);

            // Bind
            //$("#dynamic-interest").once().on("change", function(){
            $(once('visitformjs', "#dynamic-interest")).on('change', function() { // D10 change
              // Remove error class
              if ( $( this ).hasClass( "is-invalid" ) ) {
                $( this ).removeClass('required error is-invalid');
              }
              enableSubmitBtn();
              // Save the interest
              $("input[name='interest']").val( $("#dynamic-interest").val()); // Save the selected value in the webform "visitor_type" field
              // Add the value to JS Session variable
              sessionStorage.setItem('interest', $("#dynamic-interest").val());

              let campusArray = [campusCode];
              populateCollegeOptions(campusArray);

              // College
              if($("#dynamic-edit-submitted-1-college").length) {
                $("#dynamic-edit-submitted-1-college").parent().show();
                $("#dynamic-edit-submitted-major").parent().show();
              } else {
                addAndDisplayCollegeAndMajor();
              }
            });

          } else { // When not Grad
            // Let the centralized rules decide HS / Parents / Institution visibility (2/2/2026)
            applyHsAndInstitutionVisibility();
            // // HS fields
            // $("#edit-high-school-you-are-currently-attending").show();
            // // Parent info
            // $("#edit-parentsinformation").show();

            // Interest
            $("#dynamic-interest").parent().hide();
            // College
            $("#dynamic-edit-submitted-1-college").parent().hide();
            // Degrees
            $("#dynamic-edit-submitted-major").parent().hide();
          }
        }
      } // END OF function processForOnload()

      $(document).ready(function () {
        processForOnload();
        applyHsAndInstitutionVisibility();
      });


      // Bind
      //$("#dynamic-ptype").on("change", function(){
      //$(once('visitformjs', "#dynamic-ptype")).on('change', function() { // D10 change
      function personTypeChanged() {
        const selected = ($("#dynamic-ptype").val() || '').trim();

        $("#dynamic-ptype").removeClass('required error is-invalid');

        enableSubmitBtn();

        if (selected === '0' || selected === '') {
          $("input[name='visitor_type']").val("").trigger('change');
          sessionStorage.setItem('persontype', '');
          return;
        }

        sessionStorage.setItem('persontype', selected);

        // Save the selected value in the webform "visitor_type" field
        // $("input[name='visitor_type']").val( $("#dynamic-ptype").val()); // (2/2/2026)
        $("input[name='visitor_type']").val(selected).trigger('change');
        applyHsAndInstitutionVisibility();

        if(isAllGradFair == true) { // Added on 9/15/2025
          // Don't display Grad interest dropdown

          // Also hide HS fields
          //$("#edit-high-school-you-are-currently-attending").hide(); //<--- This didn't work for some reason.
          // Work-around
          $("#edit-high-school-you-are-currently-attending").removeClass('card form-control');
          $("#edit-high-school-you-are-currently-attending > .card-header").hide();
          $("#edit-high-school-you-are-currently-attending > .card-body").hide();

          // Also, hide Parent info
          $("#edit-parentsinformation").hide();

        } else { // isAllGradFair == false

          // if ($("#dynamic-ptype").val() == "Graduate student") {
          if (selected == "Graduate student") {
            // console.log("if Graduate student");

            // HS fields
            $("#edit-high-school-you-are-currently-attending").hide();
            // Parent info
            $("#edit-parentsinformation").hide();

            // Display Interest dropdown
            displayGradInterestDropdown(campusCode);
            // Bind
            //$("#dynamic-interest").once().on("change", function(){
            $(once('visitformjs', "#dynamic-interest")).on('change', function() { // D10 change
              // Remove error class
              if ( $( this ).hasClass( "is-invalid" ) ) {
                $( this ).removeClass('required error is-invalid');
              }
              enableSubmitBtn();
              // Save the interest
              $("input[name='interest']").val( $("#dynamic-interest").val()); // Save the selected value in the webform "visitor_type" field
              // Add the value to JS Session variable
              sessionStorage.setItem('interest', $("#dynamic-interest").val());

              let campusArray = [campusCode];
              populateCollegeOptions(campusArray);

              // College
              if($("#dynamic-edit-submitted-1-college").length) {
                $("#dynamic-edit-submitted-1-college").parent().show();
                $("#dynamic-edit-submitted-major").parent().show();
              } else {
                addAndDisplayCollegeAndMajor();
              }
            });

          } else { // When not Grad
            // Let centralized rules decide HS / Parents / Institution visibility
            applyHsAndInstitutionVisibility();

            // // HS fields
            // $("#edit-high-school-you-are-currently-attending").show();
            // // Parent info
            // $("#edit-parentsinformation").show();
            // Interest
            $("#dynamic-interest").parent().hide();
            // College
            $("#dynamic-edit-submitted-1-college").parent().hide();
            // Degrees
            $("#dynamic-edit-submitted-major").parent().hide();
          }
        }

      //}); // END OF Bind $(once('visitformjs', "#dynamic-ptype")).on('change', function() {
      } // END OF function personTypeChanged()

      //personTypeChanged();
      // $("#dynamic-ptype").on("change", function(){
      //   personTypeChanged();
      // });
      // $(once('visitformjs-ptype-change', '#dynamic-ptype', context)).on('change', personTypeChanged);

      function displayGradInterestDropdown($campus) {
        // console.log("displayGradInterestDropdown fired!");
        // check if the interest dropdown field is empty
        if($('#dynamic-interest').length == 0) {
          $("input[name='interest']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="interest-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>I want to study .. </label><select id="dynamic-interest" class="custom-select form-select form-control" name="dynamic-interest"></select></div>');
        }
        // fill the ptype options
        let collUrl = "/admin/asuaec_json/json/get_interests/" + $campus;
        $("#dynamic-interest").removeOption(/./);
        $("#dynamic-interest").addOption({'0':'- Please select -'});
        $("#dynamic-interest").ajaxAddOption(collUrl , null, false);

        $("#dynamic-interest").parent().show();
      } // END OF function displayGradInterestDropdown($campus)

      function addAndDisplayCollegeAndMajor() {
        var college_data = $("input[name='submitted[college]']").val();
        var major_data =  $("input[name='submitted[major]']").val();

        // check if the college field is empty
        if($('#dynamic-edit-submitted-1-college').length == 0) {
          $("input[name='college']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="college-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select college of interest</label><select id="dynamic-edit-submitted-1-college" class="custom-select form-select form-control" name="college1"></select></div>');
        }

        if($('#dynamic-edit-submitted-major').length == 0) {
          $("input[name='major']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="major-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select major of interest</label><select id="dynamic-edit-submitted-major" class="custom-select form-select form-control" name="degree1"></select></div>');
        }

        // fill the college options
        var collUrl = "/admin/asuaec_json/json/get_colleges_by_multi_campuses_and_interest/" + campusesString + "/" + interest + "/" + grad_ugrad;
        $("#dynamic-edit-submitted-1-college").removeOption(/./);
        $("#dynamic-edit-submitted-1-college").addOption({'0':'- Select college of interest -'});
        $("#dynamic-edit-submitted-1-college").ajaxAddOption(collUrl , null, false);

        // Bind
        // $("#dynamic-edit-submitted-1-college").bind("change", function(){ collegeChanged(); })
        //$("#dynamic-edit-submitted-1-college").once().on("change", function(){
        $(once('visitformjs', "#dynamic-edit-submitted-1-college")).on('change', function() { // D10 change
          collegeChanged();
        });
      }

      function collegeChanged(campusesString){
        var dynamic_college_data = $("#dynamic-edit-submitted-1-college").val();
        var degreeUrl = "/admin/asuaec_json/json/get_majors_by_multi_campuses_and_college/" + campusesString + "/" + dynamic_college_data + "/" + grad_ugrad;

        if( dynamic_college_data == 1){
          $("input[name='college']").val("");
          $("#dynamic-edit-submitted-major").attr("disabled", true);
        }
        else{
          $("input[name='college']").val( $("#dynamic-edit-submitted-1-college").val()); // Save the

          // college in the webform "college" field
          // fill the degrees options
          $("#dynamic-edit-submitted-major").removeOption(/./);
          $("#dynamic-edit-submitted-major").addOption({'0':'- Select major of interest -'});
          $("#dynamic-edit-submitted-major").ajaxAddOption(degreeUrl , null, false);
          // $("#dynamic-edit-submitted-major").bind("change", function(){ degreeChanged(); })
          //$("#dynamic-edit-submitted-major").once().on("change", function(){
          $(once('visitformjs', "#dynamic-edit-submitted-major")).on('change', function() { // D10 change
            degreeChanged();
          });
        }
      }

      function degreeChanged(){
        if($("#dynamic-edit-submitted-major option:selected") == 0){
          $("input[name='major']").val("");
        }
        else{
          $("input[name='major']").val($ ("#dynamic-edit-submitted-major").val()); // Save the degees/major options in webform "major" field
        }
      }

      function populateCollegeOptions(campusArray) {
        var campusesString = '';
        for (i = 0; i < campusArray.length; i++) {
          if(i == 0) {
            campusesString += campusArray[i];
          } else {
            campusesString += '|' + campusArray[i];
          }
        }
        var interest = sessionStorage.getItem("interest");
        var person_type = sessionStorage.getItem("persontype");
        // console.log("interest: " + interest);
        // console.log("person type: " + person_type);

        //var grad_ugrad = 'ugrad';
        if(person_type == "Graduate student") {
          grad_ugrad = 'grad';
        } else {
          grad_ugrad = 'ugrad';
        }

        // Display College/Major dropdown lists only for Grad
        if(person_type == "Graduate student") {
          if(campusArray.length > 0){
            var college_data = $("input[name='submitted[college]']").val();
            var major_data =  $("input[name='submitted[major]']").val();

            // check if the college field is empty
            if($('#dynamic-edit-submitted-1-college').length == 0) {
              $("input[name='college']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="college-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select college of interest</label><select id="dynamic-edit-submitted-1-college" class="custom-select form-select form-control" name="college1"></select></div>');
            }
            if($('#dynamic-edit-submitted-major').length == 0) {
              $("input[name='major']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="major-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select major of interest</label><select id="dynamic-edit-submitted-major" class="custom-select form-select form-control" name="degree1"></select></div>');
            }

            // fill the college options
            var collUrl = "/admin/asuaec_json/json/get_colleges_by_multi_campuses_and_interest/" + campusesString + "/" + interest + "/" + grad_ugrad;
            $("#dynamic-edit-submitted-1-college").removeOption(/./);
            $("#dynamic-edit-submitted-1-college").addOption({'0':'- Select college of interest -'});
            $("#dynamic-edit-submitted-1-college").ajaxAddOption(collUrl , null, false);

            // Bind
            // $("#dynamic-edit-submitted-1-college").bind("change", function(){ collegeChanged(); })
            //$("#dynamic-edit-submitted-1-college").once().on("change", function(){
            $(once('visitformjs', "#dynamic-edit-submitted-1-college")).on('change', function() { // D10 change
              collegeChanged(campusesString);
            });

          }  // END of if(campus_data.length > 0)
        } // END OF if(person_type == "Graduate Student")
      } // END OF function populateCollegeOptions()

      //} // END OF if(ptypelist == 'y')


      /////////////////
      // Add more JS
      /////////////////
      // Function to check if #edit-parent2 has values
      function checkParent2Values() {
        let hasValue = false;
        $('#edit-parent2 input, #edit-parent2 select').each(function() {
          if ($(this).val().trim() !== '') {
              hasValue = true;
              return false; // Exit loop early if a value is found
          }
        });

        if (hasValue) {
          $('#edit-parent2').show();
          $('#addmore').hide();
          $('#hide-parent2').show();
          $('#edit-hide-parent-guardian-2').show();
        } else {
          $('#edit-parent2').hide();
          $('#addmore').show();
          $('#hide-parent2').hide();
          $('#edit-hide-parent-guardian-2').hide();
        }
      }

      // Initial check on page load
      checkParent2Values();

      // Add More button click event
      $("#addmore").bind("click", function() { 
        $('#edit-parent2').show();
        $('#addmore').hide();
        $('#hide-parent2').show();
        $('#edit-hide-parent-guardian-2').show();
      });
      // Hide Parent button click event
      $("#hide-parent2").bind("click", function() { 
        $('#edit-parent2 input, #edit-parent2 select').val(''); // Clear all fields
        checkParent2Values(); // Re-check visibility
      });


      /////////////////////////////////////////////////////////////
      // Add required mark
      /////////////////////////////////////////////////////////////

      //----------------------------------------------------------------------//
      //-------- Add required mark to State field based on Country -----------//
      $('select[name="country"]', context).on('change', function() {
        selectedValue = this.value;
        // console.log(selectedValue);
        if(selectedValue == 'US') {
          // $('label[for="edit-state"]').addClass('js-form-required');
          if ($('#state-required-svg').length == 0) {
            $('label[for="edit-state"]').prepend('<svg id="state-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>');
          }
        } else {
          // $('label[for="edit-state"]').removeClass('js-form-required');
          if ($('#state-required-svg').length > 0) {
            $('#state-required-svg').remove();
          }

        }
      });
      //$('select[name="country"]', context).once('visit').change();
      $(once('visitformjs', 'select[name="country"]')).change(); // D10 change


      /////////////////////////////////////////////////////////////
      // Turn off autocomplete
      /////////////////////////////////////////////////////////////
//      $('input#edit-email-address-additional').attr('autocomplete', 'new-password');
      $('input[name="email_address_additional"]').attr('autocomplete', 'new-password');
      $('input#edit-parent1-fname').attr('autocomplete', 'new-password');
      $('input#edit-parent1-lname').attr('autocomplete', 'new-password');
      $('input#edit-phone').attr('autocomplete', 'new-password');
      $('input#edit-phone').attr('autocomplete', 'new-password');
      $('select[name="hsstate"]').attr('autocomplete', 'new-password');
      $('select#dynamic-hs-city').attr('autocomplete', 'new-password');
//      $('input#edit-parent1-email').attr('autocomplete', 'new-password');
//      $('input#edit-parent2-email').attr('autocomplete', 'new-password');
      $('input[name="parent1_email"]').attr('autocomplete', 'new-password');
      $('input[name="parent1_fname"]').attr('autocomplete', 'new-password');
      $('input[name="parent1_lname"]').attr('autocomplete', 'new-password');
      $('input[name="parent2_email"]').attr('autocomplete', 'new-password');
      $('input[name="parent2_fname"]').attr('autocomplete', 'new-password');
      $('input[name="parent2_lname"]').attr('autocomplete', 'new-password');

      /////////////////////////////////////////////////////////////
      // Validation
      /////////////////////////////////////////////////////////////

      // Visit form
      //$('#webform-submission-visit-form-node-24-add-form').once().submit(function(e){
      $(once('visitformjs', '#webform-submission-visit-form-node-24-add-form')).submit(function(e) { // D10 change   
        let alertMessage = '';
        let retFalse = false;

        // Get person type from URL param
        let urlParams = new URLSearchParams(window.location.search);

        let ptypelist = urlParams.get('ptypelist');

        if(ptypelist == 'y') {
          if($("#dynamic-ptype").val() == '0') {
            $("#dynamic-ptype").addClass('is-invalid');
            alertMessage += "Please select one from I am a .. \r";
            //retFalse = true;
            alert(alertMessage);
            // Smoothly scroll to the element with a duration of 500 milliseconds
            $('html, body').animate({
              scrollTop: $("#dynamic-ptype").offset().top - 200
            }, 500);
            return false;
          }
        }

        let personTypeFromURL = urlParams.get('ptype');

        //------------------------------------------------------------------------------//
        //--------- For Grad, alert if the college and major fields are empty ----------//
        if(personTypeFromURL == 'Graduate student') {
          // College of interest
          if($('select#dynamic-edit-submitted-1-college > option:selected').val() == '0') {
            // alert('Please select college of interest.');
            alertMessage += "Please select college of interest.\r";
            $('select#dynamic-edit-submitted-1-college').addClass('required error is-invalid');
            // return false;
            retFalse = true;
          }
          // Major of interest
          if($('select#dynamic-edit-submitted-major > option:selected').val() == '0') {
            // alert('Please select major of interest.');
            alertMessage += "Please select major of interest.\r";
            $('select#dynamic-edit-submitted-major').addClass('required error is-invalid');
            // return false;
            retFalse = true;
          }
        }


        //------------------------------------------------------------------------------//
        // Email address
        // NOTE: Additional email field appears only for Grad
        // NOTE: Additional email shouldn't include the same email address from Student's email address
        // NOTE: Up to 2 email addresses for Additional email field
        let student_email = $('input[name="email_address"]').val();

        if(personTypeFromURL == 'Graduate student') {
          let additional_email = $('input[name="email_address_additional"]').val();
          // console.log("additional_email:", additional_email);

          // Check if additional_email includes student_email
          if (student_email != '' && additional_email.toLowerCase().indexOf(student_email) >= 0) {
            alertMessage += "Please don't include the same email address as Student's email address.\r";
            $('input[name="email_address_additional"]').addClass('required error is-invalid');
            retFalse = true;
          }

          // Check no more than 2 email addresses for Additional email field
          // It should include only 1 comma.
          let count = (additional_email.match(/,/g) || []).length;
          if(count > 1) {
            alertMessage += "Please don't include more than 2 email addresses.\r";
            $('input[name="email_address_additional"]').addClass('required error is-invalid');
            retFalse = true;
          }
        } else if (personTypeFromURL == 'Other') {
          // Nothing to validate for "Other"
        } else { // Ugrad
          // Get Parent1 and Parent2 email addresses
          let parent1_email = $('input[name="parent1_email"]').val();
          let parent2_email = $('input[name="parent2_email"]').val();
          if(parent1_email != '' && parent1_email != undefined) {
            // if parent email address is the same as Student email address
            if(parent1_email == student_email) {
              alertMessage += "Please don't enter the same email address as Student's email address.\r";
              $('input[name="parent1_email"]').addClass('required error is-invalid');
              retFalse = true;
            }
          }
          if(parent2_email != '' && parent2_email != undefined) {
            if(parent2_email == student_email) {
              alertMessage += "Please don't enter the same email address as Student's email address.\r";
              $('input[name="parent2_email"]').addClass('required error is-invalid');
              retFalse = true;
            }
          }
          if(parent1_email != '' && parent1_email != undefined && parent2_email != '' && parent2_email != undefined) {
            if(parent1_email == parent2_email) {
              alertMessage += "Please don't enter the same email address.\r";
              $('input[name="parent2_email"]').addClass('required error is-invalid');
              retFalse = true;
            }
          }
        }

        if(retFalse == true) {
          alert(alertMessage);
          return false;
        }
      });


      /////////////////////////////////////////////////////////////
      // Remove error class on change event
      /////////////////////////////////////////////////////////////
      // HS fields
      $('select#edit-hsstate').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
        // If "International school" is selected, hide the following 2 dropdowns (HS city and HS name)
        if ( $( this ).val() == 'international' ) {
          $('.form-item-hscity').hide();
          $('.form-item-hsname').hide();
        } else {
          $('.form-item-hscity').show();
          $('.form-item-hsname').show();
        }
      });
      $('select#dynamic-hs-city').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      $('select#dynamic-hs-name').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // College and major for Grad
      $('select#dynamic-edit-submitted-1-college').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      $('select#dynamic-edit-submitted-major').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // email address
      $('input[name="email_address"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // parent1_email
      $('input[name="parent1_email"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // parent2_email
      $('input[name="parent2_email"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // Additional email addresses
      $('input[name="email_address_additional"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // College dropdown (For Grad only)
      $('input[name="college1"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // Major dropdown (For Grad only)
      $('input[name="degree1"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });
      // Parent email address
      $('input[name="parent1_email"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
          $('.form-item-parent1-email > .invalid-feedback').remove();
        }
        enableSubmitBtn();
      });
      $('input[name="parent2_email"]').on('change', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
          $('.form-item-parent2-email > .invalid-feedback').remove();
        }
        enableSubmitBtn();
      });
      $('input[name="phone"]').on('input', function() {
        if ( $( this ).hasClass( "is-invalid" ) ) {
          $( this ).removeClass('required error is-invalid');
        }
        enableSubmitBtn();
      });

      function enableSubmitBtn() {
        $("input#edit-actions-submit").val('I agree / Submit');
        $("input#edit-actions-submit").removeClass('disabled');
      }


      // (2/2/2026)
      function applyHsAndInstitutionVisibility() {
        const urlParams = new URLSearchParams(window.location.search);
        const cSid = urlParams.get('c-sid');

        const fieldsetHs = document.getElementById('edit-high-school-you-are-currently-attending');
        const fieldsetInst = document.getElementById('edit-set-us-institution');
        const fieldsetParents = document.getElementById('edit-parentsinformation');

        const visitorType = ($('input[name="visitor_type"]').val() || '').trim();


        // If c-sid exists, always hide both
        if (cSid !== null && cSid !== "null" && cSid.trim() !== "") {
          if (fieldsetHs) $(fieldsetHs).hide();
          if (fieldsetInst) $(fieldsetInst).hide();
          if (fieldsetParents) $(fieldsetParents).hide();
          return;
        }

        // HS fieldset rules
        if (fieldsetHs) {
          if (visitorType === 'Graduate student' || visitorType === 'College transfer') {
            $(fieldsetHs).hide();
          } else {
            $(fieldsetHs).show();
          }
        }

        // Institution fieldset rules
        if (fieldsetInst) {
          if (visitorType === 'College transfer') {
            $(fieldsetInst).show();
            $(fieldsetHs).hide();
          } else {
            $(fieldsetInst).hide();
          }
        }

        // Parents info fieldset rules
        if (fieldsetParents) {
          if (visitorType === 'Graduate student') {
            $(fieldsetParents).hide();
          } else {
            $(fieldsetParents).show();
          }
        }
      }

    }
  };
})(jQuery, Drupal, drupalSettings);
