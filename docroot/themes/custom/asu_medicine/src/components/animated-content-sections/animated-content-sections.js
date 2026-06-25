(function ($, Drupal) {
  Drupal.behaviors.animatedContentSections = {
    attach: function (context, settings) {
      $(once('animated-content-section', '.animated-content-section', context))
        .each(function () {
          const pushAnimatedContentSectionGAEvent = (args) => {
            const { dataLayer } = window;
            const event = {
              event: 'link',
              action: 'click',
              name: 'onclick',
              type: 'internal link',
              region: 'main content',
              ...args,
            };
            if (dataLayer) dataLayer.push(event);
          };

          const elements = document.querySelectorAll(
            '[data-ga-animated-content-section]'
          );
          elements.forEach((element) =>
            element.addEventListener('focus', () => {
              const args = {
                section: element
                  .getAttribute('data-ga-animated-content-section-section')
                  .toLowerCase(),
                text: element
                  .getAttribute('data-ga-animated-content-section')
                  .toLowerCase(),
              };
              pushAnimatedContentSectionGAEvent(args);
            })
          );

        }
      );
    }
  }
}(jQuery, Drupal));