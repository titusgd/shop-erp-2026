import { api, initPurchaseOrderForm } from './components/purchase-order-form';

async function boot() {
    const page = document.querySelector('[data-purchase-order-edit-page]');
    if (!page) {
        return;
    }

    const orderId = page.dataset.purchaseOrderId;

    try {
        const payload = await api(`/api/purchase-orders/${orderId}`);
        const order = payload.data ?? payload;

        initPurchaseOrderForm(page, {
            mode: 'edit',
            orderId,
            indexUrl: page.dataset.indexUrl,
            initial: {
                vendor_id: order.vendor_id,
                warehouse_id: order.warehouse_id,
                items: (order.items ?? []).map((item) => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                })),
            },
        });
    } catch {
        initPurchaseOrderForm(page, {
            mode: 'edit',
            orderId,
            indexUrl: page.dataset.indexUrl,
        });

        const alertBox = page.querySelector('[data-alert]');
        if (alertBox) {
            alertBox.hidden = false;
            alertBox.textContent = '載入採購單資料失敗';
            alertBox.className =
                'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
        }
    }
}

boot();
