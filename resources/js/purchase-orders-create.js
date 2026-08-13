import { initPurchaseOrderForm } from './components/purchase-order-form';

const page = document.querySelector('[data-purchase-order-create-page]');
if (page) {
    initPurchaseOrderForm(page, {
        mode: 'create',
        indexUrl: page.dataset.indexUrl,
    });
}
