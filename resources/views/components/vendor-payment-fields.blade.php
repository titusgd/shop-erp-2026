@props([
    'vendor' => null,
])

@php
    $currentMethod = old('settlement_method', $vendor?->settlement_method);
@endphp

<div class="space-y-4">
    <div>
        <label for="vendor-remittance-bank" class="mb-1.5 block text-sm font-medium text-slate-700">
            匯款銀行
            <span class="font-normal text-slate-400">（選填）</span>
        </label>
        <input
            id="vendor-remittance-bank"
            name="remittance_bank"
            type="text"
            data-field="remittance_bank"
            value="{{ old('remittance_bank', $vendor?->remittance_bank) }}"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
        >
        <p class="mt-1 hidden text-sm text-red-600" data-error="remittance_bank"></p>
    </div>

    <div>
        <label for="vendor-remittance-account" class="mb-1.5 block text-sm font-medium text-slate-700">
            匯款帳號
            <span class="font-normal text-slate-400">（選填）</span>
        </label>
        <input
            id="vendor-remittance-account"
            name="remittance_account"
            type="text"
            inputmode="numeric"
            maxlength="50"
            data-field="remittance_account"
            value="{{ old('remittance_account', $vendor?->remittance_account) }}"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
        >
        <p class="mt-1 hidden text-sm text-red-600" data-error="remittance_account"></p>
    </div>

    <div>
        <p id="vendor-settlement-method-label" class="mb-1.5 text-sm font-medium text-slate-700">
            結帳方式
            <span class="font-normal text-slate-400">（選填）</span>
        </p>
        <div
            class="grid gap-2 sm:grid-cols-2"
            role="radiogroup"
            aria-labelledby="vendor-settlement-method-label"
        >
            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <input
                    type="radio"
                    name="settlement_method"
                    value=""
                    data-field="settlement_method"
                    @checked($currentMethod === null || $currentMethod === '')
                    class="h-4 w-4 border-slate-300 text-teal-700 focus:ring-teal-500"
                >
                未指定
            </label>
            @foreach (\App\Models\Vendor::settlementMethods() as $value => $label)
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <input
                        type="radio"
                        name="settlement_method"
                        value="{{ $value }}"
                        data-field="settlement_method"
                        @checked($currentMethod === $value)
                        class="h-4 w-4 border-slate-300 text-teal-700 focus:ring-teal-500"
                    >
                    {{ $label }}
                </label>
            @endforeach
        </div>
        <p class="mt-1 hidden text-sm text-red-600" data-error="settlement_method"></p>
    </div>
</div>
