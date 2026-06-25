(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.specialfinancialassistance = {
    attach: function (context, settings) {

      // Select a tab when button is clicked.
      $('a#nav-home-btn').click(function() {
        $('#nav-home-tab')[0].click();
      });
      $('a#nav-profile-btn').click(function() {
        $('#nav-profile-tab')[0].click();
      });
      $('a#nav-contact-btn').click(function() {
        $('#nav-contact-tab')[0].click();
      });

    }
  };
})(jQuery, Drupal, drupalSettings);