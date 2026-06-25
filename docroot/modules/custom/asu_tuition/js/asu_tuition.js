/*jQuery(document).ready(function ($) {*/
/*(function ($) {
  Drupal.behaviors.asu_tuition = {
    attach: function () {
      //add class to the form
      $('#asu-tuition-search-form').addClass('uds-form');

      if ($('#asu-tuition-search-form').length == 0) {
        return;
      }
      var inputs = [];
      var hooks = [];
      var locks = [];
      var focused = null;
      var credit_hr = '';
      var site_host = document.location.hostname;
      var full_site = 'https://' + site_host;
      //hide credit hours dropdown on results pahe initially
      $('.form-item-credit-hrs').hide();
      $('.tuition-result-heading').hide();
      // These are settings gathered from the server so that we do not have to have
      // AJAX calls anymore.
      var js_settings = drupalSettings.asu_tuition;

      // Set global variables.
      var corporate_partner = $(
        '#asu-tuition-search-form [name=corporate_partner]',
      );
      var acad_year = $('#asu-tuition-search-form [name=acad_year]');
      var include_summer = $('#asu-tuition-search-form [name=include_summer]');
      var residency = $('#asu-tuition-search-form [name=residency]');
      var acad_career = $('#asu-tuition-search-form [name=acad_career]');
      var campus = $('#asu-tuition-search-form [name=campus]');
      var acad_prog = $('#asu-tuition-search-form [name=acad_prog]');
      var admit_term = $('#asu-tuition-search-form [name=admit_term]');
      var admit_level = $('#asu-tuition-search-form [name=admit_level]');
      var honors = $('#asu-tuition-search-form [name=honors]');
      var program_fee = $('#asu-tuition-search-form [name=program_fee]');
      var qtr_residency = $('#asu-tuition-search-form [name=qtr_residency]');

      $("#edit-residency option[value='QTRD']").hide();
      $("#edit-residency option[value='QTRE']").hide();
      $("#edit-residency option[value='QTRS']").hide();
      var qtr_array = ['QTRD', 'QTRE', 'QTRS'];

      $('#edit-qtr-residency').change(function () {
        var qtr_check = '';
        if ($('#edit-qtr-residency').is(':checked')) {
          // Get the value of the checked checkbox
          let checkboxValue = $('#edit-qtr-residency').val();
          qtr_check = 'Yes';
          $("#edit-residency option[value='QTRD']").show();
          $("#edit-residency option[value='QTRE']").show();
          $("#edit-residency option[value='QTRS']").show();
          // Hide all other options
          $('#edit-residency option')
            .not("[value='QTRD'], [value='QTRE'], [value='QTRS']")
            .hide();
        } else {
          qtr_check = 'no';
          $("#edit-residency option[value='QTRD']").hide();
          $("#edit-residency option[value='QTRE']").hide();
          $("#edit-residency option[value='QTRS']").hide();
          $('#edit-residency option')
            .not("[value='QTRD'], [value='QTRE'], [value='QTRS']")
            .show();
          $('#edit-residency').val('');
        }
      });

      //get inital value of campus field
      var ini_career = $('#edit-acad-career').val();
      if (!ini_career) {
        $('#edit-campus').attr('disabled', true);
      }

      //set year value on initial load
      var default_year = js_settings.acad_yar_default;
      //console.log(default_year);
      //$('#edit-acad-year').find('option:eq(0)').prop('selected', true);
      $('#edit-acad-year')
        .find('option[value="' + default_year + '"]')
        .prop('selected', true);

      //add required field attributes
      $('select[name="acad_prog"]').attr(
        'data-msg-required',
        'My college field is required',
      );

      // Bind handlers to run on field changes.
      $(acad_year).change(function () {
        $('#edit-campus').attr('disabled', true);
        acad_year_change();
      });
      $(residency).change(function () {
        $('#edit-campus').attr('disabled', true);

        residency_change();
      });
      $(acad_career).change(function () {
        $('#edit-campus').attr('disabled', false);
        acad_career_change();
      });
      $(campus).change(function () {
        var cam_val = $('#edit-campus').val();
        //console.log(cam_val);
        if (cam_val == 'LOSAN') {
          $('.form-item-honors').hide();
        } else {
          $('.form-item-honors').show();
        }
        campus_change();
      });
      $(acad_prog).change(function () {
        acad_prog_change();
      });

      // Allow form to submit values of disabled fields.
      $('#asu-tuition-search-form').submit(function () {
        $(':input').removeAttr('disabled');
      });

      // Run change functions to make sure valid options are being displayed on load.
      acad_year_change();

      // This must be run in order to disabled elements again in case the back button
      // is used.
      // $(window).unload(function() {
      $(window).on('beforeunload', function () {
        toggle_acad_program();
      });
*/
/**
 * Wrapper function to load program fields (this was used when the acad_prog and
 * online_prog were both in use but keeping in case there are other fields in
 * the future that may need to be added to this action.
 */
