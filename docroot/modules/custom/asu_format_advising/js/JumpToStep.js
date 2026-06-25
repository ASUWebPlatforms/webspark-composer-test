document.addEventListener('DOMContentLoaded', function () {
  // Add click event listeners to anchor tags with 'data-step' attribute
  document.querySelectorAll('a[data-step]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      e.preventDefault(); // Prevent default anchor behavior
      const newStep = this.dataset.step; // Get the step from the data attribute
      console.log('Go to step ' + newStep); // Logging the step for confirmation
      const selector = 'input[data-drupal-selector="edit-index-step-' + newStep + '"]';
      const targetElement = document.querySelector(selector);

      // Check if the target element exists and trigger a click if it does
      if (targetElement) {
        targetElement.click();
      } else {
        console.error('Target element not found for step ' + newStep);
      }
    });
  });
});
