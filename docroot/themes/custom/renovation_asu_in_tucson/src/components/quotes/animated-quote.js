(function($, Drupal) {
  Drupal.behaviors.asuEduAnimatedQuotes = {
    attach: function(context, settings) {
      //-----------------------------------------------------------------------
      // Animated Quote
      //-----------------------------------------------------------------------
      $(once('animatedQuote', '.block-inline-blockasu-edu-animated-quote .highlight-gold', context))
        .each(function() {
          const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                entry.target.classList.add('animate-bg-in-scroll');
              } else {
                entry.target.classList.remove('animate-bg-in-scroll');
              }
            });
          });

          observer.observe($(this).get(0));
        });
    }
  }
}(jQuery, Drupal));
