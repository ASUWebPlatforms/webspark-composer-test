(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.mccain = {
    attach: function (context, settings) {
      // Hide the overlay when class is present
      $('.hide-overlay', context).each(function () {
        const container = $(this);

        // Process each .uds-card-and-image only once
        once('mccainOverlayImage', container.find('.uds-card-and-image')).forEach((element) => {
          const $element = $(element);

          // Get the computed background-image style
          const backgroundImage = $element.css('background-image');

          // Extract the image URL using a regex pattern
          const imageUrlMatch = backgroundImage.match(/url\((['"])?(.*?)\1\)/);

          if (imageUrlMatch && imageUrlMatch[2]) {
            // Set the background image to only the extracted URL
            $element.css('background-image', `url(${imageUrlMatch[2]})`);
          }
        });
      });

      // Find all elements with the .star-pattern-bg class and add a border at the top in a matching background
      $('.star-pattern-bg', context).each(function () {
        const element = $(this);

        // Get the computed background color of the element
        let backgroundColor = element.css('background-color');

        // If no valid background color is set, fallback to a default color
        if (backgroundColor === 'rgba(0, 0, 0, 0)' || !backgroundColor) {
          backgroundColor = '#ffffff';
        }

        // Apply the 10px solid border to the top with the determined background color
        element.css({
          'border-top': `10px solid ${backgroundColor}`
        });
      });
    },
  };
})(jQuery, Drupal, once);
