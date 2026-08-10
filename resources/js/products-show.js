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

function initProductShowPage(root) {
    const productId = root.dataset.productId;
    const detail = root.querySelector('[data-product-detail]');
    const alertBox = root.querySelector('[data-alert]');

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

        detail.innerHTML = `
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
    };

    const loadProduct = async () => {
        detail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';

        try {
            const payload = await api(`/api/products/${productId}`);
            renderProduct(payload.data ?? payload);
        } catch (error) {
            detail.innerHTML =
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
