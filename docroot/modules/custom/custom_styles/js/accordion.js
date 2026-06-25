(function (Drupal) {
  Drupal.behaviors.accordionResponses = {
    attach: function (context, settings) {
      document.querySelectorAll('.responseset').forEach((responseSet, index) => {
        if (responseSet.classList.contains('accordion-processed')) {
          return;
        }
        responseSet.classList.add('accordion-processed');

        var titleElement = responseSet.querySelector('h2');
        var detailsElement = responseSet.querySelector('.responsedetail');
        var toggleElement = responseSet.querySelector('a');

        if (!titleElement || !detailsElement || !toggleElement) {
          return;
        }

        detailsElement.classList.remove("open");

        toggleElement.addEventListener("click", function (event) {
          event.preventDefault();
          if (detailsElement.classList.contains("open")) {
            detailsElement.classList.remove("open");
            toggleElement.textContent = "Show Details";
          } else {
            detailsElement.classList.add("open");
            toggleElement.textContent = "Hide Details";
          }
        });
      });
    }
  };
})(Drupal);
