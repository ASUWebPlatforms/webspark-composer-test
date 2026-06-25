import 'popper.js';
import 'bootstrap';

(function (Drupal, $, once) {

  'use strict';

  Drupal.behaviors.participants = {
    attach: function (context, settings) {
      /* Functions to disable/enable selects and options on participants exposed filters */
      $(document).ready(function () {
        var userPeerSelect = $(".form-item-field-user-peer-group-target-id select");
        if (userPeerSelect.find(":selected").val() === '15') {
          var teamCohorts = ['All', '47', '48', '49', '63'];

          $('.form-item-field-user-cohort-class-target-id option').each(function(){
            if (teamCohorts.includes($(this).val())) {
              $(this).prop('disabled', false);
            } else  {
              $(this).prop('disabled', true);
            }
          });
          $('.form-item-field-user-team-name-target-id select').removeAttr('disabled');
        } else if (userPeerSelect.find(":selected").val() === '14') {
          $('.form-item-field-user-team-name-target-id select').attr('disabled', true);
          $('.form-item-field-user-cohort-class-target-id option').each(function(){
            $(this).prop('disabled', false);
          });
        } else {
          $('.form-item-field-user-cohort-class-target-id option').each(function(){
            $(this).prop('disabled', false);
          });
        }
      })
    }
  };

})(Drupal, jQuery, once);