/*   function load_programs() {
        load_acad_program();
        load_program_fee();
      }
    */
/**
 * Load the residencies into the residency field.
 */
/*  function load_residency() {
        // Disable options not available for corporate partner.
        if (js_settings.corporate_partner) {
          $(residency)
            .find('option')
            .each(function (index) {
              $(this).removeAttr('disabled');
              if (
                index != 0 &&
                !(
                  $(this).attr('value') in
                  js_settings['corporate_partner_fields'][
                    $(corporate_partner).val()
                  ][$(acad_year).val()]
                )
              ) {
                $(this).attr('disabled', 'disabled');
              }
            });
          if (
            !(
              $(residency).val() in
              js_settings['corporate_partner_fields'][
                $(corporate_partner).val()
              ][$(acad_year).val()]
            )
          ) {
            // Get the first option and set the field to it.
            for (i in js_settings['corporate_partner_fields'][
              $(corporate_partner).val()
            ][$(acad_year).val()]) {
              $(residency).val(i);
              break;
            }
          }

          enable_element(residency);
        }
      }
    */
/**
 * Load the careers into the acad_career field.
 */
/*  function load_acad_career() {
        //hide law career option for CA residents
        var resi_value = $('#edit-residency').val();
        if (resi_value == 'CARES') {
          $('#edit-acad-career option[value="LAW"]').hide();
        } else {
          $('#edit-acad-career option[value="LAW"]').show();
        }
        // Disable options not available for corporate partner.
        if (js_settings.corporate_partner) {
          $(acad_career)
            .find('option')
            .each(function (index) {
              $(this).removeAttr('disabled');
              if (
                index != 0 &&
                !(
                  $(this).attr('value') in
                  js_settings['corporate_partner_fields'][
                    $(corporate_partner).val()
                  ][$(acad_year).val()][$(residency).val()]
                )
              ) {
                $(this).attr('disabled', 'disabled');
              }
            });
          if (
            !(
              $(acad_career).val() in
              js_settings['corporate_partner_fields'][
                $(corporate_partner).val()
              ][$(acad_year).val()][$(residency).val()]
            )
          ) {
            // Get the first option and set the field to it.
            for (i in js_settings['corporate_partner_fields'][
              $(corporate_partner).val()
            ][$(acad_year).val()][$(residency).val()]) {
              $(acad_career).val(i);
              break;
            }
          }

          enable_element(acad_career);
        }
      }
    */
/**
 * Load the campuses into the campus field.
 */
