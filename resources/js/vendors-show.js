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

function initVendorShowPage(root) {
    const vendorId = root.dataset.vendorId;
    const detail = root.querySelector('[data-vendor-detail]');
    const alertBox = root.querySelector('[data-alert]');

    const showAlert = (message, type = 'error') => {
        alertBox.hidden = false;
        alertBox.textContent = message;
        alertBox.className =
            type === 'success'
                ? 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm'
                : 'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
    };

    const renderVendor = (vendor) => {
        detail.innerHTML = `
            <dl class="space-y-5">
                ${detailRow('系統編號', `<span class="font-medium">${escapeHtml(vendor.code || '—')}</span>`)}
                ${detailRow('廠商名稱', `<span class="font-medium">${escapeHtml(vendor.name || '—')}</span>`)}
                ${detailRow('統一編號', escapeHtml(vendor.tax_id || '—'))}
                ${detailRow('聯絡人', escapeHtml(vendor.contact_name || '—'))}
                ${detailRow('電話', escapeHtml(vendor.phone || '—'))}
                ${detailRow('電子郵件', escapeHtml(vendor.email || '—'))}
                ${detailRow('郵遞區號', escapeHtml(vendor.postal_code || '—'))}
                ${detailRow('縣市', escapeHtml(vendor.city?.name || '—'))}
                ${detailRow('區域', escapeHtml(vendor.district?.name || '—'))}
                ${detailRow('地址', escapeHtml(vendor.address || '—'))}
                ${detailRow('備註', formatNotes(vendor.notes))}
                ${detailRow('狀態', statusBadge(Boolean(vendor.is_active)))}
            </dl>
        `;
    };

    const loadVendor = async () => {
        detail.innerHTML = '<p class="text-sm text-slate-500">載入中…</p>';

        try {
            const payload = await api(`/api/vendors/${vendorId}`);
            renderVendor(payload.data ?? payload);
        } catch (error) {
            detail.innerHTML =
                '<p class="text-sm text-red-600">載入失敗，請稍後再試。</p>';
            showAlert(error.message || '載入廠商明細失敗', 'error');
        }
    };

    loadVendor();
}

const page = document.querySelector('[data-vendor-show-page]');
if (page) {
    initVendorShowPage(page);
}
