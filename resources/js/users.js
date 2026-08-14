import { renderPaginationControls } from './components/pagination-controls';

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const escapeHtml = (value) =>
    String(value)
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

function initUsersPage(root) {
    const currentUserId = Number(root.dataset.currentUserId);
    const tableBody = root.querySelector('[data-table-body]');
    const meta = root.querySelector('[data-meta]');
    const searchInput = root.querySelector('[data-search]');
    const pagination = root.querySelector('[data-pagination]');
    const paginationSummary = root.querySelector('[data-pagination-summary]');
    const paginationControls = root.querySelector('[data-pagination-controls]');
    const alertBox = root.querySelector('[data-alert]');

    let page = 1;
    let lastPage = 1;
    let from = 0;
    let to = 0;
    let total = 0;
    let search = '';
    let searchTimer = null;

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

    const renderRows = (users) => {
        if (!users.length) {
            tableBody.innerHTML =
                '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500 sm:px-5">目前沒有帳號資料</td></tr>';
            return;
        }

        tableBody.innerHTML = users
            .map((user) => {
                const isSelf = Number(user.id) === currentUserId;
                const deleteDisabled = isSelf
                    ? 'disabled class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-300 cursor-not-allowed"'
                    : 'data-action="delete" data-id="' +
                      user.id +
                      '" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50"';

                return `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900 sm:px-5">${escapeHtml(user.name)}</td>
                        <td class="px-4 py-3 text-slate-700 sm:px-5">${escapeHtml(user.username)}</td>
                        <td class="px-4 py-3 text-slate-700 sm:px-5">${escapeHtml(user.email)}</td>
                        <td class="px-4 py-3 text-slate-500 sm:px-5">${escapeHtml(formatDate(user.created_at))}</td>
                        <td class="px-4 py-3 text-right sm:px-5">
                            <div class="inline-flex items-center gap-1">
                                <a href="/users/${user.id}/edit" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-teal-700 transition hover:bg-teal-50">編輯</a>
                                <button type="button" ${deleteDisabled}>刪除</button>
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join('');
    };

    const loadUsers = async () => {
        meta.textContent = '載入中…';

        const params = new URLSearchParams({
            page: String(page),
            per_page: '10',
        });

        if (search) {
            params.set('search', search);
        }

        try {
            const payload = await api(`/api/users?${params.toString()}`);
            const users = payload.data ?? [];

            page = payload.meta?.current_page ?? 1;
            lastPage = payload.meta?.last_page ?? 1;
            from = payload.meta?.from ?? (users.length ? 1 : 0);
            to = payload.meta?.to ?? users.length;
            total = payload.meta?.total ?? users.length;

            renderRows(users);
            meta.textContent = `共 ${total} 筆`;
            renderPagination();
        } catch (error) {
            tableBody.innerHTML =
                '<tr><td colspan="5" class="px-4 py-8 text-center text-red-600 sm:px-5">載入失敗，請稍後再試</td></tr>';
            meta.textContent = '載入失敗';
            pagination.hidden = true;
            showAlert(error.message || '載入帳號列表失敗', 'error');
        }
    };

    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action="delete"]');
        if (!button) {
            return;
        }

        const id = Number(button.dataset.id);

        if (!window.confirm('確定要刪除此帳號嗎？')) {
            return;
        }

        try {
            await api(`/api/users/${id}`, { method: 'DELETE' });
            showAlert('帳號已刪除');
            await loadUsers();
        } catch (error) {
            const message =
                error.payload?.errors?.user?.[0] ??
                error.payload?.message ??
                error.message ??
                '刪除失敗';
            showAlert(message, 'error');
        }
    });

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            search = searchInput.value.trim();
            page = 1;
            loadUsers();
        }, 300);
    });

    paginationControls.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) {
            return;
        }

        if (button.hasAttribute('data-page-prev') && page > 1) {
            page -= 1;
            loadUsers();
            return;
        }

        if (button.hasAttribute('data-page-next') && page < lastPage) {
            page += 1;
            loadUsers();
            return;
        }

        if (button.dataset.page) {
            const nextPage = Number(button.dataset.page);
            if (nextPage !== page && nextPage >= 1 && nextPage <= lastPage) {
                page = nextPage;
                loadUsers();
            }
        }
    });

    loadUsers();
}

const page = document.querySelector('[data-users-page]');
if (page) {
    initUsersPage(page);
}
