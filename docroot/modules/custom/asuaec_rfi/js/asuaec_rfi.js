(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.rfi = {
        attach: function (context, settings) {
            // **MOVED TO ASSET INJECTOR ON 10/22/2025**
            // console.log("very beginning");

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
             * @param jsonUrl -- For example: "https://admission-asu-csdev4.ddev.site/admin/asuaec_rfi/json/degrees/ground/ugrad/"
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
                        // plancode = $('#dynamic-program-interest').val();
                        // var plancode = $('input[name="program_of_interest_text"]').val();
                        // var plancode = $('input[data-drupal-selector="edit-program-of-interest-text"]').val();
                        var plancode = sessionStorage.getItem('selectedval_program_of_interest_text') !== null ? sessionStorage.getItem('selectedval_program_of_interest_text') : '';
                        var jsonUrl = origin + '/admin/asuaec_rfi/json/term/grad/' + plancode;
                        var appended_data = populateSelectList(jsonUrl, targetSelectList, false, ['input[name=entry_term_text]'], [], ['entry_term_text']);
                    }
                }
            } // END OF function populateTermDropdownList()



            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //--------------------------------------------------------------------------------------------------------//
            //--------------------------------------------------------------------------------------------------------//
            // Beginning of main flow
            //--------------------------------------------------------------------------------------------------------//
            //--------------------------------------------------------------------------------------------------------//
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            /* Webspark 2.10 update RFI form fix for Required red dot (2/15/2024) */
            $('.webform-submission-rfi-form').addClass('uds-form');
            // Required red dot for step 1 of 3 --- It started working without this code.
