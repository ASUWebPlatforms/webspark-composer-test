(function ($, Drupal, drupalSettings) {

    Drupal.behaviors.card = {
      attach: function (context, settings) {
        var tableArr = document.getElementsByClassName('table-responsive');
        tableArr[0].style.marginTop = "48px"
      }
    };
  })(jQuery, Drupal, drupalSettings);