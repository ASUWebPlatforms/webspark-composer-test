(function (Drupal) {
  Drupal.behaviors.wordCount = {
    attach: function (context, settings) {
      // Target the textarea by its data-drupal-selector attribute
      const textarea = context.querySelector('textarea[data-drupal-selector="edit-field-abstract"]');

      if (textarea) {
        // Check if the word count element already exists; if not, create it
        let wordCountElement = textarea.nextElementSibling && textarea.nextElementSibling.classList.contains('word-count') ? textarea.nextElementSibling : null;
        if (!wordCountElement) {
          wordCountElement = document.createElement('span');
          wordCountElement.classList.add('word-count');
          // Place the word count element directly after the textarea
          textarea.parentNode.insertBefore(wordCountElement, textarea.nextSibling);
        }

        // Function to update the word count
        const updateWordCount = () => {
          if (textarea) {
            const text = textarea.value.trim();
            const words = text.length > 0 ? text.split(/\s+/) : [];
            const wordCount = words.length;
            wordCountElement.textContent = 'Your word count is: ' + wordCount; // Update the text content of the word count element

            // If word count exceeds 350, truncate the text
            if (wordCount > 350) {
              wordCountElement.textContent = 'Your word count is: 350 (Maximum reached)';
              wordCountElement.classList.add('max-reached');
              setTimeout(() => {
                textarea.value = words.slice(0, 350).join(' ');
              }, 700);
            } else {
              wordCountElement.classList.remove('max-reached');
            }
          }
        };

        // Call the update function initially in case there's already text in the textarea
        updateWordCount();

        // Add an event listener to the textarea to update the word count on input
        textarea.addEventListener('input', updateWordCount);
      }
    }
  };
})(Drupal);
