/**
 * @file
 * Global utilities.
 *
 */


(function ($,Drupal) {

  'use strict';
 
  Drupal.behaviors.resources = {
    attach: function(context, settings) {

      //Saves current scroll position

      $(window).scroll(function() {
        sessionStorage.scrollTop = $(this).scrollTop();
      });

      // Reloads page at last saved scroll location

        if (sessionStorage.scrollTop != "undefined") {
          $(window).scrollTop(sessionStorage.scrollTop);
        }         

        // Captures click events of all <a> elements with href starting with #
      $(document).on('click', 'a[href^="#"]', function(event) {
        // Click events are captured before hashchanges. Timeout
        // causes offsetAnchor to be called after the page jump.
        window.setTimeout(function() {
          offsetAnchor();
        }, 0);
      });

      // Set the offset when entering page with hash present in the url
      window.setTimeout(offsetAnchor, 0);

    }
  };


})(jQuery, Drupal);
