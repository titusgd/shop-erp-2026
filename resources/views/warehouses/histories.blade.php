<x-layouts.app active="warehouses">
    <x-slot:title>倉庫修改歷程</x-slot:title>

    <div
        class="mx-auto max-w-4xl space-y-6"
        data-warehouse-histories-page
        data-warehouse-id="{{ $warehouse->id }}"
    >
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">修改歷程</h2>
                <p class="mt-1 text-sm text-slate-500">
                    倉庫「{{ $warehouse->name }}」
                    @if ($warehouse->code)
                        <span class="text-slate-400">（{{ $warehouse->code }}）</span>
                    @endif
                    的建立與修改紀錄，依時間由新到舊排序。
                </p>
            </div>
            <a
                href="{{ route('warehouses.edit', $warehouse) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                返回編輯
            </a>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
                <h3 class="text-sm font-semibold text-slate-900">歷程列表</h3>
                <p class="text-sm text-slate-500" data-histories-meta>載入中…</p>
            </div>

            <ul class="divide-y divide-slate-100" data-histories-list>
                <li class="px-4 py-8 text-center text-sm text-slate-500 sm:px-5">載入中…</li>
            </ul>
        </section>

        <div
            data-alert
            hidden
            class="fixed inset-x-4 bottom-4 z-40 rounded-lg border px-4 py-3 text-sm shadow-lg sm:inset-x-auto sm:right-4 sm:max-w-sm"
            role="status"
        ></div>
    </div>

    <x-slot:scripts>
        @vite('resources/js/warehouses-histories.js')
    </x-slot:scripts>
</x-layouts.app>
