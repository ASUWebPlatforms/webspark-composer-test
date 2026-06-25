(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.countdown = {
    attach: function (context, settings) {
      
      $( document ).ajaxComplete(function() {
        // Add CSS classes for styling purposes
//        $('#jquery-countdown-timer-timer1').addClass('d-flex justify-content-center bg-gray-7 p-2');
//        $('.inner-wrap.Weeks').addClass('mr-4');
//        $('.inner-wrap.Days').addClass('mr-4');
//        $('.countdown-label').addClass('ml-2');
//        $('.digit.static').addClass('text-white');
      });
    }
  };
})(jQuery, Drupal, drupalSettings);

