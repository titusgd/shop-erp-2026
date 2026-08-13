@props([
    'product',
])

<div
    data-price-histories-modal
    hidden
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="product-price-histories-modal-title"
    aria-hidden="true"
>
    <div data-price-histories-modal-backdrop class="absolute inset-0 bg-slate-900/40"></div>
    <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <h3 id="product-price-histories-modal-title" class="text-lg font-semibold text-slate-900" data-price-histories-modal-title>
                    價格設定歷史
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    商品「{{ $product->name }}」
                    @if ($product->code)
                        <span class="text-slate-400">（{{ $product->code }}）</span>
                    @endif
                </p>
            </div>
            <button
                type="button"
                data-price-histories-modal-close
                class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                aria-label="關閉"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto">
        <div class="space-y-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">區間範圍</p>
                <div class="flex flex-wrap gap-2" data-price-histories-presets>
                    <button
                        type="button"
                        data-range-preset="all"
                        class="rounded-lg border border-teal-700 bg-teal-50 px-3 py-1.5 text-sm font-medium text-teal-800"
                    >
                        全部
                    </button>
                    <button
                        type="button"
                        data-range-preset="30"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        近 30 天
                    </button>
                    <button
                        type="button"
                        data-range-preset="90"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        近 90 天
                    </button>
                    <button
                        type="button"
                        data-range-preset="365"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        近 1 年
                    </button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="product-price-histories-from" class="mb-1.5 block text-sm font-medium text-slate-700">起始日期</label>
                    <input
                        id="product-price-histories-from"
                        type="date"
                        data-price-histories-from
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                </div>
                <div>
                    <label for="product-price-histories-to" class="mb-1.5 block text-sm font-medium text-slate-700">結束日期</label>
                    <input
                        id="product-price-histories-to"
                        type="date"
                        data-price-histories-to
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-teal-100 bg-teal-50 px-4 py-3">
                    <p class="text-xs font-medium tracking-wide text-teal-800">歷史高價</p>
                    <p class="mt-1 text-lg font-semibold text-teal-900" data-price-histories-high>—</p>
                    <p class="mt-0.5 text-xs text-teal-700" data-price-histories-high-at></p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3">
                    <p class="text-xs font-medium tracking-wide text-amber-800">歷史低價</p>
                    <p class="mt-1 text-lg font-semibold text-amber-900" data-price-histories-low>—</p>
                    <p class="mt-0.5 text-xs text-amber-700" data-price-histories-low-at></p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-lg border border-slate-200 bg-white" data-price-histories-chart-wrap>
                <div class="overflow-x-auto">
                    <svg
                        data-price-histories-chart
                        class="h-48 w-full min-w-[20rem] sm:h-52"
                        viewBox="0 0 640 220"
                        preserveAspectRatio="xMidYMid meet"
                        role="img"
                        aria-label="價格折線圖"
                    ></svg>
                </div>
                <div
                    data-price-histories-chart-legend
                    hidden
                    class="flex flex-wrap gap-x-2 gap-y-1.5 border-t border-slate-100 px-3 py-2"
                    role="group"
                    aria-label="選擇要顯示的供應商"
                ></div>
                <div
                    data-price-histories-chart-tooltip
                    hidden
                    class="pointer-events-none absolute z-10 whitespace-nowrap rounded-md border border-slate-200 bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg"
                ></div>
            </div>
        </div>

        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-2.5 sm:px-6">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">歷史列表</p>
            <p class="text-sm text-slate-500" data-price-histories-modal-meta>載入中…</p>
        </div>

        <ul class="divide-y divide-slate-100" data-price-histories-modal-list>
            <li class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">載入中…</li>
        </ul>

        <div
            class="flex flex-col gap-3 border-t border-slate-200 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6"
            data-price-histories-pagination
            hidden
        >
            <p class="text-sm text-slate-600" data-price-histories-pagination-summary></p>
            <div class="w-full overflow-x-auto sm:w-auto">
                <nav
                    class="inline-flex max-w-full overflow-hidden rounded-md border border-slate-300 shadow-sm"
                    data-price-histories-pagination-controls
                    aria-label="歷史列表分頁"
                ></nav>
            </div>
        </div>
        </div>

        <div class="flex justify-end border-t border-slate-200 px-5 py-3 sm:px-6">
            <button
                type="button"
                data-price-histories-modal-close
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                關閉
            </button>
        </div>
    </div>
</div>
