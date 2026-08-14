import { api, initPurchaseRequisitionForm } from './components/purchase-requisition-form';

async function boot() {
    const page = document.querySelector('[data-purchase-requisition-edit-page]');
    if (!page) {
        return;
    }

    const requisitionId = page.dataset.purchaseRequisitionId;

    try {
        const payload = await api(`/api/purchase-requisitions/${requisitionId}`);
        const requisition = payload.data ?? payload;

        initPurchaseRequisitionForm(page, {
            mode: 'edit',
            requisitionId,
            indexUrl: page.dataset.indexUrl,
            initial: {
                requester_id: requisition.requester_id,
                warehouse_id: requisition.warehouse_id,
                items: (requisition.items ?? []).map((item) => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    notes: item.notes,
                    product: item.product,
                })),
            },
        });
    } catch {
        initPurchaseRequisitionForm(page, {
            mode: 'edit',
            requisitionId,
            indexUrl: page.dataset.indexUrl,
        });

        const alertBox = page.querySelector('[data-alert]');
        if (alertBox) {
            alertBox.hidden = false;
            alertBox.textContent = '載入請購單資料失敗';
            alertBox.className =
                'fixed inset-x-4 bottom-4 z-40 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm';
        }
    }
}

boot();
