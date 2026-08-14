import { renderPaginationControls } from './components/pagination-controls';

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

function initProductsPage(root) {
    const tableBody = root.querySelector('[data-table-body]');
    const meta = root.querySelector('[data-meta]');
    const searchInput = root.querySelector('[data-search]');
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

    const statusBadge = (isActive) =>
        isActive
            ? '<span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-800">啟用</span>'
            : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">停用</span>';

    const renderRows = (products) => {
        if (!products.length) {
            tableBody.innerHTML =
                '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500 sm:px-5">目前沒有商品資料</td></tr>';
            return;
        }

        tableBody.innerHTML = products
            .map(
                (product) => `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900 sm:px-5">${escapeHtml(product.code || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(product.name)}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(product.category?.name || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(product.unit?.name || '—')}</td>
                        <td class="px-4 py-3 sm:px-5">${statusBadge(product.is_active)}</td>
                        <td class="px-4 py-3 text-right sm:px-5">
                            <div class="inline-flex items-center gap-1">
                                <a href="/products/${product.id}" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">檢視</a>
                                <a href="/products/${product.id}/edit" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-teal-700 transition hover:bg-teal-50">編輯</a>
                                <button
                                    type="button"
                                    data-action="delete"
                                    data-id="${product.id}"
                                    data-name="${escapeHtml(product.name)}"
                                    data-code="${escapeHtml(product.code || '')}"
                                    class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50"
                                >
                                    刪除
                                </button>
                            </div>
                        </td>
                    </tr>
                `,
            )
            .join('');
    };

    const loadProducts = async () => {
        meta.textContent = '載入中…';

        const params = new URLSearchParams({
            page: String(page),
            per_page: '10',
        });

        if (search) {
            params.set('search', search);
        }

        try {
            const payload = await api(`/api/products?${params.toString()}`);
            const products = payload.data ?? [];

            page = payload.meta?.current_page ?? 1;
            lastPage = payload.meta?.last_page ?? 1;
            from = payload.meta?.from ?? (products.length ? 1 : 0);
            to = payload.meta?.to ?? products.length;
            total = payload.meta?.total ?? products.length;

            renderRows(products);
            meta.textContent = `共 ${total} 筆`;
            renderPagination();
        } catch (error) {
            tableBody.innerHTML =
                '<tr><td colspan="6" class="px-4 py-8 text-center text-red-600 sm:px-5">載入失敗，請稍後再試</td></tr>';
            meta.textContent = '載入失敗';
            pagination.hidden = true;
            showAlert(error.message || '載入商品列表失敗', 'error');
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

    const openDeleteDialog = (id, name, code) => {
        pendingDeleteId = id;
        const label = code ? `${code} ${name}` : name;
        deleteDialogMessage.textContent = `確定要刪除商品「${label}」嗎？此操作無法復原。`;
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
            await api(`/api/products/${pendingDeleteId}`, { method: 'DELETE' });
            pendingDeleteId = null;
            isDeleting = false;
            closeDeleteDialog();
            showAlert('商品已刪除');
            await loadProducts();
        } catch (error) {
            isDeleting = false;
            deleteDialogConfirm.disabled = false;
            const message = error.payload?.message ?? error.message ?? '刪除失敗';
            showAlert(message, 'error');
        }
    };

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="delete"]');
        if (!button) {
            return;
        }

        openDeleteDialog(
            Number(button.dataset.id),
            button.dataset.name ?? '',
            button.dataset.code ?? '',
        );
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
            loadProducts();
        }, 300);
    });

    paginationControls.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) {
            return;
        }

        if (button.hasAttribute('data-page-prev') && page > 1) {
            page -= 1;
            loadProducts();
            return;
        }

        if (button.hasAttribute('data-page-next') && page < lastPage) {
            page += 1;
            loadProducts();
            return;
        }

        if (button.dataset.page) {
            const nextPage = Number(button.dataset.page);
            if (nextPage !== page && nextPage >= 1 && nextPage <= lastPage) {
                page = nextPage;
                loadProducts();
            }
        }
    });

    loadProducts();
}

const page = document.querySelector('[data-products-page]');
if (page) {
    initProductsPage(page);
}
