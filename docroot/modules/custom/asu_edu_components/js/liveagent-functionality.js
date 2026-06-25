/**
 * @file
 * ASU.edu Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.asuEduComponentsLiveAgentFunctionality = {
    attach: function (context, settings) {
      var $button = $('a[href="https://internal.livechat.asu.edu/available"]', context);

      $button.hide().after('<a href="https://internal.livechat.asu.edu/unavailable" class="btn btn-maroon ml-0" style="display: none">' + Drupal.t('Live chat (unavailable)') + '</a>');

      /* $button.once('liveAgentFunctionality').click(function(ev) {
        ev.preventDefault();
        liveagent.startChat('5730W000001YVMV');
      }); */

      $(once('liveAgentFunctionality', $button)).click(function(ev) {
        ev.preventDefault();
        liveagent.startChat('5730W000001YVMV');
      });

      liveagent.init('https://d.la4-c2-ia5.salesforceliveagent.com/chat', '5720W000001UJDB', '00Dd0000000hLoc');
      
    }
  };

} (jQuery, Drupal));
