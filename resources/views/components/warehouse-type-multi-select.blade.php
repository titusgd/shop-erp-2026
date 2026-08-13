@props([
    'initialSelected' => [],
])

@php
    $initial = collect($initialSelected)->map(fn ($type) => [
        'id' => $type->id,
        'name' => $type->name,
        'code' => $type->code,
    ])->values();
@endphp

<div
    data-warehouse-type-multi-select
    data-initial-selected='@json($initial)'
>
    <label for="warehouse-type-search" class="mb-1.5 block text-sm font-medium text-slate-700">
        倉庫類型
        <span class="font-normal text-slate-400">（選填，可多選）</span>
    </label>

    <div class="relative" data-multi-select-dropdown>
        <div class="relative">
            <input
                id="warehouse-type-search"
                type="search"
                autocomplete="off"
                data-multi-select-search
                placeholder="輸入關鍵字搜尋並選擇"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                aria-controls="warehouse-type-options"
                aria-expanded="false"
                aria-autocomplete="list"
                role="combobox"
            >
            <svg
                class="pointer-events-none absolute inset-y-0 right-3 my-auto h-4 w-4 text-slate-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </div>

        <ul
            id="warehouse-type-options"
            data-multi-select-options
            role="listbox"
            hidden
            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
        ></ul>
    </div>

    <p class="mt-1 hidden text-sm text-red-600" data-error="warehouse_type_ids"></p>

    <div class="mt-3" data-multi-select-selected-wrap hidden>
        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">已選類型</p>
        <ul class="flex flex-wrap gap-2" data-multi-select-selected role="list"></ul>
    </div>
</div>
