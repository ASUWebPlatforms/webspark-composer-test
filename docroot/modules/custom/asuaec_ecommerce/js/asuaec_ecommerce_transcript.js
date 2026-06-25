(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.rfi = {
        attach: function (context, settings) {
            // Hide Autocomplete field that has "Transcript(1)" in the /transcript page.
            $('.commerce-order-item-add-to-cart-form-commerce-product-1 > div.field--name-purchased-entity').hide();
            // Styling
            $('#edit-quantity-wrapper').addClass('mb-2');
        }
    };
})(jQuery, Drupal, drupalSettings);