(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.aprpage = {
    attach: function (context, settings) {

      // When it is Specific pragrams selected, hide "Specific programs".
      // When it is All programs selected, hide "Program(s) under review".
      if ($(".programs").html().indexOf("Specific programs") >= 0) {
        console.log("it is specific programs");
        $(".programs").hide();
      } else if ($(".programs").html().indexOf("All programs") >= 0) {
        console.log("it is All programs");
        $(".apr-para-title").hide();
      }
    }
  };
})(jQuery, Drupal, drupalSettings);