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

function formatHistoryValue(value) {
    if (value === null || value === undefined || value === '') {
        return '（空）';
    }

    return String(value);
}

function initWarehouseHistoriesPage(root) {
    const warehouseId = root.dataset.warehouseId;
    const list = root.querySelector('[data-histories-list]');
    const meta = root.querySelector('[data-histories-meta]');
    const alertBox = root.querySelector('[data-alert]');

    const showAlert = (message, type = 'error') => {
        if (!alertBox) {
            return;
        }

        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
    };

    const loadHistories = async () => {
        meta.textContent = '載入中…';
        list.innerHTML = '<li class="px-4 py-8 text-center text-sm text-slate-500 sm:px-5">載入中…</li>';

        try {
            const payload = await api(`/api/warehouses/${warehouseId}/histories`);
            const histories = payload.data ?? [];

            meta.textContent = `共 ${histories.length} 筆`;

            if (!histories.length) {
                list.innerHTML =
                    '<li class="px-4 py-8 text-center text-sm text-slate-500 sm:px-5">目前尚無修改歷程</li>';
                return;
            }

            list.innerHTML = histories
                .map((history) => {
                    const userName = history.user?.name || '系統';
                    const changes = Array.isArray(history.changes) ? history.changes : [];
                    const changeHtml = changes.length
                        ? `<ul class="mt-2 space-y-1 text-sm text-slate-600">${changes
                              .map(
                                  (change) => `
                                    <li>
                                        <span class="font-medium text-slate-800">${escapeHtml(change.label || change.field)}</span>
                                        ：${escapeHtml(formatHistoryValue(change.old))}
                                        →
                                        ${escapeHtml(formatHistoryValue(change.new))}
                                    </li>
                                `,
                              )
                              .join('')}</ul>`
                        : '<p class="mt-2 text-sm text-slate-500">無欄位變更明細</p>';

                    return `
                        <li class="px-4 py-4 sm:px-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-800">
                                        ${escapeHtml(history.action_label || history.action)}
                                    </span>
                                    <span class="text-sm font-medium text-slate-900">${escapeHtml(userName)}</span>
                                </div>
                                <time class="text-xs text-slate-500">${escapeHtml(history.created_at || '')}</time>
                            </div>
                            ${changeHtml}
                        </li>
                    `;
                })
                .join('');
        } catch (error) {
            meta.textContent = '載入失敗';
            list.innerHTML =
                '<li class="px-4 py-8 text-center text-sm text-red-600 sm:px-5">載入修改歷程失敗，請稍後再試</li>';
            showAlert(error.message || '載入修改歷程失敗', 'error');
        }
    };

    loadHistories();
}

const page = document.querySelector('[data-warehouse-histories-page]');
if (page) {
    initWarehouseHistoriesPage(page);
}
