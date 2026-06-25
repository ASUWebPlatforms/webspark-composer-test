(function ($, Drupal) {
  Drupal.AjaxCommands.prototype.scrollTop = function (ajax, response) {
    var offset = $(response.selector).offset();
    var scrollTarget = response.selector;

    while ($(scrollTarget).scrollTop() == 0 && $(scrollTarget).parent()) {
      scrollTarget = $(scrollTarget).parent();
    }

    var header_height = 95;

    if (offset.top - header_height < $(scrollTarget).scrollTop()) {
      $(scrollTarget).animate({ scrollTop: offset.top - header_height }, 500);
    }
  };
})(jQuery, Drupal);
