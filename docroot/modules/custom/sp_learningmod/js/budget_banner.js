(function ($, Drupal, once) {
    Drupal.behaviors.budgetBanner = {
        attach: function (context, settings) {
            once('budget-bar-init', '.sp-budget-bar', context).forEach((progressBar) => {
                let $progressBar = $(progressBar);

                if ($progressBar.find('.loading-indicator').length > 0) {
                    return;
                }

                let percentage = parseInt($progressBar.attr('data-percentage')) || 0;

                if (percentage > 0) {
                    Drupal.spAnimateBudgetBar($progressBar, percentage);
                }
            });

            once('budget-close', '.cerrar-boton', context).forEach((btn) => {
                $(btn).on('click', function () {
                    $('#budget-banner').fadeOut();
                });
            });
        }
    };

    Drupal.spAnimateBudgetBar = function ($progressBar, percentage) {
        $progressBar.find('.loading-indicator').remove();

        percentage = parseInt(percentage) || 0;

        $progressBar.removeClass('green orange red');
        if (percentage > 100) {
            $progressBar.addClass('red');
        } else if (percentage >= 80) {
            $progressBar.addClass('orange');
        } else {
            $progressBar.addClass('green');
        }

        $progressBar.attr('data-percentage', percentage);

        let displayWidth = Math.min(percentage, 100);

        if (percentage === 0) {
            $progressBar.css({
                'width': '0%',
                'transition': 'none'
            });
            $progressBar.text('');
            return;
        }

        $progressBar.css({
            'width': '0%',
            'transition': 'none'
        });

        $progressBar[0].offsetWidth;

        $progressBar.css({
            'transition': 'width 1s ease-out',
            'width': displayWidth + '%'
        });

        let duration = 1000;
        let steps = Math.max(percentage, 1);
        let stepTime = Math.max(duration / steps, 10);
        let currentCounter = 0;

        $progressBar.text('');

        let interval = setInterval(() => {
            currentCounter++;
            $progressBar.text(currentCounter + '%');

            if (currentCounter >= percentage) {
                clearInterval(interval);
                $progressBar.text(percentage + '%');
            }
        }, stepTime);
    };

})(jQuery, Drupal, once);
