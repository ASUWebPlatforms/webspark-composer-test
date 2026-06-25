(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.rfi = {
    attach: function (context, settings) {

      /**
       * Helping function
       *
       * @param sParam
       * @returns {string}
       */
      function getURLParam(sParam) {
        var sPageURL = window.location.search.substring(1);
        //console.log(sPageURL);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
          var sParameterName = sURLVariables[i].split('=');
          //console.log(sParameterName);
          if (sParameterName[0] == sParam)
          {
              return sParameterName[1];
              break;
          }
        }
        return '';
      }

      /**
       * Helping function
       * Add 1 select option and select it.
       *
       * @param appendToElement
       * @param k
       * @param v
       */
      function addOneSelectOptionAndSelectIt (appendToElement, k, v) {
        var div_data = '<option value="' + k + '" selected>' + v + '</option>';
        $(div_data).appendTo(appendToElement);
      }

      /**
       * When came from Degree search, grab values from URL params via hidden fields
       */
      function grabURLparams() {
        //	Rules:
        //	- college=GRXX is grad. prog=UGXX is ugrad.
        //	- For Grad, Always On campus
        //	- For Ugrad, when there is location=ONLNE, it could be On campus or Online. Degree could be offered only ONLNE.
        //	- For Ugrad, when there is no location=ONLNE, it should be On Campus.

        // var plan = getURLParam('plan');	// Plan code
        // var name = getURLParam('name');	// Degree name ex: Applied%20Science%20%28Internet%20and%20Web%20Development%29
        // var prog = getURLParam('prog');	// 4 character code for college ex: UGHI
        // var contact = getURLParam('contact'); // ex: herbergeradvising@asu.edu

        if($('input[name="plan"]').val() != undefined) { // It becomes undefined in Step 2 and 3.
          // We are in Step 1.

          if($('input[name="plan"]').val() != '[current-page:query:plan]') {
            // Use hidden fields
            var plan = $('input[name="plan"]').val();	// Plan code
            var name = $('input[name="name"]').val();	// Degree name ex: Applied%20Science%20%28Internet%20and%20Web%20Development%29
            var prog = $('input[name="prog"]').val();	// 4 character code for college ex: UGHI
            // Use URL params
            // var plan = getURLParam('plan');	// Plan code
            // var name = getURLParam('name');	// Degree name ex: Applied%20Science%20%28Internet%20and%20Web%20Development%29
            // var prog = getURLParam('prog');	// 4 character code for college ex: UGHI

            // var cameFromDegreeSearch = true; -- Not used. 5/20/2022.

            // Display degree name and hide other items.
            var college = prog;
            // Area of interest -> Program of interest (degrees) (Step 1)
            if ($('#dynamic-program-interest').length == 0) {
              $('input[name=program_of_interest_text]').before("<select id='dynamic-program-interest' class='form-select custom-select'><option value='0'>Select...</option></select>");
            }
            if( $('#dynamic-program-interest > option').length == 1) {
              addOneSelectOptionAndSelectIt('#dynamic-program-interest', plan, name);
              $('#dynamic-program-interest').trigger('change');
            }
            $('input[name="program_of_interest_text"]').val(plan);
            $('input[name="program_of_interest_text"]').hide();

            // Find out if it is grad/ugrad
            var strFirstTwo = prog.substring(0,2); // UG or GR
            // Grad
            if(strFirstTwo == "GR") {
              $('input[name="grad_ugrad"]').val('GRAD');
              $('select[name="student_type_options_default"]').val('Readmission');
              sessionStorage.setItem('selectedval_student_type_options_default', 'Readmission'); // Added on 5/11/2022.
              sessionStorage.setItem('selectedval_program_of_interest_text', plan); // Added on 5/11/2022.
              sessionStorage.setItem('selectedval_campus_options', 'GROUND'); // Added on 5/11/2022.
            }
            // Ugrad
            if(strFirstTwo == "UG") {
              $('input[name="grad_ugrad"]').val('UGRAD');
            }
            // If it is Ugrad, remove Grad option
            if(strFirstTwo == "UG") {
              //$('select[name=student_type_options_default] option[value=Readmission]').hide(); //<-- Not working in Safari.(5/14/2024)
              $('select[name=student_type_options_default] option[value=Readmission]').remove();
            } else {
              $('select[name=student_type_options_default] option[value=Readmission]').show();
            }

          } // END OF if($('input[name="plan"]').val() != '[current-page:query:plan]')
        } // END OF if($('input[name="plan"]').val() != undefined) { // It becomes undefined in Step 2 and 3.

      } // END OF function grabURLparams()


      //--------------------------------------------------------------------------------------------
      //--------------------------------------------------------------------------------------------


      /**
       * Populate dynamic Select List from Web service
       *
       *  Used for:
       *  - Degree section: Area of interest -> Program of interest (Degrees)
       *  - High school section: City -> State -> HS name
       *  - Institution section: City -> State -> Institution name
       *  - Entry term dropdown list
       *
       * @param jsonUrl -- For example: "https://admission-asu-csdev4.ddev.site/admin/asuaec_rfib2_directpost/json/degrees/ground/ugrad/"
       * @param appendToElement -- For example: "#dynamic-program-interest"
       * @param notListed -- Boolean: If you add "--Not listed--" option or not.
       * @param textFieldIdtoBeClearedArray
       * @param selectOptionIdtoBeClearedArray
       * @param textFieldNametoBeClearedArray -- Used for clearSelectList. Added for clearing sessionStorage variable.
       */
      function populateSelectList(jsonUrl, appendToElement, notListed = false,
                                  textFieldIdtoBeClearedArray = array(), selectOptionIdtoBeClearedArray = array(), textFieldNametoBeClearedArray = array()) {
          // Populate select list
          $.ajax({
              type: "GET",
              url: jsonUrl,
              dataType: "json",
              async: "true",
              success: function (data) {
                  clearSelectList(appendToElement, textFieldIdtoBeClearedArray, selectOptionIdtoBeClearedArray, textFieldNametoBeClearedArray);
                  if (notListed) {
                      var div_data = "<option value='other'>--Not listed--</option>";
                      $(div_data).appendTo(appendToElement);
                  }
                  var appended_data = '';
                  var i = 0;
                  $.each(data, function (k, v) {
                      i++;
                      var div_data = '<option value="' + k + '">' + v + '</option>';
                      $(div_data).appendTo(appendToElement);
                      appended_data = appended_data + div_data;
                  });
                  // Cache it in sessionStorage
                  sessionStorage.setItem("selectdata_" + appendToElement,  appended_data); // only strings

                  // console.log("i: " , i); // "i" indicates how many results there are.
                  // Insert "Not available" message for Grad term if there are no results.
                  if(appendToElement == '#dynamic-term') {
                      if(i == 0) {
                          $('#dynamic-term').hide();
                          if($('#EntryTerm').length == 0) {
                              $('.form-item-entry-term-text').append('<textarea name="EntryTerm" id="EntryTerm" class="form-control" required="" disabled="" placeholder="The program you are interested in is not accepting new students at this time. Please select a different program of interest, and then select the semester you would like to start." style="height: 164px;"></textarea>');

                              // If came from Degree Search, and "Not available" term, then, change Previous button to redirect. 5/20/2022
                              if(sessionStorage.getItem('cameFromDegreeSearch') == 'true') {
                                  $('input[value="< Previous"]').click(function() {
                                      window.location.href = '/future-student-request';
                                      return false;
                                  });
                              }

                          }
                      } else {
                          $('#dynamic-term').show();
                          // console.log('test2');
                          // // Clear selection here? todo
                          // $('#dynamic-term option:selected').val('0').change();

                      }
                  }

              }
          });
      }

      /**
       * Clear Select List.
       * Also, clear sessionStorage variable.
       *
       * @param selectListId
       * @param textFieldIdtoBeClearedArray
       * @param selectOptionIdtoBeClearedArray
       * @param textFieldNametoBeClearedArray -- Use it to clear sessionStorage variable
       */
      function clearSelectList(selectListId, textFieldIdtoBeClearedArray = array(),  selectOptionIdtoBeClearedArray = array(), textFieldNametoBeClearedArray = array()) {
          $(selectListId)
              .find('option')
              .remove()
              .end()
              .append('<option value="0">Select...</option>')
              .val('0')
          ;
          textFieldIdtoBeClearedArray.forEach(function (textField) {
              if (textField != '') {
                  $(textField).val('');
              }
          });
          if (selectOptionIdtoBeClearedArray.length > 0) {
              selectOptionIdtoBeClearedArray.forEach(function (selectOption) {
                  $(selectOption)
                      .find('option')
                      .remove()
                      .end()
                      .append('<option value="0">Select...</option>')
                      .val('0')
                  ;
              });
          }

          // Also clear sessionStorage variable
          sessionStorage.removeItem('selectdata_' + selectListId);
          textFieldNametoBeClearedArray.forEach(function (textField) {
              if (textField != '') {
                  sessionStorage.removeItem('selectedval_' + textField);
              }
          });
      }

      /**
       * Check and decide which ones should be required fields.
       * Place red dot for required fields.
       */
      function evaluateRequiredFields() {
        // Which applies to you?
        var campus_option = $('select[name="campus_options"] option:selected').val();
        // Student status dropdown
        var student_type = $('select[name="student_type_options_default"] option:selected').val();

        var svgCode = '<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-uB2ijBlZl7Wl" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-uB2ijBlZl7Wl">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg><!-- <span title="Required" class="fa fa-icon fa-circle uds-field-required"></span> Font Awesome fontawesome.com -->';

        if (campus_option == "ONLNE") {
          // Program of interest required
          if($('.js-form-item-program-of-interest-text > label > svg').length == 0) {
            $('.form-item-program-of-interest-text > label').prepend(svgCode);
          }
        } else { // On campus
          if(student_type == "Readmission") {
            // Program of interest required
            if($('.js-form-item-program-of-interest-text > label > svg').length == 0) {
                $('.form-item-program-of-interest-text > label').prepend(svgCode);
            }
          } else {
            // Program of interest NOT required
            if($('.js-form-item-program-of-interest-text > label > svg').length > 0) {
                $('.form-item-program-of-interest-text > label > svg').remove();
            }
          }
        }
      } // END OF function evaluateRequiredFields()

      /**
       * Populate Grad Entry term dropdown list
       */
      function populateTermDropdownList() {

        var targetSelectList = '#dynamic-term';
        // var selectedval_campus_options = sessionStorage['selectedval_campus_options'] || '';
        var selectedval_campus_options = sessionStorage.getItem('selectedval_campus_options');
        // var selectedval_student_type_options_default = sessionStorage['selectedval_student_type_options_default'] || '';
        var selectedval_student_type_options_default = sessionStorage.getItem('selectedval_student_type_options_default');

        if (selectedval_campus_options == 'GROUND' || selectedval_campus_options == 'NOPREF') {
          if (selectedval_student_type_options_default == 'Readmission') {
            // If there is no dropdown, add it. 7/6/2023
            if ($('#dynamic-term').length == 0) {
                $('input[name=entry_term_text]').before("<select id='dynamic-term' class='form-select custom-select'><option>Select...</option></select>");
            }
            // Grad Entry term
            $('#dynamic-term', context).on('change', function() {
                // Write value to the original text field.
                $('input[name=entry_term_text]').val(this.value);
                // Cache selection in sessionStorage
                sessionStorage.setItem("selectedval_entry_term_text", this.value);
            });

            // plancode = $('#dynamic-program-interest').val();
            // var plancode = $('input[name="program_of_interest_text"]').val();
            // var plancode = $('input[data-drupal-selector="edit-program-of-interest-text"]').val();
            var plancode = sessionStorage.getItem('selectedval_program_of_interest_text') !== null ? sessionStorage.getItem('selectedval_program_of_interest_text') : '';
            var jsonUrl = origin + '/admin/asuaec_rfib2_directpost/json/term/grad/' + plancode;
            var appended_data = populateSelectList(jsonUrl, targetSelectList, false, ['input[name=entry_term_text]'], [], ['entry_term_text']);
          }
        }
      } // END OF function populateTermDropdownList()


      function entryTermShowHide() {
        // Use manual JS conditionals instead of Webform's conditionals. - Added on 7/9/2023.
        if($('select[name="student_type_options_default"]').val() == 'Readmission') {

          //if ($('#edit-campus-options').val() != 'ONLNE') {
          if ($('select[name="campus_options"]').val() != 'ONLNE') {
            $('fieldset[data-drupal-selector="edit-grad-entry-term"]').show();
            $('.js-form-item-entry-term').hide();

          }
          else {
            $('fieldset[data-drupal-selector="edit-grad-entry-term"]').hide();
            $('.js-form-item-entry-term').hide();
          }

        } else {
          //if ($('#edit-campus-options').val() != 'ONLNE') {
          if ($('select[name="campus_options"]').val() != 'ONLNE') {
            $('fieldset[data-drupal-selector="edit-grad-entry-term"]').hide();
            $('.js-form-item-entry-term').show();

          } else {
            $('fieldset[data-drupal-selector="edit-grad-entry-term"]').hide();
            $('.js-form-item-entry-term').hide();
          }
        }
      }


      ////////////////////////////////////////////////////////////////////////////////////////////////////////////
      //--------------------------------------------------------------------------------------------------------//
      //--------------------------------------------------------------------------------------------------------//
      // Beginning of main flow
      //--------------------------------------------------------------------------------------------------------//
      //--------------------------------------------------------------------------------------------------------//
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // sessionStorage.clear();

      /* Webspark 2.10 update RFI form fix for Required red dot (2/15/2024) */
      $('.webform-submission-rfi-b2-form').addClass('uds-form');

      /**
       * Add +1 to phone number field to be by default
       */
      var ini_phone = $('input[name="phone"]').val();
      if(ini_phone == ''){
          $('input[name="phone"]').val('+1');
      }

      // Remove Term's message box (6/28/2024)
      $('#EntryTerm').remove();

      // Get UTM param
      $('input[name="utm_source"]').val(getURLParam('utm_source'));
      $('input[name="utm_medium"]').val(getURLParam('utm_medium'));
      $('input[name="utm_campaign"]').val(getURLParam('utm_campaign'));
      $('input[name="utm_term"]').val(getURLParam('utm_term'));
      $('input[name="utm_content"]').val(getURLParam('utm_content'));

      // Get host
      var origin = window.location.origin;   // Returns base URL (https://example.com)

      entryTermShowHide(); // 7/9/2023

      // Came from Degree Search
      let cameFromDegreeSearch = false;
      if(getURLParam('plan') == '') {
        $('input[name="plan"]').val('');	// Plan code
        $('input[name="name"]').val('');	// Degree name ex: Applied%20Science%20%28Internet%20and%20Web%20Development%29
        $('input[name="prog"]').val('');	// 4 character code for college ex: UGHI
      } else {
        cameFromDegreeSearch = true;
        $('input[name="came_from_degree_search"]').val('true');
        sessionStorage.setItem('cameFromDegreeSearch', 'true'); // Added on 5/20/2022.
      }
      // When go to Step 2, this JS runs again. And we cannot get to $('input[name="plan"]').val() since it is in Step 1. Therefore, just grab URL param each time.
      if(cameFromDegreeSearch == true) {
        // Grab Parameters.
        grabURLparams();
        $('.area-interest-empty').hide();

        // Commented out on 5/14/2024. The same things were already done in grabURLparams().
        // // 7/6/2023
        // // Find out if it is Grad or Ugrad
        // if($('input[name="prog"]').val().slice(0, 2) == 'GR') { // Grad
        //   // $('#edit-student-type-options-default').val('Readmission');
        //   $('select[name="student_type_options_default"]').val('Readmission');
        //   $('input[name="grad_ugrad"]').val('GRAD');
        //   // sessionStorage.setItem('selectedval_program_of_interest_text', this.value);
        //   // sessionStorage.setItem('selectedval_student_type_options_default', 'Readmission'); // Added on 5/13/2024
        //   // sessionStorage.setItem("selectedval_campus_options", 'GROUND'); // Added on 5/13/2024
        //   sessionStorage.setItem('selectedval_student_type_options_default', 'Readmission');
        // } else { // Ugrad
        //   // $('#edit-student-type-options-default').val('First Time Freshman');
        //   $('input[name="grad_ugrad"]').val('UGRAD');
        // }
        $('select[name="campus_options"]').val('NOPREF'); // Added on 7/9/2023.
        entryTermShowHide(); // Added on 7/9/2023.

      } // END OF if(cameFromDegreeSearch == true)
      else {

        //----------------------------------------------------------------------------------------------------//
        // Add dynamic select

        // Area of interest -> Program of interest (degrees) (Step 1)
        if ($('#dynamic-program-interest').length == 0) {
          $('input[name=program_of_interest_text]').before("<select id='dynamic-program-interest' class='form-select custom-select'><option value='0'>Select...</option></select>");
        }

        // If either "Which applies to you?" or "Select your student status" is '0',
        // then, remove options from program dropdown.
        if(($('select[name="campus_options"]').val() == '0') || ($('select[name="student_type_options_default"]').val() == '0')) {
          // Clear Program dropdown list
          var selectListId = '#dynamic-program-interest';
          var textFieldIdtoBeClearedArray = ['input[name="program_of_interest_text"]'];
          var selectOptionIdtoBeClearedArray = [];
          var textFieldNametoBeClearedArray = ['program_of_interest_text'];
          clearSelectList(selectListId, textFieldIdtoBeClearedArray, selectOptionIdtoBeClearedArray, textFieldNametoBeClearedArray);
        }
        else {
          // If select options saved in sessionStorage, add the options.
          var elementSelector = '#dynamic-program-interest';
          // var options = sessionStorage['selectdata_' + elementSelector] || '';
          var options = sessionStorage.getItem('selectdata_' + elementSelector) !== null ? sessionStorage.getItem('selectdata_' + elementSelector) : '';
          if(options != '') {
            $(elementSelector).append(options);
          }
          // If selected value saved in sessionStorage, select the option.
          var textField = 'program_of_interest_text';
          // var selectedVal = sessionStorage['selectedval_' + textField] || '';
          var selectedVal = sessionStorage.getItem('selectedval_' + textField) !==null ?  sessionStorage.getItem('selectedval_' + textField) : '';
          if(selectedVal != '') {
            $(elementSelector).val(selectedVal);
          }
        }

      } // END OF else OF if(cameFromDegreeSearch == true)


      //-------------------------
      // Entry term
      // if Grad
      if(sessionStorage.getItem('selectedval_student_type_options_default') == 'Readmission') {
        if ($('#dynamic-term').length == 0) {
          $('input[name=entry_term_text]').before("<select id='dynamic-term' class='form-select custom-select'><option>Select...</option></select>");
        }
        // Get plancode from sessionStorage
        var plancode = sessionStorage.getItem('selectedval_program_of_interest_text') !== null ? sessionStorage.getItem('selectedval_program_of_interest_text') : '';
        if( plancode != '') {
          populateTermDropdownList();
        }

        // If selected value saved in sessionStorage, select the option.
        var elementSelector = '#dynamic-term';
        var textField = 'entry_term_text';
        var selectedVal = sessionStorage.getItem('selectedval_' + textField) !== null ? sessionStorage.getItem('selectedval_' + textField) : '';
        if(selectedVal != '') {
          $( document ).ajaxComplete(function() {
            $(elementSelector).val(selectedVal); // This will select the previous selection if there is the option in the dropdown list.
            // Also need to update both hidden text field (2/18/2022)
            $('input[name=entry_term_text]').val(selectedVal);
          });
        }
      } // END OF if(sessionStorage['selectedval_student_type_options_default'] == 'Readmission')


      //------------------------------
      // Hide webform text fields
      $('input[name=program_of_interest_text]').hide();
      $('input[name=entry_term_text]').hide();
      $('input[name=hscity]').hide();
      $('input[name=hsname]').hide();
      $('input[name=icity]').hide();
      $('input[name=iname]').hide();


      // Remove card CSS class from .fieldset-wrapper
      $('fieldset[data-drupal-selector=edit-grad-entry-term] > .fieldset-wrapper').removeClass('card-body');



      //--------------------------------------------------------------------------------------------------------//
      // On changes

      //----------------------------------------
      // Area of interest (in Step 1)

      // Ground Ugrad
      //$('select[name="area_of_interest_ugrad"]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name="area_of_interest_ugrad"]')).on('change', function() {

          if(cameFromDegreeSearch != true) { // If didn't come from Degree Search
          // Get selected value: Area of interest Ugrad
          let selectedValue = $('select[name="area_of_interest_ugrad"]').val();
          if (!(selectedValue == '' || selectedValue == '0')) {
            let jsonUrl = origin + "/admin/asuaec_rfib2_directpost/json/degrees/ground/ugrad/" + selectedValue;
            let appendToElement = "#dynamic-program-interest";
            populateSelectList(jsonUrl, appendToElement, false, ['#edit-program-of-interest-text'], [], []);
          }
          // Clear term in JS Session variable
          sessionStorage.removeItem("selectedval_entry_term_text");
          // Reset Term
          entryTermShowHide();

          // Remove error class
          $(this).removeClass('is-invalid');
        }
      });

      // Ground Grad
      //$('select[name="area_of_interest_grad"]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name="area_of_interest_grad"]')).on('change', function() {

        if(cameFromDegreeSearch != true) { // If didn't come from Degree Search
          // Get selected value: Area of interest Ugrad
          let selectedValue = $('select[name="area_of_interest_grad"]').val();
          if (!(selectedValue == '' || selectedValue == '0')) {
            let jsonUrl = origin + "/admin/asuaec_rfib2_directpost/json/degrees/ground/grad/" + selectedValue;
            let appendToElement = "#dynamic-program-interest";
            populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
          }
          // Clear term in JS Session variable
          sessionStorage.removeItem("selectedval_entry_term_text");
          // Reset Term
          entryTermShowHide();

          // Remove error class
          $(this).removeClass('is-invalid');
        }
      });

      // Online Ugrad
      //$('select[name="area_of_interest_ugrad_online"]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name="area_of_interest_ugrad_online"]')).on('change', function() {

        if(cameFromDegreeSearch != true) { // If didn't come from Degree Search
          // Get selected value: Area of interest Ugrad
          let selectedValue = $('select[name="area_of_interest_ugrad_online"]').val();
          if (!(selectedValue == '' || selectedValue == '0')) {
            let jsonUrl = origin + "/admin/asuaec_rfib2_directpost/json/degrees/online/ugrad/" + selectedValue;
            let appendToElement = "#dynamic-program-interest";
            populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
          }
          // Clear term in JS Session variable
          sessionStorage.removeItem("selectedval_entry_term_text");
          // Reset Term
          entryTermShowHide();

          // Remove error class
          $(this).removeClass('is-invalid');
        }
      });

      // Online Grad
      //$('select[name="area_of_interest_grad_online"]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name="area_of_interest_grad_online"]')).on('change', function() {

        if(cameFromDegreeSearch != true) { // If didn't come from Degree Search
          // Get selected value: Area of interest Ugrad
          let selectedValue = $('select[name="area_of_interest_grad_online"]').val();
          if (!(selectedValue == '' || selectedValue == '0')) {
            let jsonUrl = origin + "/admin/asuaec_rfib2_directpost/json/degrees/online/grad/" + selectedValue;
            let appendToElement = "#dynamic-program-interest";
            populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
          }
          // Clear term in JS Session variable
          sessionStorage.removeItem("selectedval_entry_term_text");
          // Reset Term
          entryTermShowHide();

          // Remove error class
          $(this).removeClass('is-invalid');
        }
      });

      // Program of interest
      //$('#dynamic-program-interest', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', '#dynamic-program-interest')).on('change', function() {
        // Write value to the original text field.
        $('input[name=program_of_interest_text]').val(this.value);
        // Cache the selection
        sessionStorage.setItem('selectedval_program_of_interest_text', this.value);
        // Populate Entry term dropdown list
        populateTermDropdownList();
        // Clear term in JS Session variable
        sessionStorage.removeItem("selectedval_entry_term_text");

        // Remove error class
        $(this).removeClass('is-invalid');
      });

      // Grad Entry term
      //$('#dynamic-term', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', '#dynamic-term')).on('change', function() {
        // Write value to the original text field.
        $('input[name=entry_term_text]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_entry_term_text", this.value);

        // Remove error class
        $(this).removeClass('is-invalid');
      });

      // Default Entry term
      //$('select[name=entry_term]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name=entry_term]')).on('change', function() {
        // Write value to the original text field.
        $('input[name=entry_term_text]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_entry_term_text", this.value);

        // Remove error class
        $(this).removeClass('is-invalid');
      });

      // Other fields that need to retrieve it in Step 2
      // GROUND/ONLNE/NOPREF
      //$('select[name=campus_options]', context).once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'select[name=campus_options]')).on('change', function() {
