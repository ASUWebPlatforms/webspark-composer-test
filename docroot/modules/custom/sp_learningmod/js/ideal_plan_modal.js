(function ($, Drupal, once) {
  Drupal.behaviors.idealPlanModal = {
    attach: function (context, settings) {
      once('modalTrigger', '.response-link', context).forEach((element) => {
        $(element).on('click', function (e) {
          e.preventDefault();
          var targetModal = $(this).attr('data-target');
          $(targetModal).modal('show');
        });
      });
    }
  };
})(jQuery, Drupal, once);
