(function ($, Drupal) {
  Drupal.behaviors.storiesExcellence = {
    attach: function (context, settings) {
      $(once('stories-of-excellence', '.stories-of-excellence', context))
        .each(function () {
          const pushStoriesExcellenceGAEvent = (args) => {
            const { dataLayer } = window;
            const event = {
              event: 'link',
              action: 'click',
              name: 'onclick',
              type: 'internal link',
              region: 'main content',
              section: 'stories of excellence',
              ...args,
            };
            if (dataLayer) dataLayer.push(event);
          };

          const elements = document.querySelectorAll(
            '[data-ga-story-excellence]'
          );
          elements.forEach((element) =>
            element.addEventListener('focus', () => {
              const args = {
                text: element
                  .getAttribute('data-ga-story-excellence')
                  .toLowerCase(),
              };
              pushStoriesExcellenceGAEvent(args);
            })
          );

        }
      );
    }
  }
}(jQuery, Drupal));