<x-layouts.app active="users">
    <x-slot:title>帳號管理</x-slot:title>

    <div class="mx-auto max-w-6xl space-y-6" data-users-page data-current-user-id="{{ auth()->id() }}">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">帳號管理</h2>
                <p class="mt-1 text-sm text-slate-500">新增、修改與刪除系統登入帳號。</p>
            </div>
            <button
                type="button"
                data-action="create"
                class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
            >
                新增帳號
            </button>
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
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium sm:px-5">姓名</th>
                            <th class="px-4 py-3 font-medium sm:px-5">帳號</th>
                            <th class="px-4 py-3 font-medium sm:px-5">電子郵件</th>
                            <th class="px-4 py-3 font-medium sm:px-5">建立時間</th>
                            <th class="px-4 py-3 font-medium sm:px-5 text-right">操作</th>
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
                <nav
                    class="inline-flex overflow-hidden rounded-md border border-slate-300 shadow-sm"
                    data-pagination-controls
                    aria-label="分頁"
                ></nav>
            </div>
        </section>

        <div
            data-alert
            hidden
            class="fixed bottom-4 right-4 z-40 max-w-sm rounded-lg border px-4 py-3 text-sm shadow-lg"
            role="status"
        ></div>

        <div data-modal hidden class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div data-modal-backdrop class="absolute inset-0 bg-slate-900/40"></div>
            <div class="relative w-full max-w-lg rounded-xl border border-slate-200 bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900" data-modal-title>新增帳號</h3>
                        <p class="mt-1 text-sm text-slate-500" data-modal-subtitle>填寫帳號基本資料。</p>
                    </div>
                    <button
                        type="button"
                        data-modal-close
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="關閉"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form data-user-form class="mt-5 space-y-4">
                    <input type="hidden" name="id" data-field="id" value="">

                    <div>
                        <label for="user-name" class="mb-1.5 block text-sm font-medium text-slate-700">姓名</label>
                        <input
                            id="user-name"
                            name="name"
                            type="text"
                            required
                            data-field="name"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="name"></p>
                    </div>

                    <div>
                        <label for="user-username" class="mb-1.5 block text-sm font-medium text-slate-700">帳號</label>
                        <input
                            id="user-username"
                            name="username"
                            type="text"
                            required
                            autocomplete="off"
                            data-field="username"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="username"></p>
                    </div>

                    <div>
                        <label for="user-email" class="mb-1.5 block text-sm font-medium text-slate-700">電子郵件</label>
                        <input
                            id="user-email"
                            name="email"
                            type="email"
                            required
                            data-field="email"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="email"></p>
                    </div>

                    <div>
                        <label for="user-password" class="mb-1.5 block text-sm font-medium text-slate-700">
                            密碼
                            <span class="font-normal text-slate-400" data-password-hint></span>
                        </label>
                        <input
                            id="user-password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            data-field="password"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                        <p class="mt-1 hidden text-sm text-red-600" data-error="password"></p>
                    </div>

                    <div>
                        <label for="user-password-confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">確認密碼</label>
                        <input
                            id="user-password-confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            data-field="password_confirmation"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                        >
                    </div>

                    <p class="hidden text-sm text-red-600" data-error="form"></p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            data-modal-close
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            取消
                        </button>
                        <button
                            type="submit"
                            data-submit
                            class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            儲存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/users.js')
    </x-slot:scripts>
</x-layouts.app>
