<x-layouts.app active="warehouse-types">
    <x-slot:title>新增倉庫類型</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-warehouse-type-create-page
        data-index-url="{{ route('warehouse-types.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">新增倉庫類型</h2>
                <p class="mt-1 text-sm text-slate-500">填寫類型名稱；儲存後將自動產生系統編號。</p>
            </div>
            <a
                href="{{ route('warehouse-types.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回列表
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
            <form data-warehouse-type-form class="space-y-4">
                <div>
                    <label for="warehouse-type-name" class="mb-1.5 block text-sm font-medium text-slate-700">類型名稱</label>
                    <input
                        id="warehouse-type-name"
                        name="name"
                        type="text"
                        required
                        autofocus
                        data-field="name"
                        value="{{ old('name') }}"
                        placeholder="例如：總倉、門市倉、退貨倉"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="name"></p>
                </div>

                <div>
                    <label for="warehouse-type-notes" class="mb-1.5 block text-sm font-medium text-slate-700">
                        備註
                        <span class="font-normal text-slate-400">（選填）</span>
                    </label>
                    <textarea
                        id="warehouse-type-notes"
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
                        啟用此類型
                    </label>
                    <p class="mt-1 hidden text-sm text-red-600" data-error="is_active"></p>
                </div>

                <p class="hidden text-sm text-red-600" data-error="form"></p>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('warehouse-types.index') }}"
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
        @vite('resources/js/warehouse-types-create.js')
    </x-slot:scripts>
</x-layouts.app>