/*  function load_campus() {
        var json = { '': js_settings.campus[''] };
        try {
          $.extend(json, js_settings.campus[$(acad_career).val()]);
        } catch (err) {
          // Do nothing as json is aready populated.
        }
        populate_select_element(campus, json);

        //hide ASU local -LA for all residents execpt for CA residents

        // Disable campuses that do not have WUE eligible programs for undergrads only.
        if ($(acad_career).val() in js_settings['wue']) {
          if ($(residency).val() == 'WUE') {
            $(campus)
              .find('option')
              .each(function (index) {
                if (
                  index != 0 &&
                  !(
                    $(this).attr('value') in
                    js_settings['wue'][$(acad_career).val()]
                  )
                ) {
                  $(this).attr('disabled', 'disabled');
                }
              });
            if (
              !($(campus).val() in js_settings['wue'][$(acad_career).val()])
            ) {
              // Get the first campus and set the field to it.
              for (i in js_settings['wue'][$(acad_career).val()]) {
                $(campus).val(i);
                break;
              }
            }
            // Show WUE message under acad_prog field.
            $('#wue-help-acad-prog').show();
          } else {
            $(campus).find('option').removeAttr('disabled');
            // Hide WUE message under acad_prog field.
            $('#wue-help-acad-prog').hide();
          }
        }

        // Disable options not available for corporate partner.
        if (js_settings.corporate_partner) {
          $(campus)
            .find('option')
            .each(function (index) {
              $(this).removeAttr('disabled');
              if (
                index != 0 &&
                !(
                  $(this).attr('value') in
                  js_settings['corporate_partner_fields'][
                    $(corporate_partner).val()
                  ][$(acad_year).val()][$(residency).val()][
                    $(acad_career).val()
                  ]
                )
              ) {
                $(this).attr('disabled', 'disabled');
              }
            });
          if (
            !(
              $(campus).val() in
              js_settings['corporate_partner_fields'][
                $(corporate_partner).val()
              ][$(acad_year).val()][$(residency).val()][$(acad_career).val()]
            )
          ) {
            // Get the first option and set the field to it.
            for (i in js_settings['corporate_partner_fields'][
              $(corporate_partner).val()
            ][$(acad_year).val()][$(residency).val()][$(acad_career).val()]) {
              $(campus).val(i);
              break;
            }
          }

          enable_element(campus);
        }

        var residency_value = $('#edit-residency').val();
        //console.log(residency_value);
        if (residency_value == 'CARES') {
          $('#edit-campus').find('option:not([value^=LOSAN])').hide();
          //$(acad_career).find('option:not([value^=UGRD])').attr('disabled', 'disabled');
        } else {
          //$('#edit-campus').find("option[value='LOSAN']").hide();
        }
      }
    */
/**
 * Load the programs into the acad_prog field.
 */
/*  function load_acad_program() {
        var json = { '': js_settings.acad_prog[''] };
        // console.log('campus l', campus);
        $.extend(
          json,
          js_settings.acad_prog[$(acad_career).val()][$(campus).val()],
        );
        populate_select_element(acad_prog, json);
        toggle_acad_program();
        toggle_program_fee();

        // Remove non-WUE eligible programs.
        if ($(residency).val() == 'WUE') {
          if (
            $(acad_career).val() in js_settings['wue'] &&
            $(campus).val() in js_settings['wue'][$(acad_career).val()]
          ) {
            $(acad_prog)
              .find('option')
              .each(function (index) {
                if (
                  index != 0 &&
                  !(
                    $(this).attr('value') in
                    js_settings['wue'][$(acad_career).val()][$(campus).val()]
                  )
                ) {
                  $(this).remove();
                }
              });
          }
        }
      }
    */
/**
 * Load the programs into the acad_prog field.
 */
/*  function load_program_fee() {
        if ($(acad_prog).val() != '') {
          var json = { '': js_settings.program_fee[''] };
          try {
            $.extend(
              json,
              js_settings.program_fee[$(acad_year).val()][$(residency).val()][
                $(acad_career).val()
              ][$(campus).val()][$(acad_prog).val()],
            );
          } catch (err) {
            // Do nothing as json is aready populated.
          }
          console.log('program fee json', json);
          populate_select_element(program_fee, json);
          toggle_acad_program();
          toggle_program_fee();
        }
      }
    */
/**
 * Toggles the disabled flag on the acad_prog field.
 */
/*  function toggle_acad_program() {
        if (
          $(acad_prog).find('> option').length < 2 ||
          $(acad_career).val() == 'UGRDN'
        ) {
          // disable_element(acad_prog);
          select_only_option(acad_prog);
        } else {
          enable_element(acad_prog);
          select_only_option(acad_prog);
        }
      }
    */
/**
 * Toggles the disabled flag on the acad_prog field.
 */
