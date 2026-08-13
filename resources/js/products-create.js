import { initSearchableMultiSelect } from './components/searchable-multi-select';
import { initVendorPurchasePriceFields, parsePrice } from './components/vendor-purchase-prices';

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

const FIELD_TAB_MAP = {
    product_category_id: 'basic',
    product_unit_id: 'basic',
    vendor_ids: 'basic',
    name: 'basic',
    notes: 'basic',
    is_active: 'basic',
    estimated_selling_price: 'price',
};

const activeTabClass =
    'whitespace-nowrap border-b-2 border-teal-700 px-3 py-3 text-sm font-medium text-teal-800 transition hover:text-teal-900';
const inactiveTabClass =
    'whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700';

function collectProductPayload(form, vendorSelect, vendorPrices) {
    const categoryId = form.querySelector('[data-field="product_category_id"]').value;
    const unitId = form.querySelector('[data-field="product_unit_id"]').value;

    return {
        product_category_id: categoryId ? Number(categoryId) : null,
        product_unit_id: unitId ? Number(unitId) : null,
        vendor_ids: vendorSelect.getSelectedIds(),
        vendor_purchase_prices: vendorPrices.collect(),
        name: form.querySelector('[data-field="name"]').value.trim(),
        notes: form.querySelector('[data-field="notes"]').value.trim(),
        estimated_selling_price: parsePrice(form.querySelector('[data-field="estimated_selling_price"]').value),
        is_active: form.querySelector('[data-field="is_active"]').checked,
    };
}

function initTabs(root) {
    const tabs = [...root.querySelectorAll('[data-tab]')];
    const panels = [...root.querySelectorAll('[data-tab-panel]')];

    const activateTab = (tabKey) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.tab === tabKey;
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.className = isActive ? activeTabClass : inactiveTabClass;
            tab.tabIndex = isActive ? 0 : -1;
        });

        panels.forEach((panel) => {
            panel.hidden = panel.dataset.tabPanel !== tabKey;
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.tab));
    });

    root.querySelector('[data-tabs]')?.addEventListener('keydown', (event) => {
        const currentIndex = tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true');
        if (currentIndex < 0) {
            return;
        }

        let nextIndex = null;

        if (event.key === 'ArrowRight') {
            nextIndex = (currentIndex + 1) % tabs.length;
        } else if (event.key === 'ArrowLeft') {
            nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
            nextIndex = 0;
        } else if (event.key === 'End') {
            nextIndex = tabs.length - 1;
        }

        if (nextIndex === null) {
            return;
        }

        event.preventDefault();
        activateTab(tabs[nextIndex].dataset.tab);
        tabs[nextIndex].focus();
    });

    activateTab('basic');

    return { activateTab };
}

function resolveErrorTab(field) {
    if (field.startsWith('vendor_ids')) {
        return 'basic';
    }

    if (field.startsWith('vendor_purchase_prices') || field === 'estimated_selling_price') {
        return 'price';
    }

    return FIELD_TAB_MAP[field] ?? 'basic';
}

async function loadSelectOptions(url, select, placeholder, selectedId = null, labelFn) {
    const params = new URLSearchParams({
        per_page: '50',
        active_only: '1',
    });

    const payload = await api(`${url}?${params.toString()}`);
    const items = payload.data ?? [];

    const options = [`<option value="">${escapeHtml(placeholder)}</option>`];

    items.forEach((item) => {
        const selected = selectedId !== null && Number(selectedId) === Number(item.id) ? ' selected' : '';
        options.push(
            `<option value="${item.id}"${selected}>${escapeHtml(labelFn(item))}</option>`,
        );
    });

    select.innerHTML = options.join('');
}

function initProductCreatePage(root) {
    const indexUrl = root.dataset.indexUrl;
    const form = root.querySelector('[data-product-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const { activateTab } = initTabs(root);
    const categorySelect = form.querySelector('[data-field="product_category_id"]');
    const unitSelect = form.querySelector('[data-field="product_unit_id"]');
    const vendorPrices = initVendorPurchasePriceFields(root.querySelector('[data-vendor-purchase-prices]'));
    const vendorSelectRoot = root.querySelector('[data-vendor-multi-select]');
    const vendorSelect = initSearchableMultiSelect(vendorSelectRoot, {
        endpoint: '/api/vendors',
        queryParams: { active_only: '1' },
        emptyLabel: '目前沒有可選的供應商',
        noResultLabel: '找不到符合的供應商',
        onChange: (items) => vendorPrices.render(items),
    });
    vendorPrices.render(vendorSelect.getSelectedItems());

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

        const errorFields = Object.keys(errors);
        if (errorFields.length > 0) {
            activateTab(resolveErrorTab(errorFields[0]));
        }

        Object.entries(errors).forEach(([field, messages]) => {
            const el =
                form.querySelector(`[data-error="${field}"]`) ??
                (field.startsWith('vendor_ids')
                    ? form.querySelector('[data-error="vendor_ids"]')
                    : field.startsWith('vendor_purchase_prices')
                      ? form.querySelector(`[data-error="${field}"]`)
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
            categorySelect,
            '請選擇商品分類',
            null,
            (item) => item.name,
        ),
        loadSelectOptions(
            '/api/product-units',
            unitSelect,
            '請選擇商品單位',
            null,
            (item) => (item.symbol ? `${item.name}（${item.symbol}）` : item.name),
        ),
    ]).catch(() => {
        showAlert('載入分類或單位選項失敗', 'error');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = collectProductPayload(form, vendorSelect, vendorPrices);
        submitButton.disabled = true;

        try {
            await api('/api/products', {
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

const page = document.querySelector('[data-product-create-page]');
if (page) {
    initProductCreatePage(page);
}
