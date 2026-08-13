import { drawPriceLineChart, formatChartMoney, seriesKey } from './price-history-chart';

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

    const number = Number(value);
    if (Number.isFinite(number)) {
        return number.toLocaleString('zh-TW', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    return String(value);
}

function historyDatePart(value) {
    return String(value ?? '').slice(0, 10);
}

function formatIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function extractPricePoints(histories) {
    return [...histories]
        .flatMap((history) => {
            const changes = Array.isArray(history.changes) ? history.changes : [];

            return changes.map((change) => {
                const price = Number(change?.new);

                if (!Number.isFinite(price)) {
                    return null;
                }

                return {
                    at: history.created_at,
                    price,
                    vendorId: change.vendor_id ?? null,
                    vendorName: change.vendor_name || '',
                };
            });
        })
        .filter(Boolean)
        .sort((a, b) => String(a.at ?? '').localeCompare(String(b.at ?? '')));
}

export function initPriceHistories(root, productId) {
    const openButtons = [...root.querySelectorAll('[data-open-price-histories]')];
    const modal = root.querySelector('[data-price-histories-modal]');
    const title = root.querySelector('[data-price-histories-modal-title]');
    const list = root.querySelector('[data-price-histories-modal-list]');
    const meta = root.querySelector('[data-price-histories-modal-meta]');
    const backdrop = root.querySelector('[data-price-histories-modal-backdrop]');
    const closeButtons = [...root.querySelectorAll('[data-price-histories-modal-close]')];
    const fromInput = root.querySelector('[data-price-histories-from]');
    const toInput = root.querySelector('[data-price-histories-to]');
    const highEl = root.querySelector('[data-price-histories-high]');
    const highAtEl = root.querySelector('[data-price-histories-high-at]');
    const lowEl = root.querySelector('[data-price-histories-low]');
    const lowAtEl = root.querySelector('[data-price-histories-low-at]');
    const chartSvg = root.querySelector('[data-price-histories-chart]');
    const presetButtons = [...root.querySelectorAll('[data-range-preset]')];
    const pagination = root.querySelector('[data-price-histories-pagination]');
    const paginationSummary = root.querySelector('[data-price-histories-pagination-summary]');
    const paginationControls = root.querySelector('[data-price-histories-pagination-controls]');

    if (!openButtons.length || !modal || !title || !list || !meta) {
        return;
    }

    const fieldLabels = {
        estimated_purchase_price: '進價設定歷史',
        estimated_selling_price: '售價設定歷史',
    };

    const presetActiveClass =
        'rounded-lg border border-teal-700 bg-teal-50 px-3 py-1.5 text-sm font-medium text-teal-800';
    const presetInactiveClass =
        'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50';

    const LIST_PER_PAGE = 20;
    let allHistories = [];
    let filteredHistories = [];
    let currentField = 'estimated_purchase_price';
    let listPage = 1;
    const hiddenSeriesKeys = new Set();

    const closeModal = () => {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
    };

    const setActivePreset = (preset) => {
        presetButtons.forEach((button) => {
            button.className = button.dataset.rangePreset === preset ? presetActiveClass : presetInactiveClass;
        });
    };

    const filterHistories = (histories) => {
        const from = fromInput?.value || '';
        const to = toInput?.value || '';

        return histories.filter((history) => {
            const datePart = historyDatePart(history.created_at);
            if (!datePart) {
                return false;
            }
            if (from && datePart < from) {
                return false;
            }
            if (to && datePart > to) {
                return false;
            }
            return true;
        });
    };

    const renderStats = (points) => {
        if (!highEl || !lowEl) {
            return;
        }

        if (!points.length) {
            highEl.textContent = '—';
            lowEl.textContent = '—';
            if (highAtEl) {
                highAtEl.textContent = '';
            }
            if (lowAtEl) {
                lowAtEl.textContent = '';
            }
            return;
        }

        const highPoint = points.reduce((best, point) => (point.price > best.price ? point : best));
        const lowPoint = points.reduce((best, point) => (point.price < best.price ? point : best));

        highEl.textContent = formatChartMoney(highPoint.price);
        lowEl.textContent = formatChartMoney(lowPoint.price);
        if (highAtEl) {
            highAtEl.textContent = highPoint.at || '';
        }
        if (lowAtEl) {
            lowAtEl.textContent = lowPoint.at || '';
        }
    };

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

    const lastListPage = (total) => Math.max(1, Math.ceil(total / LIST_PER_PAGE));

    const renderListPagination = (total) => {
        if (!pagination || !paginationSummary || !paginationControls) {
            return;
        }

        if (total <= LIST_PER_PAGE) {
            pagination.hidden = true;
            paginationControls.innerHTML = '';
            return;
        }

        const lastPage = lastListPage(total);
        if (listPage > lastPage) {
            listPage = lastPage;
        }

        const from = (listPage - 1) * LIST_PER_PAGE + 1;
        const to = Math.min(listPage * LIST_PER_PAGE, total);

        pagination.hidden = false;
        paginationSummary.innerHTML = `顯示中 <span class="font-semibold text-slate-900">${from}</span> 至 <span class="font-semibold text-slate-900">${to}</span> 於 <span class="font-semibold text-slate-900">${total}</span> 筆`;

        const buttons = [];

        buttons.push(`
            <button
                type="button"
                data-history-page-prev
                ${listPage <= 1 ? 'disabled' : ''}
                aria-label="上一頁"
                class="${pageButtonClass(false, listPage <= 1, true)}"
            >
                ${chevronLeft}
            </button>
        `);

        for (let pageNumber = 1; pageNumber <= lastPage; pageNumber += 1) {
            const isActive = pageNumber === listPage;
            buttons.push(`
                <button
                    type="button"
                    data-history-page="${pageNumber}"
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
                data-history-page-next
                ${listPage >= lastPage ? 'disabled' : ''}
                aria-label="下一頁"
                class="${pageButtonClass(false, listPage >= lastPage)}"
            >
                ${chevronRight}
            </button>
        `);

        paginationControls.innerHTML = buttons.join('');
    };

    const renderHistories = (histories, field, total) => {
        meta.textContent = `共 ${total} 筆`;

        if (!histories.length) {
            const emptyLabel = field === 'estimated_selling_price' ? '目前尚無售價設定歷史' : '目前尚無進價設定歷史';
            list.innerHTML = `<li class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">${emptyLabel}</li>`;
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
                    <li class="px-5 py-4 sm:px-6">
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
    };

    const renderHistoriesPage = () => {
        const total = filteredHistories.length;
        const lastPage = lastListPage(total || 1);
        if (listPage > lastPage) {
            listPage = lastPage;
        }

        const start = (listPage - 1) * LIST_PER_PAGE;
        const pageItems = filteredHistories.slice(start, start + LIST_PER_PAGE);

        renderHistories(pageItems, currentField, total);
        renderListPagination(total);
    };

    const applyView = (resetPage = true) => {
        filteredHistories = filterHistories(allHistories);
        const points = extractPricePoints(filteredHistories);
        const visiblePoints = points.filter((point) => !hiddenSeriesKeys.has(seriesKey(point)));

        if (resetPage) {
            listPage = 1;
        }

        renderStats(visiblePoints);
        drawPriceLineChart(chartSvg, points, {
            hiddenKeys: hiddenSeriesKeys,
            onToggleSeries: (key) => {
                if (!key) {
                    return;
                }
                if (hiddenSeriesKeys.has(key)) {
                    hiddenSeriesKeys.delete(key);
                } else {
                    hiddenSeriesKeys.add(key);
                }
                applyView(false);
            },
        });
        renderHistoriesPage();
    };

    const applyPresetRange = (preset) => {
        setActivePreset(preset);

        if (!fromInput || !toInput) {
            applyView();
            return;
        }

        if (preset === 'all') {
            const dates = allHistories.map((history) => historyDatePart(history.created_at)).filter(Boolean);
            fromInput.value = dates.length ? dates.reduce((min, date) => (date < min ? date : min)) : '';
            toInput.value = dates.length ? dates.reduce((max, date) => (date > max ? date : max)) : '';
            applyView();
            return;
        }

        const days = Number(preset);
        const today = new Date();
        const from = new Date();
        from.setDate(today.getDate() - (Number.isFinite(days) ? days : 30));
        fromInput.value = formatIsoDate(from);
        toInput.value = formatIsoDate(today);
        applyView();
    };

    const openModal = async (field) => {
        currentField = field;
        hiddenSeriesKeys.clear();
        title.textContent = fieldLabels[field] || '價格設定歷史';
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        meta.textContent = '載入中…';
        list.innerHTML =
            '<li class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">載入中…</li>';
        if (pagination) {
            pagination.hidden = true;
        }
        highEl && (highEl.textContent = '—');
        lowEl && (lowEl.textContent = '—');
        if (highAtEl) {
            highAtEl.textContent = '';
        }
        if (lowAtEl) {
            lowAtEl.textContent = '';
        }
        drawPriceLineChart(chartSvg, []);

        try {
            const params = new URLSearchParams({ field });
            const payload = await api(`/api/products/${productId}/price-histories?${params.toString()}`);
            allHistories = payload.data ?? [];
            applyPresetRange('all');
        } catch {
            allHistories = [];
            meta.textContent = '載入失敗';
            list.innerHTML =
                '<li class="px-5 py-8 text-center text-sm text-red-600 sm:px-6">載入價格設定歷史失敗，請稍後再試</li>';
            if (pagination) {
                pagination.hidden = true;
            }
            drawPriceLineChart(chartSvg, []);
        }

        closeButtons[0]?.focus();
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openModal(button.dataset.openPriceHistories);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    backdrop?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    presetButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applyPresetRange(button.dataset.rangePreset);
        });
    });

    fromInput?.addEventListener('change', () => {
        setActivePreset('');
        applyView();
    });
    toInput?.addEventListener('change', () => {
        setActivePreset('');
        applyView();
    });

    paginationControls?.addEventListener('click', (event) => {
        const target = event.target.closest('button');
        if (!target || target.disabled) {
            return;
        }

        const lastPage = lastListPage(filteredHistories.length || 1);

        if (target.hasAttribute('data-history-page-prev')) {
            listPage = Math.max(1, listPage - 1);
            renderHistoriesPage();
            return;
        }

        if (target.hasAttribute('data-history-page-next')) {
            listPage = Math.min(lastPage, listPage + 1);
            renderHistoriesPage();
            return;
        }

        const pageNumber = Number(target.dataset.historyPage);
        if (Number.isFinite(pageNumber) && pageNumber >= 1 && pageNumber <= lastPage) {
            listPage = pageNumber;
            renderHistoriesPage();
        }
    });
}
