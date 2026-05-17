(() => {
  const categorySelect = document.getElementById('assetCategory');
  const specificationsContainer = document.getElementById('dynamicSpecifications');

  if (!categorySelect || !specificationsContainer) {
    return;
  }

  const renderField = (definition) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-3';

    const label = document.createElement('label');
    label.className = 'form-label';
    label.textContent = definition.field_label;

    const input = document.createElement(
      definition.field_type === 'textarea' ? 'textarea' : 'input'
    );

    input.className = 'form-control';
    input.name = `specifications[${definition.id}]`;
    if (definition.field_type !== 'textarea') {
      input.type = definition.field_type === 'number' ? 'number' : definition.field_type === 'date' ? 'date' : 'text';
    }
    input.required = definition.is_required === 1;

    wrapper.appendChild(label);
    wrapper.appendChild(input);
    return wrapper;
  };

  const loadSpecifications = async (categoryId) => {
    specificationsContainer.innerHTML = '';

    if (!categoryId) {
      return;
    }

    const response = await fetch(`/index.php?action=specifications&category_id=${encodeURIComponent(categoryId)}`);
    if (!response.ok) {
      return;
    }

    const definitions = await response.json();
    definitions.forEach((definition) => specificationsContainer.appendChild(renderField(definition)));
  };

  categorySelect.addEventListener('change', (event) => loadSpecifications(event.target.value));
})();
