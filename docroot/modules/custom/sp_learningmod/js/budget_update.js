(function ($, Drupal, once) {
  Drupal.behaviors.budgetUpdate = {
    attach: function (context, settings) {
      let $progressBar = $('#budget-progress-bar');
      let costs = JSON.parse($('[data-drupal-selector="edit-responses-table"]').attr('data-costs') || '{}');

      function updateProgressBar(totalCost) {
        $progressBar.css('width', Math.min(totalCost, 100) + '%');

        $progressBar.text(totalCost > 0 ? totalCost + '%' : '');

        $progressBar.removeClass('green orange red');
        if (totalCost > 100) {
          $progressBar.addClass('red');
        } else if (totalCost >= 80) {
          $progressBar.addClass('orange');
        } else {
          $progressBar.addClass('green');
        }
      }

      function calculateTotalCost() {
        let totalCost = 0;
        $('[data-drupal-selector^="edit-responses-table-"]:checked').each(function () {
          let responseId = $(this).val();
          if (costs.hasOwnProperty(responseId)) {
            totalCost += parseInt(costs[responseId]);
          }
        });
        return totalCost;
      }

      $(window).on('load', function () {
        updateProgressBar(calculateTotalCost());
      });

      if (document.readyState === 'complete') {
        updateProgressBar(calculateTotalCost());
      }

      once('budgetUpdate', '[data-drupal-selector="edit-responses-table"] input[type="checkbox"]', context).forEach(function (element) {
        $(element).on('change', function () {
          updateProgressBar(calculateTotalCost());
        });
      });
    }
  };
})(jQuery, Drupal, once);
