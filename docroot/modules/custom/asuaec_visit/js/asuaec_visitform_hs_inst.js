(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.hsjs = {
    attach: function (context, settings) {

      /////////////////////////////////////////////////////////////
      // High school and institute JS
      /////////////////////////////////////////////////////////////

      /**
       * Populate dynamic Select List from Web service
       *
       *  Used for:
       *  - Degree section: Area of interest -> Program of interest (Degrees)
       *  - High school section: City -> State -> HS name
       *  - Institution section: City -> State -> Institution name
       *  - Entry term dropdown list
       *
       * @param jsonUrl -- For example: "https://admission-asu-csdev4.ddev.site/admin/asuaec_rfi/json/degrees/ground/ugrad/"
       * @param appendToElement -- For example: "#dynamic-edit-submitted-major"
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
//            if (notListed) {
//              var div_data = "<option value='other'>--Not listed--</option>";
//              $(div_data).appendTo(appendToElement);
//            }
            var appended_data = '';
            var i = 0;
            $.each(data, function (k, v) {
              i++;
              var div_data = '<option value="' + k + '">' + v + '</option>';
              $(div_data).appendTo(appendToElement);
              appended_data = appended_data + div_data;
            });
            // Sort by name (text) for HS and Institution
            if(appendToElement == "#dynamic-hs-name" || appendToElement == "#dynamic-iname") {
              var options = $(appendToElement + ' option');
//              console.log("options:", options);
              var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
//              console.log("arr:", arr);
              arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
//              console.log("arr2:", arr);
              options.each(function(i, o) {
                o.value = arr[i].v;
                $(o).text(arr[i].t);
              });

              // Add "Not listed" at the top.
              if (notListed) {
                var option_notlisted = "<option value='other'>--Not listed--</option>";
                $(option_notlisted).prependTo(appendToElement);
              }
              // Move "Select..." at the top.
              var option_select = $(appendToElement + ' option[value="0"]');
              option_select.remove();
              option_select.prependTo(appendToElement); 
              $(appendToElement).val('0'); // Select... will be selected to begin with.
            }

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




      ////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Beginning of main flow
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////

      // High school section (in Step 3)

      //--- HS city
      if ($('#dynamic-hs-city').length == 0) {
        $('input[name=hscity]').before("<select id='dynamic-hs-city' class='form-select custom-select'><option value='0'>Select...</option></select>");
      }
      // --- HS name
      if ($('#dynamic-hs-name').length == 0) {
        $('input[name=hsname]').before("<select id='dynamic-hs-name' class='form-select custom-select' ><option value='0'>Select...</option></select>");
      }

      // if High school state is '0',
      // then, remove options from HS city and HS name dropdown lists.
      if(($('select[name="hsstate"]').val() == '0')) {
        // Clear HS city dropdown list
        var selectListId = '#dynamic-hs-city';
        var textFieldIdtoBeClearedArray = ['input[name="hscity"]'];
        var selectOptionIdtoBeClearedArray = [];
        var textFieldNametoBeClearedArray = ['hscity'];
        clearSelectList(selectListId, textFieldIdtoBeClearedArray, selectOptionIdtoBeClearedArray, textFieldNametoBeClearedArray);

        // Clear HS name dropdown list
        var selectListId2 = '#dynamic-hs-name';
        var textFieldIdtoBeClearedArray2 = ['input[name="hsname"]'];
        var selectOptionIdtoBeClearedArray2 = [];
        var textFieldNametoBeClearedArray2 = ['hsname'];
        clearSelectList(selectListId2, textFieldIdtoBeClearedArray2, selectOptionIdtoBeClearedArray2, textFieldNametoBeClearedArray2);
      }
      // If select options saved in sessionStorage, add the options.
      else {

        // If state is selected, populate city dropdown list

        // // Option A - Didn't work.
        // $('select[name=hsstate]').trigger('change');
        //
        // $( document ).ajaxComplete(function() { //<-- This didn't work.
        //
        //     var textField = 'hscity';
        //     var selectedVal = sessionStorage['selectedval_' + textField] || '';
        //     if(selectedVal != '') {
        //         $(elementSelector).val(selectedVal);
        //     }
        // });

        // Option B - Works
        // If select options saved in sessionStorage, add the options.
        var elementSelector = '#dynamic-hs-city';
        var options = sessionStorage.getItem('selectdata_' + elementSelector) !== null ? sessionStorage.getItem('selectdata_' + elementSelector) : '';
        if(options != '') {
          $(elementSelector).append(options);
        }

        // If select options saved in sessionStorage, add the options.
        var textField = 'hscity';
        var selectedVal = sessionStorage.getItem('selectedval_' + textField) !== null ? sessionStorage.getItem('selectedval_' + textField) : '';
        if(selectedVal != '') {
          $(elementSelector).val(selectedVal);
        }
      }

      // --- HS name
      // If select options saved in sessionStorage, add the options.
      if($('#dynamic-hs-city').val() != '0') {

        // Option B - Works
        // If select options saved in sessionStorage, add the options.
        var elementSelector = '#dynamic-hs-name';
        var options = sessionStorage.getItem('selectdata_' + elementSelector) !== null ? sessionStorage.getItem('selectdata_' + elementSelector) : '';
        if(options != '') {
          $(elementSelector).append(options);
        }

        // If select options saved in sessionStorage, add the options.
        var textField = 'hsname';
        var selectedVal = sessionStorage.getItem('selectedval_' + textField) !== null ? sessionStorage.getItem('selectedval_' + textField) : '';
        if(selectedVal != '') {
          $(elementSelector).val(selectedVal);
        }
      }

      // Institution section (Step 3)

      // --- Institution city
      if ($('#dynamic-icity').length == 0) {
        $('input[name=icity]').before("<select id='dynamic-icity' class='form-select custom-select'><option>Select...</option></select>");
      }
      // --- Institution name
      if ($('#dynamic-iname').length == 0) {
        $('input[name=iname]').before("<select id='dynamic-iname' class='form-select custom-select'><option>Select...</option></select>");
      }

      // if Institution state is '0',
      // then, remove options from Institution city and Institution name dropdown lists.
      if(($('select[name="istate"]').val() == '0')) {
        // Clear HS city dropdown list
        var selectListId = '#dynamic-icity';
        var textFieldIdtoBeClearedArray = ['input[name="icity"]'];
        var selectOptionIdtoBeClearedArray = [];
        var textFieldNametoBeClearedArray = ['icity'];
        clearSelectList(selectListId, textFieldIdtoBeClearedArray, selectOptionIdtoBeClearedArray, textFieldNametoBeClearedArray);

        // Clear HS name dropdown list
        var selectListId2 = '#dynamic-iname';
        var textFieldIdtoBeClearedArray2 = ['input[name="iname"]'];
        var selectOptionIdtoBeClearedArray2 = [];
        var textFieldNametoBeClearedArray2 = ['iname'];
        clearSelectList(selectListId2, textFieldIdtoBeClearedArray2, selectOptionIdtoBeClearedArray2, textFieldNametoBeClearedArray2);
      }
      // If select options saved in sessionStorage, add the options.
      else {

        // If state is selected, populate city dropdown list

        // Option B - Works
        // If select options saved in sessionStorage, add the options.
        var elementSelector = '#dynamic-icity';
        var options = sessionStorage.getItem('selectdata_' + elementSelector) !== null ? sessionStorage.getItem('selectdata_' + elementSelector) : '';
        if(options != '') {
          $(elementSelector).append(options);
        }

        // If select options saved in sessionStorage, add the options.
        var textField = 'icity';
        var selectedVal = sessionStorage.getItem('selectedval_' + textField) !== null ? sessionStorage.getItem('selectedval_' + textField) : '';
        if(selectedVal != '') {
          $(elementSelector).val(selectedVal);
        }
      }

      // --- Institution name
      // If select options saved in sessionStorage, add the options.
      if($('#dynamic-icity').val() != '0') {

        // Option B - Works
        // If select options saved in sessionStorage, add the options.
        var elementSelector = '#dynamic-iname';
        var options = sessionStorage.getItem('selectdata_' + elementSelector) !== null ? sessionStorage.getItem('selectdata_' + elementSelector) : '';
        if(options != '') {
          $(elementSelector).append(options);
        }

        // If select options saved in sessionStorage, add the options.
        var textField = 'iname';
        var selectedVal = sessionStorage.getItem('selectedval_' + textField) !== null ? sessionStorage.getItem('selectedval_' + textField) : '';
        if(selectedVal != '') {
          $(elementSelector).val(selectedVal);
        }
      }

      // Hide webform text fields
      // $('input[name=program_of_interest_text]').hide();
      $('input[name=hscity]').hide();
      $('input[name=hsname]').hide();
      $('input[name=icity]').hide();
      $('input[name=iname]').hide();
      // $('input[name=entry_term_text]').hide();





      //--------------------------------------------------------------------------------------------------------//
      // On changes

      //---------------------------------
      // High school section (in Step 3)

      // High school state
      //$('select[name="hsstate"]', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', 'select[name="hsstate"]')).on('change', function() { // D10 change  
        // Get selected value: HS state
        selectedValue = this.value;
        if(!(selectedValue == '' || selectedValue == '0')) {
          jsonUrl = origin + "/admin/asuaec_webform_optionsdata/json/cities/hs/" + selectedValue;
          appendToElement = "#dynamic-hs-city";
          populateSelectList(jsonUrl, appendToElement, false, ['input[name="hscity"]', 'input[name="hsname"]'], ['#dynamic-hs-name'], ['hscity', 'hsname']);
        }
      });

      // High school city
      //$('#dynamic-hs-city', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', '#dynamic-hs-city')).on('change', function() { // D10 change   
        // Get selected value: HS state and city
        selectedValue = $('select[name="hsstate"]').val();
        selectedValue2 = this.value;
        if(!(selectedValue == '' || selectedValue == '0')) {
          // Populate next dropdown list
          jsonUrl = origin + "/admin/asuaec_webform_optionsdata/json/names/hs/" + selectedValue + '/' + selectedValue2;
          appendToElement = "#dynamic-hs-name";
          populateSelectList(jsonUrl, appendToElement, true, ['input[name="hsname"]'], [], ['hsname']);
        }
        // Copy selected value into Webform text field.
        $('input[name="hscity"]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_hscity", this.value);
      });

      // High school name
      //$('#dynamic-hs-name', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', '#dynamic-hs-name')).on('change', function() { // D10 change  
        // Copy selected value into Webform text field.
        $('input[name="hsname"]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_hsname", this.value);
      });

      //-----------------------------------
      // Institution section

      // Institution state
      //$('select[name="istate"]', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', 'select[name="istate"]')).on('change', function() { // D10 change  
        // Get selected value: HS state
        selectedValue = this.value;
        if(!(selectedValue == '' || selectedValue == '0')) {
          jsonUrl = origin + "/admin/asuaec_webform_optionsdata/json/cities/inst/" + selectedValue;
          appendToElement = "#dynamic-icity";
          populateSelectList(jsonUrl, appendToElement, false, ['input[name="icity"]', 'input[name="iname"]'], ['#dynamic-iname'], ['icity', 'iname']);
        }
      });

      // Institution city
      //$('#dynamic-icity', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', '#dynamic-icity')).on('change', function() { // D10 change  
        // Get selected value: HS state and city
        selectedValue = $('select[name="istate"]').val();
        selectedValue2 = this.value;
        if(!(selectedValue == '' || selectedValue == '0')) {
          jsonUrl = origin + "/admin/asuaec_webform_optionsdata/json/names/inst/" + selectedValue + '/' + selectedValue2;
          appendToElement = "#dynamic-iname";
          populateSelectList(jsonUrl, appendToElement, true, ['input[name="iname"]'], [], ['iname']);
        }
        // Copy selected value into Webform element
        $('input[name="icity"]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_icity", this.value);
      });

      // Institution name
      //$('#dynamic-iname', context).once('rfi').on('change', function() {
      $(once('visitformhsjs', '#dynamic-iname')).on('change', function() { // D10 change   
        // Write value to the original text field.
        $('input[name="iname"]').val(this.value);
        // Cache selection in sessionStorage
        sessionStorage.setItem("selectedval_iname", this.value);
      });


    }
  };
})(jQuery, Drupal, drupalSettings);
