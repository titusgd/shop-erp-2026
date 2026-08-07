<x-layouts.app active="vendors">
    <x-slot:title>廠商管理</x-slot:title>

    <div class="mx-auto max-w-6xl space-y-6" data-vendors-page>
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">廠商管理</h2>
                <p class="mt-1 text-sm text-slate-500">新增、修改與刪除廠商資料。</p>
            </div>
            <a
                href="{{ route('vendors.create') }}"
                aria-label="新增廠商"
                title="新增廠商"
                class="group inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 sm:w-auto sm:px-3 sm:hover:px-4 sm:focus-visible:px-4"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="whitespace-nowrap transition-all duration-200 sm:max-w-0 sm:overflow-hidden sm:opacity-0 sm:group-hover:max-w-[5rem] sm:group-hover:opacity-100 sm:group-focus-visible:max-w-[5rem] sm:group-focus-visible:opacity-100">
                    新增廠商
                </span>
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="relative w-full sm:max-w-xs">
                    <label for="vendor-search" class="sr-only">搜尋廠商</label>
                    <input
                        id="vendor-search"
                        type="search"
                        data-search
                        placeholder="搜尋名稱、系統編號、統編、聯絡人或電話"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                </div>
                <p class="text-sm text-slate-500" data-meta>載入中…</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[48rem] w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">系統編號</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">廠商名稱</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">統一編號</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">聯絡人</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">電話</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">狀態</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5 text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" data-table-body>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 sm:px-5">載入中…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                data-pagination
                hidden
            >
                <p class="text-sm text-slate-600" data-pagination-summary></p>
                <div class="w-full overflow-x-auto sm:w-auto">
                    <nav
                        class="inline-flex max-w-full overflow-hidden rounded-md border border-slate-300 shadow-sm"
                        data-pagination-controls
                        aria-label="分頁"
                    ></nav>
                </div>
            </div>
        </section>

        <div
            data-alert
            hidden
            class="fixed inset-x-4 bottom-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>

        <div
            data-delete-dialog
            hidden
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="vendor-delete-dialog-title"
            aria-hidden="true"
        >
            <div data-delete-dialog-backdrop class="absolute inset-0 bg-slate-900/40"></div>
            <div class="relative w-full max-w-md rounded-xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
                <h3 id="vendor-delete-dialog-title" class="text-lg font-semibold text-slate-900">確認刪除</h3>
                <p class="mt-2 text-sm text-slate-600" data-delete-dialog-message></p>
                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        data-delete-dialog-cancel
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        取消
                    </button>
                    <button
                        type="button"
                        data-delete-dialog-confirm
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        確認刪除
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/vendors.js')
    </x-slot:scripts>
</x-layouts.app>