//              console.log("Campus_options change event fired");
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_campus_options", this.value);
        // Clear dropdowwns
        evaluateRequiredFields();

        entryTermShowHide(); // Added on 7/9/2023.

        // Clear term in JS Session variable
        sessionStorage.removeItem("selectedval_entry_term_text");

        // Remove error class
        $(this).removeClass('is-invalid');
      });

      // First Time Freshman/Transfer/Readmission
      //$('select[name=student_type_options_default]', context).once('rfi').on('change', function() {
      $(once('rfi', 'select[name=student_type_options_default]')).on('change', function() {
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_student_type_options_default", this.value);
        if(this.value == 'Readmission') {
            $('input[name="grad_ugrad"]').val('GRAD');
            populateTermDropdownList(); // Added on 7/6/2023.
        } else {
            $('input[name="grad_ugrad"]').val('UGRAD');
        }
        evaluateRequiredFields();

        entryTermShowHide(); // Added on 7/9/2023.

        // Clear term in JS Session variable
        sessionStorage.removeItem("selectedval_entry_term_text");

        // Remove error class
        $(this).removeClass('is-invalid');
      });

      // Consent
      //$('input#edit-gdpr-consent-1').once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'input#edit-gdpr-consent-1')).on('change', function() {
        // Remove error class
        $(this).removeClass('error');
      });
      //$('input#edit-gdpr-consent-online-1').once('rfi').on('change', function() { // D10 change
      $(once('rfi', 'input#edit-gdpr-consent-online-1')).on('change', function() {
        // Remove error class
        $(this).removeClass('error');
      });



      //---------------------------------
      // Postal code (in Step 2) needs to rollover to Zip code in Step 3 (2/18/2022)
      //$('input[name=postal_code]', context).on('change', function() { // D10 change
      $(once('rfi', 'input[name=postal_code]')).on('change', function() {
          // Cache selection in sessionStorage
          sessionStorage.setItem("selectedval_postal_code", this.value);
      });

      // if($('input[name=zip_code]').val() == '') { //<---- I think that we need this.
          $('input[name=zip_code]').val(sessionStorage.getItem("selectedval_postal_code"));
      // }





      //----------------------------------------------------------------
      // Other setups

      // Add required dott mark
      var svgCode = '<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-uB2ijBlZl7Wl" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-uB2ijBlZl7Wl">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg><!-- <span title="Required" class="fa fa-icon fa-circle uds-field-required"></span> Font Awesome fontawesome.com -->';

      //$('.form-item-campus-options > label').once('rfi').prepend(svgCode); // D10 change
      $(once('rfi', '.form-item-campus-options > label')).prepend(svgCode);
      //$('.form-item-student-type-options-default > label').once('rfi').prepend(svgCode); // D10 change
      $(once('rfi', '.form-item-student-type-options-default > label')).prepend(svgCode);
      //$('.form-item-area-of-interest-empty > label').once('rfi').prepend(svgCode); // D10 change
      $(once('rfi', '.form-item-area-of-interest-empty > label')).prepend(svgCode);
      if($('.js-form-item-program-of-interest-text > label > svg').length == 0) {
          //$('.form-item-program-of-interest-text > label').once('rfi').prepend(svgCode); // D10 change
          $(once('rfi', '.form-item-program-of-interest-text > label')).prepend(svgCode);
      }
      //$('.form-item-entry-term > label').once('rfi').prepend(svgCode); // D10 change
      $(once('rfi', '.form-item-entry-term > label')).prepend(svgCode);

      //$('.form-item-area-of-interest-ugrad > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-area-of-interest-ugrad > label')).prepend(svgCode);
      //$('.form-item-area-of-interest-grad > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-area-of-interest-grad > label')).prepend(svgCode);
      //$('.form-item-area-of-interest-ugrad-online > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-area-of-interest-ugrad-online > label')).prepend(svgCode);
      //$('.form-item-area-of-interest-grad-online > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-area-of-interest-grad-online > label')).prepend(svgCode);
      //$('.form-item-postal-code > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-postal-code > label')).prepend(svgCode);
      //$('.form-item-citizenship-country > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-citizenship-country > label')).prepend(svgCode);
      //$('.form-item-gdpr-consent-i-consent > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-gdpr-consent-i-consent > label')).prepend(svgCode);

      //$('.form-item-entry-term-text > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-entry-term-text > label')).prepend(svgCode);

      //$('.form-item-hsstate > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-hsstate > label')).prepend(svgCode);
      //$('.form-item-hscity > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-hscity > label')).prepend(svgCode);
      //$('.form-item-hsname > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-hsname > label')).prepend(svgCode);
      //$('.form-item-istate > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-istate > label')).prepend(svgCode);
      //$('.form-item-icity > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-icity > label')).prepend(svgCode);
      //$('.form-item-iname > label').once('rfi').prepend(svgCode);
      $(once('rfi', '.form-item-iname > label')).prepend(svgCode);

      evaluateRequiredFields();


      // For dynamic dropdown, add is-invalid class to add red line when hidden field has is-invalid class when comes back from validation.
      // Program of interest
      if($('input[name="program_of_interest_text"]' ).hasClass( "is-invalid" )) {
          $('select#dynamic-program-interest').addClass("is-invalid");
      } else {
          $('select#dynamic-program-interest').removeClass("is-invalid");
      }

      // Entry term
      if($('input[name="entry_term_text"]' ).hasClass( "is-invalid" )) {
          $('select#dynamic-term').addClass("is-invalid");
      } else {
          $('select#dynamic-term').removeClass("is-invalid");
      }

      // When validation massage displays, need to populate entry term dropdown
      if($('div.alert.alert-danger').length > 0) {
        if ($('select[name="campus_options"]').val() != 'ONLNE') { // If it is not Online
          // if it is Grad Ground, check if program is selected
          if ($('select[name=student_type_options_default]').val() == 'Readmission') {
            populateTermDropdownList(); // Added on 7/6/2023.
            $('select#dynamic-term').show();
          }
        }
      }

      // Phone field
      $('input[name="phone"]').attr('placeholder','+1 (123) 456-7890');
      $('input[name="phone"]').keyup(function(){
        var val = $(this).val();
        var first_char = val.charAt(0);
        var sec_char = val.charAt(1);
        var flag_class = $('.iti__flag').attr('class').split(/\s+/);
        var flag_country = flag_class[1];
        if(flag_country == "iti__us"){
          if((first_char == '+') &&  (sec_char == "1")){
            $(this).val($(this).val().replace(/^[\+]?(\d{1})(\d{3,3})(\d{3})(\d+)\-?/g,'+1 ($2) $3-$4'));
          }
          else{ //append + at the beginning
            $(this).val('+1'+$(this).val());
            $(this).val($(this).val().replace(/^[\+]?(\d{1})(\d{3,3})(\d{3})(\d+)\-?/g,'+1 ($2) $3-$4'));

          }
        }
      });

      // Grad entry term - Add red boarder if the fieldset has is-invalid. (6/19/2024)
      if ($("#edit-grad-entry-term").hasClass('is-invalid')) {
        $("#dynamic-term").addClass('is-invalid');
      }


    }
  };
})(jQuery, Drupal, drupalSettings);
