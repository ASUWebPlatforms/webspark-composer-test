(function (Drupal) {
  Drupal.behaviors.ajaxEndorsement = {
    attach: function (context, settings) {
      const elements = context.querySelectorAll('.ajax-endorsement');

      elements.forEach(function (element) {
        if (!element.hasAttribute('data-ajax-endorsement-processed')) {
          element.setAttribute('data-ajax-endorsement-processed', 'true');

          element.addEventListener('click', function () {
            const eid = element.getAttribute('data-endorsement-id');

            // Remove existing tooltips before displaying a new one
            document.querySelectorAll('.custom-tooltip').forEach(function(tooltip) {
              tooltip.remove();
            });

            fetch('/graduate-faculty/employee/endorsement/' + eid)
              .then(response => response.json())
              .then(plans => {
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip p-2 rounded border border-dark bg-gray-2';

                plans.forEach(plan => {
                  const planLink = document.createElement('a');
                  planLink.href = '/graduate-faculty/degree/' + plan.plancode;
                  planLink.textContent = plan.highest_lvl_approval !== 'Member' 
                    ? `${plan.plan_descr} (Endorsed to ${plan.highest_lvl_approval})`
                    : plan.plan_descr;

                  const row = document.createElement('div');
                  row.className = 'plan-row';
                  row.appendChild(planLink);
                  tooltip.appendChild(row);
                });

                document.body.appendChild(tooltip);
                const buttonRect = element.getBoundingClientRect();
                tooltip.style.position = 'absolute';
                tooltip.style.top = window.scrollY + buttonRect.top + 'px';
                tooltip.style.left = window.scrollX + buttonRect.left + buttonRect.width + 10 + 'px';
                tooltip.style.zIndex = 1000;

                setTimeout(() => tooltip.remove(), 3000);
              })
              .catch(error => console.error('Error:', error));
          });
        }
      });
    }
  };
})(Drupal);
