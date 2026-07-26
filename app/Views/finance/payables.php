<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Finance.' . $key, $params);
$isRtl = in_array(service('request')->getLocale(), ['ar', 'he', 'fa', 'ur'], true);
$rows = $rows ?? [];
$summary = $summary ?? [];
$agingSummary = $aging_summary ?? [];
$liquidityOptions = $liquidity_account_options ?? [];
$returnTo = urlencode('finance/payables');
$formatMoney = static fn($amount) => formatCurrency((float) $amount);
$bucketCards = [
    'current' => ['label' => $tr('currentBucket'), 'class' => 'from-emerald-50 to-green-100 text-emerald-800 border-emerald-200'],
    'days_1_30' => ['label' => $tr('days1To30'), 'class' => 'from-amber-50 to-yellow-100 text-amber-800 border-amber-200'],
    'days_31_60' => ['label' => $tr('days31To60'), 'class' => 'from-orange-50 to-amber-100 text-orange-800 border-orange-200'],
    'days_61_90' => ['label' => $tr('days61To90'), 'class' => 'from-rose-50 to-red-100 text-rose-800 border-rose-200'],
    'days_91_plus' => ['label' => $tr('days91Plus'), 'class' => 'from-slate-100 to-gray-200 text-slate-800 border-slate-300'],
];
$statusClass = static function (string $status): string {
    return match ($status) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'partial' => 'bg-blue-100 text-blue-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'pending' => 'bg-amber-100 text-amber-700',
        default => 'bg-gray-100 text-gray-700',
    };
};
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<style>
.finance-payables-wrap {
    width: min(100%, 1500px);
}

.finance-payables-hero {
    border-radius: 1.75rem;
    background:
        radial-gradient(circle at 10% 8%, rgba(255, 255, 255, 0.24), transparent 30%),
        radial-gradient(circle at 90% 0%, rgba(251, 191, 36, 0.30), transparent 34%),
        linear-gradient(135deg, #b45309 0%, #c2410c 42%, #0f172a 100%);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
}

[dir="rtl"] .finance-payables-hero {
    background:
        radial-gradient(circle at 90% 8%, rgba(255, 255, 255, 0.24), transparent 30%),
        radial-gradient(circle at 10% 0%, rgba(251, 191, 36, 0.30), transparent 34%),
        linear-gradient(225deg, #b45309 0%, #c2410c 42%, #0f172a 100%);
}

.finance-payables-card,
.finance-payables-panel {
    position: relative;
    isolation: isolate;
    overflow: hidden;
}

.finance-payables-card {
    border-radius: 1.5rem;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.finance-payables-card::after {
    content: "";
    position: absolute;
    inset: auto -18% -46% auto;
    width: 72%;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.15);
    z-index: -1;
}

[dir="rtl"] .finance-payables-card::after {
    inset-inline-start: -18%;
    inset-inline-end: auto;
}

.finance-payables-panel {
    border-radius: 1.5rem;
    border: 1px solid rgba(226, 232, 240, 0.92);
    background: rgba(255, 255, 255, 0.97);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.dataTables_wrapper {
    position: relative !important;
}
.dataTables_length,
.dataTables_filter,
.dataTables_info,
.dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.dataTables_filter {
    margin-left: auto !important;
}
.dataTables_length label,
.dataTables_filter label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-right: 12px !important;
}
.dataTables_length select,
.dataTables_filter input {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}
.dataTables_length select:focus,
.dataTables_filter input:focus {
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.14) !important;
}
.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}
.dataTables_paginate .paginate_button.current {
    background-color: #d97706 !important;
    border-color: #d97706 !important;
    color: white !important;
}
.dataTables_wrapper > .row:first-child,
.dataTables_wrapper > .row:last-child {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin: 16px 0 !important;
    gap: 16px !important;
}
@media (max-width: 768px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        justify-content: center !important;
        width: 100% !important;
        margin: 8px 0 !important;
    }
    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper > .row:last-child {
        flex-direction: column !important;
    }
}
</style>

