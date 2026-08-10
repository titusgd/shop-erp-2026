<x-layouts.app active="vendors">
    <x-slot:title>新增廠商</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-vendor-create-page
        data-index-url="{{ route('vendors.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">新增廠商</h2>
                <p class="mt-1 text-sm text-slate-500">填寫廠商基本資料；儲存後將自動產生系統編號。</p>
            </div>
            <a
                href="{{ route('vendors.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
            <form data-vendor-form class="space-y-4">
                <div>
                    <label for="vendor-name" class="mb-1.5 block text-sm font-medium text-slate-700">廠商名稱</label>
                    <input
                        id="vendor-name"
                        name="name"
                        type="text"
                        required
                        autofocus
                        data-field="name"
                        value="{{ old('name') }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="name"></p>
                </div>

                <div>
                    <label for="vendor-tax-id" class="mb-1.5 block text-sm font-medium text-slate-700">
                        統一編號
                        <span class="font-normal text-slate-400">（選填，不可重複）</span>
                    </label>
                    <input
                        id="vendor-tax-id"
                        name="tax_id"
                        type="text"
                        inputmode="numeric"
                        maxlength="20"
                        data-field="tax_id"
                        value="{{ old('tax_id') }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="tax_id"></p>
                </div>

                <div>
                    <label for="vendor-contact-name" class="mb-1.5 block text-sm font-medium text-slate-700">
                        聯絡人
                        <span class="font-normal text-slate-400">（選填）</span>
                    </label>
                    <input
                        id="vendor-contact-name"
                        name="contact_name"
                        type="text"
                        data-field="contact_name"
                        value="{{ old('contact_name') }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="contact_name"></p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="vendor-phone" class="mb-1.5 block text-sm font-medium text-slate-700">
                            電話
                            <span class="font-normal text-slate-400">（選填）</span>
                        </label>
                        <input
                            id="vendor-phone"
                            name="phone"
                            type="text"
                            data-field="phone"
                            value="{{ old('phone') }}"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="phone"></p>
                    </div>

                    <div>
                        <label for="vendor-email" class="mb-1.5 block text-sm font-medium text-slate-700">
                            電子郵件
                            <span class="font-normal text-slate-400">（選填）</span>
                        </label>
                        <input
                            id="vendor-email"
                            name="email"
                            type="email"
                            data-field="email"
                            value="{{ old('email') }}"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="email"></p>
                    </div>
                </div>

                <x-address-location-fields id-prefix="vendor" />

                <div>
                    <label for="vendor-address" class="mb-1.5 block text-sm font-medium text-slate-700">
                        地址
                        <span class="font-normal text-slate-400">（選填）</span>
                    </label>
                    <input
                        id="vendor-address"
                        name="address"
                        type="text"
                        data-field="address"
                        value="{{ old('address') }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="address"></p>
                </div>

                <div>
                    <label for="vendor-notes" class="mb-1.5 block text-sm font-medium text-slate-700">
                        備註
                        <span class="font-normal text-slate-400">（選填）</span>
                    </label>
                    <textarea
                        id="vendor-notes"
                        name="notes"
                        rows="3"
                        data-field="notes"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >{{ old('notes') }}</textarea>
                    <p class="mt-1 hidden text-sm text-red-600" data-error="notes"></p>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input
                            type="checkbox"
                            name="is_active"
                            data-field="is_active"
                            value="1"
                            @checked(old('is_active', true))
                            class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                        >
                        啟用此廠商
                    </label>
                    <p class="mt-1 hidden text-sm text-red-600" data-error="is_active"></p>
                </div>

                <p class="hidden text-sm text-red-600" data-error="form"></p>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('vendors.index') }}"
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
            </form>
        </section>

        <div
            data-alert
            hidden
            class="fixed inset-x-4 bottom-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/vendors-create.js')
    </x-slot:scripts>
</x-layouts.app>
