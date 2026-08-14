import { initPurchaseRequisitionForm } from './components/purchase-requisition-form';

const page = document.querySelector('[data-purchase-requisition-create-page]');
if (page) {
    initPurchaseRequisitionForm(page, {
        mode: 'create',
        indexUrl: page.dataset.indexUrl,
        currentUserId: page.dataset.currentUserId ? Number(page.dataset.currentUserId) : null,
    });
}
