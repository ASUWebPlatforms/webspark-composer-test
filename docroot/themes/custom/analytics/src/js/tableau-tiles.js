document.addEventListener('DOMContentLoaded', function () {
  const id = 'tableau-tiles-modal';
  const modal = document.getElementById(id);
  const modalTitle = document.getElementById(`${id}-label`);
  const modalContent = document.getElementById(`${id}-content`);
  const modalSourceLink = modal.querySelector('.source-link');

  // Update the modal content
  modal.addEventListener('show.bs.modal', event => {
    // Get the button that triggered the modal
    const button = event.relatedTarget;

    // Retrieve the data-* attributes from the button
    const title = button.getAttribute('data-modal-title');
    const content = button.getAttribute('data-modal-content');
    const contentHTML = `
      <div class="tw-shadow-md ratio ratio-16x9">
        <tableau-viz id="tableau-viz" src="${content}" width="1920" height="1080" toolbar="top"></tableau-viz>
      </div>
    `;

    modalTitle.innerHTML = title;
    modalContent.innerHTML = contentHTML;
    modalSourceLink.href = content;
  });

  // Remove the report embed when the modal is closed
  modal.addEventListener('hidden.bs.modal', event => {
    modalTitle.innerHTML = null;
    modalContent.innerHTML = null;
  });
});
