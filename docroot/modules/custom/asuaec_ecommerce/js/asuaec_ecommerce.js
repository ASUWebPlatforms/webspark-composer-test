(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.rfi = {
        attach: function (context, settings) {

            function addFieldsetForTranscript() {
                // Add heading "Check out"
                var pathname = window.location.pathname;
                var a = location.pathname.split("/");
                if($('h2.checkout').length == 0) {
                    
                    if ( a.length > 2 && a[3] == "order_information" ) {
                        $('.layout-region-checkout-main').prepend('<h2 class="checkout">Check out</h2><h3 class="m-1">Order information</h3>');

                    } else if ( a.length > 2 && a[3] == "review" ) {
                        $('.layout-region-checkout-main').prepend('<h2 class="checkout">Check out</h2><h3 class="m-1">Review</h3>');

                    } else {
                        $('.layout-region-checkout-main').prepend('<h2 class="checkout">Check out</h2>');
                    }
                }
              
                if($('#edit-shipping-information .field--name-field-first-name').length > 0) { // If the personal info fields are displayed, add fieldset.

                  // Add fieldset
                  if($('fieldset.personal-info').length == 0) {
//                    $('<fieldset class="personal-info card mb-3"><div class="card-header"><legend class="m-0"><span class="fieldset-legend">Personal information</span></legend></div><div class="card-body fieldset-wrapper card-body-personal-info"></div></fieldset>').once().insertBefore( "#edit-shipping-information" ); // D10 change
                    $(once('ecommercejs', $('<fieldset class="personal-info card mb-3"><div class="card-header"><legend class="m-0"><span class="fieldset-legend">Personal information</span></legend></div><div class="card-body fieldset-wrapper card-body-personal-info"></div></fieldset>'), context)).each(function() {
                      $(this).insertBefore( "#edit-shipping-information" );
                    });
                  }
                  // Move fields into "Personal info" fieldset
                  if($('#edit-shipping-information .field--name-field-phone-number').length > 0) {
                      $('.field--name-field-phone-number').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-email-address').length > 0) {
                      $('.field--name-field-email-address').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-date-of-birth').length > 0) {
                      $('.field--name-field-date-of-birth').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-transcript-dates-att-paper').length > 0) {
                      $('.field--name-field-transcript-dates-att-paper').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-transcript-grad-date-paper').length > 0) {
                      $('.field--name-field-transcript-grad-date-paper').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-transcript-tbird-alumni').length > 0) {
                      $('.field--name-field-transcript-tbird-alumni').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-transcript-thunderbird-id').length > 0) {
                      $('.field--name-field-transcript-thunderbird-id').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-former-names').length > 0) {
                      $('.field--name-field-former-names').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-last-name').length > 0) {
                      $('.field--name-field-last-name').prependTo( ".card-body-personal-info" );
                  }
                  if($('#edit-shipping-information .field--name-field-first-name').length > 0) {
                      $('.field--name-field-first-name').prependTo( ".card-body-personal-info" );
                  }

                  // Date of birth - Changed on 1/2/2024
                  $('div.field--name-field-date-of-birth > h4').hide();
                  $('div.field--name-field-date-of-birth.form-wrapper > div > div.form-item > label').removeClass('visually-hidden');
                  let temp = $('div.field--name-field-date-of-birth.form-wrapper > div > div.form-item > label').html();
                  temp = temp.replace("Date", "Date of Birth");
                  $('div.field--name-field-date-of-birth.form-wrapper > div > div.form-item > label').html(temp);

                  // Graduation date
                  $('.field--name-field-transcript-grad-date-paper > h4').replaceWith(function () {
                      return "<label>" + $(this).html() + "</label>";
                  });

                  // Date of attendance
                  $('.field--name-field-transcript-dates-att-paper > fieldset > .card-body > h4').replaceWith(function () {
                      return "<label>" + $(this).html() + "</label>";
                  });

                }

            }

            function enterLabelThunderbirdId() {
                $('input[name="shipping_information[shipping_profile][field_label_thunderbird_id][0][target_id]"]').val('Thunderbird ID (176)');
            }

            // Add margin
//            $('.commerce-checkout-flow-multistep-default').once().addClass('m-4');
//            $('.cart').once().addClass('m-4');
            $('.commerce-checkout-flow-multistep-default').addClass('m-4');
            $('.cart').addClass('m-4');
            // D10 change
            $('.card-body-personal-info .form-item').addClass('mb-2');
            $('#edit-shipping-information-shipping-profile .form-item').addClass('mb-2');
          
          
          
          

            //-----------------------
            // Show/hide elements
          
            // Hide Graduation Date
            $('.field--name-field-transcript-grad-date-paper').hide();
            $('.field--name-field-transcript-dates-att-paper').hide();
            if($('select[name="shipping_information[shipping_profile][field_transcript_tbird_alumni]"] option:selected').val() == 'Yes') {
                $('.field--name-field-transcript-grad-date-paper').show();
                $('.field--name-field-transcript-dates-att-paper').hide();
                // Also, clear content
                $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][date]"]').val('');
                $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][date]"]').val('');

            } else if($('select[name="shipping_information[shipping_profile][field_transcript_tbird_alumni]"] option:selected').val() == 'No') {
                $('.field--name-field-transcript-grad-date-paper').hide();
                $('.field--name-field-transcript-dates-att-paper').show();
                // Also, clear content
                $('input[name="shipping_information[shipping_profile][field_transcript_grad_date_paper][0][value][date]"]').val('');
            }

            // Hide labels that are used in Checkout review page
            // Thunderbird ID label field
            $('.form-item-shipping-information-shipping-profile-field-label-thunderbird-id-0-target-id').hide();
            // Thunderbird School of Global Management alumni label field
            $('.form-item-shipping-information-shipping-profile-field-label-thunderbird-school-o-0-target-id').hide();
            // Phone number
            $('.field--name-field-label-phone-number').hide();

            // Uncheck "Save to my address book." and hide it
            $('input[name="shipping_information[shipping_profile][copy_to_address_book]"]').prop( "checked", false );
            $('div.form-item-shipping-information-shipping-profile-copy-to-address-book').hide();
            $('input[name="payment_information[billing_information][copy_to_address_book]"]').prop( "checked", false );
            $('div.form-item-payment-information-billing-information-copy-to-address-book').hide();

            //-----------------------
            // On change events
            $('select[name="shipping_information[shipping_profile][field_transcript_tbird_alumni]"]').on('change', function() {
              
                if($('select[name="shipping_information[shipping_profile][field_transcript_tbird_alumni]"] option:selected').val() == 'Yes') {
                    $('.field--name-field-transcript-grad-date-paper').show();
                    $('.field--name-field-transcript-dates-att-paper').hide();
                    // Also, clear content
                    $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][date]"]').val('');
                    $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][date]"]').val('');

                } else {
                    $('.field--name-field-transcript-grad-date-paper').hide();
                    $('.field--name-field-transcript-dates-att-paper').show();
                    // Also, clear content
                    $('input[name="shipping_information[shipping_profile][field_transcript_grad_date_paper][0][value][date]"]').val('');
                }
            });

            // When Thunderbird ID is entered, populate the label field.
            $('input[name="shipping_information[shipping_profile][field_transcript_thunderbird_id][0][value]"]').on('change', function() {
                if($('input[name="shipping_information[shipping_profile][field_transcript_thunderbird_id][0][value]"]').val() == ''){
                    $('input[name="shipping_information[shipping_profile][field_label_thunderbird_id][0][target_id]"]').val('');
                } else {
                    enterLabelThunderbirdId();
                }
            });
          


            //-----------------------
            // Add Fieldset

            // For Thunderbird store - Transcript checkout page

            // When page loaded
            addFieldsetForTranscript();

            // After Ajax runs
            $( document ).ajaxComplete(function( event, xhr, settings ) {
              addFieldsetForTranscript();
            });
          
          
          
            // Move Profile fields
            //$('.profile--type--customer-transcript').once().wrapInner("<div class='personalinfo'></div>"); // D10 change
            $(once('ecommercejs', '.profile--type--customer-transcript', context)).each(function() {
              $(this).wrapInner("<div class='personalinfo'></div>");
            });
            //$('.personalinfo').once().wrapInner("<div class='personalinfo-content'></div>"); // D10 change
            $(once('ecommercejs', '.personalinfo', context)).each(function() {
              $(this).wrapInner("<div class='personalinfo-content'></div>");
            });
            let address = $("p.address").detach();
            $('.personalinfo').before($(address[0]));
            let shippingmethod = $('#edit-review-shipping-information-summary-0').detach();
            $('p.address').after($(shippingmethod[0]));
            let contexualButton = $('.personalinfo > .personalinfo-content > div').detach();
            $('p.address').after(contexualButton[0]);
            // Add heading
            if($('.personalinfo-heading').length == 0) {
              //$('<h5 class="personalinfo-heading">Personal information</h5>').once().prependTo($("div.personalinfo")); // D10 change
              $(once('ecommercejs', $('<h5 class="personalinfo-heading">Personal information</h5>'), context)).each(function() {
                $(this).prependTo($("div.personalinfo"));
              });
            }
            // "Dates of Attendance:" - Remove the second one if showing 2 times.
            if($('.personalinfo-content').length > 0) {
              let thePersonalInfo = $('.personalinfo-content').html();
              let result = thePersonalInfo.replace("- Dates of Attendance:", "- ");
              $('.personalinfo-content').html(result);
            }

            // Wrap heading with h5
            // Shipping information
            //$('fieldset#edit-review-shipping-information span.fieldset-legend').once().wrapInner('<h5></h5>'); // D10 change
            $(once('ecommercejs', 'fieldset#edit-review-shipping-information span.fieldset-legend', context)).each(function() {
                $(this).wrapInner('<h5></h5>');
            });
          
            // Payment information
            //$('fieldset#edit-review-payment-information span.fieldset-legend').once().wrapInner('<h5></h5>'); // D10 change
            $(once('ecommercejs', 'fieldset#edit-review-payment-information span.fieldset-legend', context)).each(function() {
                $(this).wrapInner('<h5></h5>');
            });

            // Change heading from "Contact information" to "Email"
            // Contact information
            //$('fieldset#edit-review-contact-information span.fieldset-legend').once().html('<h5>Email</h5>'); // D10 change
            $(once('ecommercejs', 'fieldset#edit-review-contact-information span.fieldset-legend', context)).each(function() {
                $(this).html('<h5>Email</h5>');
            });
          
          

            //-----------------------
            // For Students store - Using Commerce Checkout Order Fields module

            if($('fieldset.student-info').length == 0){
              //$( '#edit-order-fieldscheckout' ).once().wrap( '<fieldset class="student-info card mb-3"><div class="card-body fieldset-wrapper card-body-student-info"></div></fieldset>' ); // D10 change
              $(once('ecommercejs', '#edit-order-fieldscheckout', context)).each(function() {
                $(this).wrap( '<fieldset class="student-info card mb-3"><div class="card-body fieldset-wrapper card-body-student-info"></div></fieldset>' );
              });
            }
            if($('.card-header-student-info').length == 0) {
              $('<div class="card-header card-header-student-info"><legend class="m-0"><span class="fieldset-legend">Student information</span></legend></div>').prependTo('fieldset.student-info');
            }
          
            //-----------------------
            // Hide time fields for Dates of Attendance (12/6/2024)
          
            // Start date
            // Select the time field by name and hide it
            $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][time]"]').hide();

            // Function to clear and hide the time field
            function clearAndHideTimeFieldStart() {
              $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][time]"]').val('').hide();
            }

            // Add event listener for changes in the date field
            $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][date]"]').on('input', function() {
              let dateValue = $(this).val();
              let timeField = $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][value][time]"]');

              // Check if the date is valid and not empty or placeholder "mm/dd/yyyy"
              if (dateValue && (dateValue !== 'mm/dd/yyyy')) {
                // Set time to 12:00:00 AM when a valid date is entered
                timeField.val('00:00:00');
              } else {
                // Clear the time field if the date field is empty or placeholder
                clearAndHideTimeFieldStart();
              }
            });

            // End date
            // Select the time field by name and hide it
            $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][time]"]').hide();

            // Function to clear and hide the time field
            function clearAndHideTimeFieldEnd() {
              $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][time]"]').val('').hide();
            }

            // Add event listener for changes in the date field
            $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][date]"]').on('input', function() {
              let dateValue = $(this).val();
              let timeField = $('input[name="shipping_information[shipping_profile][field_transcript_dates_att_paper][0][end_value][time]"]');

              // Check if the date is valid and not empty or placeholder "mm/dd/yyyy"
              if (dateValue && (dateValue !== 'mm/dd/yyyy')) {
                // Set time to 12:00:00 AM when a valid date is entered
                timeField.val('00:00:00');
              } else {
                // Clear the time field if the date field is empty or placeholder
                clearAndHideTimeFieldEnd();
              }
            });

            // "Thunderbird School of Global Management alumni"
            // Add event listener for changes in the "Thunderbird School of Global Management alumni" select field
            $('select[name="shipping_information[shipping_profile][field_transcript_tbird_alumni]"]').on('change', function() {
              let selectedValue = $(this).val();

              // Clear the time fields if "Yes" is selected
              if (selectedValue === 'Yes') {
                clearAndHideTimeFieldStart();
                clearAndHideTimeFieldEnd();
              }
            });
          
        }
    };
})(jQuery, Drupal, drupalSettings);