(function (Drupal, once) {
  const BREAKPOINT = 992; // Bootstrap lg
  const SELECTOR = [
    'details[data-drupal-selector^="edit-field-keyword"]',
    'details[data-drupal-selector^="edit-field-author"]',
    'details[data-drupal-selector^="edit-field-collection"]',
  ].join(", ");

  function isLarge() {
    return window.innerWidth >= BREAKPOINT;
  }

  function hasChecked(details) {
    return details.querySelector('input[type="checkbox"]:checked') !== null;
  }

  function applyToggle(details) {
    const summary = details.querySelector("summary");
    const ul = details.querySelector(".bef-checkboxes ul");
    if (!ul) return;

    const items = Array.from(ul.children).filter((el) => el.tagName === "LI");

    // Clean up previous state
    const existing = details.querySelector(".keyword-toggle-wrapper");
    if (existing) existing.remove();
    if (details._summaryHandler) {
      summary.removeEventListener("click", details._summaryHandler);
      delete details._summaryHandler;
    }

    if (!hasChecked(details)) {
      details.removeAttribute("open");
    } else {
      details.setAttribute("open", "");
    }

    // Only add show more/less toggle for fields with more than 10 items
    if (items.length <= 10) return;

    const toggleWrap = document.createElement("div");
    toggleWrap.className = "keyword-toggle-wrapper mt-2";

    const toggle = document.createElement("a");
    toggle.href = "#";
    toggle.className = "keyword-toggle-link";
    toggle.setAttribute("role", "button");
    toggleWrap.appendChild(toggle);
    ul.insertAdjacentElement("afterend", toggleWrap);

    let expanded = false;

    const render = () => {
      items.forEach((item, index) => {
        item.classList.toggle("is-hidden", !expanded && index >= 10);
      });
      toggle.textContent = expanded ? "Show less" : "Show more";
    };

    render();

    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      expanded = !expanded;
      render();
    });
  }

  Drupal.behaviors.keywordToggle = {
    attach(context) {
      once("keywordToggle", SELECTOR, context).forEach(applyToggle);
    },
  };

  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      document.querySelectorAll(SELECTOR).forEach((details) => {
        if (!isLarge() && !hasChecked(details)) {
          details.removeAttribute("open");
        }
        applyToggle(details);
      });
    }, 150);
  });
})(Drupal, once);
