/**
 * @file
 * customrenovation behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.access = {
    attach: function (context, settings) {

      /* Remove some section tags off audit */
      var els = document.querySelectorAll('section.div_only');
      for (var i = 0; i < els.length ; i++) {
          els[i].outerHTML = '<div class="question-set">' + els[i].innerHTML + '</div>';
      };
    }
  };
} (jQuery, Drupal));
