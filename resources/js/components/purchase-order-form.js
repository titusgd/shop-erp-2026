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

function formatMoney(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) {
        return '0.00';
    }

    return number.toLocaleString('zh-TW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
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
 *   orderId?: string|number|null,
 *   indexUrl: string,
 *   initial?: {
 *     vendor_id?: number|null,
 *     warehouse_id?: number|null,
 *     items?: Array<{product_id:number, quantity:string|number, unit_price:string|number}>
 *   }
 * }} options
 */
export function initPurchaseOrderForm(root, options) {
    const form = root.querySelector('[data-purchase-order-form]');
    const submitButton = root.querySelector('[data-submit]');
    const alertBox = root.querySelector('[data-alert]');
    const vendorSelect = form.querySelector('[data-field="vendor_id"]');
    const warehouseSelect = form.querySelector('[data-field="warehouse_id"]');
    const itemsBody = form.querySelector('[data-items-body]');
    const addItemButton = form.querySelector('[data-add-item]');
    const totalAmountEl = form.querySelector('[data-total-amount]');
    const isCancelled = root.dataset.isCancelled === '1';

    /** @type {Array<{id:number, name:string, code?:string|null, unit?:{name?:string, symbol?:string|null}|null}>} */
    let products = [];
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
        const unit = product.unit?.symbol || product.unit?.name;
        const base = product.code ? `${product.code} ${product.name}` : product.name;
        return unit ? `${base}（${unit}）` : base;
    };

    const productOptionsHtml = (selectedId = '') => {
        const options = ['<option value="">請選擇商品</option>'];
        products.forEach((product) => {
            const selected = selectedId !== '' && Number(selectedId) === Number(product.id) ? ' selected' : '';
            options.push(
                `<option value="${product.id}"${selected}>${escapeHtml(productLabel(product))}</option>`,
            );
        });
        return options.join('');
    };

    const recalculateTotals = () => {
        let total = 0;
        itemsBody.querySelectorAll('[data-item-row]').forEach((row) => {
            const quantity = Number(row.querySelector('[data-item-quantity]').value);
            const unitPrice = Number(row.querySelector('[data-item-unit-price]').value);
            const amount = (Number.isFinite(quantity) ? quantity : 0) * (Number.isFinite(unitPrice) ? unitPrice : 0);
            row.querySelector('[data-item-amount]').textContent = formatMoney(amount);
            total += amount;
        });
        totalAmountEl.textContent = formatMoney(total);
    };

    const reindexRows = () => {
        itemsBody.querySelectorAll('[data-item-row]').forEach((row, index) => {
            row.dataset.itemIndex = String(index);
        });
    };

    const addItemRow = (item = null) => {
        const rowId = `item-${itemSeq}`;
        itemSeq += 1;

        const tr = document.createElement('tr');
        tr.dataset.itemRow = '';
        tr.dataset.rowId = rowId;
        tr.innerHTML = `
            <td class="px-3 py-2 align-top">
                <select
                    data-item-product
                    class="block w-full min-w-[12rem] rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                    ${productOptionsHtml(item?.product_id ?? '')}
                </select>
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
                    type="number"
                    min="0"
                    step="0.01"
                    data-item-unit-price
                    value="${escapeHtml(item?.unit_price ?? '0')}"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                <p class="mt-1 hidden text-sm text-red-600" data-error="item.unit_price"></p>
            </td>
            <td class="px-3 py-2 align-top text-right font-medium text-slate-900">
                <span data-item-amount>0.00</span>
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
        reindexRows();
        recalculateTotals();
    };

    const refreshProductOptions = async () => {
        const params = new URLSearchParams({
            per_page: '50',
            active_only: '1',
        });

        const payload = await api(`/api/products?${params.toString()}`);
        products = payload.data ?? [];

        const selectedByRow = new Map();
        itemsBody.querySelectorAll('[data-item-row]').forEach((row) => {
            selectedByRow.set(row.dataset.rowId, row.querySelector('[data-item-product]').value);
        });

        itemsBody.querySelectorAll('[data-item-row]').forEach((row) => {
            const select = row.querySelector('[data-item-product]');
            const previous = selectedByRow.get(row.dataset.rowId) ?? '';
            select.innerHTML = productOptionsHtml(previous);
            if (previous && !Array.from(select.options).some((option) => option.value === previous)) {
                select.value = '';
            }
        });
    };

    const collectPayload = () => {
        const items = [];
        itemsBody.querySelectorAll('[data-item-row]').forEach((row) => {
            const productId = row.querySelector('[data-item-product]').value;
            const quantity = row.querySelector('[data-item-quantity]').value;
            const unitPrice = row.querySelector('[data-item-unit-price]').value;

            if (!productId && !quantity && !unitPrice) {
                return;
            }

            items.push({
                product_id: productId ? Number(productId) : null,
                quantity: quantity === '' ? null : Number(quantity),
                unit_price: unitPrice === '' ? null : Number(unitPrice),
            });
        });

        return {
            vendor_id: vendorSelect.value ? Number(vendorSelect.value) : null,
            warehouse_id: warehouseSelect.value ? Number(warehouseSelect.value) : null,
            order_date: form.querySelector('[data-field="order_date"]').value,
            expected_date: form.querySelector('[data-field="expected_date"]').value || null,
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
        row?.remove();
        reindexRows();
        recalculateTotals();

        if (!itemsBody.querySelector('[data-item-row]')) {
            addItemRow();
        }
    });

    itemsBody.addEventListener('input', (event) => {
        if (
            event.target.matches('[data-item-quantity]') ||
            event.target.matches('[data-item-unit-price]')
        ) {
            recalculateTotals();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        if (isCancelled) {
            showAlert('已取消的採購單不可修改', 'error');
            return;
        }

        const payload = collectPayload();
        submitButton.disabled = true;

        try {
            if (options.mode === 'create') {
                await api('/api/purchase-orders', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });
            } else {
                await api(`/api/purchase-orders/${options.orderId}`, {
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

    Promise.all([
        loadSelectOptions(
            '/api/vendors',
            vendorSelect,
            '請選擇供應商',
            initial.vendor_id ?? null,
            (item) => (item.code ? `${item.code} ${item.name}` : item.name),
        ),
        loadSelectOptions(
            '/api/warehouses',
            warehouseSelect,
            '請選擇進貨倉庫',
            initial.warehouse_id ?? null,
            (item) => (item.code ? `${item.code} ${item.name}` : item.name),
        ),
        refreshProductOptions(),
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
                showAlert('已取消的採購單不可修改', 'error');
            }
        })
        .catch(() => {
            showAlert('載入表單選項失敗', 'error');
            addItemRow();
        });
}