//            if($('.form-group.form-item-campus-options > label > svg').length == 0) {
//              $('<svg class="svg-inline--fa fa-circle uds-field-required" aria-labelledby="svg-inline--fa-title-99yu0aIOrYIj" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-99yu0aIOrYIj">Required</title><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg>').prependTo($('.form-group.form-item-campus-options > label'));
//            }

            /**
             * Add +1 to phone number field to be by default
             */
            var ini_phone = $('input[name="phone"]').val();
            if(ini_phone == ''){
                $('input[name="phone"]').val('+1');
            }

            // Get host
            var origin = window.location.origin;   // Returns base URL (https://example.com)
            cameFromDegreeSearch = false;
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


            // Hide webform text fields
            $('input[name=program_of_interest_text]').hide();
            $('input[name=hscity]').hide();
            $('input[name=hsname]').hide();
            $('input[name=icity]').hide();
            $('input[name=iname]').hide();
            $('input[name=entry_term_text]').hide();

            // Remove card CSS class from .fieldset-wrapper
            $('fieldset[data-drupal-selector=edit-grad-entry-term] > .fieldset-wrapper').removeClass('card-body');



            //--------------------------------------------------------------------------------------------------------//
            // On changes

            //----------------------------------------
            // Area of interest (in Step 1)

            // Ground Ugrad
            once('rfi', 'select[name="area_of_interest_ugrad"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    // Get selected value: Area of interest Ugrad
                    selectedValue = element.value;
                    if (!(selectedValue == '' || selectedValue == '0')) {
                        jsonUrl = origin + "/admin/asuaec_rfi/json/degrees/ground/ugrad/" + selectedValue;
                        appendToElement = "#dynamic-program-interest";
                        populateSelectList(jsonUrl, appendToElement, false, ['#edit-program-of-interest-text'], [], []);
                    }
                });
            });

            // Ground Grad
            once('rfi', 'select[name="area_of_interest_grad"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    selectedValue = element.value;
                    if (!(selectedValue == '' || selectedValue == '0')) {
                        jsonUrl = origin + "/admin/asuaec_rfi/json/degrees/ground/grad/" + selectedValue;
                        appendToElement = "#dynamic-program-interest";
                        populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
                    }
                });
            });

            // Online Ugrad
            once('rfi', 'select[name="area_of_interest_ugrad_online"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    selectedValue = element.value;
                    if (!(selectedValue == '' || selectedValue == '0')) {
                        jsonUrl = origin + "/admin/asuaec_rfi/json/degrees/online/ugrad/" + selectedValue;
                        appendToElement = "#dynamic-program-interest";
                        populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
                    }
                });
            });

            // Online Grad
            once('rfi', 'select[name="area_of_interest_grad_online"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    selectedValue = element.value;
                    if (!(selectedValue == '' || selectedValue == '0')) {
                        jsonUrl = origin + "/admin/asuaec_rfi/json/degrees/online/grad/" + selectedValue;
                        appendToElement = "#dynamic-program-interest";
                        populateSelectList(jsonUrl, appendToElement, false, ['input[name="program_of_interest_text"]'], [], []);
                    }
                });
            });

            // Program of interest
            once('rfi', '#dynamic-program-interest', context).forEach((element) => {
                element.addEventListener('change', function() {
                    $('input[name=program_of_interest_text]').val(element.value);
                    sessionStorage.setItem('selectedval_program_of_interest_text', element.value);
                    populateTermDropdownList();
                });
            });
          
            // Grad Entry term
            $('#dynamic-term', context).on('change', function() {
                // Write value to the original text field.
                $('input[name=entry_term_text]').val(this.value);
                // Cache selection in sessionStorage
                sessionStorage.setItem("selectedval_entry_term_text", this.value);
            });

          

            // Default Entry Term
            once('rfi', 'select[name="entry_term"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    $('input[name="entry_term_text"]').val(element.value);
                    sessionStorage.setItem("selectedval_entry_term_text", element.value);
                });
            });

            // Campus Options (GROUND/ONLINE/NOPREF)
            once('rfi', 'select[name="campus_options"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    sessionStorage.setItem("selectedval_campus_options", element.value);
                    evaluateRequiredFields();
                });
            });

            // First Time Freshman / Transfer / Readmission
            once('rfi', 'select[name="student_type_options_default"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    sessionStorage.setItem("selectedval_student_type_options_default", element.value);
                    
                    if (element.value === 'Readmission') {
                        $('input[name="grad_ugrad"]').val('GRAD');
                    } else {
                        $('input[name="grad_ugrad"]').val('UGRAD');
                    }
                    
                    evaluateRequiredFields();
                });
            });
          

            //---------------------------------
            // Postal code (in Step 2) needs to rollover to Zip code in Step 3 (2/18/2022)
          
            // Postal code (Step 2) → Zip code (Step 3)
            once('rfi', 'input[name="postal_code"]', context).forEach((element) => {
                element.addEventListener('change', function() {
                    sessionStorage.setItem("selectedval_postal_code", element.value);
                });
            });

            // Ensure the Zip Code field gets updated with the stored Postal Code value
            const storedPostalCode = sessionStorage.getItem("selectedval_postal_code");
            if (storedPostalCode && !$('input[name="zip_code"]').val()) {
                $('input[name="zip_code"]').val(storedPostalCode);
            }

          
            //----------------------------------------------------------------
            // Other setups

            // Add required dot mark
            const svgCode = `<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-uB2ijBlZl7Wl" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-uB2ijBlZl7Wl">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>`;

            // List of form items to prepend the required dot
            const requiredFields = [
                '.form-item-campus-options > label',
                '.form-item-student-type-options-default > label',
                '.form-item-area-of-interest-empty > label',
                '.form-item-area-of-interest-ugrad > label',
                '.form-item-area-of-interest-grad > label',
                '.form-item-area-of-interest-ugrad-online > label',
                '.form-item-area-of-interest-grad-online > label',
                '.form-item-gdpr-consent-i-consent > label',
                '.form-item-entry-term-text > label',
                '.form-item-hsstate > label',
                '.form-item-hscity > label',
                '.form-item-hsname > label',
                '.form-item-istate > label',
                '.form-item-icity > label',
                '.form-item-iname > label'
            ];

            // Apply `once` to each required field
            requiredFields.forEach(selector => {
                once('rfi', selector, context).forEach(element => {
                    element.insertAdjacentHTML('afterbegin', svgCode);
                });
            });

            // Special case: Check if .js-form-item-program-of-interest-text already has an SVG
            once('rfi', '.form-item-program-of-interest-text > label', context).forEach(element => {
                if (element.querySelector('svg') === null) {
                    element.insertAdjacentHTML('afterbegin', svgCode);
                }
            });

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

        }
    };
})(jQuery, Drupal, drupalSettings);
