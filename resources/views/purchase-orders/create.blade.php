<x-layouts.app active="purchase-orders">
    <x-slot:title>新增採購單</x-slot:title>

    <div
        class="mx-auto max-w-5xl space-y-6"
        data-purchase-order-create-page
        data-index-url="{{ route('purchase-orders.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">新增採購單</h2>
                <p class="mt-1 text-sm text-slate-500">選擇供應商與進貨倉庫，並填寫採購明細；儲存後將自動產生採購單號。</p>
            </div>
            <a
                href="{{ route('purchase-orders.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        @include('purchase-orders._form')
    </div>

    <x-slot:scripts>
        @vite('resources/js/purchase-orders-create.js')
    </x-slot:scripts>
</x-layouts.app>
