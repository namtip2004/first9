(function () {
  const form = document.querySelector('[data-promotion-form]');
  if (!form) {
    return;
  }

  const config = window.promotionFormConfig || {};
  const startInput = form.querySelector('input[name="pm_start_date"]');
  const endInput = form.querySelector('input[name="pm_end_date"]');
  const selectedContainer = form.querySelector('[data-selected-services]');
  const addServiceBtn = form.querySelector('[data-add-service]');
  const emptyState = form.querySelector('[data-empty-state]');
  const payloadInput = form.querySelector('[data-payload]');

  const modalEl = document.getElementById('serviceSelectModal');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  const modalWarning = modalEl ? modalEl.querySelector('[data-modal-warning]') : null;
  const serviceList = modalEl ? modalEl.querySelector('[data-service-checkboxes]') : null;
  const selectAllCheckbox = modalEl ? modalEl.querySelector('[data-select-all]') : null;
  const noServiceInfo = modalEl ? modalEl.querySelector('[data-no-service]') : null;
  const confirmServicesBtn = modalEl ? modalEl.querySelector('[data-confirm-services]') : null;

  const selectedServiceIds = new Set();

  function updateEmptyState() {
    if (!emptyState) return;
    if (selectedServiceIds.size === 0) {
      emptyState.classList.remove('d-none');
    } else {
      emptyState.classList.add('d-none');
    }
  }

  function clampPercent(value) {
    let v = parseFloat(value);
    if (isNaN(v) || v < 0) v = 0;
    if (v > 100) v = 100;
    return v;
  }

  function formatCurrency(value) {
    return '฿' + Number(value || 0).toFixed(2);
  }

  function recalcOptionRow(row) {
    const price = parseFloat(row.dataset.price || '0');
    const percentInput = row.querySelector('.promotion-option-percent');
    const percent = clampPercent(percentInput.value);
    percentInput.value = percent;
    const discountAmount = price * percent / 100;
    const finalPrice = price - discountAmount;

    row.dataset.discount = discountAmount.toFixed(2);
    row.dataset.final = finalPrice.toFixed(2);

    const discountLabel = row.querySelector('[data-discount-amount]');
    const finalLabel = row.querySelector('[data-final-price]');
    if (discountLabel) {
      discountLabel.textContent = formatCurrency(discountAmount);
    }
    if (finalLabel) {
      finalLabel.textContent = formatCurrency(finalPrice);
    }
  }

  function handlePercentInput(event) {
    recalcOptionRow(event.target.closest('.promotion-option-row'));
  }

  function handleToggleChange(event) {
    const row = event.target.closest('.promotion-option-row');
    const percentInput = row.querySelector('.promotion-option-percent');
    const details = row.querySelector('.promotion-option-details');
    if (event.target.checked) {
      percentInput.removeAttribute('disabled');
      if (details) details.classList.remove('text-muted');
      recalcOptionRow(row);
    } else {
      percentInput.setAttribute('disabled', 'disabled');
      if (details) details.classList.add('text-muted');
      row.dataset.discount = '0.00';
      row.dataset.final = row.dataset.price;
      const discountLabel = row.querySelector('[data-discount-amount]');
      const finalLabel = row.querySelector('[data-final-price]');
      if (discountLabel) {
        discountLabel.textContent = formatCurrency(0);
      }
      if (finalLabel) {
        finalLabel.textContent = formatCurrency(row.dataset.price);
      }
    }
  }

  function createOptionRow(option, enabled = true) {
    const wrapper = document.createElement('div');
    wrapper.className = 'promotion-option-row border rounded p-3 mb-2';
    wrapper.dataset.optionId = option.option_id;
    wrapper.dataset.price = option.price ?? 0;
    wrapper.dataset.discount = option.discount_amount ?? 0;
    wrapper.dataset.final = option.final_price ?? option.price ?? 0;

    const isInitiallyEnabled = Boolean(enabled && (option.included !== false));
    const percentValue = clampPercent(option.discount_percent ?? 0);

    wrapper.innerHTML = `
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="form-check form-switch">
          <input class="form-check-input promotion-option-toggle" type="checkbox" ${isInitiallyEnabled ? 'checked' : ''}>
          <label class="form-check-label">
            ระยะเวลา ${option.duration ?? '-'} นาที - ราคา ${formatCurrency(option.price)}
          </label>
        </div>
        <div class="d-flex align-items-center gap-2 promotion-option-details ${isInitiallyEnabled ? '' : 'text-muted'}">
          <div class="input-group input-group-sm">
            <input type="number" min="0" max="100" step="0.01" class="form-control promotion-option-percent" value="${percentValue}" ${isInitiallyEnabled ? '' : 'disabled'}>
            <span class="input-group-text">%</span>
          </div>
          <div class="text-nowrap">
            ส่วนลด <span data-discount-amount>${formatCurrency(option.discount_amount ?? 0)}</span>
          </div>
          <div class="text-nowrap">
            ราคาสุทธิ <span data-final-price>${formatCurrency(option.final_price ?? option.price)}</span>
          </div>
        </div>
      </div>
    `;

    const percentInput = wrapper.querySelector('.promotion-option-percent');
    const toggle = wrapper.querySelector('.promotion-option-toggle');
    percentInput.addEventListener('input', handlePercentInput);
    toggle.addEventListener('change', handleToggleChange);
    if (isInitiallyEnabled) {
      recalcOptionRow(wrapper);
    }
    return wrapper;
  }

  function createServiceCard(service) {
    const card = document.createElement('div');
    card.className = 'col-12 mb-3';
    card.dataset.serviceId = service.service_id;
    const header = document.createElement('div');
    header.className = 'card border border-primary';
    header.innerHTML = `
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">${service.service_name}</h5>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-service>&times;</button>
      </div>
      <div class="card-body" data-option-container></div>
    `;
    card.appendChild(header);

    const optionContainer = header.querySelector('[data-option-container]');
    (service.options || []).forEach(option => {
      const isIncluded = option.included !== false;
      const row = createOptionRow(option, isIncluded);
      optionContainer.appendChild(row);
    });

    header.querySelector('[data-remove-service]').addEventListener('click', () => {
      selectedServiceIds.delete(service.service_id);
      card.remove();
      updateEmptyState();
    });

    return card;
  }

  function renderService(service) {
    if (selectedServiceIds.has(service.service_id)) {
      return;
    }
    selectedServiceIds.add(service.service_id);
    const card = createServiceCard(service);
    selectedContainer.appendChild(card);
    updateEmptyState();
  }

  function fetchServiceOptions(serviceId) {
    const url = `${config.optionsUrl}?service_id=${encodeURIComponent(serviceId)}`;
    return fetch(url)
      .then(res => res.json())
      .then(options => Array.isArray(options) ? options : [])
      .catch(() => []);
  }

  function fetchAvailableServices() {
    if (!serviceList) return Promise.resolve([]);
    const start = startInput ? startInput.value : '';
    const end = endInput ? endInput.value : '';

    if (!start || !end) {
      if (modalWarning) modalWarning.classList.remove('d-none');
      return Promise.resolve([]);
    }
    if (modalWarning) modalWarning.classList.add('d-none');

    const params = new URLSearchParams({ start, end });
    if (config.promotionId) {
      params.append('promotion_id', config.promotionId);
    }

    return fetch(`${config.availableServiceUrl}?${params.toString()}`)
      .then(res => res.json())
      .then(data => Array.isArray(data) ? data : [])
      .catch(() => []);
  }

  function openServiceModal() {
    if (!modal) return;
    fetchAvailableServices().then(services => {
      serviceList.innerHTML = '';
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
      }
      if (!services.length) {
        if (noServiceInfo) noServiceInfo.classList.remove('d-none');
      } else {
        if (noServiceInfo) noServiceInfo.classList.add('d-none');
        services.forEach(service => {
          const item = document.createElement('label');
          item.className = 'list-group-item list-group-item-action';
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-check-input me-2';
          checkbox.value = service.service_id;
          checkbox.dataset.name = service.service_name;
          if (selectedServiceIds.has(service.service_id)) {
            checkbox.disabled = true;
          }
          item.appendChild(checkbox);
          item.append(` ${service.service_name}`);
          serviceList.appendChild(item);
        });
      }
      modal.show();
    });
  }

  function handleSelectAll() {
    if (!serviceList) return;
    const checkboxes = serviceList.querySelectorAll('input[type="checkbox"]:not(:disabled)');
    checkboxes.forEach(cb => {
      cb.checked = selectAllCheckbox.checked;
    });
  }

  function handleConfirmServices() {
    if (!serviceList) return;
    const checkboxes = serviceList.querySelectorAll('input[type="checkbox"]:checked');
    const selections = Array.from(checkboxes).map(cb => ({
      service_id: parseInt(cb.value, 10),
      service_name: cb.dataset.name || ''
    })).filter(service => service.service_id && !selectedServiceIds.has(service.service_id));

    if (!selections.length) {
      if (modal) modal.hide();
      return;
    }

    const promises = selections.map(service => {
      return fetchServiceOptions(service.service_id).then(options => {
        service.options = options.map(opt => ({
          option_id: opt.option_id,
          duration: opt.duration,
          price: parseFloat(opt.price || 0),
          discount_percent: 0,
          discount_amount: 0,
          final_price: parseFloat(opt.price || 0),
          included: true
        }));
        return service;
      });
    });

    Promise.all(promises).then(list => {
      list.forEach(renderService);
      if (modal) modal.hide();
    });
  }

  function collectPayload() {
    const payload = [];
    const cards = selectedContainer.querySelectorAll('[data-service-id]');
    cards.forEach(card => {
      const serviceId = parseInt(card.dataset.serviceId, 10);
      if (!serviceId) return;
      const options = [];
      card.querySelectorAll('.promotion-option-row').forEach(row => {
        const toggle = row.querySelector('.promotion-option-toggle');
        if (!toggle || !toggle.checked) {
          return;
        }
        const optionId = parseInt(row.dataset.optionId, 10);
        if (!optionId) return;
        const percentInput = row.querySelector('.promotion-option-percent');
        const percent = clampPercent(percentInput.value);
        options.push({
          option_id: optionId,
          discount_percent: percent
        });
      });
      if (options.length > 0) {
        payload.push({ service_id: serviceId, options });
      }
    });
    return payload;
  }

  function validateDates() {
    if (!startInput || !endInput) return true;
    if (!startInput.value || !endInput.value) return false;
    const start = new Date(startInput.value);
    const end = new Date(endInput.value);
    return !isNaN(start.getTime()) && !isNaN(end.getTime()) && end > start;
  }

  form.addEventListener('submit', function (event) {
    if (!validateDates()) {
      event.preventDefault();
      alert('กรุณาระบุวันและเวลาที่ถูกต้อง');
      return;
    }

    const payload = collectPayload();
    if (!payload.length) {
      event.preventDefault();
      alert('กรุณาเลือกบริการและ option ที่ต้องการจัดโปรโมชั่น');
      return;
    }

    payloadInput.value = JSON.stringify(payload);
  });

  if (addServiceBtn) {
    addServiceBtn.addEventListener('click', openServiceModal);
  }
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', handleSelectAll);
  }
  if (confirmServicesBtn) {
    confirmServicesBtn.addEventListener('click', handleConfirmServices);
  }

  // Initialize with existing services if provided
  if (Array.isArray(config.initialServices)) {
    config.initialServices.forEach(service => {
      service.options = (service.options || []).map(opt => ({
        option_id: opt.option_id,
        duration: opt.duration,
        price: parseFloat(opt.price ?? 0),
        discount_percent: opt.discount_percent ?? 0,
        discount_amount: parseFloat(opt.discount_amount ?? 0),
        final_price: parseFloat(opt.final_price ?? opt.price ?? 0),
        included: opt.included !== false
      }));
      renderService(service);
    });
  }

  updateEmptyState();
})();
