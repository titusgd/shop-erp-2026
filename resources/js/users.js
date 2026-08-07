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
    const modal = root.querySelector('[data-modal]');
    const modalTitle = root.querySelector('[data-modal-title]');
    const modalSubtitle = root.querySelector('[data-modal-subtitle]');
    const passwordHint = root.querySelector('[data-password-hint]');
    const form = root.querySelector('[data-user-form]');
    const submitButton = root.querySelector('[data-submit]');

    let page = 1;
    let lastPage = 1;
    let from = 0;
    let to = 0;
    let total = 0;
    let search = '';
    let searchTimer = null;

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
                ? 'fixed bottom-4 right-4 z-40 max-w-sm rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg'
                : 'fixed bottom-4 right-4 z-40 max-w-sm rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg';

        window.clearTimeout(showAlert.timer);
        showAlert.timer = window.setTimeout(() => {
            alertBox.hidden = true;
        }, 3200);
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
            const el = form.querySelector(`[data-error="${field}"]`);
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

    const closeModal = () => {
        modal.hidden = true;
        form.reset();
        clearErrors();
    };

    const openCreateModal = () => {
        form.reset();
        clearErrors();
        form.querySelector('[data-field="id"]').value = '';
        modalTitle.textContent = '新增帳號';
        modalSubtitle.textContent = '填寫帳號基本資料。';
        passwordHint.textContent = '';
        form.querySelector('[data-field="password"]').required = true;
        modal.hidden = false;
        form.querySelector('[data-field="name"]').focus();
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

    root.querySelector('[data-action="create"]').addEventListener('click', openCreateModal);

    root.querySelectorAll('[data-modal-close], [data-modal-backdrop]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

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
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        const payload = {
            name: form.querySelector('[data-field="name"]').value.trim(),
            username: form.querySelector('[data-field="username"]').value.trim(),
            email: form.querySelector('[data-field="email"]').value.trim(),
            password: form.querySelector('[data-field="password"]').value,
            password_confirmation: form.querySelector('[data-field="password_confirmation"]').value,
        };

        submitButton.disabled = true;

        try {
            await api('/api/users', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            showAlert('帳號已新增');
            closeModal();
            await loadUsers();
        } catch (error) {
            if (error.status === 422) {
                showErrors(error.payload?.errors ?? {}, error.payload?.message);
            } else {
                showAlert(error.message || '儲存失敗', 'error');
            }
        } finally {
            submitButton.disabled = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    loadUsers();
}

const page = document.querySelector('[data-users-page]');
if (page) {
    initUsersPage(page);
}
