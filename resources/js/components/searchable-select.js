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
 *   queryParams?: Record<string, string> | (() => Record<string, string>),
 *   emptyLabel?: string,
 *   noResultLabel?: string,
 *   placeholder?: string,
 *   disabledPlaceholder?: string,
 *   onChange?: (item: {id: number, name: string, code?: string|null}|null) => void,
 * }} options
 */
export function initSearchableSelect(root, options) {
    const searchInput = root.querySelector('[data-searchable-select-search]');
    const optionsList = root.querySelector('[data-searchable-select-options]');
    const hiddenInput = root.querySelector('[data-searchable-select-value]');
    const clearButton = root.querySelector('[data-searchable-select-clear]');
    const endpoint = options.endpoint;
    const emptyLabel = options.emptyLabel ?? '目前沒有可選項目';
    const noResultLabel = options.noResultLabel ?? '找不到符合的項目';
    const placeholder = options.placeholder ?? '輸入關鍵字搜尋';
    const disabledPlaceholder = options.disabledPlaceholder ?? placeholder;

    /** @type {{id: number, name: string, code?: string|null}|null} */
    let selected = null;
    let searchTimer = null;
    let latestRequestId = 0;
    let highlightedIndex = -1;
    let isSearching = false;
    /** @type {Array<{id: number, name: string, code?: string|null}>} */
    let visibleOptions = [];

    const resolveQueryParams = () => {
        const params = options.queryParams;
        return typeof params === 'function' ? params() : (params ?? {});
    };

    const syncClearButton = () => {
        if (!clearButton) {
            return;
        }

        clearButton.hidden = !selected || searchInput.disabled;
    };

    const showSelectedLabel = () => {
        isSearching = false;
        searchInput.value = selected?.name ?? '';
        searchInput.placeholder = searchInput.disabled ? disabledPlaceholder : placeholder;
        syncClearButton();
    };

    const closeDropdown = () => {
        optionsList.hidden = true;
        searchInput.setAttribute('aria-expanded', 'false');
        highlightedIndex = -1;
    };

    const openDropdown = () => {
        optionsList.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
    };

    const setSelected = (item, { emit = true } = {}) => {
        selected = item
            ? {
                  id: Number(item.id),
                  name: item.name ?? '',
                  code: item.code ?? null,
              }
            : null;

        if (hiddenInput) {
            hiddenInput.value = selected ? String(selected.id) : '';
        }

        showSelectedLabel();

        if (emit && typeof options.onChange === 'function') {
            options.onChange(selected);
        }
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

        visibleOptions = items;

        if (!visibleOptions.length) {
            const label = searchInput.value.trim() && isSearching ? noResultLabel : emptyLabel;
            optionsList.innerHTML = `<li class="px-3 py-2 text-sm text-slate-500" role="presentation">${escapeHtml(label)}</li>`;
            openDropdown();
            return;
        }

        optionsList.innerHTML = visibleOptions
            .map((item, index) => {
                const isActive = index === highlightedIndex;
                const isCurrent = selected && Number(selected.id) === Number(item.id);

                return `
                    <li
                        role="option"
                        data-option-id="${item.id}"
                        data-option-index="${index}"
                        aria-selected="${isActive || isCurrent ? 'true' : 'false'}"
                        class="cursor-pointer px-3 py-2 text-sm ${
                            isActive
                                ? 'bg-teal-50 text-teal-900'
                                : isCurrent
                                  ? 'bg-slate-50 text-slate-900'
                                  : 'text-slate-700 hover:bg-slate-50'
                        }"
                    >
                        <span class="font-medium">${escapeHtml(item.name)}</span>
                        ${item.code ? `<span class="ml-2 text-xs text-slate-400">${escapeHtml(item.code)}</span>` : ''}
                    </li>
                `;
            })
            .join('');

        openDropdown();
    };

    const updateHighlight = () => {
        optionsList.querySelectorAll('[data-option-index]').forEach((el) => {
            const index = Number(el.dataset.optionIndex);
            const isActive = index === highlightedIndex;
            const item = visibleOptions[index];
            const isCurrent = selected && item && Number(selected.id) === Number(item.id);
            el.setAttribute('aria-selected', isActive || isCurrent ? 'true' : 'false');
            el.className = `cursor-pointer px-3 py-2 text-sm ${
                isActive
                    ? 'bg-teal-50 text-teal-900'
                    : isCurrent
                      ? 'bg-slate-50 text-slate-900'
                      : 'text-slate-700 hover:bg-slate-50'
            }`;
        });
    };

    const loadOptions = async (search) => {
        if (searchInput.disabled) {
            return;
        }

        const requestId = ++latestRequestId;
        renderOptions([], { loading: true });

        const params = new URLSearchParams({
            ...resolveQueryParams(),
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

    const selectOption = (item) => {
        setSelected(item);
        closeDropdown();
        searchInput.blur();
    };

    const clearSelection = () => {
        setSelected(null);
        closeDropdown();
        searchInput.focus();
        loadOptions('');
    };

    searchInput.addEventListener('focus', () => {
        if (searchInput.disabled) {
            return;
        }

        isSearching = true;
        searchInput.value = '';
        searchInput.placeholder = selected?.name || placeholder;
        highlightedIndex = -1;
        loadOptions('');
    });

    searchInput.addEventListener('input', () => {
        if (searchInput.disabled) {
            return;
        }

        isSearching = true;
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            highlightedIndex = -1;
            loadOptions(searchInput.value.trim());
        }, 250);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (searchInput.disabled) {
            return;
        }

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
            showSelectedLabel();
            searchInput.blur();
        }
    });

    searchInput.addEventListener('blur', () => {
        window.setTimeout(() => {
            if (!root.contains(document.activeElement)) {
                closeDropdown();
                showSelectedLabel();
            }
        }, 120);
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

    clearButton?.addEventListener('mousedown', (event) => {
        event.preventDefault();
        clearSelection();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeDropdown();
            showSelectedLabel();
        }
    });

    const initialId = root.dataset.initialId || '';
    const initialName = root.dataset.initialName || '';

    if (initialId) {
        setSelected(
            {
                id: Number(initialId),
                name: initialName || `#${initialId}`,
                code: null,
            },
            { emit: false },
        );

        if (!initialName) {
            fetchJson(`${endpoint.replace(/\/$/, '')}/${initialId}`)
                .then((payload) => {
                    const item = payload.data;
                    if (item && Number(item.id) === Number(initialId)) {
                        setSelected(item, { emit: false });
                    }
                })
                .catch(() => {
                    // Keep fallback label.
                });
        }
    } else {
        showSelectedLabel();
    }

    return {
        getValue: () => (selected ? selected.id : null),
        getSelected: () => selected,
        setValue: (item, opts = {}) => setSelected(item, opts),
        clear: () => setSelected(null, { emit: true }),
        setDisabled: (disabled) => {
            searchInput.disabled = Boolean(disabled);
            if (disabled) {
                closeDropdown();
            }
            showSelectedLabel();
        },
        reload: () => {
            if (!optionsList.hidden) {
                loadOptions(isSearching ? searchInput.value.trim() : '');
            }
        },
    };
}
