import { initPriceHistories } from './components/price-histories-modal';

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

const activeTabClass =
    'whitespace-nowrap border-b-2 border-teal-700 px-3 py-3 text-sm font-medium text-teal-800 transition hover:text-teal-900';
const inactiveTabClass =
    'whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700';

function initTabs(root) {
    const tabs = [...root.querySelectorAll('[data-tab]')];
    const panels = [...root.querySelectorAll('[data-tab-panel]')];

    const activateTab = (tabKey) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.tab === tabKey;
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.className = isActive ? activeTabClass : inactiveTabClass;
            tab.tabIndex = isActive ? 0 : -1;
        });

        panels.forEach((panel) => {
            panel.hidden = panel.dataset.tabPanel !== tabKey;
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.tab));
    });

    root.querySelector('[data-tabs]')?.addEventListener('keydown', (event) => {
        const currentIndex = tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true');
        if (currentIndex < 0) {
            return;
        }

        let nextIndex = null;

        if (event.key === 'ArrowRight') {
            nextIndex = (currentIndex + 1) % tabs.length;
        } else if (event.key === 'ArrowLeft') {
            nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
            nextIndex = 0;
        } else if (event.key === 'End') {
            nextIndex = tabs.length - 1;
        }

        if (nextIndex === null) {
            return;
        }

        event.preventDefault();
        activateTab(tabs[nextIndex].dataset.tab);
        tabs[nextIndex].focus();
    });

    activateTab('basic');

    return { activateTab };
}

function statusBadge(isActive) {
    return isActive
        ? '<span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-800">啟用</span>'
        : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">停用</span>';
}

function vendorTags(vendors) {
    if (!vendors?.length) {
        return '<span class="text-slate-500">—</span>';
    }

    return `
        <ul class="flex flex-wrap gap-2">
            ${vendors
                .map(
                    (vendor) => `
                        <li class="inline-flex max-w-full items-center rounded-md border border-teal-200 bg-teal-50 px-2.5 py-1 text-sm font-medium text-teal-900">
                            <span class="truncate" title="${escapeHtml(vendor.code || vendor.name)}">${escapeHtml(vendor.name)}</span>
                        </li>
                    `,
                )
                .join('')}
        </ul>
    `;
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

function formatMoney(value) {
    if (value === null || value === undefined || value === '') {
        return '<span class="text-slate-500">—</span>';
    }

    const number = Number(value);
    if (!Number.isFinite(number)) {
        return '<span class="text-slate-500">—</span>';
    }

    return escapeHtml(
        number.toLocaleString('zh-TW', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }),
    );
}

function initProductShowPage(root) {
    const productId = root.dataset.productId;
    const basicDetail = root.querySelector('[data-product-detail-basic]');
    const priceDetail = root.querySelector('[data-product-detail-price]');
    const alertBox = root.querySelector('[data-alert]');

    initTabs(root);
    initPriceHistories(root, productId);

    const showAlert = (message, type = 'error') => {
        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
    };

    const renderProduct = (product) => {
        const unitLabel = product.unit
            ? product.unit.symbol
                ? `${product.unit.name}（${product.unit.symbol}）`
                : product.unit.name
            : '—';

        basicDetail.innerHTML = `
            <dl class="space-y-5">
                ${detailRow('系統編號', `<span class="font-medium">${escapeHtml(product.code || '—')}</span>`)}
                ${detailRow('商品分類', escapeHtml(product.category?.name || '—'))}
                ${detailRow('商品單位', escapeHtml(unitLabel))}
                ${detailRow('商品名稱', `<span class="font-medium">${escapeHtml(product.name || '—')}</span>`)}
                ${detailRow('供應商', vendorTags(product.vendors))}
                ${detailRow('備註', formatNotes(product.notes))}
                ${detailRow('狀態', statusBadge(Boolean(product.is_active)))}
            </dl>
        `;

        priceDetail.innerHTML = `
            <div class="space-y-6">
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-700">預計進價</p>
                    ${
                        product.vendors?.length
                            ? `<dl class="space-y-4">
                                ${product.vendors
                                    .map(
                                        (vendor) => `
                                            <div class="grid gap-1 sm:grid-cols-[8rem_minmax(0,1fr)] sm:gap-4">
                                                <dt class="text-sm font-medium text-slate-500">${escapeHtml(vendor.name)}</dt>
                                                <dd class="text-sm text-slate-900">${formatMoney(vendor.estimated_purchase_price)}</dd>
                                            </div>
                                        `,
                                    )
                                    .join('')}
                            </dl>`
                            : '<p class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">尚未選擇供應商，因此沒有對應進價。</p>'
                    }
                </div>
                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-700">預計售價</p>
                    <p class="text-sm text-slate-900">${formatMoney(product.estimated_selling_price)}</p>
                </div>
            </div>
        `;
    };

    const loadProduct = async () => {
        basicDetail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';
        priceDetail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';

        try {
            const payload = await api(`/api/products/${productId}`);
            renderProduct(payload.data ?? payload);
        } catch (error) {
            basicDetail.innerHTML =
                '<p class="text-sm text-red-600">載入失敗，請稍後再試。</p>';
            priceDetail.innerHTML =
                '<p class="text-sm text-red-600">載入失敗，請稍後再試。</p>';
            showAlert(error.message || '載入商品明細失敗', 'error');
        }
    };

    loadProduct();
}

const page = document.querySelector('[data-product-show-page]');
if (page) {
    initProductShowPage(page);
}
