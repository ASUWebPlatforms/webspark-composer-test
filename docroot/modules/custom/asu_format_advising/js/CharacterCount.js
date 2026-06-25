(function (Drupal, once) {
  Drupal.behaviors.characterCounter = {
    attach: function (context, settings) {
      // Use the 'once' function to ensure we only add the behavior once per element.
      const elements = once('character-counter', 'input.js-textfield-character-limit[maxlength], textarea.js-textfield-character-limit[maxlength]', context);

      elements.forEach((element) => {
        const maxLength = element.getAttribute('maxlength');

        // Create a counter element and insert it after the input/textarea.
        const counter = document.createElement('div');
        counter.className = 'text-muted';
        counter.textContent = `Please limit this line to 90 characters (${maxLength} remaining)`;
        element.parentNode.insertBefore(counter, element.nextSibling);

        // Update the counter on keyup and input events to capture all types of input.
        element.addEventListener('keyup', updateCounter);
        element.addEventListener('input', updateCounter);

        function updateCounter() {
          const remaining = maxLength - element.value.length;
          counter.textContent = `Please limit this line to 90 characters (${remaining} remaining)`;
        }
      });
    }
  };
})(Drupal, once);