/*  function toggle_program_fee() {
        if (
          $(acad_prog).val() == '' ||
          $(program_fee).find('> option').length < 2 ||
          $(acad_career).val() == 'UGRDN'
        ) {
          //disable_element(program_fee);
          select_only_option(program_fee);
        } else {
          enable_element(program_fee);
          select_only_option(program_fee);
        }

        // Disable program_fee if residency = WUE for 2019 and prior.
        if (
          $(residency).val() == 'WUE' &&
          parseInt($(acad_year).val()) <= 2022
        ) {
          disable_element(program_fee);
        }
      }
    */
/**
 * This function disables certain options when certain years are selected.
 */
/*  function acad_year_change() {
        // Determine if the summer checkbox should be displayed.

        if (parseInt(js_settings.include_summer[$(acad_year).val()])) {
          enable_element(include_summer);
        } else {
          $(include_summer).prop('checked', false);
          disable_element(include_summer);
        }

        load_residency();
        // Go to the next change handler.
        residency_change();
      }

      // Updated to accommodate SFAODEV-2051
      function residency_change() {
        // Enable all acad_career options and set acad_career to default (undergrad)
        $(acad_career).find('option').removeAttr('disabled');
        $(acad_career).val(js_settings.form_defaults.acad_career);

        // Update enabled acad_career options based on selected residency
        switch ($(residency).val()) {
          case 'WUE':
            // Disable all careers other than undergrad.
            $(acad_career)
              .find('option[value!=UGRD]')
              .attr('disabled', 'disabled');
            break;
          case 'AZHS':
            // Disable all careers other than undergrad and undergrad nondegree.
            $(acad_career)
              .find('option:not([value^=UGRD])')
              .attr('disabled', 'disabled');
            break;
          default:
            // Do nothing, i.e., keep all acad_career options enabled.
            break;
        }

        load_acad_career();
        // Go to the next change handler.
        acad_career_change();
      }

      function acad_career_change() {
        // Disable honors field if not degree-seeking undergraduate.
        if ($(acad_career).val() != 'UGRD') {
          disable_element(honors);
        } else {
          enable_element(honors);
        }

        load_campus();
        // Go to the next change handler.
        campus_change();
      }

      function campus_change() {
        load_acad_program();
        // Go to the next change handler.
        acad_prog_change();
      }

      function acad_prog_change() {
        // Only show admit_term and admit_level for certain acad_years and acad_progs.
        var show_base_term_fields = false;
        try {
          if (
            js_settings['base_term_fee_code'][$(acad_year).val()][
              $(residency).val()
            ][$(acad_career).val()][$(campus).val()][$(acad_prog).val()]
          ) {
            show_base_term_fields = true;
          }
        } catch (err) {
          show_base_term_fields = false;
        } finally {
          if (show_base_term_fields) {
            // Only show admit_term for acad year 2019 and later.
            if (parseInt($(acad_year).val()) <= 2019) {
              enable_element(admit_term);
            } else {
              disable_element(admit_term);
            }
            if ($(acad_career).val() === 'UGRD') {
              enable_element(admit_level);
            }
          } else {
            $(admit_term).val('');
            $(admit_level).val('');
            disable_element(admit_term);
            disable_element(admit_level);
          }
        }

        // Reload programs.
        load_program_fee();
        update_wrappers();
      }

      function populate_select_element(element, new_options) {
        if (element && new_options) {
          var current_value = $(element).val();
          // Populate options.
          element[0].options.length = 0;
          var x;
          var i = 0;
          for (x in new_options) {
            element[0].options[i] = new Option(new_options[x], x);
            i += 1;
          }

          // Drupal prepopulates our box and there is a discrepancy between our
          // selected index and Drupal's, specifically on page 2 of multi-page forms
          // this uses the value and matches it with index.
          $(element).val(current_value);
        }
      }
  */
/**
 * Select only option when a drop-down only has the blank and one other option.
 */
/*    function select_only_option(element) {
        if ($(element).find('> option').length == 2) {
          $(element).val($(element).find('option:last').attr('value'));
        }
      }
  */
/**
 * Enabling is straight forward.
 */
