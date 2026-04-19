(() => {
  const form = document.getElementById('expense-category-create-form');
  const code = document.getElementById('code');
  const name = document.getElementById('name');
  const description = document.getElementById('description');

  if (!form || !code || !name || !description) {
    return;
  }

  const focusField = (field) => {
    if (!field) return;

    window.requestAnimationFrame(() => {
      field.focus();

      if (typeof field.select === 'function' && field.tagName !== 'TEXTAREA') {
        field.select();
      }
    });
  };

  const moveOnEnter = (current, next) => {
    current.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') return;
      if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;

      event.preventDefault();
      focusField(next);
    });
  };

  moveOnEnter(code, name);
  moveOnEnter(name, description);

  description.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;

    event.preventDefault();

    if (typeof form.requestSubmit === 'function') {
      form.requestSubmit();
      return;
    }

    form.submit();
  });

  focusField(code);
})();
