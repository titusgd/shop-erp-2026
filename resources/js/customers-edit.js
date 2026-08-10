import { initAddressLocationFields } from './components/address-location-fields';

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

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

function collectCustomerPayload(form, locationFields) {
    const location = locationFields.getValues();

    return {
        name: form.querySelector('[data-field="name"]').value.trim(),
        tax_id: form.querySelector('[data-field="tax_id"]').value.trim(),
        contact_name: form.querySelector('[data-field="contact_name"]').value.trim(),
        phone: form.querySelector('[data-field="phone"]').value.trim(),
        email: form.querySelector('[data-field="email"]').value.trim(),
        postal_code: form.querySelector('[data-field="postal_code"]').value.trim(),
        city_id: location.city_id,
        district_id: location.district_id,
        address: form.querySelector('[data-field="address"]').value.trim(),
        notes: form.querySelector('[data-field="notes"]').value.trim(),
        is_active: form.querySelector('[data-field="is_active"]').checked,
    };
}

function initCustomerEditPage(root) {
    const customerId = root.dataset.customerId;
    const indexUrl = root.dataset.indexUrl;
    const form = root.querySelector('[data-customer-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const locationRoot = root.querySelector('[data-address-location-fields]');
    const locationFields = initAddressLocationFields(locationRoot, api);

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

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = collectCustomerPayload(form, locationFields);
        submitButton.disabled = true;

        try {
            await api(`/api/customers/${customerId}`, {
                method: 'PUT',
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

const page = document.querySelector('[data-customer-edit-page]');
if (page) {
    initCustomerEditPage(page);
}
