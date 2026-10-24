<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
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
    'current' => ['label' => $tr('currentBucket'), 'class' => 'from-emerald-50 to-green-100 v3-text-primary v3-border-default'],
    'days_1_30' => ['label' => $tr('days1To30'), 'class' => 'from-amber-50 to-yellow-100 v3-text-warning v3-border-default'],
    'days_31_60' => ['label' => $tr('days31To60'), 'class' => 'from-orange-50 to-amber-100 v3-text-warning v3-border-default'],
    'days_61_90' => ['label' => $tr('days61To90'), 'class' => 'from-rose-50 to-red-100 v3-text-danger v3-border-default'],
    'days_91_plus' => ['label' => $tr('days91Plus'), 'class' => 'from-slate-100 to-gray-200 v3-text-ink v3-border-default'],
];
$statusClass = static function (string $status): string {
    return match ($status) {
        'paid' => 'v3-bg-paid v3-text-primary',
        'partial' => 'v3-bg-scheduled v3-text-primary',
        'overdue' => 'v3-bg-alert v3-text-danger',
        'pending' => 'v3-bg-waiting v3-text-warning',
        default => 'v3-bg-soft v3-text-ink',
    };
};
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">


<div dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="finance-payables-wrap mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-payables-hero overflow-hidden border sm:p-6 lg:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center border">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide v3-text-warning"><?= esc($tr('accountsPayableAging')) ?></p>
                        <h1 class="mt-1 v3-section-title tracking-tight v3-title"><?= esc($tr('supplierPayables')) ?></h1>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <?php if (has_permission('finance', 'create')): ?>
                        <a href="<?= base_url('finance/expenses/create') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm v3-text-warning transition v3-hover-primary v3-btn v3-btn--secondary">
                            <i class="fas fa-plus"></i>
                            <?= esc($tr('newSupplierExpense')) ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($can_access_finance_reports)): ?>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold border transition v3-btn v3-btn--secondary">
                            <i class="fas fa-chart-simple"></i>
                            <?= esc($tr('reports')) ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold border transition v3-btn v3-btn--secondary">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= esc($tr('financeManagement')) ?>
                    </a>
                </div>
            </div>
        </section>

        <form method="get" action="<?= base_url('finance/payables') ?>" class="finance-payables-panel">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(180px,240px)_auto] sm:items-end">
                <div>
                    <label for="asOfDate" class="mb-2 block text-xs uppercase tracking-wide"><?= esc($tr('asOfDate')) ?></label>
                    <input id="asOfDate" type="date" name="as_of" value="<?= esc($as_of_date ?? date('Y-m-d')) ?>" class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 text-sm transition v3-btn v3-btn--primary">
                    <i class="fas fa-filter"></i>
                    <?= esc($tr('filter')) ?>
                </button>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="finance-payables-card">
                <p class="text-xs uppercase tracking-wide v3-text-warning"><?= esc($tr('openBalance')) ?></p>
                <p class="mt-3 v3-title"><?= $formatMoney($summary['open_balance'] ?? 0) ?></p>
            </div>
            <div class="finance-payables-card">
                <p class="text-xs uppercase tracking-wide v3-text-primary"><?= esc($tr('openBills')) ?></p>
                <p class="mt-3 v3-title"><?= number_format((int) ($summary['open_count'] ?? 0)) ?></p>
            </div>
            <div class="finance-payables-card">
                <p class="text-xs uppercase tracking-wide v3-text-danger"><?= esc($tr('overdueBills')) ?></p>
                <p class="mt-3 v3-title"><?= number_format((int) ($summary['overdue_count'] ?? 0)) ?></p>
            </div>
            <div class="finance-payables-card">
                <p class="text-xs uppercase tracking-wide"><?= esc($tr('overdueBalance')) ?></p>
                <p class="mt-3 v3-title"><?= $formatMoney($summary['overdue_balance'] ?? 0) ?></p>
            </div>
        </div>

        <details class="finance-payables-panel">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 text-sm">
                <span class="inline-flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left v3-text-warning"></i>
                    <?= esc($tr('accountsPayableAging')) ?>
                </span>
                <i class="fas fa-chevron-down text-xs transition group-open:rotate-180"></i>
            </summary>
            <div class="grid grid-cols-2 gap-3 border-t p-4 md:grid-cols-5">
                <?php foreach ($bucketCards as $bucket => $card): ?>
                    <div class="border p-4 <?= esc($card['class']) ?>">
                        <p class="text-xs uppercase tracking-wide"><?= esc($card['label']) ?></p>
                        <p class="mt-2 text-lg"><?= $formatMoney($agingSummary[$bucket] ?? 0) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </details>

        <div>
            <div id="tableView" class="finance-payables-panel">
                <div class="border-b px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-2.5 w-2.5 v3-bg-warning"></div>
                            <h3 class="text-lg"><?= esc($tr('apAgingDetail')) ?></h3>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:space-x-3">
                            <button onclick="exportPayablesToPDF()" class="inline-flex items-center px-3 py-2 text-sm font-bold border transition v3-btn v3-btn--secondary">
                                <i class="fas fa-file-pdf mr-2 v3-text-danger"></i>
                                <?= esc($tr('exportPdf')) ?>
                            </button>
                            <button onclick="printPayables()" class="inline-flex items-center px-3 py-2 text-sm font-bold border transition v3-btn v3-btn--secondary">
                                <i class="fas fa-print mr-2"></i>
                                <?= esc($tr('print')) ?>
                            </button>
                            <button onclick="exportPayablesToCSV()" class="inline-flex items-center px-3 py-2 text-sm font-bold border transition v3-btn v3-btn--secondary">
                                <i class="fas fa-file-csv mr-2 v3-text-primary"></i>
                                <?= esc($tr('exportCsv')) ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="dataTables-wrapper">
                        <table id="payablesTable" class="w-full min-w-full">
                        <thead class="">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-building v3-text-primary"></i>
                                        <span><?= esc($tr('supplierOrVendor')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-receipt v3-text-primary"></i>
                                        <span><?= esc($tr('transaction')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-tag v3-text-primary"></i>
                                        <span><?= esc($tr('expenses')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign v3-text-primary"></i>
                                        <span><?= esc($tr('total')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle v3-text-primary"></i>
                                        <span><?= esc($tr('status')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar v3-text-primary"></i>
                                        <span><?= esc($tr('dueDate')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-cogs v3-text-primary"></i>
                                        <span><?= esc($tr('actions')) ?></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="payablesTableBody" class="divide-y v3-border-default">
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
                                                <i class="fas fa-building v3-section-title"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium truncate"><?= esc($vendorName) ?></div>
                                                <div class="text-xs truncate"><?= esc($row['expense_category'] ?? $tr('vendor')) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a data-row-action href="<?= workflow_context_url('finance/' . $financeId) ?>" class="block rounded-md v3-hover-primary transition-colors duration-200 cursor-pointer">
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                                    <i class="fas fa-receipt v3-text-warning text-xl"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium truncate"><?= esc($row['transaction_id'] ?? ('#' . $financeId)) ?></div>
                                                    <div class="text-xs"><?= esc($tr('transaction')) ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium v3-bg-waiting v3-text-warning">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= esc($row['expense_category'] ?? $tr('expenses')) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-order="<?= esc((string) ($row['total_amount'] ?? 0)) ?>">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-arrow-down v3-text-danger text-xs"></i>
                                                <span class="text-sm font-medium v3-text-danger"><?= $formatMoney($row['total_amount'] ?? 0) ?></span>
                                            </div>
                                            <div class="text-xs"><?= esc($tr('balanceDue')) ?>: <?= $formatMoney($balance) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium <?= esc($statusClass($status)) ?>">
                                            <i class="fas <?= $status === 'partial' ? 'fa-adjust' : ($status === 'overdue' ? 'fa-exclamation-triangle' : 'fa-clock') ?> mr-1"></i>
                                            <?= esc($tr($status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-order="<?= esc($dueDate) ?>">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar v3-text-primary text-xs"></i>
                                            <div>
                                                <div class="text-sm"><?= $dueDate !== '' ? esc(formatDate($dueDate)) : '-' ?></div>
                                                <div class="text-xs"><?= $age > 0 ? esc($age . ' ' . $tr('daysPastDue')) : esc($tr('currentBucket')) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <a data-row-action href="<?= workflow_context_url('finance/' . $financeId) ?>" class="p-2 rounded-md transition-colors duration-200" title="<?= esc($tr('viewTransaction')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!empty($row['supplier_id'])): ?>
                                                <a data-row-action href="<?= base_url('inventory/suppliers/' . (int) $row['supplier_id']) ?>" class="p-2 v3-text-warning v3-hover-text-warning rounded-md v3-hover-primary transition-colors duration-200" title="<?= esc($tr('supplierDirectory')) ?>">
                                                    <i class="fas fa-address-book"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (has_permission('finance', 'edit')): ?>
                                                <button data-row-action type="button" onclick="openPayablePaymentModal(<?= $financeId ?>, <?= esc(json_encode($balance)) ?>)" class="p-2 v3-text-primary v3-hover-text-primary rounded-md v3-hover-primary transition-colors duration-200 v3-btn v3-btn--secondary" title="<?= esc($tr('recordPayablePayment')) ?>">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                                <button data-row-action type="button" onclick="markPayablePaid(<?= $financeId ?>)" class="p-2 rounded-md transition-colors duration-200 v3-btn v3-btn--secondary" title="<?= esc($tr('markPayableAsPaid')) ?>">
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
    <div class="fixed inset-0" data-payable-modal-close></div>
    <div class="relative flex min-h-screen items-center justify-center p-4" data-payable-modal-close>
        <div class="relative w-full max-w-xl" data-payable-modal-panel>
            <div class="flex items-start justify-between gap-4 rounded-t-2xl px-5 py-4">
                <div>
                    <h3 id="payablePaymentTitle" class="text-lg"><?= esc($tr('recordPayablePayment')) ?></h3>
                    <p id="payablePaymentHelp" class="mt-1 text-sm"></p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg transition v3-btn v3-btn--icon" onclick="closePayablePaymentModal()" aria-label="<?= esc($tr('cancel')) ?>">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <form id="payablePaymentForm" class="space-y-4">
                <input type="hidden" id="payablePaymentFinanceId">
                <div>
                    <label for="payablePaymentAmount" class="mb-2 block text-sm font-bold">
                        <?= esc($tr('paymentAmount')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                    </label>
                    <input id="payablePaymentAmount" type="number" step="0.01" min="0.01" required class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="payablePaymentMethod" class="mb-2 block text-sm font-bold">
                            <?= esc($tr('paymentMethodRequired')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <select id="payablePaymentMethod" data-force-select2 required class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-select">
                            <option value="cash"><?= esc($tr('cash')) ?></option>
                            <option value="card"><?= esc($tr('card')) ?></option>
                            <option value="bank_transfer"><?= esc($tr('bankTransfer')) ?></option>
                            <option value="check"><?= esc($tr('check')) ?></option>
                            <option value="other"><?= esc($tr('other')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="payablePaymentDate" class="mb-2 block text-sm font-bold">
                            <?= esc($tr('settlementDate')) ?><span class="required-indicator notranslate" aria-hidden="true">*</span>
                        </label>
                        <input id="payablePaymentDate" type="date" required class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                    </div>
                </div>
                <div id="payableLiquidityField" class="hidden">
                    <label for="payableLiquidityAccount" class="mb-2 block text-sm font-bold"><?= esc($tr('paidFromAccount')) ?></label>
                    <select id="payableLiquidityAccount" data-force-select2 class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-select">
                        <option value="bank"><?= esc($liquidityOptions['bank'] ?? $tr('liquidityAccount.bank')) ?></option>
                        <option value="undeposited_checks"><?= esc($liquidityOptions['undeposited_checks'] ?? $tr('liquidityAccount.undeposited_checks')) ?></option>
                    </select>
                </div>
                <div>
                    <label for="payablePaymentNotes" class="mb-2 block text-sm font-bold"><?= esc($tr('notes')) ?></label>
                    <textarea id="payablePaymentNotes" rows="3" class="w-full border-2 px-4 py-3 text-sm font-semibold v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea" placeholder="<?= esc($tr('optionalNotesPlaceholder')) ?>"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closePayablePaymentModal()" class="inline-flex items-center justify-center px-4 py-3 text-sm font-bold transition v3-btn v3-btn--secondary"><?= esc($tr('cancel')) ?></button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold transition v3-btn v3-btn--primary">
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
    $('.dataTables_length select').addClass('v3-focus-control v3-focus-control');
    $('.dataTables_filter input').addClass('v3-focus-control v3-focus-control');
    $('.dataTables_info').addClass('v3-text-muted');
    $('.dataTables_paginate .paginate_button').each(function () {
        if ($(this).hasClass('current')) {
            $(this).addClass('v3-bg-primary v3-border-default v3-text-on-primary');
        } else {
            $(this).addClass('v3-hover-primary v3-border-default v3-hover-text-primary');
        }
    });
    $('.dataTables_length label, .dataTables_filter label').addClass('v3-text-ink font-semibold');
    $('.dataTables_length i, .dataTables_filter i').addClass('v3-text-primary');
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
        dom: 'B<"px-6 py-4 border-b v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
            $(this).addClass('cursor-pointer v3-hover-primary transition-colors duration-150');
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

</div>
<?= $this->endSection() ?>
