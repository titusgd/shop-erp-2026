const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

async function api(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        },
        credentials: 'same-origin',
        ...options,
    });

    let payload = null;

    if (response.status !== 204) {
        payload = await response.json().catch(() => null);
    }

    if (!response.ok) {
        const error = new Error(payload?.message ?? '請求失敗');
        error.status = response.status;
        error.payload = payload;
        throw error;
    }

    return payload;
}

function collectDistrictPayload(form) {
    const cityId = form.querySelector('[data-field="city_id"]').value;

    return {
        city_id: cityId ? Number(cityId) : null,
        name: form.querySelector('[data-field="name"]').value.trim(),
        notes: form.querySelector('[data-field="notes"]').value.trim(),
        is_active: form.querySelector('[data-field="is_active"]').checked,
    };
}

async function loadCityOptions(select, selectedId = null) {
    const params = new URLSearchParams({
        per_page: '50',
        active_only: '1',
    });

    const payload = await api(`/api/cities?${params.toString()}`);
    const cities = payload.data ?? [];

    const options = ['<option value="">請選擇縣市</option>'];

    cities.forEach((city) => {
        const selected = selectedId !== null && Number(selectedId) === Number(city.id) ? ' selected' : '';
        options.push(
            `<option value="${city.id}"${selected}>${escapeHtml(city.name)}</option>`,
        );
    });

    select.innerHTML = options.join('');
}

function initDistrictCreatePage(root) {
    const indexUrl = root.dataset.indexUrl;
    const form = root.querySelector('[data-district-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const citySelect = form.querySelector('[data-field="city_id"]');

    const showAlert = (message, type = 'error') => {
        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
    };

    const clearErrors = () => {
        form.querySelectorAll('[data-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    };

    const showErrors = (errors = {}, message = null) => {
        clearErrors();

        Object.entries(errors).forEach(([field, messages]) => {
            const el = form.querySelector(`[data-error="${field}"]`);
            if (el) {
                el.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                el.classList.remove('hidden');
            }
        });

        if (message && Object.keys(errors).length === 0) {
            const formError = form.querySelector('[data-error="form"]');
            formError.textContent = message;
            formError.classList.remove('hidden');
        }
    };

    loadCityOptions(citySelect).catch(() => {
        showAlert('載入縣市選項失敗', 'error');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = collectDistrictPayload(form);
        submitButton.disabled = true;

        try {
            await api('/api/districts', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            window.location.href = indexUrl;
        } catch (error) {
            if (error.status === 422) {
                showErrors(error.payload?.errors ?? {}, error.payload?.message);
            } else {
                showAlert(error.message || '儲存失敗', 'error');
            }
            submitButton.disabled = false;
        }
    });
}

const page = document.querySelector('[data-district-create-page]');
if (page) {
    initDistrictCreatePage(page);
}
