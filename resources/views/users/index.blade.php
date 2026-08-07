<x-layouts.app active="users">
    <x-slot:title>帳號管理</x-slot:title>

    <div class="mx-auto max-w-6xl space-y-6" data-users-page data-current-user-id="{{ auth()->id() }}">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">帳號管理</h2>
                <p class="mt-1 text-sm text-slate-500">新增、修改與刪除系統登入帳號。</p>
            </div>
            <a
                href="{{ route('users.create') }}"
                aria-label="新增帳號"
                title="新增帳號"
                class="group inline-flex w-full items-center justify-center gap-2 overflow-hidden rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 sm:w-auto sm:px-3 sm:hover:px-4 sm:focus-visible:px-4"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="whitespace-nowrap transition-all duration-200 sm:max-w-0 sm:overflow-hidden sm:opacity-0 sm:group-hover:max-w-[5rem] sm:group-hover:opacity-100 sm:group-focus-visible:max-w-[5rem] sm:group-focus-visible:opacity-100">
                    新增帳號
                </span>
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="relative w-full sm:max-w-xs">
                    <label for="user-search" class="sr-only">搜尋帳號</label>
                    <input
                        id="user-search"
                        type="search"
                        data-search
                        placeholder="搜尋姓名、帳號或電子郵件"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                </div>
                <p class="text-sm text-slate-500" data-meta>載入中…</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[40rem] w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">姓名</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">帳號</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">電子郵件</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5">建立時間</th>
                            <th class="whitespace-nowrap px-4 py-3 font-medium sm:px-5 text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" data-table-body>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 sm:px-5">載入中…</td>
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
    </div>

    <x-slot:scripts>
        @vite('resources/js/users.js')
    </x-slot:scripts>
</x-layouts.app>