/*  function enable_element(element) {
        // For corporate partner, the residency, acad_career, and campus fields
        // require special attention. Only show field if more than one option
        // is available.
        if (js_settings.corporate_partner) {
          $(element)
            .filter('[name="residency"], [name="acad_career"], [name="campus"]')
            .each(function () {
              if ($(this).find('option:enabled[value!=""]').length > 1) {
                $(this).closest('.form-item').closest('.form-item').show();
              } else {
                $(this).closest('.form-item').closest('.form-item').hide();
              }
            });
          $(element)
            .filter('[name!="residency"][name!="acad_career"][name!="campus"]')
            .closest('.form-item')
            .show();
        } else {
          $(element).closest('.form-item').show();
        }
      }
    */
/**
 * Disabling is not as straight forward.
 */
/*  function disable_element(element) {
        // We are only hiding now so the value does not have to be touched.
        $(element).closest('.form-item').hide();
      }

      function update_wrappers() {
        $('#asu-tuition-search-form fieldset.option-fieldset.form-wrapper')
          .show()
          .each(function () {
            $(this)
              .attr('data-show', 'false')
              .find('.fieldset-wrapper .form-item:visible')
              .closest('.form-wrapper')
              .attr('data-show', 'true');
            if ($(this).attr('data-show') == 'true') {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
      }

      //load results upon calucalte button click
      // $('.tuition-calculator-button').on('click', function(){
      //$('.tuition-calculator-button').once().on('click', function(){
      $('.tuition-calculator-button')
        .off('click')
        .on('click', function () {
          $('initial_help_div_text').hide();
          //call_ajax_function(credit_hr);
          var form_data = $('#asu-tuition-search-form').serializeArray();
          //console.log(form_data);
          var max_credit_hr = '';
          var default_hr = '';
          var ini_residency_value = $('#edit-residency').val();
          var ini_campus_value = $('#edit-campus').val();
          var stu_status = $('#edit-acad-career').val();
          //console.log(stu_status);

          if (
            ini_residency_value == 'NORES' ||
            ini_residency_value == 'AZHS' ||
            ini_residency_value == 'WUE' ||
            ini_residency_value == 'INTL' ||
            ini_residency_value == 'CARES'
          ) {
            max_credit_hr = 18;
            default_hr = 12;
          } else {
            max_credit_hr = 18;
            default_hr = 7;
          }

          if (
            ini_campus_value == 'COCHS' ||
            ini_campus_value == 'CALHC' ||
            ini_campus_value == 'PIMA' ||
            ini_campus_value == 'CAC' ||
            ini_campus_value == 'EAC' ||
            ini_campus_value == 'YAVAP' ||
            ini_campus_value == 'AWC' ||
            ini_campus_value == 'LOSAN'
          ) {
            max_credit_hr = 18;
            default_hr = 12;
          }

          if (ini_campus_value == 'ONLNE' || ini_campus_value == 'TBIRD') {
            max_credit_hr = 18;
            default_hr = 7;
          }

          if (stu_status == 'LAW') {
            max_credit_hr = 18;
            default_hr = 18;
          }
          //console.log(max_credit_hr);
          var options = new Array();
          var i = '';
          for (i = 0; i <= max_credit_hr; i++) {
            options[i] = i;
          }

          //console.log(options);
          //$('select[name="credit_hrs"]').val('12');
          var $el = $('select[name="credit_hrs"]');
          $el.empty(); // remove old options

          $.each(options, function (key, value) {
            //console.log(value);
            $el.append(
              $('<option></option>')
                .attr('value', value)
                .text(key + ' hours'),
            );
            $("select[name='credit_hrs']  option[value='0']").remove();
            $(
              'select[name="credit_hrs"] option[value="' + default_hr + '"]',
            ).prop('selected', true);
          });
          $('html, body').animate(
            {
              scrollTop: $('.tuition-results-rhs').offset().top,
            },
            0,
          );
          validateForm(form_data);
        });

      //$('select[name="credit_hrs"]').once().on('change', function(){
      $('select[name="credit_hrs"]').on('change', function () {
        var cre_hr = $(this).val();
        $('select[name="credit_hrs"]').val(cre_hr);
        call_ajax_function(cre_hr);
      });

      function validateForm(form_data) {
        let error_free = true;

        $('select').each(function () {
          const $select = $(this);
          const element_value = $select.val();
          const element_id = $select.attr('id') + '_error';
          const error_message =
            $select.attr('data-msg-required') || 'Field is required.';

          // Check if it's required and empty
          if (
            $select.hasClass('required') &&
            (!element_value || element_value.length === 0)
          ) {
            $select.addClass('error');
            error_free = false;

            if ($('#' + element_id).length === 0) {
              $select.after(
                `<label id="${element_id}" class="error tu-cus-error" for="${element_id}">${error_message}</label>`,
              );
            } else {
              $('#' + element_id).show();
            }
          } else {
            $select.removeClass('error');
            $('#' + element_id).hide();
          }
        });

        // Proceed if no errors
        if (error_free) {
          const new_credit_hr = $('select[name="credit_hrs"]').val();
          call_ajax_function(new_credit_hr);
        }
      }

      var ini_cre_hr = $('select[name="credit_hrs"]').val();
      //console.log(ini_cre_hr);

      function call_ajax_function(credit_hr) {
        //console.log(credit_hr);

        var acad_year_value = $('#edit-acad-year').val();
        console.log('acad_year_value', acad_year_value);
        var acad_year_text = $('#edit-acad-year option:selected').text();
        //console.log(acad_year_text);
        $('.acad-year-text').text(acad_year_text);
        $('.credit-text').text(credit_hr);
        var acad_year_text = $(
          'select[name="acad_year"] option:selected',
        ).text();
        var include_summer_check = $('input[name="include_summer"]').is(
          ':checked',
        );
        if (include_summer_check == false) {
          include_summer_value = 0;
        }
        if (include_summer_check == true) {
          include_summer_value = 1;
        }

        var residency_value = $('#edit-residency').val();
        var career_value = $('#edit-acad-career').val();
        var campus_value = $('#edit-campus').val();
        var acad_prog_value = $('#edit-acad-prog').val();
        var program_fee_value = $('#edit-program-fee').val();
        //var honors_init_value = $('input[name="honors"]:checked').val();
        var honors_init_value = $('input[name="honors"]').is(':checked');
        var term_value = $('#edit-admit-term').val();
        var acad_level_value = $('#edit-admit-level').val();
        if (honors_init_value == false) {
          honors_value = 0;
        }
        if (honors_init_value == true) {
          honors_value = 1;
        }
        //console.log(honors_init_value);

        //$('#edit-credit-hrs')

        var result_url =
          full_site +
          '/tuition/results/json?acad_year=' +
          acad_year_value +
          '&include_summer=' +
          include_summer_value +
          '&residency=' +
          residency_value +
          '&acad_career=' +
          career_value +
          '&campus=' +
          campus_value +
          '&acad_prog=' +
          acad_prog_value +
          '&admit_term=' +
          term_value +
          '&admit_level=' +
          acad_level_value +
          '&honors=' +
          honors_value +
          '&program_fee=' +
          program_fee_value +
          '&credit_hr=' +
          credit_hr;
        console.log(result_url);
        $.ajax({
          url: result_url,
          dataType: 'text',
          contentType: 'application/json',
          cache: false,
          async: false,
          success: function (data) {
            var jsonData = $.parseJSON(data);
            var jsonIntlString = jsonData.resultsData;
            if (data.length > 20) {
              $('#results_div').html(jsonIntlString);
            } else {
              $('#results_div').html('');
            }

            // if($('.calculator-result-heading').length == 0){

            // }
          },
          error: function () {
            $('#results_div').html('');
          },
        });
        $('.form-item-credit-hrs').show();

        $('.tuition-result-heading').show();
        var ele_length = $('.tuition_all_link').length;

        if (ele_length == 1) {
          //code to change + sign to - sign  and hide price when table is shown in in results table header
          $('.tuition-result-a').on('click', function () {
            console.log('clicked');
            e.preventDefault();
            var id = $(this).attr('id');
            //console.log(id);
            var showhide2 = '';
            //$('#'+id).children('.header-price').toggleClass('hide');
            if ($(this).hasClass('collapsed')) {
              $(this).children('.header-price').removeClass('hide');
              $('.hide-show-link').text('Hide all');
            } else {
              $(this).children('.header-price').addClass('hide');
              $('.hide-show-link').text('Show all');
            }
            //$('.show').sibling('.card-header').find('.header-price').toggleClass('hide');
            $(this)
              .children('.tuition-icon')
              .find('svg')
              .toggleClass('fa-circle-plus fa-circle-minus');
           
          });
        }

        //change show/hide text and hide/show tuition tables
        //change show/hide text and hide/show tuition tables
        $('.hide-show-link').on('click', function () {
          // e.preventDefault();
          var $link = $(this);
          var $collapses = $('#accordionTuition').find('.collapse');
          var $icons = $('.tuition-result-a').find('.tuition-icon svg');
          var isShowAll = $link.text().trim() === 'Show all';

          if (isShowAll) {
            $collapses.addClass('show');
            $('.header-price').addClass('hide');
            $link.text('Hide all');
            $icons.removeClass('fa-circle-plus').addClass('fa-circle-minus');
          } else {
            $collapses.removeClass('show');
            $('.header-price').removeClass('hide');
            $link.text('Show all');
            $icons.removeClass('fa-circle-minus').addClass('fa-circle-plus');
          }
        });*/

