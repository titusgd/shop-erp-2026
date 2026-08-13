<x-layouts.app active="products">
    <x-slot:title>檢視商品</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-product-show-page
        data-product-id="{{ $product->id }}"
        data-index-url="{{ route('products.index') }}"
        data-edit-url="{{ route('products.edit', $product) }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">檢視商品</h2>
                <p class="mt-1 text-sm text-slate-500">查看商品主檔明細。</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    返回列表
                </a>
                <a
                    href="{{ route('products.edit', $product) }}"
                    data-edit-link
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 sm:w-auto"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                    </svg>
                    編輯
                </a>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 sm:px-8" data-tabs>
                <nav class="-mb-px flex gap-1 overflow-x-auto" role="tablist" aria-label="商品資料分頁">
                    <button
                        type="button"
                        role="tab"
                        id="product-tab-basic"
                        data-tab="basic"
                        aria-controls="product-panel-basic"
                        aria-selected="true"
                        class="whitespace-nowrap border-b-2 border-teal-700 px-3 py-3 text-sm font-medium text-teal-800 transition hover:text-teal-900"
                    >
                        基本資料
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="product-tab-price"
                        data-tab="price"
                        aria-controls="product-panel-price"
                        aria-selected="false"
                        class="whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                    >
                        價格
                    </button>
                </nav>
            </div>

            <div class="space-y-4 p-4 sm:p-8">
                <div
                    id="product-panel-basic"
                    role="tabpanel"
                    aria-labelledby="product-tab-basic"
                    data-tab-panel="basic"
                >
                    <div data-product-detail-basic class="space-y-5">
                        <p class="text-sm text-slate-500">載入中…</p>
                    </div>
                </div>

                <div
                    id="product-panel-price"
                    role="tabpanel"
                    aria-labelledby="product-tab-price"
                    data-tab-panel="price"
                    hidden
                >
                    <div class="space-y-6">
                        <div data-product-detail-price>
                            <p class="text-sm text-slate-500">載入中…</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">價格設定歷史</h3>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                <button
                                    type="button"
                                    data-open-price-histories="estimated_purchase_price"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    進價
                                </button>
                                <button
                                    type="button"
                                    data-open-price-histories="estimated_selling_price"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    售價
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div
            data-alert
            hidden
            class="fixed inset-x-4 bottom-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>

        <x-product-price-histories-modal :product="$product" />
    </div>

    <x-slot:scripts>
        @vite('resources/js/products-show.js')
    </x-slot:scripts>
</x-layouts.app>
