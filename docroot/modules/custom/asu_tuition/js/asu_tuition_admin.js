(function ($, Drupal) {
  Drupal.behaviors.reapplyStates = {
    attach: function (context, settings) {

       // Trigger conditions-check if checked
       const len = $(context).find(':input[name="conditions-check"]').length;
       console.log(len);
       const $conditionsCheckbox = $('#edit-conditions-check', context);
       if ($(context).find(':input[name="conditions-check"]').is(':checked')) {
        // console.log('Triggering conditions-check change...');
         $(context).find(':input[name="conditions-check"]').trigger('change');
       }

       // Trigger join-check if checked
       const $joinCheckbox = $('#edit-join-check', context);
       if ($(context).find(':input[name="join-check"]').is(':checked')) {
         console.log('Triggering join-check change...');
         $(context).find(':input[name="join-check"]').trigger('change');
       }

      $(context)
        .find(':input[name="query_operations"]')
        .each(function () {
          // Trigger after states are initialized
          setTimeout(() => {
            console.log('Triggering change...');
            $(this).trigger('change');
          }, 50);
        });

    },
  };
})(jQuery, Drupal);
