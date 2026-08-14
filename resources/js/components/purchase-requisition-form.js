import { initSearchableSelect } from './searchable-select';

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

export const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

export async function api(url, options = {}) {
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

async function loadSelectOptions(url, select, placeholder, selectedId = null, labelFn) {
    const params = new URLSearchParams({
        per_page: '50',
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

    if (selectedId && !hasSelected) {
        try {
            const itemPayload = await api(`${url}/${selectedId}`);
            const item = itemPayload.data;
            if (item) {
                options.splice(
                    1,
                    0,
                    `<option value="${item.id}" selected>${escapeHtml(labelFn(item))}</option>`,
                );
            }
        } catch {
            // Ignore; validation will catch missing relation.
        }
    }

    select.innerHTML = options.join('');
}

/**
 * @param {HTMLElement} root
 * @param {{
 *   mode: 'create' | 'edit',
 *   requisitionId?: string|number|null,
 *   indexUrl: string,
 *   currentUserId?: number|null,
 *   initial?: {
 *     requester_id?: number|null,
 *     warehouse_id?: number|null,
 *     items?: Array<{product_id:number, quantity:string|number, notes?:string|null}>
 *   }
 * }} options
 */
export function initPurchaseRequisitionForm(root, options) {
    const form = root.querySelector('[data-purchase-requisition-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const requesterSelect = form.querySelector('[data-field="requester_id"]');
    const warehouseSelect = form.querySelector('[data-field="warehouse_id"]');
    const itemsBody = form.querySelector('[data-items-body]');
    const addItemButton = form.querySelector('[data-add-item]');
    const isCancelled = root.dataset.isCancelled === '1';

    let itemSeq = 0;

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
            const text = Array.isArray(messages) ? messages[0] : String(messages);
            let el = form.querySelector(`[data-error="${field}"]`);

            if (!el && field.startsWith('items.')) {
                const match = field.match(/^items\.(\d+)\.(\w+)$/);
                if (match) {
                    const row = itemsBody.querySelector(`[data-item-index="${match[1]}"]`);
                    el = row?.querySelector(`[data-error="item.${match[2]}"]`) ?? form.querySelector('[data-error="items"]');
                } else {
                    el = form.querySelector('[data-error="items"]');
                }
            }

            if (el) {
                el.textContent = text;
                el.classList.remove('hidden');
            }
        });

        if (message && Object.keys(errors).length === 0) {
            const formError = form.querySelector('[data-error="form"]');
            formError.textContent = message;
            formError.classList.remove('hidden');
        }
    };

    const productLabel = (product) => {
        if (!product) {
            return '';
        }

        const unit = product.unit?.symbol || product.unit?.name;
        return unit ? `${product.name}（${unit}）` : (product.name ?? '');
    };

    const formatProductItem = (item) => {
        const unit = item.unit?.symbol || item.unit?.name;
        const name = unit ? `${item.name ?? ''}（${unit}）` : (item.name ?? '');

        return {
            id: Number(item.id),
            name,
            code: item.code ?? null,
        };
    };

    const reindexRows = () => {
        itemsBody.querySelectorAll('[data-item-row]').forEach((row, index) => {
            row.dataset.itemIndex = String(index);
        });
    };

    const addItemRow = (item = null) => {
        const rowId = `item-${itemSeq}`;
        itemSeq += 1;

        const selectedProductId = item?.product_id ?? '';
        const selectedProductName = productLabel(item?.product);
        const searchId = `${rowId}-product-search`;
        const optionsId = `${rowId}-product-options`;

        const tr = document.createElement('tr');
        tr.dataset.itemRow = '';
        tr.dataset.rowId = rowId;
        tr.innerHTML = `
            <td class="px-3 py-2 align-top">
                <div
                    data-item-product-select
                    data-initial-id="${escapeHtml(selectedProductId)}"
                    data-initial-name="${escapeHtml(selectedProductName)}"
                    class="min-w-[12rem]"
                >
                    <input
                        type="hidden"
                        data-item-product
                        data-searchable-select-value
                        value="${escapeHtml(selectedProductId)}"
                    >
                    <div class="relative">
                        <input
                            id="${searchId}"
                            type="text"
                            autocomplete="off"
                            data-searchable-select-search
                            placeholder="輸入關鍵字搜尋商品"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 pr-8 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
                            aria-controls="${optionsId}"
                            aria-expanded="false"
                            aria-autocomplete="list"
                            role="combobox"
                        >
                        <svg
                            class="pointer-events-none absolute inset-y-0 right-2.5 my-auto h-4 w-4 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                        <ul
                            id="${optionsId}"
                            data-searchable-select-options
                            role="listbox"
                            hidden
                            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                        ></ul>
                    </div>
                </div>
                <p class="mt-1 hidden text-sm text-red-600" data-error="item.product_id"></p>
            </td>
            <td class="px-3 py-2 align-top">
                <input
                    type="number"
                    min="0.001"
                    step="0.001"
                    data-item-quantity
                    value="${escapeHtml(item?.quantity ?? '1')}"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                <p class="mt-1 hidden text-sm text-red-600" data-error="item.quantity"></p>
            </td>
            <td class="px-3 py-2 align-top">
                <input
                    type="text"
                    maxlength="255"
                    data-item-notes
                    value="${escapeHtml(item?.notes ?? '')}"
                    class="block w-full min-w-[8rem] rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                <p class="mt-1 hidden text-sm text-red-600" data-error="item.notes"></p>
            </td>
            <td class="px-3 py-2 align-top text-right">
                <button
                    type="button"
                    data-remove-item
                    class="rounded-lg px-2 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50"
                >
                    移除
                </button>
            </td>
        `;

        itemsBody.appendChild(tr);

        const productSelectRoot = tr.querySelector('[data-item-product-select]');
        if (productSelectRoot) {
            initSearchableSelect(productSelectRoot, {
                endpoint: '/api/products',
                queryParams: { active_only: '1' },
                placeholder: '輸入關鍵字搜尋商品',
                emptyLabel: '目前沒有可請購商品',
                noResultLabel: '找不到符合的商品',
                fixedDropdown: true,
                formatItem: formatProductItem,
            });
        }

        reindexRows();
    };

    const collectPayload = () => {
        const items = [];
        itemsBody.querySelectorAll('[data-item-row]').forEach((row) => {
            const productId = row.querySelector('[data-item-product]').value;
            const quantity = row.querySelector('[data-item-quantity]').value;
            const notes = row.querySelector('[data-item-notes]').value.trim();

            if (!productId && !quantity && !notes) {
                return;
            }

            items.push({
                product_id: productId ? Number(productId) : null,
                quantity: quantity === '' ? null : Number(quantity),
                notes: notes === '' ? null : notes,
            });
        });

        return {
            requester_id: requesterSelect.value ? Number(requesterSelect.value) : null,
            warehouse_id: warehouseSelect.value ? Number(warehouseSelect.value) : null,
            request_date: form.querySelector('[data-field="request_date"]').value,
            required_date: form.querySelector('[data-field="required_date"]').value || null,
            status: form.querySelector('[data-field="status"]').value,
            notes: form.querySelector('[data-field="notes"]').value.trim(),
            items,
        };
    };

    const setFormDisabled = (disabled) => {
        form.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el.matches('[data-submit], [data-add-item], [data-remove-item]') || el.tagName === 'SELECT' || el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (el.closest('a')) {
                    return;
                }
                el.disabled = disabled;
            }
        });
        addItemButton.disabled = disabled;
        submitButton.disabled = disabled;
    };

    addItemButton.addEventListener('click', () => addItemRow());

    itemsBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-item]');
        if (!button) {
            return;
        }

        const row = button.closest('[data-item-row]');
        const optionsId = row?.querySelector('[data-searchable-select-search]')?.getAttribute('aria-controls');
        row?.remove();
        if (optionsId) {
            document.getElementById(optionsId)?.remove();
        }
        reindexRows();

        if (!itemsBody.querySelector('[data-item-row]')) {
            addItemRow();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        if (isCancelled) {
            showAlert('已取消的請購單不可修改', 'error');
            return;
        }

        const payload = collectPayload();
        submitButton.disabled = true;

        try {
            if (options.mode === 'create') {
                await api('/api/purchase-requisitions', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
            } else {
                await api(`/api/purchase-requisitions/${options.requisitionId}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload),
                });
            }

            window.location.href = options.indexUrl;
        } catch (error) {
            if (error.status === 422) {
                showErrors(error.payload?.errors ?? {}, error.payload?.message);
            } else {
                showAlert(error.message || '儲存失敗', 'error');
            }
            submitButton.disabled = false;
        }
    });

    const initial = options.initial ?? {};
    const defaultRequesterId = initial.requester_id ?? options.currentUserId ?? null;

    Promise.all([
        loadSelectOptions(
            '/api/users',
            requesterSelect,
            '請選擇請購人',
            defaultRequesterId,
            (item) => item.name,
        ),
        loadSelectOptions(
            '/api/warehouses',
            warehouseSelect,
            '請選擇進貨倉庫',
            initial.warehouse_id ?? null,
            (item) => (item.code ? `${item.code} ${item.name}` : item.name),
        ),
    ])
        .then(() => {
            const initialItems = initial.items ?? [];
            if (initialItems.length) {
                initialItems.forEach((item) => addItemRow(item));
            } else {
                addItemRow();
            }

            if (isCancelled) {
                setFormDisabled(true);
                showAlert('已取消的請購單不可修改', 'error');
            }
        })
        .catch(() => {
            showAlert('載入表單選項失敗', 'error');
            addItemRow();
        });
}
