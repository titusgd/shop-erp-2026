import { api, escapeHtml } from './components/purchase-order-form';
import { renderPaginationControls } from './components/pagination-controls';

function initPurchaseOrdersPage(root) {
    const tableBody = root.querySelector('[data-table-body]');
    const meta = root.querySelector('[data-meta]');
    const searchInput = root.querySelector('[data-search]');
    const statusFilter = root.querySelector('[data-status-filter]');
    const pagination = root.querySelector('[data-pagination]');
    const paginationSummary = root.querySelector('[data-pagination-summary]');
    const paginationControls = root.querySelector('[data-pagination-controls]');
    const alertBox = root.querySelector('[data-alert]');
    const deleteDialog = root.querySelector('[data-delete-dialog]');
    const deleteDialogMessage = root.querySelector('[data-delete-dialog-message]');
    const deleteDialogCancel = root.querySelector('[data-delete-dialog-cancel]');
    const deleteDialogConfirm = root.querySelector('[data-delete-dialog-confirm]');
    const deleteDialogBackdrop = root.querySelector('[data-delete-dialog-backdrop]');

    let page = 1;
    let lastPage = 1;
    let from = 0;
    let to = 0;
    let total = 0;
    let search = '';
    let status = '';
    let searchTimer = null;
    let pendingDeleteId = null;
    let isDeleting = false;

    const renderPagination = () => {
        if (total <= 0) {
            pagination.hidden = true;
            return;
        }

        pagination.hidden = false;
        paginationSummary.innerHTML = `顯示中 <span class="font-semibold text-slate-900">${from}</span> 至 <span class="font-semibold text-slate-900">${to}</span> 於 <span class="font-semibold text-slate-900">${total}</span> 結果`;
        paginationControls.innerHTML = renderPaginationControls({
            current: page,
            lastPage,
        });
    };

    const showAlert = (message, type = 'success') => {
        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';

        window.clearTimeout(showAlert.timer);
        showAlert.timer = window.setTimeout(() => {
            alertBox.hidden = true;
        }, 3200);
    };

    const statusBadge = (statusValue, label) => {
        const styles = {
            draft: 'bg-slate-100 text-slate-700',
            confirmed: 'bg-teal-50 text-teal-800',
            cancelled: 'bg-red-50 text-red-700',
        };

        return `<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[statusValue] ?? 'bg-slate-100 text-slate-600'}">${escapeHtml(label || statusValue)}</span>`;
    };

    const formatMoney = (value) => {
        const number = Number(value);
        if (!Number.isFinite(number)) {
            return '0.00';
        }

        return number.toLocaleString('zh-TW', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const renderRows = (orders) => {
        if (!orders.length) {
            tableBody.innerHTML =
                '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500 sm:px-5">目前沒有採購單資料</td></tr>';
            return;
        }

        tableBody.innerHTML = orders
            .map((order) => {
                const canDelete = order.status !== 'confirmed';
                return `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900 sm:px-5">${escapeHtml(order.code || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(order.order_date || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(order.vendor?.name || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(order.warehouse?.name || '—')}</td>
                        <td class="px-4 py-3 text-right text-slate-900 sm:px-5">${escapeHtml(formatMoney(order.total_amount))}</td>
                        <td class="px-4 py-3 sm:px-5">${statusBadge(order.status, order.status_label)}</td>
                        <td class="px-4 py-3 text-right sm:px-5">
                            <div class="inline-flex items-center gap-1">
                                <a href="/purchase-orders/${order.id}" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">檢視</a>
                                <a href="/purchase-orders/${order.id}/edit" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-teal-700 transition hover:bg-teal-50">編輯</a>
                                ${
                                    canDelete
                                        ? `<button
                                    type="button"
                                    data-action="delete"
                                    data-id="${order.id}"
                                    data-code="${escapeHtml(order.code || '')}"
                                    class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50"
                                >
                                    刪除
                                </button>`
                                        : ''
                                }
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join('');
    };

    const loadOrders = async () => {
        meta.textContent = '載入中…';

        const params = new URLSearchParams({
            page: String(page),
            per_page: '10',
        });

        if (search) {
            params.set('search', search);
        }

        if (status) {
            params.set('status', status);
        }

        try {
            const payload = await api(`/api/purchase-orders?${params.toString()}`);
            const orders = payload.data ?? [];

            page = payload.meta?.current_page ?? 1;
            lastPage = payload.meta?.last_page ?? 1;
            from = payload.meta?.from ?? (orders.length ? 1 : 0);
            to = payload.meta?.to ?? orders.length;
            total = payload.meta?.total ?? orders.length;

            renderRows(orders);
            meta.textContent = `共 ${total} 筆`;
            renderPagination();
        } catch (error) {
            tableBody.innerHTML =
                '<tr><td colspan="7" class="px-4 py-8 text-center text-red-600 sm:px-5">載入失敗，請稍後再試</td></tr>';
            meta.textContent = '載入失敗';
            pagination.hidden = true;
            showAlert(error.message || '載入採購單列表失敗', 'error');
        }
    };

    const closeDeleteDialog = () => {
        if (isDeleting) {
            return;
        }

        pendingDeleteId = null;
        deleteDialog.hidden = true;
        deleteDialog.setAttribute('aria-hidden', 'true');
        deleteDialogConfirm.disabled = false;
    };

    const openDeleteDialog = (id, code) => {
        pendingDeleteId = id;
        deleteDialogMessage.textContent = `確定要刪除採購單「${code || id}」嗎？此操作無法復原。`;
        deleteDialog.hidden = false;
        deleteDialog.setAttribute('aria-hidden', 'false');
        deleteDialogConfirm.focus();
    };

    const confirmDelete = async () => {
        if (!pendingDeleteId || isDeleting) {
            return;
        }

        isDeleting = true;
        deleteDialogConfirm.disabled = true;

        try {
            await api(`/api/purchase-orders/${pendingDeleteId}`, { method: 'DELETE' });
            pendingDeleteId = null;
            isDeleting = false;
            closeDeleteDialog();
            showAlert('採購單已刪除');
            await loadOrders();
        } catch (error) {
            isDeleting = false;
            deleteDialogConfirm.disabled = false;
            const message = error.payload?.message ?? error.payload?.errors?.status?.[0] ?? error.message ?? '刪除失敗';
            showAlert(message, 'error');
        }
    };

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="delete"]');
        if (!button) {
            return;
        }

        openDeleteDialog(Number(button.dataset.id), button.dataset.code ?? '');
    });

    deleteDialogCancel.addEventListener('click', closeDeleteDialog);
    deleteDialogBackdrop.addEventListener('click', closeDeleteDialog);
    deleteDialogConfirm.addEventListener('click', confirmDelete);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !deleteDialog.hidden) {
            closeDeleteDialog();
        }
    });

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            search = searchInput.value.trim();
            page = 1;
            loadOrders();
        }, 300);
    });

    statusFilter.addEventListener('change', () => {
        status = statusFilter.value;
        page = 1;
        loadOrders();
    });

    paginationControls.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) {
            return;
        }

        if (button.hasAttribute('data-page-prev') && page > 1) {
            page -= 1;
            loadOrders();
            return;
        }

        if (button.hasAttribute('data-page-next') && page < lastPage) {
            page += 1;
            loadOrders();
            return;
        }

        if (button.dataset.page) {
            const nextPage = Number(button.dataset.page);
            if (nextPage !== page && nextPage >= 1 && nextPage <= lastPage) {
                page = nextPage;
                loadOrders();
            }
        }
    });

    loadOrders();
}

const page = document.querySelector('[data-purchase-orders-page]');
if (page) {
    initPurchaseOrdersPage(page);
}
