<x-layouts.app active="dashboard">
    <x-slot:title>儀表板</x-slot:title>

    <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-teal-700">歡迎回來</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                {{ $user->name }}
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                你已成功登入 {{ config('app.name') }}。後續模組可從此處延伸。
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">帳號</p>
                <p class="mt-2 truncate text-base font-semibold text-slate-900">{{ $user->username }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">登入狀態</p>
                <p class="mt-2 text-base font-semibold text-teal-700">已驗證工作階段</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                <p class="text-sm font-medium text-slate-500">系統</p>
                <p class="mt-2 text-base font-semibold text-slate-900">{{ config('app.name') }}</p>
            </div>
        </section>
    </div>
</x-layouts.app>
