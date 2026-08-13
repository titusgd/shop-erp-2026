const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

function parsePrice(value) {
    const trimmed = String(value ?? '').trim();
    if (trimmed === '') {
        return null;
    }

    const number = Number(trimmed);
    return Number.isFinite(number) ? number : trimmed;
}

function formatInputPrice(value) {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const number = Number(value);
    if (!Number.isFinite(number)) {
        return String(value);
    }

    return number.toFixed(2);
}

/**
 * @param {HTMLElement} container
 */
export function initVendorPurchasePriceFields(container) {
    /** @type {Map<number, string>} */
    const savedPrices = new Map();

    const captureCurrentValues = () => {
        container.querySelectorAll('[data-vendor-purchase-price]').forEach((input) => {
            savedPrices.set(Number(input.dataset.vendorId), input.value);
        });
    };

    const setInitialPrices = (prices = {}) => {
        Object.entries(prices).forEach(([vendorId, price]) => {
            if (price === null || price === undefined || price === '') {
                return;
            }
            savedPrices.set(Number(vendorId), formatInputPrice(price));
        });
    };

    const render = (vendors = []) => {
        captureCurrentValues();

        if (!vendors.length) {
            container.innerHTML = `
                <p class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    請先在「基本資料」選擇供應商，系統會依供應商數量產生對應的進價欄位。
                </p>
            `;
            return;
        }

        container.innerHTML = `
            <div class="space-y-4">
                ${vendors
                    .map((vendor) => {
                        const vendorId = Number(vendor.id);
                        const value = savedPrices.has(vendorId) ? savedPrices.get(vendorId) : '';
                        const code = vendor.code ? `（${escapeHtml(vendor.code)}）` : '';

                        return `
                            <div>
                                <label
                                    for="vendor-purchase-price-${vendorId}"
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    ${escapeHtml(vendor.name)}
                                    <span class="font-normal text-slate-400">${code}</span>
                                </label>
                                <input
                                    id="vendor-purchase-price-${vendorId}"
                                    name="vendor_purchase_prices[${vendorId}]"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    data-vendor-purchase-price
                                    data-vendor-id="${vendorId}"
                                    data-field="vendor_purchase_prices.${vendorId}"
                                    value="${escapeHtml(value)}"
                                    placeholder="0.00"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="vendor_purchase_prices.${vendorId}"></p>
                            </div>
                        `;
                    })
                    .join('')}
            </div>
        `;
    };

    const collect = () => {
        const prices = {};

        container.querySelectorAll('[data-vendor-purchase-price]').forEach((input) => {
            prices[Number(input.dataset.vendorId)] = parsePrice(input.value);
        });

        return prices;
    };

    return {
        setInitialPrices,
        render,
        collect,
    };
}

export { parsePrice };
