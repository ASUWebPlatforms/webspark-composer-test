(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.provostaboutstaff = {
    attach: function (context, settings) {
      // JS for /about/staff page
      
      // Insert 'profile-img' CSS class.
      $('.views-field-field-user-picture > .field-content > img').addClass('profile-img');
      // Add div with person class
      $(".uds-person-profile").once().wrapInner("<div class='person' />");
    }
  };
})(jQuery, Drupal, drupalSettings);