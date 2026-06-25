document.addEventListener('DOMContentLoaded', () => {
  const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
  const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
      v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
  )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

  document.querySelectorAll('#graduate-results thead th').forEach(th => th.addEventListener('click', function() {
      const table = th.closest('table');
      const tbody = table.querySelector('tbody');
      Array.from(tbody.querySelectorAll('tr'))
          .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
          .forEach(tr => tbody.appendChild(tr));

      // Update sort icon
      document.querySelectorAll('#graduate-results .sort-icon').forEach(icon => icon.textContent = ''); // Clear existing icons
      th.querySelector('.sort-icon').textContent = this.asc ? '▲' : '▼'; // Set the icon on the clicked header
  }));
});
