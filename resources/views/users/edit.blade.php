<x-layouts.app active="users">
    <x-slot:title>編輯帳號</x-slot:title>

    <div
        class="mx-auto max-w-2xl space-y-6"
        data-user-edit-page
        data-user-id="{{ $user->id }}"
        data-index-url="{{ route('users.index') }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">編輯帳號</h2>
                <p class="mt-1 text-sm text-slate-500">更新帳號資料；密碼留空表示不變更。</p>
            </div>
            <a
                href="{{ route('users.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                返回列表
            </a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form data-user-form class="space-y-4">
                <div>
                    <label for="user-name" class="mb-1.5 block text-sm font-medium text-slate-700">姓名</label>
                    <input
                        id="user-name"
                        name="name"
                        type="text"
                        required
                        data-field="name"
                        value="{{ old('name', $user->name) }}"
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
                        value="{{ old('username', $user->username) }}"
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
                        value="{{ old('email', $user->email) }}"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    >
                    <p class="mt-1 hidden text-sm text-red-600" data-error="email"></p>
                </div>

                <div>
                    <label for="user-password" class="mb-1.5 block text-sm font-medium text-slate-700">
                        密碼
                        <span class="font-normal text-slate-400">（選填）</span>
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
                    <a
                        href="{{ route('users.index') }}"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        取消
                    </a>
                    <button
                        type="submit"
                        data-submit
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        儲存
                    </button>
                </div>
            </form>
        </section>

        <div
            data-alert
            hidden
            class="fixed bottom-4 right-4 z-40 max-w-sm rounded-lg border px-4 py-3 text-sm shadow-lg"
            role="status"
        ></div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/users-edit.js')
    </x-slot:scripts>
</x-layouts.app>
