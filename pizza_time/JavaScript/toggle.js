const methodSelect = document.querySelector('select[name="methodID"]');
const tableGroup  = document.getElementById('table-group');

function toggleTable() {
  const text = methodSelect.options[methodSelect.selectedIndex].text.toLowerCase();
  if (text.includes('dine')) {
    tableGroup.classList.remove('d-none');
  } else {
    tableGroup.classList.add('d-none');
    tableGroup.querySelector('select').value = '';
  }
}

methodSelect.addEventListener('change', toggleTable);
toggleTable();