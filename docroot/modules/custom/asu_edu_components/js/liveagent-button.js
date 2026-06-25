/**
 * @file
 * ASU.edu Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.asuEduComponentsLiveAgentButton = {
    attach: function (context, settings) {
      if (!window._laq) {
        window._laq = [];
      }
      window._laq.push(function () {
        liveagent.showWhenOnline('5730W000001YVMV', document.querySelector('a[href="https://internal.livechat.asu.edu/available"]'));
        liveagent.showWhenOffline('5730W000001YVMV', document.querySelector('a[href="https://internal.livechat.asu.edu/unavailable"]'));
      });
    }
  };

} (jQuery, Drupal));
