(function($, Drupal, drupalSettings, once) {
  Drupal.behaviors.csrdUtils = {
    attach: function(context, settings) {
      // Adding cart icon to navigation
      const cartIcon = document.createElement("span");

      $(cartIcon).addClass("fa-shopping-cart fas");
      $(cartIcon).css("paddingRight", "8px");

      if (drupalSettings.cartItems > 0) {
        const cartItems = document.createElement("span");
        $(cartItems).html(drupalSettings.cartItems + " items in ");
        $(once('cartAdd','nav > div > form > a', context)).prepend(cartItems);
        $(once('iconAddFirst','nav > div > form > a', context)).prepend(cartIcon);
      }

      $(once('iconAdd','nav > div > form > a', context)).prepend(cartIcon);

      // Code for main store view.
      const productPreviewImageHeight = $(".product-img").height();
      $(".overlay-img-product").css("height", productPreviewImageHeight);
      $(".button-img-product").css("top", productPreviewImageHeight / 2 - 15);

      $(".close-modal").click(() => {
        $(".ui-dialog-titlebar-close").click();
      });
    }
  };
})(jQuery, Drupal, drupalSettings, once);
