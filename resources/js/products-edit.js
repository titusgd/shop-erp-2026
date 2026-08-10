import { initSearchableMultiSelect } from './components/searchable-multi-select';

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

function collectProductPayload(form, vendorSelect) {
    const categoryId = form.querySelector('[data-field="product_category_id"]').value;
    const unitId = form.querySelector('[data-field="product_unit_id"]').value;

    return {
        product_category_id: categoryId ? Number(categoryId) : null,
        product_unit_id: unitId ? Number(unitId) : null,
        vendor_ids: vendorSelect.getSelectedIds(),
        name: form.querySelector('[data-field="name"]').value.trim(),
        notes: form.querySelector('[data-field="notes"]').value.trim(),
        is_active: form.querySelector('[data-field="is_active"]').checked,
    };
}

async function loadSelectOptions(url, showUrl, select, placeholder, selectedId = null, labelFn) {
    const params = new URLSearchParams({
        per_page: '50',
        active_only: '1',
    });

    const payload = await api(`${url}?${params.toString()}`);
    const items = payload.data ?? [];

    const options = [`<option value="">${escapeHtml(placeholder)}</option>`];
    let hasSelected = false;

    items.forEach((item) => {
        const selected = selectedId !== null && Number(selectedId) === Number(item.id);
        if (selected) {
            hasSelected = true;
        }
        options.push(
            `<option value="${item.id}"${selected ? ' selected' : ''}>${escapeHtml(labelFn(item))}</option>`,
        );
    });

    // Keep current option available even if it is inactive.
    if (selectedId && !hasSelected) {
        try {
            const itemPayload = await api(`${showUrl}/${selectedId}`);
            const item = itemPayload.data;
            if (item) {
                options.splice(
                    1,
                    0,
                    `<option value="${item.id}" selected>${escapeHtml(labelFn(item))}</option>`,
                );
            }
        } catch {
            // Ignore load failure; validation will catch missing relation.
        }
    }

    select.innerHTML = options.join('');
}

function initProductEditPage(root) {
    const productId = root.dataset.productId;
    const categoryId = root.dataset.productCategoryId;
    const unitId = root.dataset.productUnitId;
    const indexUrl = root.dataset.indexUrl;
    const form = root.querySelector('[data-product-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const categorySelect = form.querySelector('[data-field="product_category_id"]');
    const unitSelect = form.querySelector('[data-field="product_unit_id"]');
    const vendorSelectRoot = root.querySelector('[data-vendor-multi-select]');
    const vendorSelect = initSearchableMultiSelect(vendorSelectRoot, {
        endpoint: '/api/vendors',
        queryParams: { active_only: '1' },
        emptyLabel: '目前沒有可選的供應商',
        noResultLabel: '找不到符合的供應商',
    });

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
            const el =
                form.querySelector(`[data-error="${field}"]`) ??
                (field.startsWith('vendor_ids')
                    ? form.querySelector('[data-error="vendor_ids"]')
                    : null);
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

    Promise.all([
        loadSelectOptions(
            '/api/product-categories',
            '/api/product-categories',
            categorySelect,
            '請選擇商品分類',
            categoryId,
            (item) => item.name,
        ),
        loadSelectOptions(
            '/api/product-units',
            '/api/product-units',
            unitSelect,
            '請選擇商品單位',
            unitId,
            (item) => (item.symbol ? `${item.name}（${item.symbol}）` : item.name),
        ),
    ]).catch(() => {
        showAlert('載入分類或單位選項失敗', 'error');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = collectProductPayload(form, vendorSelect);
        submitButton.disabled = true;

        try {
            await api(`/api/products/${productId}`, {
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

const page = document.querySelector('[data-product-edit-page]');
if (page) {
    initProductEditPage(page);
}
