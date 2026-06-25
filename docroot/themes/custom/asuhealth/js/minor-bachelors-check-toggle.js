(function ($, Drupal, once) {
  Drupal.behaviors.showMinorWhenBachelorsChecked = {
    attach: function (context) {
      const minorWrapper = context.querySelector('.form-item-field-degree-tags-705');
      const bachelorsCheckbox = context.querySelector('input[data-drupal-selector="edit-field-degree-tags-701"]');
      const minorCheckbox = context.querySelector('input[data-drupal-selector="edit-field-degree-tags-705"]');

      if (!minorWrapper || !bachelorsCheckbox || !minorCheckbox) return;

      function updateVisibilityAndState() {
        const bachelorsChecked = bachelorsCheckbox.checked;
        const minorChecked = minorCheckbox.checked;

        // Show Minor if either is checked
        minorWrapper.style.display = (bachelorsChecked || minorChecked) ? '' : 'none';

        // If Minor is checked and Bachelor's is not, apply indeterminate state
        if (!bachelorsChecked && minorChecked) {
          bachelorsCheckbox.indeterminate = true;
        } else {
          bachelorsCheckbox.indeterminate = false;
        }
      }

      // Set initial state on every attach
      updateVisibilityAndState();

      // Bind listeners once
      if (!minorWrapper.classList.contains('js-minor-toggle-initialized')) {
        minorWrapper.classList.add('js-minor-toggle-initialized');

        bachelorsCheckbox.addEventListener('change', updateVisibilityAndState);
        minorCheckbox.addEventListener('change', function () {
          if (this.checked) {
            bachelorsCheckbox.checked = false;
          } else {
            bachelorsCheckbox.checked = true;
          }
          updateVisibilityAndState();
        });
      }
    }
  };
})(jQuery, Drupal, once);
