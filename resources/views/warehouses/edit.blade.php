<x-layouts.app active="warehouses">
    <x-slot:title>編輯倉庫</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-warehouse-edit-page
        data-warehouse-id="{{ $warehouse->id }}"
        data-index-url="{{ route('warehouses.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">編輯倉庫</h2>
                <p class="mt-1 text-sm text-slate-500">更新倉庫資料。</p>
            </div>
            <a
                href="{{ route('warehouses.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <form data-warehouse-form>
                <div class="border-b border-slate-200 px-4 sm:px-8" data-tabs>
                    <nav class="-mb-px flex gap-1 overflow-x-auto" role="tablist" aria-label="倉庫資料分頁">
                        <button
                            type="button"
                            role="tab"
                            id="warehouse-tab-basic"
                            data-tab="basic"
                            aria-controls="warehouse-panel-basic"
                            aria-selected="true"
                            class="whitespace-nowrap border-b-2 border-teal-700 px-3 py-3 text-sm font-medium text-teal-800 transition hover:text-teal-900"
                        >
                            基本資料
                        </button>
                        <button
                            type="button"
                            role="tab"
                            id="warehouse-tab-contact"
                            data-tab="contact"
                            aria-controls="warehouse-panel-contact"
                            aria-selected="false"
                            class="whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        >
                            聯絡資訊
                        </button>
                        <button
                            type="button"
                            role="tab"
                            id="warehouse-tab-other"
                            data-tab="other"
                            aria-controls="warehouse-panel-other"
                            aria-selected="false"
                            class="whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        >
                            其他
                        </button>
                        <button
                            type="button"
                            role="tab"
                            id="warehouse-tab-system"
                            data-tab="system"
                            aria-controls="warehouse-panel-system"
                            aria-selected="false"
                            class="whitespace-nowrap border-b-2 border-transparent px-3 py-3 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        >
                            系統資訊
                        </button>
                    </nav>
                </div>

                <div class="space-y-4 p-4 sm:p-8">
                    <div
                        id="warehouse-panel-basic"
                        role="tabpanel"
                        aria-labelledby="warehouse-tab-basic"
                        data-tab-panel="basic"
                    >
                        <div class="space-y-4">
                            <div>
                                <label for="warehouse-code" class="mb-1.5 block text-sm font-medium text-slate-700">系統編號</label>
                                <input
                                    id="warehouse-code"
                                    type="text"
                                    value="{{ $warehouse->code }}"
                                    readonly
                                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                >
                            </div>

                            <div>
                                <label for="warehouse-name" class="mb-1.5 block text-sm font-medium text-slate-700">倉庫名稱</label>
                                <input
                                    id="warehouse-name"
                                    name="name"
                                    type="text"
                                    required
                                    data-field="name"
                                    value="{{ old('name', $warehouse->name) }}"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="name"></p>
                            </div>

                            <x-warehouse-type-multi-select :initial-selected="$warehouse->warehouseTypes" />

                            <div>
                                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        data-field="is_active"
                                        value="1"
                                        @checked(old('is_active', $warehouse->is_active))
                                        class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                                    >
                                    啟用此倉庫
                                </label>
                                <p class="mt-1 hidden text-sm text-red-600" data-error="is_active"></p>
                            </div>
                        </div>
                    </div>

                    <div
                        id="warehouse-panel-contact"
                        role="tabpanel"
                        aria-labelledby="warehouse-tab-contact"
                        data-tab-panel="contact"
                        hidden
                    >
                        <div class="space-y-4">
                            <div>
                                <label for="warehouse-contact-name" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    聯絡人
                                    <span class="font-normal text-slate-400">（選填）</span>
                                </label>
                                <input
                                    id="warehouse-contact-name"
                                    name="contact_name"
                                    type="text"
                                    data-field="contact_name"
                                    value="{{ old('contact_name', $warehouse->contact_name) }}"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="contact_name"></p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="warehouse-phone" class="mb-1.5 block text-sm font-medium text-slate-700">
                                        電話
                                        <span class="font-normal text-slate-400">（選填）</span>
                                    </label>
                                    <input
                                        id="warehouse-phone"
                                        name="phone"
                                        type="text"
                                        data-field="phone"
                                        value="{{ old('phone', $warehouse->phone) }}"
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                    >
                                    <p class="mt-1 hidden text-sm text-red-600" data-error="phone"></p>
                                </div>

                                <div>
                                    <label for="warehouse-email" class="mb-1.5 block text-sm font-medium text-slate-700">
                                        電子郵件
                                        <span class="font-normal text-slate-400">（選填）</span>
                                    </label>
                                    <input
                                        id="warehouse-email"
                                        name="email"
                                        type="email"
                                        data-field="email"
                                        value="{{ old('email', $warehouse->email) }}"
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                    >
                                    <p class="mt-1 hidden text-sm text-red-600" data-error="email"></p>
                                </div>
                            </div>

                            <x-address-location-fields
                                id-prefix="warehouse"
                                :postal-code="$warehouse->postal_code"
                                :city-id="$warehouse->city_id"
                                :city-name="$warehouse->city?->name"
                                :district-id="$warehouse->district_id"
                                :district-name="$warehouse->district?->name"
                            />

                            <div>
                                <label for="warehouse-address" class="mb-1.5 block text-sm font-medium text-slate-700">
                                    地址
                                    <span class="font-normal text-slate-400">（選填）</span>
                                </label>
                                <input
                                    id="warehouse-address"
                                    name="address"
                                    type="text"
                                    data-field="address"
                                    value="{{ old('address', $warehouse->address) }}"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                >
                                <p class="mt-1 hidden text-sm text-red-600" data-error="address"></p>
                            </div>
                        </div>
                    </div>

                    <div
                        id="warehouse-panel-other"
                        role="tabpanel"
                        aria-labelledby="warehouse-tab-other"
                        data-tab-panel="other"
                        hidden
                    >
                        <div>
                            <label for="warehouse-notes" class="mb-1.5 block text-sm font-medium text-slate-700">
                                備註
                                <span class="font-normal text-slate-400">（選填）</span>
                            </label>
                            <textarea
                                id="warehouse-notes"
                                name="notes"
                                rows="5"
                                data-field="notes"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                            >{{ old('notes', $warehouse->notes) }}</textarea>
                            <p class="mt-1 hidden text-sm text-red-600" data-error="notes"></p>
                        </div>
                    </div>

                    <div
                        id="warehouse-panel-system"
                        role="tabpanel"
                        aria-labelledby="warehouse-tab-system"
                        data-tab-panel="system"
                        hidden
                    >
                        <div class="space-y-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">建立人員</label>
                                    <input
                                        type="text"
                                        value="{{ $warehouse->creator?->name ?? '—' }}"
                                        readonly
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">建立時間</label>
                                    <input
                                        type="text"
                                        value="{{ $warehouse->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') ?? '—' }}"
                                        readonly
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">修改人員</label>
                                    <input
                                        type="text"
                                        value="{{ $warehouse->updater?->name ?? '—' }}"
                                        readonly
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">修改日期</label>
                                    <input
                                        type="text"
                                        value="{{ $warehouse->updated_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') ?? '—' }}"
                                        readonly
                                        class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none"
                                    >
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">修改歷程</h3>
                                <p class="mt-1 text-sm text-slate-500">依時間由新到舊檢視此倉庫的建立與修改紀錄。</p>

                                <button
                                    type="button"
                                    data-open-histories-modal
                                    class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    檢視修改歷程
                                </button>
                            </div>
                        </div>
                    </div>

                    <p class="hidden text-sm text-red-600" data-error="form"></p>

                    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('warehouses.index') }}"
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
            class="fixed inset-x-4 bottom-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>

        <div
            data-histories-modal
            hidden
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="warehouse-histories-modal-title"
            aria-hidden="true"
        >
            <div data-histories-modal-backdrop class="absolute inset-0 bg-slate-900/40"></div>
            <div class="relative flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="min-w-0">
                        <h3 id="warehouse-histories-modal-title" class="text-lg font-semibold text-slate-900">修改歷程</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            倉庫「{{ $warehouse->name }}」
                            @if ($warehouse->code)
                                <span class="text-slate-400">（{{ $warehouse->code }}）</span>
                            @endif
                        </p>
                    </div>
                    <button
                        type="button"
                        data-histories-modal-close
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                        aria-label="關閉"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-2.5 sm:px-6">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">歷程列表</p>
                    <p class="text-sm text-slate-500" data-histories-modal-meta>載入中…</p>
                </div>

                <ul class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto" data-histories-modal-list>
                    <li class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">載入中…</li>
                </ul>

                <div class="flex justify-end border-t border-slate-200 px-5 py-3 sm:px-6">
                    <button
                        type="button"
                        data-histories-modal-close
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        關閉
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/warehouses-edit.js')
    </x-slot:scripts>
</x-layouts.app>
