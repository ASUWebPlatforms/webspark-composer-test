/**
 * @file
 * Custom scripts for Great Game Lab theme.
 */

(function ($, Drupal) {
    'use strict';
    
    Drupal.behaviors.exposedFormSelect = {
      attach: function (context, settings) {
        const select = once('exposed-form-select', '#edit-field-tags-target-id', context);
        
        $(select).on('focus', function() {
          const firstOption = this.options[0];
          if (firstOption.text === 'Categories') {
            firstOption.text = '- Any -';
          }
        }).on('blur', function() {
          const firstOption = this.options[0];
          if (firstOption.text === '- Any -' && this.value === 'All') {
            firstOption.text = 'Categories';
          }
        });
      }
    };
    
  })(jQuery, Drupal);