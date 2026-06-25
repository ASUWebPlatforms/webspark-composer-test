// Moved to Asset injector on 3/14/2025
/*
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.summerfinancialaid = {
    attach: function (context, settings) {

        var summer_a = 0x001;
        var summer_b = 0x010;
        var summer_c = 0x100;

        var groups = new Array();
        groups[0x000] = '.weeks-none';
        groups[0x001] = '.weeks-6';
        groups[0x010] = '.weeks-6';
        groups[0x011] = '.weeks-12';
        groups[0x100] = '.weeks-8';
        groups[0x101] = '.weeks-8';
        groups[0x110] = '.weeks-12';
        groups[0x111] = '.weeks-12';

        var current_group = 0x000;





        // Hide all groups on page load and then load the current group.
        $('table.group').hide();
        $(groups[current_group]).show();

        // Bind our handler to run on checkbox clicks.
        $('.session-selector').click(function() {
          var old_group = current_group;

          // XOR group with the value clicked to toggle the session.
          switch ($(this).val()) {
            case 'a':
              current_group ^= summer_a;
              break;
            case 'b':
              current_group ^= summer_b;
              break;
            case 'c':
              current_group ^= summer_c;
              break;
          }

          // Hide all tables first then show.
          $('table.group').hide();
          $(groups[current_group]).show();

          // When Session C is selected, disable Session A and B.
          if ($('#summer-c').is(':checked')) {
            $("#summer-a").attr("disabled", true);
            $("#summer-b").attr("disabled", true);
          } else {
            $("#summer-a").removeAttr("disabled");
            $("#summer-b").removeAttr("disabled");
          }

          // When Session A or C is selected, disable Session C.
          if ($('#summer-a').is(':checked') || $('#summer-b').is(':checked')) {
            $("#summer-c").attr("disabled", true);
          } else {
            $("#summer-c").removeAttr("disabled");
          }

        });

    }
  };
})(jQuery, Drupal, drupalSettings);
*/