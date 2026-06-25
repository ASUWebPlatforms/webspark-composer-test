//jQuery(document).ready(function ($) {
(function($, Drupal){
    Drupal.behaviors.customCode = {
        attach: function (context, settings) {
            'use strict';
            /****** code for Parent blog pages *******/
           /* var maxHeight = 0;
            $(window).bind("load", function() {
                $(".h1-100").each(function () {
                    if ($(this).height() > maxHeight) {
                        maxHeight = $(this).height();
                    }
                    $(".parent-front-posts").height(maxHeight);
                });
            });*/

            /* Code to add svg icons next to category items */
            /* ACADEMIC ICON */
            $('.Academic').each(function () {
                var acad_text = $(this).text();
				console.log(acad_text);
                $(this).addClass('academic-icon');
                //if($('.acad-icon').length == 0) {
                    $(this).once().html('<svg aria-hidden="true" class="svg-inline--fa fa-graduation-cap fa-w-20 acad-icon" data-fa-i2svg="" data-icon="graduation-cap" data-prefix="fa" focusable="false" role="img" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M622.34 153.2L343.4 67.5c-15.2-4.67-31.6-4.67-46.79 0L17.66 153.2c-23.54 7.23-23.54 38.36 0 45.59l48.63 14.94c-10.67 13.19-17.23 29.28-17.88 46.9C38.78 266.15 32 276.11 32 288c0 10.78 5.68 19.85 13.86 25.65L20.33 428.53C18.11 438.52 25.71 448 35.94 448h56.11c10.24 0 17.84-9.48 15.62-19.47L82.14 313.65C90.32 307.85 96 298.78 96 288c0-11.57-6.47-21.25-15.66-26.87.76-15.02 8.44-28.3 20.69-36.72L296.6 284.5c9.06 2.78 26.44 6.25 46.79 0l278.95-85.7c23.55-7.24 23.55-38.36 0-45.6zM352.79 315.09c-28.53 8.76-52.84 3.92-65.59 0l-145.02-44.55L128 384c0 35.35 85.96 64 192 64s192-28.65 192-64l-14.18-113.47-145.03 44.56z" fill="currentColor"></path></svg>').append('&nbsp;' + acad_text);
                //}
            });

            /* Financial icon */
            $('.Financial').each(function () {
                var money_text = $(this).text();
                $(this).addClass('money-icon');
                //if($('.money_icon').length == 0) {
                    $(this).once().html('<svg aria-hidden="true" class="svg-inline--fa fa-money-bill-alt money_icon fa-w-20" data-fa-i2svg="" data-icon="money-bill-alt" data-prefix="far" focusable="false" role="img" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M320 144c-53.02 0-96 50.14-96 112 0 61.85 42.98 112 96 112 53 0 96-50.13 96-112 0-61.86-42.98-112-96-112zm40 168c0 4.42-3.58 8-8 8h-64c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h16v-55.44l-.47.31a7.992 7.992 0 0 1-11.09-2.22l-8.88-13.31a7.992 7.992 0 0 1 2.22-11.09l15.33-10.22a23.99 23.99 0 0 1 13.31-4.03H328c4.42 0 8 3.58 8 8v88h16c4.42 0 8 3.58 8 8v16zM608 64H32C14.33 64 0 78.33 0 96v320c0 17.67 14.33 32 32 32h576c17.67 0 32-14.33 32-32V96c0-17.67-14.33-32-32-32zm-16 272c-35.35 0-64 28.65-64 64H112c0-35.35-28.65-64-64-64V176c35.35 0 64-28.65 64-64h416c0 35.35 28.65 64 64 64v160z" fill="currentColor"></path></svg>').append('&nbsp;' + money_text);
                //}
            });

            /* preparing for college  icon */
            $('.Preparing').each(function () {
                var prepare_text = $(this).text();
                $(this).addClass('pencil-icon');
                //if($('.fa-pencil-alt').length == 0) {
                    $(this).once().html('<svg aria-hidden="true" class="svg-inline--fa fa-pencil-alt fa-w-16" data-fa-i2svg="" data-icon="pencil-alt" data-prefix="fa" focusable="false" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z" fill="currentColor"></path></svg>').append('&nbsp;' + prepare_text);
                //}
            });


            /* preparing for college  icon */
            $('.College').each(function () {
                var college_text = $(this).text();
                $(this).addClass('univ-icon');
               // if($('.fa-university').length == 0) {
                    $(this).once().html('<svg aria-hidden="true" class="svg-inline--fa fa-university fa-w-16" data-fa-i2svg="" data-icon="university" data-prefix="fa" focusable="false" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M496 128v16a8 8 0 0 1-8 8h-24v12c0 6.627-5.373 12-12 12H60c-6.627 0-12-5.373-12-12v-12H24a8 8 0 0 1-8-8v-16a8 8 0 0 1 4.941-7.392l232-88a7.996 7.996 0 0 1 6.118 0l232 88A8 8 0 0 1 496 128zm-24 304H40c-13.255 0-24 10.745-24 24v16a8 8 0 0 0 8 8h464a8 8 0 0 0 8-8v-16c0-13.255-10.745-24-24-24zM96 192v192H60c-6.627 0-12 5.373-12 12v20h416v-20c0-6.627-5.373-12-12-12h-36V192h-64v192h-64V192h-64v192h-64V192H96z" fill="currentColor"></path></svg>').append('&nbsp;' + college_text);
               // }
            });
            /****** end of parent blog pages code *******/

            /****** JS code for findmyrep page *******/

            var in_val = '';
            var selval = '';
            var sp_email = '';
            var sp_hs = '';
            var sp_nid = '';
            var button_link = '';
            $('.freshmanrepview').find('.js-form-submit').css({'display': 'none'});
            $('.freshmanrepview').find('.js-form-submit').addClass('ftf-submit-button');
            $('.js-form-item-field-specialist-hs-names-value').css({'display': 'none'});
            in_val = $('select[name="field_specialist_state_freshman_value"]').val();
            //showHs(in_val);
            if (in_val === "Arizona") {
                $('.js-form-item-field-specialist-hs-names-value').css({'display': 'block'});
            }
            if (in_val != "Arizona") {
                $('.js-form-item-field-specialist-hs-names-value').css({'display': 'none'});
            }

            $(document).on('change', 'select[name="field_specialist_state_freshman_value"]', function () {
                selval = $('select[name="field_specialist_state_freshman_value"]').val();
                $('.freshmenrep-views-row').hide();
                $('input[name="field_specialist_hs_names_value"]').val('');
                showHsFtf(selval);
            });


            function showHsFtf(selval) {

                if (selval === "Arizona") {
                    $('.js-form-item-field-specialist-hs-names-value').css({'display': 'block'});
                    console.log('visible');
                    $('input[name="field_specialist_hs_names_value"]').change(function() {
                        $('.freshmanrepview').find('.js-form-submit').trigger('click');
                    })

                }

                if (selval != "Arizona") {
                    $('.js-form-item-field-specialist-hs-names-value').css({'display': 'none'});
                    console.log('nvisible');
                    $('.freshmanrepview').find('.js-form-submit').trigger('click');

                }

            }

            $('.freshmanrepview').find('.js-form-submit').on('click', function (e) {
                $('.js-form-item-field-specialist-hs-names-value').hide();
                var final_value = $('select[name="field_specialist_state_freshman_value"]').val();
                sp_hs = $('input[name="field_specialist_hs_names_value"]').val();
                console.log(sp_hs);
                if (final_value == "Arizona") {
                    $('.js-form-item-field-specialist-hs-names-value').show();
                }

                if (final_value != "Arizona") {
                    $('.js-form-item-field-specialist-hs-names-value').hide();
                }

                /** code to update the link of contact rep button **/
                setTimeout(function(){ //Set the time so the below code can run after results are displayed
                    $('.freshmenrep-views-row').each(function() {
                        //contact/freshmanrep?state=Alaska&hs=&email=ASUBaileyHays@asu.edu&nid=2446&location_from=findmyrep
                        sp_email = $(this).find('.views-field-field-specialist-email').children('.field-content').text();
                        sp_nid = $(this).find('.views-field-nid').children('.field-content').text();
                        button_link = "/contact/freshmanrep?state=" + final_value + "&hs=" + sp_hs + "&email=" + sp_email + "&nid=" + sp_nid + "&location_from=findmyrep";
                        // console.log('button_link', button_link);
                        $(this).find('#CounselRepLink').attr('href', button_link);

                    });
                },1000);

            });
         /***** end of Findmyrep JS code  *****/




         /****** JS code for transfer specialist page *******/

            var tr_in_val = '';
            var tr_selval = '';
            var tr_sp_email = '';
            var tr_sp_tax = '';
            var tr_sp_nid = '';
            var tr_button_link = '';
            $('.transferrep-top-block').parents('.container').addClass('custom-look-class');
            $('.transferrepview').find('.js-form-submit').css({'display': 'none'});
            $('.trasnferrepview').find('.js-form-submit').addClass('ftf-submit-button');
            $('select[name="tid"]').css({'display': 'none'});
            $('select[name="tid"] option[value="All"]').text('Arizona college or university*');
            //$('select[name="field_specialist_state_value"]').attr('placeholder','Arizona college or university*');
            $('select[name="field_specialist_state_value"] option[value="All"]').text('Where are you studying in the U.S?*');
            tr_in_val = $('select[name="field_specialist_state_value"]').val();
            if (tr_in_val === "AZ") {
                $('select[name="tid"]').css({'display': 'block'});
            }
            if (tr_in_val != "AZ") {
                $('select[name="tid"]').css({'display': 'none'});
            }

            $(document).on('change', 'select[name="field_specialist_state_value"]', function () {
                tr_selval = $(this).val();
                $('.transfer-views-row').hide();
                $('select[name="tid"]').val('All');
                showHsTrn(tr_selval);
            });

            $(document).on('change', 'input[name="submitted[what_do_you_want_to_do]"]', function () {
                $('#any-of-the-following-apply-to-you').val('');
                $('select[name="field_specialist_state_value"]').val('All');
                $('select[name="tid"]').val('All');
            })


            function showHsTrn(tr_selval) {
                console.log('tr-state',tr_selval);
                if (tr_selval === "AZ") {
                    $('select[name="tid"]').css({'display': 'block'});
                    $(document).on('change', 'select[name="tid"]', function () {
                         $('.transferrepview').find('.js-form-submit').trigger('click');
                    })

                }

                if (tr_selval != "AZ") {
                    $('select[name="tid"]').css({'display': 'none'});
                    $('.transferrepview').find('.js-form-submit').trigger('click');

                }

            }

            $('.transferrepview').find('.js-form-submit').on('click', function (e) {
                //$('select[name="tid"]').hide();
                var tr_final_value = $('select[name="field_specialist_state_value"]').val();
                var tr_st_value = $('select[name="field_specialist_state_value"] option:selected').text();
                tr_sp_tax = $('select[name="tid"]').val();
                if (tr_final_value == "AZ") {
                    $('select[name="tid"]').show();
                }

                if (tr_final_value != "AZ") {
                    $('select[name="tid"]').hide();
                }

                /** code to update the link of contact rep button **/
                setTimeout(function(){ //Set the time so the below code can run after results are displayed
                    $('.transfer-views-row').each(function() {
                        //contact/freshmanrep?state=Alaska&hs=&email=ASUBaileyHays@asu.edu&nid=2446&location_from=findmyrep
                        tr_sp_email = $(this).find('.views-field-field-specialist-email').children('.field-content').text();
                        tr_sp_nid = $(this).find('.views-field-nid').children('.field-content').text();
                        tr_button_link = "/contact/transfer?istate=" + tr_final_value + "&icode=" + tr_sp_tax + "&email=" + tr_sp_email + "&nid=" + tr_sp_nid + "&state="+tr_st_value;
                        // console.log('button_link', button_link);
                        $(this).find('#CounselRepLink').attr('href', tr_button_link);

                    });
                },1000);

            });

         /** code for rest of the transfer contact page ***/
            $('#div-military-asuonline-or-oncampus').hide();
            $('.military-only').hide(); // Military advisors
            // $('.transferrepview').hide();

            /**
             * Displays Advisor profiles. - Chizuko on 4/26/2019
             *
             * Parameters:
             *   rep_emails - Email addresses separated by |. For example, ASUnikychokshi@asu.edu|ASUdeeprajgahatraj@asu.edu
             *
             * If rep_emails contain multiple email addresses, it will display profiles side by side.
             */
            function displayAdvisorProfiles(rep_email) {

                var content = '';
                var rep_name = '';
                var button = '';

                if(rep_email != ''){
                    switch(rep_email){
                        case 'ASUGradyFoster@asu.edu':
                            content = '<div class="profile_image"><img class="int_rep_image" src="/sites/default/files/avatar.png"></div><div class="right-col"><strong>Grady Foster</strong><br><span class="smalltext">International Admissions Coordinator <br> Admission Service<br><br><i class="fa fa-envelope" aria-hidden="true"> </i>&nbsp;<a href="mailto:ASUGradyFoster@asu.edu">ASUGradyFoster@asu.edu</a><br><i class="fa fa-phone" aria-hidden="true"> </i>&nbsp;480-727-0140</span><br><br>Schedule a time to connect with me: <a href="https://calendly.com/grady-foster">https://calendly.com/grady-foster</a></div>';
                            rep_name = 'Grady Foster';
                            break;


                        case 'enrollment@asuonline.asu.edu':
                            content = '<div><span class="smalltext">Please contact <a href="mailto:enrollment@asuonline.asu.edu">enrollment@asuonline.asu.edu</a><br><i class="fa fa-phone" aria-hidden="true"> </i>&nbsp;866-277-6589</span><br><br><span class="smalltext">If you\'re on a pathway program to ASU, please contact <a href="mailto:EnrollmentOnline@asu.edu">EnrollmentOnline@asu.edu</a><br><i class="fa fa-phone" aria-hidden="true"> </i>&nbsp;844-353-7953</span></div>';
                            rep_name = '';
                            break;

                        case 'enrollmentonline@asu.edu':
                            content = '<div><span class="smalltext">Please contact <a href="mailto:enrollmentonline@asu.edu">enrollmentonline@asu.edu</a><br/><i class="fa fa-phone" aria-hidden="true"> </i>&nbsp;844-353-7953</span></div>';
                            rep_name = '';
                            break;

                    } // END OF switch
                }
                button = '<a href="/new-transfer-contact-rep-form?email=' + rep_email + '"><p class="btn btn-maroon">Contact ' + rep_name + '</p></a>';

                var output = '';
                output += '<div class="advisor-profile">';
                if(rep_name != ''){
                    output += content;
                    output += button;
                }
                else{
                    output += content;
                    //output += button;
                }
                output += '</div>';
                // console.log(output);
                $('#advisor-info').html(output);

            } // END OF function addAdvisorIntoArray()

            // Helping function to sort array of Objects
            function compare(a, b) {
                // Use toUpperCase() to ignore character casing
                const advisorA = a.name.trim().charAt(0).toUpperCase(); // Sort by 1st character of first name
                const advisorB = b.name.trim().charAt(0).toUpperCase();

                let comparison = 0;
                if (advisorA > advisorB) {
                    comparison = 1;
                } else if (advisorA < advisorB) {
                    comparison = -1;
                }
                return comparison;
            } // END OF vunciton compare(a, b)

            // Do any of the following apply to you?
            $(document).on('change','#any-of-the-following-apply-to-you', function () {
                var selection = $(this).val();

                if (selection == 'F-1 student visa') {
                    $('#advisor-info').html('');
                    $('.transferrepview').hide();
                    $('.military-only').hide(); //Military advisors
                    $('#div-military-asuonline-or-oncampus').hide();

                    var advisorEmail = 'ASUGradyFoster@asu.edu';
                    displayAdvisorProfiles(advisorEmail);
                }
                else if (selection == 'military-affiliated') {
                    $('#advisor-info').html('');
                    $('.transferrepview').hide();
                    $('.military-only').hide(); //Military advisors

                    $('#div-military-asuonline-or-oncampus').show();
                }
                else if (selection == 'ASU Online') {
                    $('#advisor-info').html('');
                    $('.transferrepview').hide();
                    $('.military-only').hide(); //Military advisors
                    $('#div-military-asuonline-or-oncampus').hide();

                    var advisorEmail = 'enrollment@asuonline.asu.edu';
                    displayAdvisorProfiles(advisorEmail);
                }
                else if (selection == 'corporate partner') {
                    $('#advisor-info').html('');
                    $('.transferrepview').hide();
                    $('.military-only').hide();
                    $('#div-military-asuonline-or-oncampus').hide();

                    var advisorEmail = 'enrollmentonline@asu.edu';
                    displayAdvisorProfiles(advisorEmail);
                }
                else if (selection == 'on-campus') {
                    $('#advisor-info').html('');
                    $('#div-military-asuonline-or-oncampus').hide();
                    $('.military-only').hide(); //Military advisors
                    $('select[name="field_specialist_state_value"]').show();
                    $('.transferrepview').show();
                }
                else {
                    // Clear #advisor-info
                    $('#advisor-info').html('');
                    $('#div-military-asuonline-or-oncampus').hide();
                    $('.transferrepview').hide();
                    $('.military-only').hide();
                }
            });


            $('#military-asuonline-or-oncampus').change(function(){
                var selection = $(this).val();

                if (selection == 'asuonline') {
                    $('#advisor-info').html('');
                    $('.military-only').hide(); //Military advisors
                    $('.transferrepview').hide();

                    var advisorEmail = 'enrollment@asuonline.asu.edu';
                    displayAdvisorProfiles(advisorEmail);
                }
                else if (selection == 'oncampus') {
                    $('#advisor-info').html('');
                    $('.transferrepview').hide();

                    $('.military-only').show(); //Military advisors
                }
                else {
                    // Clear #advisor-info
                    $('#advisor-info').html('');
                    $('#div-military-asuonline-or-oncampus').hide();
                    $('.transferrepview').hide();
                    $('.military-only').hide();
                }
            });



            /***** end of transfer specialist JS code  *****/





        }
    };
}(jQuery, Drupal));
