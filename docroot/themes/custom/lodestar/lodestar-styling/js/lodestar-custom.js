(function ($) {
  $(document).ready(function () {
    // Add a click handler to all links in the nav-list to prevent default behavior
    $('.nav-list a').on('click',(e) => {
      let target = e.target;
      if(e.target.tag !== 'A') {
        target = e.target.closest('a');
      }
      let link = target.getAttribute('href');
      if (link === undefined || link.length === 0 || link === '#') {
        e.preventDefault();
      }
    });
  });
})(jQuery);
