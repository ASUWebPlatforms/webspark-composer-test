(function ($) {
  let flag = false;
  Drupal.behaviors.asu_campus_fit = {
    attach: function () {
    if (flag === false) {
      flag = true;
      $('.webform-button--submit').hide();
        //hide catcha initially
        $('#captcha').hide();
        var dynamic_results = drupalSettings.asu_campus_fit.fit_results; // drupalSettings.module.variablename
        //console.log(dynamic_results);
        var nid_array = dynamic_results['ofs'];
        $('#edit-wait-text').hide(); //hide wait icon
        var top_campus_nid = dynamic_results['top_campus_var_for_js'];
        var result = '';
        $('input[type="radio"]').change(function () {
          var ele_name = ($(this).attr('name'));
          result = $(this).val().split('-');
          checkArray = result[1] in nid_array;
          if(checkArray == true){
            $('input[name="result_nid"]').val(nid_array[result[1]]);
            $('.webform-button--submit').trigger('click');
            $('#edit-wait-text').show();
            $('#webform-submission-campus-fit-add-form').submit();
          }

          $(this).parents('.form-wrapper').addClass('fit-group');
            $('.webform-submission-campus-fit-form').find("." + result[1]).removeClass('fit-group');
          });

            //code to add Next button under checkboxes only if any of the options are selected
          //$('input[type="checkbox"]').change(function () {
          $('input[type="checkbox"]').each(function () {
            //$(this).parents('.js-webform-checkboxes').once().after('<input type="button" class="checkbox_next btn-gold btn" value="Continue" />');
            $(this).parents('.js-webform-checkboxes').each(function () {
              if (!$(this).data('processed')) {
                $(this).data('processed', true);
                $(this).after('<input type="button" class="checkbox_next btn-gold btn" value="Continue" />');
              }
            });
          });

          $(document).on('click','.checkbox_next', function () {
            $(this).parents('.form-wrapper').addClass('fit-group');
            $(this).parents('.form-wrapper').next('.form-wrapper').removeClass('fit-group');
          });

          //undergrad last question change code
          $('.ugrad-last-question').find('input[type="radio"]').change(function () {
            var copy_dynamic_results = drupalSettings.asu_campus_fit.fit_results; // drupalSettings.module.variablename
            $('.webform-button--submit').trigger('click');
            $('#edit-wait-text').show();
                //$('#webform-submission-campus-fit-add-form').submit();
          })

          //make ASU local question label to include field description as clickable
          var la_dec = $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-in-los-angeles-lalocalcaresults--description').text();
          var la_label_text = $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-in-los-angeles-lalocalcaresults--description').parent().siblings('label').text();
          $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-in-los-angeles-lalocalcaresults--description').parent().siblings('label').html(la_label_text + "<br /><span class='text-muted'>" + la_dec + "</span>");

          var local_dec = $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-local-9q--description').text();
          var local_label_text = $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-local-9q--description').parent().siblings('label').text();

          $('#edit-asu-has-several-options-that-would-fit-you-select-the-one-that-i-asu-local-9q--description').parent().siblings('label').html(local_label_text + "<br /><span class='text-muted'>" + local_dec + "<br /><br /></span>");

          var on_la_dec = $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-local-9q--description').text();
          
          var on_la_label_text = $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-local-9q--description').parent().siblings('label').text();
          
          $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-local-9q--description').parent().siblings('label').html(on_la_label_text + "<br /><span class='text-muted'>" + on_la_dec + "</span>");

          var on_local_dec = $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-online-13q--description').text();
          
          var on_local_label_text = $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-online-13q--description').parent().siblings('label').text();

          $('#edit-online-option-asu-has-several-options-that-would-fit-you-select-asu-online-13q--description').parent().siblings('label').html(on_local_label_text + "<br /><span class='text-muted'>" + on_local_dec + "<br /><br /><br /></span>");

          /** capture value of interest and save it to hidden field **/
          //$('input[name="would_you_rather_study"]').change(function(){
          $(document).on('change','input[name="would_you_rather_study"]', function () {
            var intval = jQuery(this).val();
            console.log(intval);
            if((intval == "Film-15q")    || (intval == "Fashion-15q")){
              $('input[name="film_fashion_interest_value"]').val(intval);
            }
          });
    }
  }
}

})(jQuery, Drupal, drupalSettings);