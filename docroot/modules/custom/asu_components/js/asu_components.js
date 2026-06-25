/**
 * @file
 */

 (function ($, Drupal, once) {

  Drupal.behaviors.highlight = {

    attach: function (context) {
      $('.highlight-gold', context)
        .once('animatedQuote')
        .each(function () {
          const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                entry.target.classList.add('animate-bg-in-scroll');
              }
              else {
                entry.target.classList.remove('animate-bg-in-scroll');
              }
            });
          });

          observer.observe($(this).get(0));
        });
    }

  }

}(jQuery, Drupal, once));
