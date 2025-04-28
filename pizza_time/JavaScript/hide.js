const methodEl = document.getElementById('methodID');
const dineFields = document.querySelectorAll('.dinein-field');

function toggleDineIn() {
  const isDine = methodEl.selectedOptions[0].text.includes('Dine-in');
  dineFields.forEach(el => el.style.display = isDine ? 'block' : 'none');
}

methodEl.addEventListener('change', toggleDineIn);
toggleDineIn();  // run on page load