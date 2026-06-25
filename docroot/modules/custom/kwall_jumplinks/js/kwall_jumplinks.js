(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.kwall_jumplinks = {
    attach: function (context, settings) {
      once('jump-link', '.jumplinks', context).forEach(function(el) {
        var sel = $('<select>');
        sel.append($("<option>").attr('value', '').text($('li:eq(0)', this).text()));
        $('li:eq(0)', this).remove();
/*
        $('li a', this).each(function() {
          sel.append($("<option>").attr('value', $(this).attr('href')).text($(this).text()));
        });
*/
        var currentoptgroup = false;
        $('li', this).each(function() {
          if ($('a', this).length > 0) {
            if (currentoptgroup !== false) {
              currentoptgroup.append($("<option>").attr('value', $('a', this).attr('href')).text($('a', this).text()));
            }
            else {
              sel.append($("<option>").attr('value', $('a', this).attr('href')).text($('a', this).text()));
            }
          }
          else {
            if (currentoptgroup !== false) {
              sel.append(currentoptgroup);
            }

            currentoptgroup = $("<optgroup label='" + $(this).text() + "'>");
          }
        });
        if (currentoptgroup !== false) {
          sel.append(currentoptgroup);
        }

        $(this).html(sel);
        sel.change(function() {
          if ($(this).val() != '') {
            window.location.href = $(this).val();
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
