const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

async function fetchJson(url) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        const error = new Error(payload?.message ?? '請求失敗');
        error.status = response.status;
        error.payload = payload;
        throw error;
    }

    return payload;
}

/**
 * @param {HTMLElement} root
 * @param {{
 *   endpoint: string,
 *   queryParams?: Record<string, string>,
 *   emptyLabel?: string,
 *   noResultLabel?: string,
 * }} options
 */
export function initSearchableMultiSelect(root, options) {
    const searchInput = root.querySelector('[data-multi-select-search]');
    const optionsList = root.querySelector('[data-multi-select-options]');
    const selectedList = root.querySelector('[data-multi-select-selected]');
    const selectedWrap = root.querySelector('[data-multi-select-selected-wrap]');
    const endpoint = options.endpoint;
    const queryParams = options.queryParams ?? {};
    const emptyLabel = options.emptyLabel ?? '目前沒有可選項目';
    const noResultLabel = options.noResultLabel ?? '找不到符合的項目';

    /** @type {Map<number, {id: number, name: string, code?: string|null}>} */
    const selected = new Map();
    let searchTimer = null;
    let latestRequestId = 0;
    let highlightedIndex = -1;
    /** @type {Array<{id: number, name: string, code?: string|null}>} */
    let visibleOptions = [];

    try {
        const initial = JSON.parse(root.dataset.initialSelected || '[]');
        if (Array.isArray(initial)) {
            initial.forEach((item) => {
                if (item?.id != null) {
                    selected.set(Number(item.id), {
                        id: Number(item.id),
                        name: item.name ?? '',
                        code: item.code ?? null,
                    });
                }
            });
        }
    } catch {
        // ignore invalid initial payload
    }

    const closeDropdown = () => {
        optionsList.hidden = true;
        searchInput.setAttribute('aria-expanded', 'false');
        highlightedIndex = -1;
    };

    const openDropdown = () => {
        optionsList.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
    };

    const renderSelected = () => {
        const items = [...selected.values()];

        if (!items.length) {
            selectedWrap.hidden = true;
            selectedList.innerHTML = '';
            return;
        }

        selectedWrap.hidden = false;
        selectedList.innerHTML = items
            .map(
                (item) => `
                    <li class="inline-flex max-w-full items-center gap-1.5 rounded-md border border-teal-200 bg-teal-50 py-1 pl-2.5 pr-1 text-sm text-teal-900">
                        <span class="truncate font-medium" title="${escapeHtml(item.code || item.name)}">${escapeHtml(item.name)}</span>
                        <button
                            type="button"
                            data-remove-id="${item.id}"
                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded text-teal-700 transition hover:bg-teal-100 hover:text-teal-950"
                            aria-label="移除 ${escapeHtml(item.name)}"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </li>
                `,
            )
            .join('');
    };

    const optionLabel = (item) => {
        const code = item.code ? `（${item.code}）` : '';
        return `${item.name}${code}`;
    };

    const renderOptions = (items, { loading = false, errorMessage = null } = {}) => {
        if (loading) {
            optionsList.innerHTML =
                '<li class="px-3 py-2 text-sm text-slate-500" role="presentation">搜尋中…</li>';
            openDropdown();
            return;
        }

        if (errorMessage) {
            optionsList.innerHTML = `<li class="px-3 py-2 text-sm text-red-600" role="presentation">${escapeHtml(errorMessage)}</li>`;
            openDropdown();
            return;
        }

        visibleOptions = items.filter((item) => !selected.has(Number(item.id)));

        if (!visibleOptions.length) {
            const label = searchInput.value.trim() ? noResultLabel : emptyLabel;
            optionsList.innerHTML = `<li class="px-3 py-2 text-sm text-slate-500" role="presentation">${escapeHtml(label)}</li>`;
            openDropdown();
            return;
        }

        optionsList.innerHTML = visibleOptions
            .map(
                (item, index) => `
                    <li
                        role="option"
                        data-option-id="${item.id}"
                        data-option-index="${index}"
                        aria-selected="${index === highlightedIndex ? 'true' : 'false'}"
                        class="cursor-pointer px-3 py-2 text-sm ${
                            index === highlightedIndex
                                ? 'bg-teal-50 text-teal-900'
                                : 'text-slate-700 hover:bg-slate-50'
                        }"
                    >
                        <span class="font-medium">${escapeHtml(item.name)}</span>
                        <span class="ml-2 text-xs text-slate-400">${escapeHtml(item.code || '')}</span>
                    </li>
                `,
            )
            .join('');

        openDropdown();
    };

    const updateHighlight = () => {
        optionsList.querySelectorAll('[data-option-index]').forEach((el) => {
            const index = Number(el.dataset.optionIndex);
            const isActive = index === highlightedIndex;
            el.setAttribute('aria-selected', isActive ? 'true' : 'false');
            el.className = `cursor-pointer px-3 py-2 text-sm ${
                isActive ? 'bg-teal-50 text-teal-900' : 'text-slate-700 hover:bg-slate-50'
            }`;
        });
    };

    const selectOption = (item) => {
        selected.set(Number(item.id), {
            id: Number(item.id),
            name: item.name,
            code: item.code ?? null,
        });
        renderSelected();
        searchInput.value = '';
        highlightedIndex = -1;
        loadOptions('');
        searchInput.focus();
    };

    const loadOptions = async (search) => {
        const requestId = ++latestRequestId;
        renderOptions([], { loading: true });

        const params = new URLSearchParams({
            ...queryParams,
            per_page: '20',
        });

        if (search) {
            params.set('search', search);
        }

        try {
            const payload = await fetchJson(`${endpoint}?${params.toString()}`);
            if (requestId !== latestRequestId) {
                return;
            }

            renderOptions(payload.data ?? []);
        } catch (error) {
            if (requestId !== latestRequestId) {
                return;
            }

            renderOptions([], { errorMessage: error.message || '載入失敗' });
        }
    };

    searchInput.addEventListener('focus', () => {
        loadOptions(searchInput.value.trim());
    });

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            highlightedIndex = -1;
            loadOptions(searchInput.value.trim());
        }, 250);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (optionsList.hidden) {
                loadOptions(searchInput.value.trim());
                return;
            }
            if (!visibleOptions.length) {
                return;
            }
            highlightedIndex = (highlightedIndex + 1) % visibleOptions.length;
            updateHighlight();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (!visibleOptions.length) {
                return;
            }
            highlightedIndex =
                highlightedIndex <= 0 ? visibleOptions.length - 1 : highlightedIndex - 1;
            updateHighlight();
            return;
        }

        if (event.key === 'Enter') {
            if (!optionsList.hidden && highlightedIndex >= 0 && visibleOptions[highlightedIndex]) {
                event.preventDefault();
                selectOption(visibleOptions[highlightedIndex]);
            }
            return;
        }

        if (event.key === 'Escape') {
            closeDropdown();
        }
    });

    optionsList.addEventListener('mousedown', (event) => {
        event.preventDefault();
        const option = event.target.closest('[data-option-id]');
        if (!option) {
            return;
        }

        const id = Number(option.dataset.optionId);
        const item = visibleOptions.find((entry) => Number(entry.id) === id);
        if (item) {
            selectOption(item);
        }
    });

    selectedList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-id]');
        if (!button) {
            return;
        }

        selected.delete(Number(button.dataset.removeId));
        renderSelected();
        if (!optionsList.hidden) {
            loadOptions(searchInput.value.trim());
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeDropdown();
        }
    });

    renderSelected();

    return {
        getSelectedIds: () => [...selected.keys()],
        getSelectedItems: () => [...selected.values()],
        optionLabel,
    };
}
