(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.transferoptiontag = {
    attach: function (context, settings) {
      // console.log("drupalSettings:", drupalSettings);

      // Transfer Option search for MAPP
      $('#views-exposed-form-transfer-option-search-block-1.views-exposed-form').addClass('row');
      $('#views-exposed-form-transfer-option-search-block-1 .form-item-campusstringarray').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-1 .form-item-plancatdescr').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-1 .form-item-keywordscombined').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-1 .form-item-diplomadescr').addClass('col-12  col-lg-6');
      $('#views-exposed-form-transfer-option-search-block-1 .form-actions').addClass('col-12  col-lg-2 mt-lg-3 ml-lg-2');

      // Transfer Option search for TAG - Community college exposed filter
      $('#views-exposed-form-transfer-option-search-block-3.views-exposed-form').addClass('row');
      $('#views-exposed-form-transfer-option-search-block-3 .form-item-tacommunitycollege').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-3 .form-item-campusstringarray').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-3 .form-item-plancatdescr').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-3 .form-item-keywordscombined').addClass('col-12  col-lg-4');
      $('#views-exposed-form-transfer-option-search-block-3 .form-item-diplomadescr').addClass('col-12  col-lg-5');
      $('#views-exposed-form-transfer-option-search-block-3 .form-actions').addClass('col-12  col-lg-2 mt-lg-3 ml-lg-2');

      // Add a red dot to indicate required field
      if($('#views-exposed-form-transfer-option-search-block-3 .form-item-tacommunitycollege > label').length) {
        // console.log("chizuko test");
        $('.form-item-tacommunitycollege > label').once().prepend('<svg title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title-Op71e0VQrEum" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-Op71e0VQrEum">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>');
      }
      
      // Insert a paragrapsh requested by Yesenia Rojas on 7/13/2022
      if($('#views-exposed-form-transfer-option-search-block-1 .form-actions').length > 0) {
        $('form#views-exposed-form-transfer-option-search-block-1').once().append("<div class='col-12 col-lg-3 mt-lg-3'><p>To view 2022-2023 pathways visit <a href='https://webapp4.asu.edu/transfercreditguide/app/transfermap?_ga=2.125865231.362001010.1657055583-937402149.1641234187'>MyPath2ASU majors</a></p></div>");
      }
      
      // Added spacing. Added on 7/13/2022.
      $('.views-element-container:first').addClass('pb-7');
      
      // Submit button click event
      $("#views-exposed-form-transfer-option-search-block-3 .js-form-submit").once().click(function(event){
        // console.log("event:", event);
        var error_free = true;
        // If "Select your college" is selected, don't submit the form.
        if($('select[name="tACommunityCollege"]').attr("selected", true).val() == '') {
          error_free = false;
        }
        if (error_free == true) {
          $( document ).ajaxComplete(function() {
            $('.table-responsive').show();
          });
        }

        else if (error_free == false){
          $('select[name="tACommunityCollege"]').addClass('error is-invalid');
          alert("Please select your college");
          event.preventDefault();

          // Hide search results
          $( document ).ajaxComplete(function() {
            $('.table-responsive').hide()
          });

          return false;

        }
      });




    }
  };
})(jQuery, Drupal, drupalSettings);
