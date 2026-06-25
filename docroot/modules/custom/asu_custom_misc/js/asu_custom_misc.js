(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.searchbutton = {
    attach: function (context, settings) {
      
      // Temporary fix for Search button in header
      $('form[name="gs"]').attr('action', 'https://search.asu.edu/search');

    }
  };
})(jQuery, Drupal, drupalSettings);