@props([
    'postalCode' => null,
    'cityId' => null,
    'cityName' => null,
    'districtId' => null,
    'districtName' => null,
    'idPrefix' => 'address',
])

@php
    $resolvedCityId = old('city_id', $cityId);
    $resolvedDistrictId = old('district_id', $districtId);
@endphp

<div
    data-address-location-fields
    data-city-id="{{ $resolvedCityId }}"
    data-city-name="{{ old('city_name', $cityName) }}"
    data-district-id="{{ $resolvedDistrictId }}"
    data-district-name="{{ old('district_name', $districtName) }}"
>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="{{ $idPrefix }}-postal-code" class="mb-1.5 block text-sm font-medium text-slate-700">
                郵遞區號
                <span class="font-normal text-slate-400">（選填）</span>
            </label>
            <input
                id="{{ $idPrefix }}-postal-code"
                name="postal_code"
                type="text"
                inputmode="numeric"
                autocomplete="postal-code"
                data-field="postal_code"
                value="{{ old('postal_code', $postalCode) }}"
                placeholder="例如：100"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
            >
            <p class="mt-1 hidden text-sm text-red-600" data-error="postal_code"></p>
        </div>

        <div
            data-city-searchable-select
            data-initial-id="{{ $resolvedCityId }}"
            data-initial-name="{{ old('city_name', $cityName) }}"
        >
            <label for="{{ $idPrefix }}-city-search" class="mb-1.5 block text-sm font-medium text-slate-700">
                縣市
                <span class="font-normal text-slate-400">（選填）</span>
            </label>

            <input type="hidden" name="city_id" data-field="city_id" data-searchable-select-value value="{{ $resolvedCityId }}">

            <div class="relative">
                <input
                    id="{{ $idPrefix }}-city-search"
                    type="text"
                    autocomplete="off"
                    data-searchable-select-search
                    placeholder="輸入關鍵字搜尋縣市"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                    aria-controls="{{ $idPrefix }}-city-options"
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

                <ul
                    id="{{ $idPrefix }}-city-options"
                    data-searchable-select-options
                    role="listbox"
                    hidden
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                ></ul>
            </div>

            <p class="mt-1 hidden text-sm text-red-600" data-error="city_id"></p>
        </div>

        <div
            data-district-searchable-select
            data-initial-id="{{ $resolvedDistrictId }}"
            data-initial-name="{{ old('district_name', $districtName) }}"
        >
            <label for="{{ $idPrefix }}-district-search" class="mb-1.5 block text-sm font-medium text-slate-700">
                區域
                <span class="font-normal text-slate-400">（選填）</span>
            </label>

            <input type="hidden" name="district_id" data-field="district_id" data-searchable-select-value value="{{ $resolvedDistrictId }}">

            <div class="relative">
                <input
                    id="{{ $idPrefix }}-district-search"
                    type="text"
                    autocomplete="off"
                    data-searchable-select-search
                    placeholder="請先選擇縣市"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pr-9 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
                    aria-controls="{{ $idPrefix }}-district-options"
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

                <ul
                    id="{{ $idPrefix }}-district-options"
                    data-searchable-select-options
                    role="listbox"
                    hidden
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                ></ul>
            </div>

            <p class="mt-1 hidden text-sm text-red-600" data-error="district_id"></p>
        </div>
    </div>
</div>
