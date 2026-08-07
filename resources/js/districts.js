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

function initDistrictsPage(root) {
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

    const chevronLeft = `
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
    `;

    const chevronRight = `
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
    `;

    const pageButtonClass = (active, disabled = false, isFirst = false) => {
        const base =
            'inline-flex h-9 min-w-9 items-center justify-center px-3 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-500';
        const border = isFirst ? '' : 'border-l border-slate-300';

        if (disabled) {
            return `${base} ${border} cursor-not-allowed bg-slate-50 text-slate-400`;
        }

        if (active) {
            return `${base} ${border} bg-teal-700 text-white`;
        }

        return `${base} ${border} bg-white text-teal-700 hover:bg-teal-50`;
    };

    const renderPagination = () => {
        if (total <= 0) {
            pagination.hidden = true;
            return;
        }

        pagination.hidden = false;
        paginationSummary.innerHTML = `顯示中 <span class="font-semibold text-slate-900">${from}</span> 至 <span class="font-semibold text-slate-900">${to}</span> 於 <span class="font-semibold text-slate-900">${total}</span> 結果`;

        const buttons = [];

        buttons.push(`
            <button
                type="button"
                data-page-prev
                ${page <= 1 ? 'disabled' : ''}
                aria-label="上一頁"
                class="${pageButtonClass(false, page <= 1, true)}"
            >
                ${chevronLeft}
            </button>
        `);

        for (let pageNumber = 1; pageNumber <= lastPage; pageNumber += 1) {
            const isActive = pageNumber === page;
            buttons.push(`
                <button
                    type="button"
                    data-page="${pageNumber}"
                    aria-label="第 ${pageNumber} 頁"
                    aria-current="${isActive ? 'page' : 'false'}"
                    class="${pageButtonClass(isActive)}"
                >
                    ${pageNumber}
                </button>
            `);
        }

        buttons.push(`
            <button
                type="button"
                data-page-next
                ${page >= lastPage ? 'disabled' : ''}
                aria-label="下一頁"
                class="${pageButtonClass(false, page >= lastPage)}"
            >
                ${chevronRight}
            </button>
        `);

        paginationControls.innerHTML = buttons.join('');
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

    const renderRows = (districts) => {
        if (!districts.length) {
            tableBody.innerHTML =
                '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500 sm:px-5">目前沒有地區資料</td></tr>';
            return;
        }

        tableBody.innerHTML = districts
            .map(
                (district) => `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900 sm:px-5">${escapeHtml(district.code || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(district.city?.name || '—')}</td>
                        <td class="px-4 py-3 text-slate-900 sm:px-5">${escapeHtml(district.name)}</td>
                        <td class="px-4 py-3 sm:px-5">${statusBadge(district.is_active)}</td>
                        <td class="px-4 py-3 text-right sm:px-5">
                            <div class="inline-flex items-center gap-1">
                                <a href="/districts/${district.id}/edit" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-teal-700 transition hover:bg-teal-50">編輯</a>
                                <button
                                    type="button"
                                    data-action="delete"
                                    data-id="${district.id}"
                                    data-name="${escapeHtml(district.name)}"
                                    data-code="${escapeHtml(district.code || '')}"
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

    const loadDistricts = async () => {
        meta.textContent = '載入中…';

        const params = new URLSearchParams({
            page: String(page),
            per_page: '10',
        });

        if (search) {
            params.set('search', search);
        }

        try {
            const payload = await api(`/api/districts?${params.toString()}`);
            const districts = payload.data ?? [];

            page = payload.meta?.current_page ?? 1;
            lastPage = payload.meta?.last_page ?? 1;
            from = payload.meta?.from ?? (districts.length ? 1 : 0);
            to = payload.meta?.to ?? districts.length;
            total = payload.meta?.total ?? districts.length;

            renderRows(districts);
            meta.textContent = `共 ${total} 筆`;
            renderPagination();
        } catch (error) {
            tableBody.innerHTML =
                '<tr><td colspan="5" class="px-4 py-8 text-center text-red-600 sm:px-5">載入失敗，請稍後再試</td></tr>';
            meta.textContent = '載入失敗';
            pagination.hidden = true;
            showAlert(error.message || '載入地區列表失敗', 'error');
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
        deleteDialogMessage.textContent = `確定要刪除地區「${label}」嗎？此操作無法復原。`;
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
            await api(`/api/districts/${pendingDeleteId}`, { method: 'DELETE' });
            pendingDeleteId = null;
            isDeleting = false;
            closeDeleteDialog();
            showAlert('地區已刪除');
            await loadDistricts();
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
            loadDistricts();
        }, 300);
    });

    paginationControls.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) {
            return;
        }

        if (button.hasAttribute('data-page-prev') && page > 1) {
            page -= 1;
            loadDistricts();
            return;
        }

        if (button.hasAttribute('data-page-next') && page < lastPage) {
            page += 1;
            loadDistricts();
            return;
        }

        if (button.dataset.page) {
            const nextPage = Number(button.dataset.page);
            if (nextPage !== page && nextPage >= 1 && nextPage <= lastPage) {
                page = nextPage;
                loadDistricts();
            }
        }
    });

    loadDistricts();
}

const page = document.querySelector('[data-districts-page]');
if (page) {
    initDistrictsPage(page);
}
