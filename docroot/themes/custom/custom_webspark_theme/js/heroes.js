(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.controlVideo = {
    attach: function (context) {
      // Use Drupal.once() to process elements
      once(
        "videoButtonClickBehaviour",
        ".uds-video-hero button",
        context,
      ).forEach(function (button) {
        $(button).click(function (e) {
          e.stopImmediatePropagation();
          var $button = $(this);
          var $video = $button.closest(".uds-video-hero").find("video");
          var $buttonsContainer = $button.closest(".buttons");

          if ($button.hasClass("play")) {
            $video.get(0).play();
            $button.hide();
            $buttonsContainer.find("button.pause").show();
          }

          if ($button.hasClass("pause")) {
            $video.get(0).pause();
            $button.hide();
            $buttonsContainer.find("button.play").show();
          }
        });
      });
    },
  };
})(jQuery, Drupal, once);