<div dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="finance-payables-wrap mx-auto space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-payables-hero overflow-hidden border border-white/20 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg shadow-black/10 ring-1 ring-white/20">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-amber-100/90"><?= esc($tr('accountsPayableAging')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl"><?= esc($tr('supplierPayables')) ?></h1>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <?php if (has_permission('finance', 'create')): ?>
                        <a href="<?= base_url('finance/expenses/create') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-amber-800 shadow-lg shadow-black/10 transition hover:bg-amber-50">
                            <i class="fas fa-plus"></i>
                            <?= esc($tr('newSupplierExpense')) ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($can_access_finance_reports)): ?>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                            <i class="fas fa-chart-simple"></i>
                            <?= esc($tr('reports')) ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= esc($tr('financeManagement')) ?>
                    </a>
                </div>
            </div>
        </section>

        <form method="get" action="<?= base_url('finance/payables') ?>" class="finance-payables-panel p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(180px,240px)_auto] sm:items-end">
                <div>
                    <label for="asOfDate" class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('asOfDate')) ?></label>
                    <input id="asOfDate" type="date" name="as_of" value="<?= esc($as_of_date ?? date('Y-m-d')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-700 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:from-amber-600 hover:to-orange-800">
                    <i class="fas fa-filter"></i>
                    <?= esc($tr('filter')) ?>
                </button>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="finance-payables-card bg-gradient-to-br from-amber-500 to-orange-700 p-5 text-white shadow-amber-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-amber-50/95"><?= esc($tr('openBalance')) ?></p>
                <p class="mt-3 text-3xl font-black"><?= $formatMoney($summary['open_balance'] ?? 0) ?></p>
            </div>
            <div class="finance-payables-card bg-gradient-to-br from-sky-600 to-blue-900 p-5 text-white shadow-sky-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-sky-50/90"><?= esc($tr('openBills')) ?></p>
                <p class="mt-3 text-3xl font-black"><?= number_format((int) ($summary['open_count'] ?? 0)) ?></p>
            </div>
            <div class="finance-payables-card bg-gradient-to-br from-rose-600 to-pink-800 p-5 text-white shadow-rose-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-rose-50/90"><?= esc($tr('overdueBills')) ?></p>
                <p class="mt-3 text-3xl font-black"><?= number_format((int) ($summary['overdue_count'] ?? 0)) ?></p>
            </div>
            <div class="finance-payables-card bg-gradient-to-br from-slate-700 to-slate-950 p-5 text-white shadow-slate-500/25">
                <p class="text-xs font-black uppercase tracking-wide text-slate-100/90"><?= esc($tr('overdueBalance')) ?></p>
                <p class="mt-3 text-3xl font-black"><?= $formatMoney($summary['overdue_balance'] ?? 0) ?></p>
            </div>
        </div>

        <details class="finance-payables-panel group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 text-sm font-black text-slate-800">
                <span class="inline-flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-amber-600"></i>
                    <?= esc($tr('accountsPayableAging')) ?>
                </span>
                <i class="fas fa-chevron-down text-xs text-slate-500 transition group-open:rotate-180"></i>
            </summary>
            <div class="grid grid-cols-2 gap-3 border-t border-slate-100 p-4 md:grid-cols-5">
                <?php foreach ($bucketCards as $bucket => $card): ?>
                    <div class="rounded-2xl border bg-gradient-to-br p-4 shadow-lg shadow-slate-200/60 <?= esc($card['class']) ?>">
                        <p class="text-xs font-black uppercase tracking-wide"><?= esc($card['label']) ?></p>
                        <p class="mt-2 text-lg font-black"><?= $formatMoney($agingSummary[$bucket] ?? 0) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </details>

        <div>
            <div id="tableView" class="finance-payables-panel">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-800 to-slate-950 px-6 py-5 text-white">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-amber-400 shadow-lg shadow-amber-300/40"></div>
                            <h3 class="text-lg font-black"><?= esc($tr('apAgingDetail')) ?></h3>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:space-x-3">
                            <button onclick="exportPayablesToPDF()" class="inline-flex items-center rounded-xl bg-white/10 px-3 py-2 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                <?= esc($tr('exportPdf')) ?>
                            </button>
                            <button onclick="printPayables()" class="inline-flex items-center rounded-xl bg-white/10 px-3 py-2 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                                <i class="fas fa-print mr-2 text-slate-200"></i>
                                <?= esc($tr('print')) ?>
                            </button>
                            <button onclick="exportPayablesToCSV()" class="inline-flex items-center rounded-xl bg-white/10 px-3 py-2 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                                <?= esc($tr('exportCsv')) ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="dataTables-wrapper">
                        <table id="payablesTable" class="w-full min-w-full">
                        <thead class="bg-slate-100/95">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-building text-green-600"></i>
                                        <span><?= esc($tr('supplierOrVendor')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-receipt text-green-600"></i>
                                        <span><?= esc($tr('transaction')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-tag text-green-600"></i>
                                        <span><?= esc($tr('expenses')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign text-green-600"></i>
                                        <span><?= esc($tr('total')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle text-green-600"></i>
                                        <span><?= esc($tr('status')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-green-600"></i>
                                        <span><?= esc($tr('dueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-cogs text-green-600"></i>
                                        <span><?= esc($tr('actions')) ?></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="payablesTableBody" class="bg-white divide-y divide-gray-200">
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $financeId = (int) ($row['id'] ?? 0);
                                $supplierName = trim((string) ($row['supplier_name'] ?? ''));
                                $vendorName = $supplierName !== '' ? $supplierName : trim((string) ($row['counterparty_name'] ?? ''));
                                $vendorName = $vendorName !== '' ? $vendorName : $tr('unknown');
                                $status = (string) ($row['payment_status'] ?? 'pending');
                                $balance = (float) ($row['balance_due'] ?? 0);
                                $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : (string) ($row['transaction_date'] ?? '');
                                $age = (int) ($row['days_past_due'] ?? 0);
                                ?>
                                <tr data-row-url="<?= workflow_context_url('finance/' . $financeId) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                                <i class="fas fa-building text-slate-600 text-2xl"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate"><?= esc($vendorName) ?></div>
                                                <div class="text-xs text-gray-500 truncate"><?= esc($row['expense_category'] ?? $tr('vendor')) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a data-row-action href="<?= workflow_context_url('finance/' . $financeId) ?>" class="block rounded-md hover:bg-green-50/60 transition-colors duration-200 cursor-pointer">
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                                    <i class="fas fa-receipt text-orange-700 text-xl"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 truncate"><?= esc($row['transaction_id'] ?? ('#' . $financeId)) ?></div>
                                                    <div class="text-xs text-gray-500"><?= esc($tr('transaction')) ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= esc($row['expense_category'] ?? $tr('expenses')) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-order="<?= esc((string) ($row['total_amount'] ?? 0)) ?>">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-arrow-down text-red-600 text-xs"></i>
                                                <span class="text-sm font-medium text-red-600"><?= $formatMoney($row['total_amount'] ?? 0) ?></span>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= esc($tr('balanceDue')) ?>: <?= $formatMoney($balance) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= esc($statusClass($status)) ?>">
                                            <i class="fas <?= $status === 'partial' ? 'fa-adjust' : ($status === 'overdue' ? 'fa-exclamation-triangle' : 'fa-clock') ?> mr-1"></i>
                                            <?= esc($tr($status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-order="<?= esc($dueDate) ?>">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-green-600 text-xs"></i>
                                            <div>
                                                <div class="text-sm text-gray-900"><?= $dueDate !== '' ? esc(formatDate($dueDate)) : '-' ?></div>
                                                <div class="text-xs text-gray-500"><?= $age > 0 ? esc($age . ' ' . $tr('daysPastDue')) : esc($tr('currentBucket')) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <a data-row-action href="<?= workflow_context_url('finance/' . $financeId) ?>" class="p-2 text-indigo-600 hover:text-indigo-700 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="<?= esc($tr('viewTransaction')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!empty($row['supplier_id'])): ?>
                                                <a data-row-action href="<?= base_url('inventory/suppliers/' . (int) $row['supplier_id']) ?>" class="p-2 text-amber-600 hover:text-amber-700 rounded-md hover:bg-amber-50 transition-colors duration-200" title="<?= esc($tr('supplierDirectory')) ?>">
                                                    <i class="fas fa-address-book"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (has_permission('finance', 'edit')): ?>
                                                <button data-row-action type="button" onclick="openPayablePaymentModal(<?= $financeId ?>, <?= esc(json_encode($balance)) ?>)" class="p-2 text-emerald-600 hover:text-emerald-700 rounded-md hover:bg-emerald-50 transition-colors duration-200" title="<?= esc($tr('recordPayablePayment')) ?>">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                                <button data-row-action type="button" onclick="markPayablePaid(<?= $financeId ?>)" class="p-2 text-blue-600 hover:text-blue-700 rounded-md hover:bg-blue-50 transition-colors duration-200" title="<?= esc($tr('markPayableAsPaid')) ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="payablePaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="payablePaymentTitle" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/55 backdrop-blur-sm" data-payable-modal-close></div>
    <div class="relative flex min-h-screen items-center justify-center p-4" data-payable-modal-close>
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl" data-payable-modal-panel>
            <div class="flex items-start justify-between gap-4 rounded-t-2xl bg-slate-900 px-5 py-4 text-white">
                <div>
                    <h3 id="payablePaymentTitle" class="text-lg font-black"><?= esc($tr('recordPayablePayment')) ?></h3>
                    <p id="payablePaymentHelp" class="mt-1 text-sm text-slate-300"></p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20" onclick="closePayablePaymentModal()" aria-label="<?= esc($tr('cancel')) ?>">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <form id="payablePaymentForm" class="space-y-4 p-5">
                <input type="hidden" id="payablePaymentFinanceId">
                <div>
                    <label for="payablePaymentAmount" class="mb-2 block text-sm font-bold text-slate-700">
                        <?= esc($tr('paymentAmount')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                    </label>
                    <input id="payablePaymentAmount" type="number" step="0.01" min="0.01" required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payablePaymentMethod" class="mb-2 block text-sm font-bold text-slate-700">
                            <?= esc($tr('paymentMethodRequired')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <select id="payablePaymentMethod" data-force-select2 required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                            <option value="cash"><?= esc($tr('cash')) ?></option>
                            <option value="card"><?= esc($tr('card')) ?></option>
                            <option value="bank_transfer"><?= esc($tr('bankTransfer')) ?></option>
                            <option value="check"><?= esc($tr('check')) ?></option>
                            <option value="other"><?= esc($tr('other')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="payablePaymentDate" class="mb-2 block text-sm font-bold text-slate-700">
                            <?= esc($tr('settlementDate')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <input id="payablePaymentDate" type="date" required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                    </div>
                </div>
                <div id="payableLiquidityField" class="hidden">
                    <label for="payableLiquidityAccount" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('paidFromAccount')) ?></label>
                    <select id="payableLiquidityAccount" data-force-select2 class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15">
                        <option value="bank"><?= esc($liquidityOptions['bank'] ?? $tr('liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc($liquidityOptions['undeposited_checks'] ?? $tr('liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                </div>
                <div>
                    <label for="payablePaymentNotes" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('notes')) ?></label>
                    <textarea id="payablePaymentNotes" rows="3" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15" placeholder="<?= esc($tr('optionalNotesPlaceholder')) ?>"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closePayablePaymentModal()" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200"><?= esc($tr('cancel')) ?></button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-3 text-sm font-bold text-white transition hover:from-amber-600 hover:to-orange-700">
                        <i class="fas fa-save"></i>
                        <?= esc($tr('postSettlement')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
let payablePaymentMaxAmount = 0;
let payablesTable = null;
const payableCurrencySymbol = <?= json_encode($currency_symbol ?? '') ?>;
const payableCurrencyPosition = <?= json_encode($currency_position ?? 'before') ?>;

function applyPayablesDataTablesStyling() {
    $('.dataTables_length select').addClass('focus:ring-green-500 focus:border-green-500');
    $('.dataTables_filter input').addClass('focus:ring-green-500 focus:border-green-500');
    $('.dataTables_info').addClass('text-gray-600');
    $('.dataTables_paginate .paginate_button').each(function () {
        if ($(this).hasClass('current')) {
            $(this).addClass('bg-green-600 border-green-600 text-white');
        } else {
            $(this).addClass('hover:bg-green-50 hover:border-green-300 hover:text-green-700');
        }
    });
    $('.dataTables_length label, .dataTables_filter label').addClass('text-gray-700 font-semibold');
    $('.dataTables_length i, .dataTables_filter i').addClass('text-green-600');
}

window.exportPayablesToPDF = function () {
    if (payablesTable) {
        payablesTable.button('.btn-pdf').trigger();
    }
};

window.printPayables = function () {
    if (payablesTable) {
        payablesTable.button('.btn-print').trigger();
    }
};

window.exportPayablesToCSV = function () {
    if (payablesTable) {
        payablesTable.button('.btn-csv').trigger();
    }
};

function payableFormatMoney(value) {
    const amount = Number(value || 0);
    const formatted = amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return payableCurrencyPosition === 'after' ? `${formatted} ${payableCurrencySymbol}` : `${payableCurrencySymbol}${formatted}`;
}

function notifyPayable(type, message) {
    if (typeof window.showToast === 'function') {
        window.showToast(type, type === 'success' ? '<?= esc($tr('success')) ?>' : '<?= esc($tr('error')) ?>', message);
        return;
    }
    alert(message);
}

function syncPayableLiquidityField() {
    const method = document.getElementById('payablePaymentMethod')?.value || '';
    document.getElementById('payableLiquidityField')?.classList.toggle('hidden', method !== 'check');
}

function closePayablePaymentModal() {
    document.getElementById('payablePaymentModal')?.classList.add('hidden');
    document.getElementById('payablePaymentForm')?.reset();
}

function openPayablePaymentModal(id, maxAmount) {
    payablePaymentMaxAmount = Number(maxAmount || 0);
    document.getElementById('payablePaymentFinanceId').value = String(id);
    document.getElementById('payablePaymentAmount').value = payablePaymentMaxAmount > 0 ? payablePaymentMaxAmount.toFixed(2) : '';
    document.getElementById('payablePaymentDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('payablePaymentHelp').textContent = '<?= esc($tr('maximumPayable')) ?>: ' + payableFormatMoney(payablePaymentMaxAmount);
    syncPayableLiquidityField();
    document.getElementById('payablePaymentModal')?.classList.remove('hidden');
}

async function markPayablePaid(financeId) {
    const formData = new FormData();
    formData.append(window.csrfConfig.name, window.getCsrfToken());

    const response = await fetch(`<?= base_url('finance') ?>/${financeId}/mark-paid`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const result = await response.json();
    if (result.csrf_token && window.refreshCsrfToken) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifyPayable(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
}

document.querySelectorAll('[data-payable-modal-close]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (event.target.closest('[data-payable-modal-panel]')) {
            return;
        }
        closePayablePaymentModal();
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !document.getElementById('payablePaymentModal')?.classList.contains('hidden')) {
        closePayablePaymentModal();
    }
});

document.getElementById('payablePaymentMethod')?.addEventListener('change', syncPayableLiquidityField);

$(document).ready(function () {
    payablesTable = $('#payablesTable').DataTable({
        dom: 'B<"px-6 py-4 border-b border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t border-gray-200 bg-gray-50"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[5, 'asc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: <?= json_encode($tr('supplierPayables')) ?>,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i><?= esc($tr('print')) ?>',
                className: 'btn-print',
                title: <?= json_encode($tr('supplierPayables')) ?>,
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: <?= json_encode($tr('supplierPayables')) ?>,
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            }
        ],
        columnDefs: [
            { orderable: false, searchable: false, targets: 6 }
        ],
        language: {
            processing: <?= json_encode($tr('loadingTransactions')) ?>,
            search: <?= json_encode($tr('search')) ?>,
            lengthMenu: <?= json_encode($tr('show') . ' _MENU_') ?>,
            info: <?= json_encode($tr('showing') . ' _START_ ' . $tr('to') . ' _END_ ' . $tr('of') . ' _TOTAL_ ' . $tr('supplierPayables')) ?>,
            infoEmpty: <?= json_encode($tr('showing') . ' 0 ' . $tr('to') . ' 0 ' . $tr('of') . ' 0 ' . $tr('supplierPayables')) ?>,
            infoFiltered: '',
            zeroRecords: <?= json_encode($tr('noSupplierPayables')) ?>,
            emptyTable: <?= json_encode($tr('noSupplierPayables')) ?>,
            paginate: {
                previous: <?= json_encode($tr('previous')) ?>,
                next: <?= json_encode($tr('next')) ?>
            }
        },
        drawCallback: function () {
            applyPayablesDataTablesStyling();
        }
    });

    $('#payablesTable tbody')
        .on('click', 'tr', function (event) {
            if ($(event.target).closest('a, button').length) {
                return;
            }
            const rowUrl = this.getAttribute('data-row-url');
            if (rowUrl) {
                window.location.href = window.workflowContextUrl(rowUrl);
            }
        })
        .on('mouseenter', 'tr', function () {
            $(this).addClass('cursor-pointer hover:bg-green-50/50 transition-colors duration-150');
        });

    applyPayablesDataTablesStyling();
});

document.getElementById('payablePaymentForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const financeId = document.getElementById('payablePaymentFinanceId').value;
    const amount = parseFloat(document.getElementById('payablePaymentAmount').value || '0');
    if (!amount || amount <= 0 || amount > payablePaymentMaxAmount + 0.0001) {
        notifyPayable('error', '<?= esc($tr('invalidPaymentAmount')) ?>');
        return;
    }

    const formData = new FormData();
    formData.append(window.csrfConfig.name, window.getCsrfToken());
    formData.append('amount', amount.toFixed(2));
    formData.append('payment_method', document.getElementById('payablePaymentMethod').value);
    formData.append('liquidity_account', document.getElementById('payableLiquidityAccount').value);
    formData.append('payment_date', document.getElementById('payablePaymentDate').value);
    formData.append('notes', document.getElementById('payablePaymentNotes').value);

    const response = await fetch(`<?= base_url('finance') ?>/${financeId}/payments`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const result = await response.json();
    if (result.csrf_token && window.refreshCsrfToken) {
        window.refreshCsrfToken(result.csrf_token);
    }
    notifyPayable(result.success ? 'success' : 'error', result.message || 'Request failed.');
    if (result.success) {
        window.location.reload();
    }
});
</script>

<?= $this->endSection() ?>
