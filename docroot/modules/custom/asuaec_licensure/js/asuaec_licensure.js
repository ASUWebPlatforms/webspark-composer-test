(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.licensure = {
        attach: function (context, settings) {


            /**
             * Populate dynamic Select List from Web service
             *
             * @param jsonUrl -- For example: "https://admission-asu-csdev10.ddev.site/admin/asuaec_licensure/json/programnodetitles/477"
             * @param appendToElement -- For example: "#dynamic-program"
             * @param notListed -- Boolean: If you add "--Not listed--" option or not.
             * @param textFieldIdtoBeClearedArray
             * @param selectOptionIdtoBeClearedArray
             * @param textFieldNametoBeClearedArray -- Used for clearSelectList. Added for clearing sessionStorage variable.
             */
            async function populateSelectList(jsonUrl, appendToElement, notListed = false,
                                        textFieldIdtoBeClearedArray = array(), selectOptionIdtoBeClearedArray = array(), textFieldNametoBeClearedArray = array()) {
                // Populate select list
                return $.ajax({
                    type: "GET",
                    url: jsonUrl,
                    dataType: "json",
                    async: "true",
                    success: function (data) {
                        // Clear "Program" dropdown list
                        clearSelectList(appendToElement, textFieldIdtoBeClearedArray, selectOptionIdtoBeClearedArray, textFieldNametoBeClearedArray);
                        $('select#dynamic-program option:selected').val('0').trigger('change');

                        if (notListed) {
                            var div_data = "<option value='other'>--Not listed--</option>";
                            $(div_data).appendTo(appendToElement);
                        }
                        var appended_data = '';
                        var i = 0;
                        $.each(data, function (k, v) {
                            i++;
                            var div_data = '<option value="' + k + '">' + v + '</option>';
                            // var div_data = '<option value="' + v + '">' + k + '</option>';
                            $(div_data).appendTo(appendToElement);
                            appended_data = appended_data + div_data;
                        });

                        // Cache it in sessionStorage
                        sessionStorage.setItem("selectdata_" + appendToElement,  appended_data); // only strings

                        // console.log("i: " , i); // "i" indicates how many results there are.
                        // Insert "Not available" message for Grad term if there are no results.
                    }
                });
            }

            /**
             * Clear Select List.
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

            }

            //--------------------------------------------------------------
            // console.log("very beginning");

            // Add dynamic dropdown list
            if ($('#dynamic-program').length == 0) {
                // Added dynamic dropdown before .program-block. So, it doesn't get refreshed by Ajax. So, the dropdown keeps the selection and populated options.
                // $('.program-block').once().before("<form class='uds-form'><label>Program</label><select id='dynamic-program' name='dynamic-program' class='form-select custom-select' ><option value='0'>Select...</option></select></form>");
                $(once('licensure', '.program-block')).before("<form class='uds-form'><label>Program</label><select id='dynamic-program' name='dynamic-program' class='form-select custom-select' ><option value='0'>Select...</option></select></form>"); // D10 change
            }

            // Get host
            var origin = window.location.origin;   // Returns base URL (https://example.com)

            // When field_program_college_school_target_id dropdown changes, populate dynamic-program dropdown list.
//             $('select[name="field_program_college_school_target_id"]', context).once('licensure').on('change', async function() {
            $(once('licensure', 'select[name="field_program_college_school_target_id"]')).on('change', async function() { // D10 change

                // Get selected value: title
                var selectedValue = $('select[name="field_program_college_school_target_id"]').val();

                // selectedValue = $('select[name="area_of_interest_ugrad"]').val();
                if(!(selectedValue == '' || selectedValue == '0')) {
                    jsonUrl = origin + "/admin/asuaec_licensure/json/programnodetitles/" + selectedValue; // For example: https://admission-asu-csdev10.ddev.site/admin/asuaec_licensure/json/programnodetitles/477
                    appendToElement = "#dynamic-program";
                    try{
                        var retValue = await populateSelectList(jsonUrl, appendToElement, false, ['input[name="title"]'], [], []);
                        var selectedText = $('select[name="field_program_college_school_target_id"] option:selected').text();
                        // Copy the value to the hidden text filter. Added "College or school" filter on 11/8/2022.
                        $('input[name="field_program_college_school_target_id"]').val(selectedText).trigger('change');

                    } catch(e) {
                        // Clear Program dropdown
                        clearSelectList(appendToElement, ['input[name="title"]'], [], []);
                        $('select#dynamic-program option:selected').val('0').trigger('change');
                        alert("No program found for the college/school.");
                    }


//					var selectedText = $('select[name="field_program_college_school_target_id"] option:selected').text();
//
//					// Copy the value to the hidden text filter. Added "College or school" filter on 11/8/2022.
//					$('input[name="field_program_college_school_target_id"]').val(selectedText).trigger('change');

                }
            });

            // When dynamic-program changes, copy the value to the hidden text filter.
            $('select#dynamic-program', context).on('change', function() {
                // Get selected value: title
                var selectedValue = $('select#dynamic-program').val();
                // console.log("selectedValue: ", selectedValue);


                // Copy the value to the hidden text filter.
                $('input[name="title"]').val(selectedValue).trigger('change');
            });

            // Add css id - Fix accordion after Webspark update (3/22/2024)
            $('.accordion.views-row').attr('id', 'professionalLicensureAccordion');

        }
    };
})(jQuery, Drupal, drupalSettings);
