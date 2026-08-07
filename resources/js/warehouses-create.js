import { initSearchableMultiSelect } from './components/searchable-multi-select';

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

const FIELD_TAB_MAP = {
    name: 'basic',
    warehouse_type_ids: 'basic',
    is_active: 'basic',
    contact_name: 'contact',
    phone: 'contact',
    email: 'contact',
    address: 'contact',
    notes: 'other',
};

const activeTabClass =
    'whitespace-nowrap border-b-2 border-teal-700 px-3 py-3 text-sm font-medium text-teal-800 transition hover:text-teal-900';
const inactiveTabClass =
    'whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700';

function collectWarehousePayload(form, typeSelect) {
    return {
        name: form.querySelector('[data-field="name"]').value.trim(),
        contact_name: form.querySelector('[data-field="contact_name"]').value.trim(),
        phone: form.querySelector('[data-field="phone"]').value.trim(),
        email: form.querySelector('[data-field="email"]').value.trim(),
        address: form.querySelector('[data-field="address"]').value.trim(),
        notes: form.querySelector('[data-field="notes"]').value.trim(),
        is_active: form.querySelector('[data-field="is_active"]').checked,
        warehouse_type_ids: typeSelect.getSelectedIds(),
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
    if (field.startsWith('warehouse_type_ids')) {
        return 'basic';
    }

    return FIELD_TAB_MAP[field] ?? 'basic';
}

function initWarehouseCreatePage(root) {
    const indexUrl = root.dataset.indexUrl;
    const form = root.querySelector('[data-warehouse-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const { activateTab } = initTabs(root);
    const typeSelectRoot = root.querySelector('[data-warehouse-type-multi-select]');
    const typeSelect = initSearchableMultiSelect(typeSelectRoot, {
        endpoint: '/api/warehouse-types',
        queryParams: { active_only: '1' },
        emptyLabel: '目前沒有可選的倉庫類型',
        noResultLabel: '找不到符合的倉庫類型',
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

        const errorFields = Object.keys(errors);
        if (errorFields.length > 0) {
            activateTab(resolveErrorTab(errorFields[0]));
        }

        Object.entries(errors).forEach(([field, messages]) => {
            const el =
                form.querySelector(`[data-error="${field}"]`) ??
                (field.startsWith('warehouse_type_ids')
                    ? form.querySelector('[data-error="warehouse_type_ids"]')
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

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = collectWarehousePayload(form, typeSelect);
        submitButton.disabled = true;

        try {
            await api('/api/warehouses', {
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

const page = document.querySelector('[data-warehouse-create-page]');
if (page) {
    initWarehouseCreatePage(page);
}
