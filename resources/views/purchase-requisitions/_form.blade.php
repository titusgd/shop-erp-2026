@php
    $isEdit = isset($purchaseRequisition);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-8">
    <form data-purchase-requisition-form class="space-y-6">
        @if ($isEdit)
            <div>
                <label for="purchase-requisition-code" class="mb-1.5 block text-sm font-medium text-slate-700">請購單號</label>
                <input
                    id="purchase-requisition-code"
                    type="text"
                    value="{{ $purchaseRequisition->code }}"
                    readonly
                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none sm:max-w-xs"
                >
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="purchase-requisition-requester" class="mb-1.5 block text-sm font-medium text-slate-700">
                    請購人
                    <span class="text-red-600">*</span>
                </label>
                <select
                    id="purchase-requisition-requester"
                    name="requester_id"
                    required
                    data-field="requester_id"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                    <option value="">請選擇請購人</option>
                </select>
                <p class="mt-1 hidden text-sm text-red-600" data-error="requester_id"></p>
            </div>

            <div>
                <label for="purchase-requisition-warehouse" class="mb-1.5 block text-sm font-medium text-slate-700">
                    進貨倉庫
                    <span class="text-red-600">*</span>
                </label>
                <select
                    id="purchase-requisition-warehouse"
                    name="warehouse_id"
                    required
                    data-field="warehouse_id"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                    <option value="">請選擇進貨倉庫</option>
                </select>
                <p class="mt-1 hidden text-sm text-red-600" data-error="warehouse_id"></p>
            </div>

            <div>
                <label for="purchase-requisition-date" class="mb-1.5 block text-sm font-medium text-slate-700">
                    請購日期
                    <span class="text-red-600">*</span>
                </label>
                <input
                    id="purchase-requisition-date"
                    name="request_date"
                    type="date"
                    required
                    data-field="request_date"
                    value="{{ old('request_date', $isEdit ? $purchaseRequisition->request_date?->format('Y-m-d') : now()->toDateString()) }}"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                <p class="mt-1 hidden text-sm text-red-600" data-error="request_date"></p>
            </div>

            <div>
                <label for="purchase-requisition-required-date" class="mb-1.5 block text-sm font-medium text-slate-700">
                    需求日期
                    <span class="font-normal text-slate-400">（選填）</span>
                </label>
                <input
                    id="purchase-requisition-required-date"
                    name="required_date"
                    type="date"
                    data-field="required_date"
                    value="{{ old('required_date', $isEdit ? $purchaseRequisition->required_date?->format('Y-m-d') : '') }}"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                <p class="mt-1 hidden text-sm text-red-600" data-error="required_date"></p>
            </div>

            <div>
                <label for="purchase-requisition-status" class="mb-1.5 block text-sm font-medium text-slate-700">
                    狀態
                    <span class="text-red-600">*</span>
                </label>
                <select
                    id="purchase-requisition-status"
                    name="status"
                    required
                    data-field="status"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >
                    @php
                        $currentStatus = old('status', $isEdit ? $purchaseRequisition->status : 'draft');
                    @endphp
                    <option value="draft" @selected($currentStatus === 'draft')>草稿</option>
                    <option value="confirmed" @selected($currentStatus === 'confirmed')>已確認</option>
                    <option value="cancelled" @selected($currentStatus === 'cancelled')>已取消</option>
                </select>
                <p class="mt-1 hidden text-sm text-red-600" data-error="status"></p>
            </div>
        </div>

        <div>
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">請購明細</h3>
                    <p class="mt-0.5 text-xs text-slate-500">可輸入商品編號或名稱搜尋；同一商品不可重複。</p>
                </div>
                <button
                    type="button"
                    data-add-item
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    新增明細
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-[36rem] w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-3 py-2.5 font-medium">商品</th>
                            <th class="whitespace-nowrap px-3 py-2.5 font-medium w-28">數量</th>
                            <th class="whitespace-nowrap px-3 py-2.5 font-medium">備註</th>
                            <th class="whitespace-nowrap px-3 py-2.5 font-medium w-20 text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" data-items-body></tbody>
                </table>
            </div>
            <p class="mt-1 hidden text-sm text-red-600" data-error="items"></p>
        </div>

        <div>
            <label for="purchase-requisition-notes" class="mb-1.5 block text-sm font-medium text-slate-700">
                備註
                <span class="font-normal text-slate-400">（選填）</span>
            </label>
            <textarea
                id="purchase-requisition-notes"
                name="notes"
                rows="3"
                data-field="notes"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
            >{{ old('notes', $isEdit ? $purchaseRequisition->notes : '') }}</textarea>
            <p class="mt-1 hidden text-sm text-red-600" data-error="notes"></p>
        </div>

        <p class="hidden text-sm text-red-600" data-error="form"></p>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
            <a
                href="{{ route('purchase-requisitions.index') }}"
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
