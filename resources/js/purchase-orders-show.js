import { api, escapeHtml } from './components/purchase-order-form';

function statusBadge(status, label) {
    const styles = {
        draft: 'bg-slate-100 text-slate-700',
        confirmed: 'bg-teal-50 text-teal-800',
        cancelled: 'bg-red-50 text-red-700',
    };

    return `<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[status] ?? 'bg-slate-100 text-slate-600'}">${escapeHtml(label || status)}</span>`;
}

function detailRow(label, valueHtml) {
    return `
        <div class="grid gap-1 sm:grid-cols-[8rem_minmax(0,1fr)] sm:gap-4">
            <dt class="text-sm font-medium text-slate-500">${escapeHtml(label)}</dt>
            <dd class="text-sm text-slate-900">${valueHtml}</dd>
        </div>
    `;
}

function formatNotes(notes) {
    if (!notes) {
        return '<span class="text-slate-500">—</span>';
    }

    return `<p class="whitespace-pre-wrap break-words">${escapeHtml(notes)}</p>`;
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

function renderItems(items) {
    if (!items?.length) {
        return '<p class="text-sm text-slate-500">尚無明細</p>';
    }

    const rows = items
        .map(
            (item) => `
            <tr>
                <td class="px-3 py-2 text-slate-900">
                    <div class="font-medium">${escapeHtml(item.product?.name || '—')}</div>
                    <div class="text-xs text-slate-500">${escapeHtml(item.product?.code || '')}</div>
                </td>
                <td class="px-3 py-2 text-slate-900">${escapeHtml(item.quantity)}</td>
                <td class="px-3 py-2 text-slate-900">${escapeHtml(formatMoney(item.unit_price))}</td>
                <td class="px-3 py-2 text-right text-slate-900">${escapeHtml(formatMoney(item.amount))}</td>
            </tr>
        `,
        )
        .join('');

    return `
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[36rem] w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-3 py-2.5 font-medium">商品</th>
                        <th class="px-3 py-2.5 font-medium">數量</th>
                        <th class="px-3 py-2.5 font-medium">單價</th>
                        <th class="px-3 py-2.5 font-medium text-right">金額</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">${rows}</tbody>
            </table>
        </div>
    `;
}

function initPurchaseOrderShowPage(root) {
    const orderId = root.dataset.purchaseOrderId;
    const detail = root.querySelector('[data-purchase-order-detail]');
    const alertBox = root.querySelector('[data-alert]');
    const editLink = root.querySelector('[data-edit-link]');

    const showAlert = (message, type = 'error') => {
        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
    };

    const renderOrder = (order) => {
        if (order.status === 'cancelled' && editLink) {
            editLink.classList.add('pointer-events-none', 'opacity-50');
            editLink.setAttribute('aria-disabled', 'true');
        }

        detail.innerHTML = `
            <dl class="space-y-5">
                ${detailRow('採購單號', `<span class="font-medium">${escapeHtml(order.code || '—')}</span>`)}
                ${detailRow('採購日期', escapeHtml(order.order_date || '—'))}
                ${detailRow('預計到貨日', escapeHtml(order.expected_date || '—'))}
                ${detailRow('供應商', escapeHtml(order.vendor?.name || '—'))}
                ${detailRow('進貨倉庫', escapeHtml(order.warehouse?.name || '—'))}
                ${detailRow('狀態', statusBadge(order.status, order.status_label))}
                ${detailRow('總金額', `<span class="font-semibold">${escapeHtml(formatMoney(order.total_amount))}</span>`)}
                ${detailRow('備註', formatNotes(order.notes))}
            </dl>
            <div class="mt-8 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">採購明細</h3>
                ${renderItems(order.items)}
            </div>
        `;
    };

    const loadOrder = async () => {
        detail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';

        try {
            const payload = await api(`/api/purchase-orders/${orderId}`);
            renderOrder(payload.data ?? payload);
        } catch (error) {
            detail.innerHTML =
                '<p class="text-sm text-red-600">載入失敗，請稍後再試。</p>';
            showAlert(error.message || '載入採購單明細失敗', 'error');
        }
    };

    loadOrder();
}

const page = document.querySelector('[data-purchase-order-show-page]');
if (page) {
    initPurchaseOrderShowPage(page);
}
