<x-layouts.app active="products">
    <x-slot:title>編輯商品</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-product-edit-page
        data-product-id="{{ $product->id }}"
        data-product-category-id="{{ $product->product_category_id }}"
        data-product-unit-id="{{ $product->product_unit_id }}"
        data-vendor-purchase-prices='@json($product->vendors->mapWithKeys(fn ($vendor) => [$vendor->id => $vendor->pivot?->estimated_purchase_price]))'
        data-index-url="{{ route('products.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">編輯商品</h2>
                <p class="mt-1 text-sm text-slate-500">更新商品基本資料、各供應商預計進價與預計售價。</p>
            </div>
            <a
                href="{{ route('products.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <form data-product-form>
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
                        <div class="space-y-4">
                            <div>
                                <label for="product-code" class="mb-1.5 block text-sm font-medium text-slate-700">系統編號</label>
                                <input
                                    id="product-code"
                                    type="text"
                                    value="{{ $product->code }}"
                                    readonly
                                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                >
                            </div>

                            <div>
                                <label for="product-category" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    商品分類
                                    <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="product-category"
                                    name="product_category_id"
                                    required
                                    data-field="product_category_id"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                    <option value="">請選擇商品分類</option>
                                </select>
                                <p class="mt-1 hidden text-sm text-red-600" data-error="product_category_id"></p>
                            </div>

                            <div>
                                <label for="product-unit" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    商品單位
                                    <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="product-unit"
                                    name="product_unit_id"
                                    required
                                    data-field="product_unit_id"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                    <option value="">請選擇商品單位</option>
                                </select>
                                <p class="mt-1 hidden text-sm text-red-600" data-error="product_unit_id"></p>
                            </div>

                            <div>
                                <label for="product-name" class="mb-1.5 block text-sm font-medium text-slate-700">商品名稱</label>
                                <input
                                    id="product-name"
                                    name="name"
                                    type="text"
                                    required
                                    data-field="name"
                                    value="{{ old('name', $product->name) }}"
                                    placeholder="例如：礦泉水 600ml"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="name"></p>
                            </div>

                            <div>
                                <x-vendor-multi-select :initial-selected="$product->vendors" />
                            </div>

                            <div>
                                <label for="product-notes" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    備註
                                    <span class="font-normal text-slate-400">（選填）</span>
                                </label>
                                <textarea
                                    id="product-notes"
                                    name="notes"
                                    rows="3"
                                    data-field="notes"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >{{ old('notes', $product->notes) }}</textarea>
                                <p class="mt-1 hidden text-sm text-red-600" data-error="notes"></p>
                            </div>

                            <div>
                                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        data-field="is_active"
                                        value="1"
                                        @checked(old('is_active', $product->is_active))
                                        class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                                    >
                                    啟用此商品
                                </label>
                                <p class="mt-1 hidden text-sm text-red-600" data-error="is_active"></p>
                            </div>
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
                            <div>
                                <p class="mb-1.5 text-sm font-medium text-slate-700">
                                    預計進價
                                    <span class="font-normal text-slate-400">（選填，依供應商分別設定）</span>
                                </p>
                                <div data-vendor-purchase-prices></div>
                            </div>

                            <div>
                                <label for="product-estimated-selling-price" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    預計售價
                                    <span class="font-normal text-slate-400">（選填）</span>
                                </label>
                                <input
                                    id="product-estimated-selling-price"
                                    name="estimated_selling_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    data-field="estimated_selling_price"
                                    value="{{ old('estimated_selling_price', $product->estimated_selling_price) }}"
                                    placeholder="0.00"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="estimated_selling_price"></p>
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

                    <p class="hidden text-sm text-red-600" data-error="form"></p>

                    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            取消
                        </a>
                        <button
                            type="submit"
                            data-submit
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            儲存
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <div
            data-alert
            hidden
            class="fixed inset-x-4 top-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>

        <x-product-price-histories-modal :product="$product" />
    </div>

    <x-slot:scripts>
        @vite('resources/js/products-edit.js')
    </x-slot:scripts>
</x-layouts.app>
