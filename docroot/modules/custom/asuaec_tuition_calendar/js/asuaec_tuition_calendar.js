(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.tuitioncalendar = {
    attach: function (context, settings) {

      // Gray tr background - When checkbox is checked, change tr background color to gray.
      $('.gray').each(function( index ) {
        if ($(this).html() == '1') {
          $(this).parent().parent().css("background-color", "#fafafa");
        }
      });

      // ------------------------------------ Default value / Button clicks -------------------------------------------

      // When page loaded - Tuition and Billing
      var filter = $('input[data-drupal-selector=edit-field-ps-value-value]').val();
      changeActiveButtonColorTuitionBilling(filter);

      // When page loaded - Financial aid
      var filter = $('select[data-drupal-selector=edit-field-academic-year-target-id] option:selected').val();
      changeActiveButtonColorFinancialaid(filter);

      // Update filter for "Jump to" exposed filter



      // Change event when term button is clicked - Tuition and Billing
      $('.term-btn').click(function() {
        var filter_selected = $(this).attr('value');
        $('input[data-drupal-selector=edit-field-ps-value-value]').val(filter_selected).trigger('change');
        changeActiveButtonColorTuitionBilling(filter_selected);
      });

      // Change event when year button is clicked - Financial aid
      $('.year-btn').click(function() { // With Select field
        var filter_selected = $(this).attr('value');
        $('select[data-drupal-selector=edit-field-academic-year-target-id] option[value=' + filter_selected + ']').attr('selected', 'selected').trigger('change');
        changeActiveButtonColorFinancialaid(filter_selected);
      });

      // changeActiveButtonColor - Tuition and Billing
      function changeActiveButtonColorTuitionBilling(filter) {
        if (typeof filter === "undefined") {
          filter = $('input[data-drupal-selector=edit-field-ps-value-value]').val();
        }
        $('.term-buttons > table > tbody > tr').each(function( index, elem ) {
          // console.log("test", $(elem).find('a').attr('value'));
          if($(elem).find('a').attr('value') == filter) {
            if(!$(this).find('a').hasClass('btn-gold')) {
              $(this).find('a').addClass('active btn-gold');
            }
            $(this).find('a').removeClass('btn-gray');
          } else {
            $(this).find('a').removeClass('active btn-gold');
            if(!$(this).find('a').hasClass('btn-gray')) {
              $(this).find('a').addClass('btn-gray');
            }
          }
        });
      }

      // changeActiveButtonColor - Financial aid
      function changeActiveButtonColorFinancialaid(filter) {
        if (typeof filter === "undefined") {
          filter = $('select[data-drupal-selector=edit-field-academic-year-target-id]').val();
          // console.log("filter:", filter);
        }
        $('.year-buttons > table > tbody > tr').each(function( index, elem ) {
          // console.log("test", $(elem).find('a').attr('value'));
          if($(elem).find('a').attr('value') == filter) {
            if(!$(this).find('a').hasClass('btn-gold')) {
              $(this).find('a').addClass('active btn-gold');
            }
            $(this).find('a').removeClass('btn-gray');
          } else {
            $(this).find('a').removeClass('active btn-gold');
            if(!$(this).find('a').hasClass('btn-gray')) {
              $(this).find('a').addClass('btn-gray');
            }
          }
        });
      }


      // ------------------------------------------ Jump to ------------------------------------------------------

      // Add CSS id to caption
      $('caption').each(function( index ) {
        var mytext = $.trim($(this).text());
        mytext2 = mytext.replace(/ /g, '-');
        $(this).attr('id', mytext2);
        $(this).css('scroll-margin-top', '100px');
      });

      // Prep to use Flexbox
      $('.financialaid-calendar > .table-responsive').wrapAll('<div class="wrapped-tables" />');
      $('.financialaid-jumpto').parent().addClass('financialaid-jumpto-parent');
      $('.session-desc').parent().addClass('session-desc-parent mb-2');

      // Remove duplicate options
      $("#jumpto-select option").each(function() {
        $(this).siblings('[value="'+ this.value +'"]').remove();
      });

      $("#jumpto-select option").each(function() {
        // console.log($(this).html());
        var temp = $(this).html().replace("-", " ");
        $(this).html(temp)
      });

      // Set up change events for the dropdown list.
      $("#jumpto-select").change(function() {
        var valSelected = $("#jumpto-select option:selected").val();
        var protocol = window.location.protocol;
        var hostname = window.location.hostname;
        var path = window.location.pathname;
        window.location.href = protocol + "//" + hostname + path + "#" + valSelected;
        // window.location.href = "https://tuition-asu-csdev.ddev.site/asu-payment-schedule#January-2021";
      });


      // ------------------------------------------ Previous semester page -----------------------------------------------
      $('#views-exposed-form-tuition-and-billing-previous-semester-calendar-block-1 div[data-drupal-selector=edit-actions]').addClass("mb-4");



    }
  };
})(jQuery, Drupal, drupalSettings);