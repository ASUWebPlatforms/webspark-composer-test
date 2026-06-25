(function($, Drupal, drupalSettings) {
  Drupal.behaviors.csrdUtils = {
    attach: function(context, settings) {
      // Adding cart icon to navigation
      //const cartIcon = document.createElement("span");
      console.log("Line 6 console log");
      // $(cartIcon).addClass("fa-shopping-cart fas");
      // $(cartIcon).css("paddingRight", "8px");

      // if (drupalSettings.cartItems > 0) {
      //   const cartItems = document.createElement("span");
      //   $(cartItems).html(drupalSettings.cartItems + " items in ");
      //   $("nav > div > form > a").once().prepend(cartItems);
      //   $(cartItems).once().prepend(cartIcon);
      // }

      // $("nav > div > form > a").once().prepend(cartIcon);

      // // Code for main store view.
      // const productPreviewImageHeight = $(".product-img").height();
      // $(".overlay-img-product").css("height", productPreviewImageHeight);
      // $(".button-img-product").css("top", productPreviewImageHeight / 2 - 15);

      const closeButton = $(".close-modal");
      $(closeButton).click(() => {
        const iconClose = $(".ui-icon-closethick");

        iconClose.click();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
