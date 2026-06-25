(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.azscholarshipjs = {
    attach: function (context, settings) {

      // Two page solution
      $('#cities-2pages').change(function() {
        if($(this).val() != '') {
          location.href = "/arizona-public-employee-scholarship-municipality-page?citynid=" + $(this).val();
        }
      });

    }
  };
})(jQuery, Drupal, drupalSettings);