import { api, escapeHtml } from './components/purchase-requisition-form';

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
                <td class="px-3 py-2 text-slate-900">${escapeHtml(item.notes || '—')}</td>
            </tr>
        `,
        )
        .join('');

    return `
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[28rem] w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-3 py-2.5 font-medium">商品</th>
                        <th class="px-3 py-2.5 font-medium">數量</th>
                        <th class="px-3 py-2.5 font-medium">備註</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">${rows}</tbody>
            </table>
        </div>
    `;
}

function initPurchaseRequisitionShowPage(root) {
    const requisitionId = root.dataset.purchaseRequisitionId;
    const detail = root.querySelector('[data-purchase-requisition-detail]');
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

    const renderRequisition = (requisition) => {
        if (requisition.status === 'cancelled' && editLink) {
            editLink.classList.add('pointer-events-none', 'opacity-50');
            editLink.setAttribute('aria-disabled', 'true');
        }

        detail.innerHTML = `
            <dl class="space-y-5">
                ${detailRow('請購單號', `<span class="font-medium">${escapeHtml(requisition.code || '—')}</span>`)}
                ${detailRow('請購日期', escapeHtml(requisition.request_date || '—'))}
                ${detailRow('需求日期', escapeHtml(requisition.required_date || '—'))}
                ${detailRow('請購人', escapeHtml(requisition.requester?.name || '—'))}
                ${detailRow('進貨倉庫', escapeHtml(requisition.warehouse?.name || '—'))}
                ${detailRow('狀態', statusBadge(requisition.status, requisition.status_label))}
                ${detailRow('備註', formatNotes(requisition.notes))}
            </dl>
            <div class="mt-8 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">請購明細</h3>
                ${renderItems(requisition.items)}
            </div>
        `;
    };

    const loadRequisition = async () => {
        detail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';

        try {
            const payload = await api(`/api/purchase-requisitions/${requisitionId}`);
            renderRequisition(payload.data ?? payload);
        } catch (error) {
            detail.innerHTML =
                '<p class="text-sm text-red-600">載入失敗，請稍後再試。</p>';
            showAlert(error.message || '載入請購單明細失敗', 'error');
        }
    };

    loadRequisition();
}

const page = document.querySelector('[data-purchase-requisition-show-page]');
if (page) {
    initPurchaseRequisitionShowPage(page);
}