/*$(".hide-show-link").on("click", function () {
          var link_text = $(".hide-show-link").text();
          //console.log(link_text);
          var showhide = Boolean;
          var showhide1 = "";
          $("#accordionTuition")
            .find(".collapse")
            .each(function () {
              showhide1 = $(".show").length;
            });
          console.log(showhide1);
          var showhide = $("#accordionTuition")
            .find(".collapse")
            .hasClass("show").length;
          console.log(showhide1);
          if (link_text == "Show all") {
            $(".header-price").addClass("hide");
            if (showhide1 == 2 || showhide1 == 0) {
              $("#accordionTuition")
                .find(".collapse")
                .each(function () {
                  $(this).addClass("show");
                });
              $(".hide-show-link").text("Hide all");
              $(".tuition-result-a").each(function () {
                $(this)
                  .children(".tuition-icon")
                  .find("svg")
                  .removeClass("fa-circle-plus");
                $(this)
                  .children(".tuition-icon")
                  .find("svg")
                  .addClass("fa-circle-minus");
              });
              //$('.tuition-icon').children('svg').removeClass('fa-plus-circle');
              //$('.tuition-icon').children('svg').addClass('fa-minus-circle');
            }
          }
          if (link_text == "Hide all") {
            $(".header-price").removeClass("hide");
            if (showhide1 > 0) {
              $("#accordionTuition").find(".collapse").removeClass("show");
              $(".hide-show-link").text("Show all");
              //$('.tuition-icon').children('svg').removeClass('fa-minus-circle');
              //$('.tuition-icon').children('svg').addClass('fa-plus-circle');
              $(".tuition-result-a").each(function () {
                $(this)
                  .children(".tuition-icon")
                  .find("svg")
                  .removeClass("fa-circle-minus");
                $(this)
                  .children(".tuition-icon")
                  .find("svg")
                  .addClass("fa-circle-plus");
              });
            }
          }

        });*/
/*}

      $('.tuition-reset-button').on('click', function () {
        $('select').each(function () {
          $(this).val('');
        });
        $("input[type='checkbox']").each(function () {
          $(this).prop('checked', false);
        });
        $('#results_div').html(
          '<div id="results_div"><img src="' +
            full_site +
            '"/sites/default/files/2022-08/tuition_background.png" alt="Tuition calculator placeholder image" class="img-fluid" data-v-1a0316f1="" data-v-3033d586=""><div class="initial_help_div_text"><div class="initial_help_p_text"><div class="tr-hr">&nbsp;</div><br />Simply input a few pieces of information into the tuition calculator and choose “Calculate” to see the estimated cost breakdown.</div></div>',
        );
        $('.tuition-result-heading').hide();
        $('.form-item-credit-hrs').hide();
      });
    },
  };
})(jQuery, Drupal, drupalSettings);*/
