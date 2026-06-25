import 'popper.js';
import 'bootstrap';

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.signupButton = {
    attach: function (context) {
      $(document).ready(function() {
        const $button = $("#asuHeader .buttons-container a");
        const originalWidth = $(window).width();
        if (originalWidth > 1261) {
          $button.attr("href", "#stayuptodate");
        }
      });

      $(window).resize(function() {
        const $button = $("#asuHeader .buttons-container a");
        let newWidth = $(window).width();
        if (newWidth > 1280) {
          $button.attr("href", "#stayuptodate");
        } else {
          $button.attr("href", "/stayuptodate#stayuptodate");
        }
      });
    },
  };

})(jQuery, Drupal);
