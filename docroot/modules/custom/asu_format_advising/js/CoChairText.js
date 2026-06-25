(function (Drupal) {
  Drupal.behaviors.chairBehavior = {
    attach: function (context, settings) {
      // Ensure your code runs once per element.
      const committeeChairsSelect = context.querySelector('select[data-drupal-selector="edit-field-committee-chairs"]');
      const chairLabel = context.querySelector('label[for="edit-field-committee-chair-name"]');
      const memberLabel = context.querySelector('label[for="edit-field-committee-mem-first"]');
      const memberLabelDiv = context.querySelector('.form-item-field-committee-mem-first');
      const memberInput = context.querySelector('#edit-field-committee-mem-first'); // Adjust this selector to match your input element's ID

      function updateLabelsAndVisibility(value) {
        const isCoChair = value == '2';
        if (chairLabel) {
          chairLabel.textContent = isCoChair ? 'Co-Chair' : 'Chair';
        }
        if (memberLabel) {
          memberLabel.textContent = isCoChair ? 'Co-Chair' : 'First Committee Member';
          memberLabelDiv.style.display = isCoChair ? 'block' : 'none';
          // Clear content when visibility changes
          if (!isCoChair) {
            memberInput.value = ''; // Clear the input field when not visible or not applicable
          }
        }
      }

      if (committeeChairsSelect) {
        committeeChairsSelect.addEventListener('change', function () {
          updateLabelsAndVisibility(this.value);
        });

        // Initial check in case the select is already set to '2' or another value on page load.
        updateLabelsAndVisibility(committeeChairsSelect.value);
      }
    }
  };
})(Drupal);
