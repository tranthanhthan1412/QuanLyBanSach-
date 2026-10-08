document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
document.querySelectorAll('input[data-preview]').forEach(input => {
  let previous;
  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    if (previous) URL.revokeObjectURL(previous);
    previous = URL.createObjectURL(file);
    document.getElementById(input.dataset.preview).src = previous;
  });
});
