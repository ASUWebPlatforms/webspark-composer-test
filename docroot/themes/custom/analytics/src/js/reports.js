document.addEventListener('DOMContentLoaded', function () {
  const id = 'report-modal';
  const modal = document.getElementById(id);
  const modalContent = document.getElementById(`${id}-content`);
  const sourceContent = document.getElementById('report-embed');

  // Update the modal content
  modal.addEventListener('show.bs.modal', event => {
    modalContent.innerHTML = sourceContent.innerHTML;
  });

  // Remove the report embed when the modal is closed
  modal.addEventListener('hidden.bs.modal', event => {
    modalContent.innerHTML = null;
  });
});
