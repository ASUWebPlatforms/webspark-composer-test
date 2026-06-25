(function (Drupal, once) {
  Drupal.behaviors.keepFiltersOpen = {
    attach(context) {
      once('keepFiltersOpen', '#collapseExample', context).forEach((element) => {
        const filterInputs = element.querySelectorAll(
          'input[type="checkbox"]:checked, ' +
          'input[type="radio"]:checked, ' +
          'select option:checked:not([value="All"])'
        );

        if (filterInputs.length > 0) {
          // Expand the collapse container
          element.classList.add('show');

          // Update toggle aria attributes
          const toggle = document.querySelector('[data-bs-toggle="collapse"][href="#collapseExample"], [data-bs-toggle="collapse"][data-bs-target="#collapseExample"]');
          if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
          }
        }
      });
    }
  };
})(Drupal, once);
