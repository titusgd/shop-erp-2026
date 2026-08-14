<x-layouts.app active="purchase-requisitions">
    <x-slot:title>編輯請購單</x-slot:title>

    <div
        class="mx-auto max-w-5xl space-y-6"
        data-purchase-requisition-edit-page
        data-purchase-requisition-id="{{ $purchaseRequisition->id }}"
        data-index-url="{{ route('purchase-requisitions.index') }}"
        data-is-cancelled="{{ $purchaseRequisition->status === 'cancelled' ? '1' : '0' }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">編輯請購單</h2>
                <p class="mt-1 text-sm text-slate-500">更新請購人、進貨倉庫、狀態與請購明細。</p>
            </div>
            <a
                href="{{ route('purchase-requisitions.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        @include('purchase-requisitions._form', ['purchaseRequisition' => $purchaseRequisition])
    </div>

    <x-slot:scripts>
        @vite('resources/js/purchase-requisitions-edit.js')
    </x-slot:scripts>
</x-layouts.app>
