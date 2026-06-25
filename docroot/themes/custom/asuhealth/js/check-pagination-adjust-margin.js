document.addEventListener("DOMContentLoaded", function () {
  // Function to check and add margin
  const checkAndAddMargin = () => {
    const pagination = document.querySelector("ul.pagination.pager__items.js-pager__items.justify-content-center");
    if (!pagination) {
      const cardArrangements = document.querySelectorAll(".uds-card-arrangement");
      cardArrangements.forEach(cardArrangement => {
        cardArrangement.style.marginBottom = "4rem"; // Adjust the margin value as needed
      });
    }
  };

  // Initial check when the page loads
  checkAndAddMargin();

  // Observe changes in the DOM
  const observer = new MutationObserver(() => {
    checkAndAddMargin();
  });

  // Start observing the body or a specific container for changes
  observer.observe(document.body, {
    childList: true, // Look for added/removed elements
    subtree: true,   // Observe changes in all descendants
  });
});
