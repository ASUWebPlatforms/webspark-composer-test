// Show more/Show less in degree detail page
(function () {
  const list = document.querySelector('[data-course-list]');
  if (!list) return;

  const toggleBtn = list.querySelector('[data-course-toggle]');
  const items = list.querySelectorAll('[data-course-item]');

  if (!toggleBtn) return;

  toggleBtn.addEventListener('click', (event) => {
    event.preventDefault();
    const hiddenItems = Array.from(items).filter(i => i.classList.contains('d-none'));

    if (hiddenItems.length) {
      items.forEach(i => i.classList.remove('d-none'));
      toggleBtn.textContent = 'Show less';
    } else {
      items.forEach((i, idx) => {
        if (idx >= 8) i.classList.add('d-none');
      });
      toggleBtn.textContent = 'Show more';
      list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });  

})();