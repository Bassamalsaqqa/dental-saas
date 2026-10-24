<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<p class="px-4 py-2 text-sm text-amber-800"><?= esc(lang('Finance.providerCurrencyWarning')) ?></p>
<?php
$formatMoney = static fn($amount) => $amount === null ? esc(lang('Finance.providerCurrencyUnavailable')) : formatCurrency((float) $amount);
$ownerSummary = $owner_summary ?? [];
$treasuryMovement = $treasury_movement ?? [];
$treasuryClosing = $treasury_closing ?? [];
$liquidityLabels = $liquidity_account_labels ?? [];
$bucketLabels = [
    'current' => lang('Finance.currentBucket'),
    'days_1_30' => lang('Finance.days1To30'),
    'days_31_60' => lang('Finance.days31To60'),
    'days_61_90' => lang('Finance.days61To90'),
    'days_91_plus' => lang('Finance.days91Plus'),
];
?>



<div>
    <div class="finance-reports-wrap mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <section class="finance-reports-hero overflow-hidden border sm:p-6 lg:p-7">
            <div class="relative">
                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center ring-1">
                            <i class="fas fa-chart-line text-lg"></i>
                        </div>
                        <div>
                            <h1 class="v3-section-title tracking-tight"><?= lang('Finance.financialReports') ?></h1>
                            <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold">
                                <span class="px-3 py-1.5 ring-1"><?= esc($start_date) ?> - <?= esc($end_date) ?></span>
                            </div>
                        </div>
                    </div>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold ring-1 transition">
                        <i class="fas fa-arrow-left rtl-back-icon"></i>
                        <?= lang('Finance.backToFinance') ?>
                    </a>
                </div>
                <form method="GET" action="<?= base_url('finance/reports') ?>" class="finance-reports-filter relative mt-5 flex flex-wrap items-end gap-4 border px-5 py-4">
                    <div class="flex-1 min-w-[160px] max-w-xs space-y-1">
                        <label class="block text-xs font-bold"><?= lang('Finance.startDate') ?></label>
                        <input type="text" name="start_date" data-clinic-date="1" value="<?= esc($start_date) ?>" class="w-full border px-3 py-2.5 text-sm font-semibold outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
                    </div>
                    <div class="flex-1 min-w-[160px] max-w-xs space-y-1">
                        <label class="block text-xs font-bold"><?= lang('Finance.endDate') ?></label>
                        <input type="text" name="end_date" data-clinic-date="1" value="<?= esc($end_date) ?>" class="w-full border px-3 py-2.5 text-sm font-semibold outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="px-4 py-2.5 text-sm font-extrabold transition">
                            <?= lang('Finance.filter') ?>
                        </button>
                        <button type="button" onclick="exportReport()" class="border px-4 py-2.5 text-sm font-bold transition">
                            <?= lang('Finance.exportCashCsv') ?>
                        </button>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex justify-center border px-4 py-2.5 text-sm font-bold transition">
                            <?= lang('Finance.reset') ?>
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section>
            <div class="mb-4">
                <div>
                    <h2 class="text-xl"><?= lang('Finance.ownerSummaryTitle') ?></h2>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 sm:gap-6 items-stretch">
                <?php
                $summaryCards = [
                    ['icon' => 'fa-arrow-down-wide-short', 'title' => lang('Finance.moneyIn'), 'value' => $ownerSummary['money_in'] ?? 0, 'accent' => 'green'],
                    ['icon' => 'fa-arrow-up-wide-short', 'title' => lang('Finance.moneyOut'), 'value' => $ownerSummary['money_out'] ?? null, 'accent' => 'rose'],
                    ['icon' => 'fa-file-invoice-dollar', 'title' => lang('Finance.customersOweUs'), 'value' => $ownerSummary['customers_owe_us'] ?? 0, 'accent' => 'amber'],
                    ['icon' => 'fa-hand-holding-dollar', 'title' => lang('Finance.weOweOthers'), 'value' => $ownerSummary['we_owe_others'] ?? null, 'accent' => 'indigo'],
                    ['icon' => 'fa-scale-balanced', 'title' => lang('Finance.netPosition'), 'value' => $ownerSummary['net_position'] ?? null, 'accent' => 'slate'],
                ];
                $accentMap = [
                    'green' => ['card' => 'bg-gradient-to-br from-emerald-600 to-teal-800 shadow-emerald-500/25', 'badge' => 'bg-white/20 text-white ring-white/20', 'value' => 'text-white', 'label' => 'text-emerald-50/90'],
                    'rose' => ['card' => 'bg-gradient-to-br from-rose-600 to-pink-800 shadow-rose-500/25', 'badge' => 'bg-white/20 text-white ring-white/20', 'value' => 'text-white', 'label' => 'text-rose-50/90'],
                    'amber' => ['card' => 'bg-gradient-to-br from-amber-500 to-orange-700 shadow-amber-500/25', 'badge' => 'bg-white/20 text-white ring-white/20', 'value' => 'text-white', 'label' => 'text-amber-50/95'],
                    'indigo' => ['card' => 'bg-gradient-to-br from-indigo-600 to-blue-900 shadow-indigo-500/25', 'badge' => 'bg-white/20 text-white ring-white/20', 'value' => 'text-white', 'label' => 'text-indigo-50/90'],
                    'slate' => ['card' => ((float) ($ownerSummary['net_position'] ?? null)) >= 0 ? 'bg-gradient-to-br from-slate-700 to-slate-950 shadow-slate-500/25' : 'bg-gradient-to-br from-rose-700 to-slate-950 shadow-rose-500/25', 'badge' => 'bg-white/10 text-white ring-white/20', 'value' => 'text-white', 'label' => 'text-slate-100/90'],
                ];
                ?>
                <?php foreach ($summaryCards as $card): ?>
                    <?php $accent = $accentMap[$card['accent']]; ?>
                    <div class="finance-reports-summary-card relative overflow-hidden <?= esc($accent['card']) ?>">
                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_0%,rgba(255,255,255,0.22),transparent_34%)]"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wide <?= esc($accent['label']) ?>"><?= esc($card['title']) ?></p>
                                <p class="mt-3 v3-title <?= esc($accent['value']) ?>"><?= $formatMoney($card['value']) ?></p>
                            </div>
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center ring-1 <?= esc($accent['badge']) ?>">
                                <i class="fas <?= esc($card['icon']) ?> text-base"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <details class="finance-reports-panel">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b px-6 py-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl"><?= lang('Finance.apAgingDetail') ?></h2>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold ring-1"><?= esc(lang('Finance.openPayablesTop', ['count' => count($ap_aging_detail ?? [])])) ?></span>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center ring-1 transition group-open:rotate-180">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </summary>
            <div class="overflow-x-auto">
                <table class="finance-reports-table min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide">
                            <th class="px-6 py-4"><?= lang('Finance.expense') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.vendor') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.dueDate') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.daysPastDue') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-right"><?= lang('Finance.balanceDueLabel') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100">
                        <?php if (!empty($ap_aging_detail)): ?>
                            <?php foreach ($ap_aging_detail as $row): ?>
                                <tr class="cursor-pointer transition-colors duration-150" data-href="<?= esc(workflow_context_url('finance/' . (int) ($row['id'] ?? 0)), 'attr') ?>">
                                    <td class="px-6 py-4 font-semibold"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                    <td class="px-6 py-4"><?= esc($row['counterparty_name'] ?? '-') ?></td>
                                    <td class="px-6 py-4"><?= esc($row['due_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4"><?= number_format((int) ($row['days_past_due'] ?? 0)) ?></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-semibold"><?= esc($bucketLabels[$row['aging_bucket'] ?? 'current'] ?? ($row['aging_bucket'] ?? 'Current')) ?></span></td>
                                    <td class="px-6 py-4 text-right font-semibold"><?= $formatMoney($row['balance_due'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center"><?= lang('Finance.noOutstandingPayablesForDate') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </details>

        <section class="finance-reports-panel border-emerald-100">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl"><?= lang('Finance.treasuryOverviewTitle') ?></h2>
                </div>
                <a href="<?= base_url('finance/transfers') ?>" class="inline-flex items-center justify-center border border-emerald-200 px-4 py-2.5 text-sm font-bold text-emerald-800 hover:bg-emerald-50">
                    <i class="fas fa-right-left mr-2"></i><?= lang('Finance.treasuryTransfers') ?>
                </a>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="border border-emerald-200 bg-emerald-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= esc($liquidityLabels['safe_cash'] ?? lang('Finance.liquidityAccount.safe_cash')) ?></p>
                    <p class="mt-2 v3-title text-emerald-950"><?= $formatMoney($treasuryClosing['safe_cash'] ?? null) ?></p>
                </div>
                <div class="border">
                    <p class="text-xs font-semibold uppercase tracking-wide"><?= esc($liquidityLabels['bank'] ?? lang('Finance.liquidityAccount.bank')) ?></p>
                    <p class="mt-2 v3-title"><?= $formatMoney($treasuryClosing['bank'] ?? null) ?></p>
                </div>
                <div class="border border-amber-200 bg-amber-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700"><?= esc($liquidityLabels['undeposited_checks'] ?? lang('Finance.liquidityAccount.undeposited_checks')) ?></p>
                    <p class="mt-2 v3-title text-amber-950"><?= $formatMoney($treasuryClosing['undeposited_checks'] ?? null) ?></p>
                </div>
                <div class="border">
                    <p class="text-xs font-semibold uppercase tracking-wide"><?= lang('Finance.totalLiquidFunds') ?></p>
                    <p class="mt-2 v3-title"><?= $formatMoney($treasuryClosing['total_liquid_funds'] ?? null) ?></p>
                </div>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <?php
                $movementAccentMap = [
                    'safe_cash' => ['card' => 'border-emerald-200 bg-emerald-50', 'in' => 'border-emerald-200 bg-white', 'out' => 'border-rose-200 bg-white', 'net' => 'border-emerald-100 bg-emerald-100/60'],
                    'bank' => ['card' => 'border-blue-200 bg-blue-50', 'in' => 'border-blue-200 bg-white', 'out' => 'border-rose-200 bg-white', 'net' => 'border-blue-100 bg-blue-100/60'],
                    'undeposited_checks' => ['card' => 'border-amber-200 bg-amber-50', 'in' => 'border-amber-200 bg-white', 'out' => 'border-rose-200 bg-white', 'net' => 'border-amber-100 bg-amber-100/60'],
                ];
                ?>
                <?php foreach (['safe_cash', 'bank', 'undeposited_checks'] as $accountKey): ?>
                    <?php $bucket = $treasuryMovement[$accountKey] ?? ['in' => 0, 'out' => 0, 'net' => 0]; ?>
                    <?php $movementAccent = $movementAccentMap[$accountKey] ?? $movementAccentMap['safe_cash']; ?>
                    <div class="border <?= esc($movementAccent['card']) ?>">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold"><?= esc($liquidityLabels[$accountKey] ?? lang('Finance.liquidityAccount.' . $accountKey)) ?></h3>
                            <span class="px-2.5 py-1 text-[11px] font-semibold"><?= lang('Finance.periodMovement') ?></span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-lg border p-3 <?= esc($movementAccent['in']) ?>">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.inflow') ?></p>
                                <p class="mt-2 text-lg text-emerald-950"><?= $formatMoney($bucket['in'] ?? null) ?></p>
                            </div>
                            <div class="rounded-lg border p-3 <?= esc($movementAccent['out']) ?>">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.outflow') ?></p>
                                <p class="mt-2 text-lg text-rose-950"><?= $formatMoney($bucket['out'] ?? null) ?></p>
                            </div>
                            <div class="rounded-lg border p-3 <?= esc($movementAccent['net']) ?>">
                                <p class="text-[11px] font-semibold uppercase tracking-wide"><?= lang('Finance.net') ?></p>
                                <p class="mt-2 text-lg <?= ((float) ($bucket['net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= $formatMoney($bucket['net'] ?? null) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="border">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold"><?= lang('Finance.totalLiquidMovement') ?></h3>
                        <span class="px-2.5 py-1 text-[11px] font-semibold"><?= lang('Finance.selectedPeriod') ?></span>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div class="rounded-lg p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-200"><?= lang('Finance.inflow') ?></p>
                            <p class="mt-2 text-lg"><?= $formatMoney($treasuryMovement['totals']['in'] ?? null) ?></p>
                        </div>
                        <div class="rounded-lg p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-200"><?= lang('Finance.outflow') ?></p>
                            <p class="mt-2 text-lg"><?= $formatMoney($treasuryMovement['totals']['out'] ?? null) ?></p>
                        </div>
                        <div class="rounded-lg p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide"><?= lang('Finance.net') ?></p>
                            <p class="mt-2 text-lg"><?= $formatMoney($treasuryMovement['totals']['net'] ?? null) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <details class="finance-reports-panel border-amber-100">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-amber-100 px-6 py-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl"><?= lang('Finance.arAgingDetail') ?></h2>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold ring-1"><?= esc(lang('Finance.openInvoicesTop', ['count' => count($ar_aging_detail ?? [])])) ?></span>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center ring-1 transition group-open:rotate-180">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </summary>
            <div class="overflow-x-auto">
                <table class="finance-reports-table min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-amber-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide">
                            <th class="px-6 py-4"><?= lang('Finance.invoice') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.dueDate') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.daysPastDue') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-right"><?= lang('Finance.balanceDueLabel') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        <?php if (!empty($ar_aging_detail)): ?>
                            <?php foreach ($ar_aging_detail as $row): ?>
                                <tr class="cursor-pointer transition-colors duration-150 hover:bg-amber-50/70" data-href="<?= esc(workflow_context_url('finance/' . (int) ($row['id'] ?? 0)), 'attr') ?>">
                                    <td class="px-6 py-4 font-semibold"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium"><?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></div>
                                        <?php if (!empty($row['patient_number'])): ?><div class="text-xs"><?= esc($row['patient_number']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= esc($row['due_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4"><?= number_format((int) ($row['days_past_due'] ?? 0)) ?></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-semibold"><?= esc($bucketLabels[$row['aging_bucket'] ?? 'current'] ?? ($row['aging_bucket'] ?? 'Current')) ?></span></td>
                                    <td class="px-6 py-4 text-right font-semibold text-rose-700"><?= $formatMoney($row['balance_due'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center"><?= lang('Finance.noOutstandingInvoicesForDate') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </details>

        <details class="finance-reports-panel">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b px-6 py-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <h2 class="text-xl"><?= lang('Finance.unifiedFinancialLedger') ?></h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="px-3 py-1 text-xs font-bold ring-1"><?= number_format((int) ($ledger_summary['count'] ?? 0)) ?> <?= lang('Finance.externalCashEvents') ?></span>
                        <span class="bg-emerald-400/20 px-3 py-1 text-xs font-bold text-emerald-100 ring-1 ring-emerald-200/20"><?= lang('Finance.externalInflow') ?>: <?= $formatMoney($ledger_summary['inflow_total'] ?? 0) ?></span>
                        <span class="bg-rose-400/20 px-3 py-1 text-xs font-bold text-rose-100 ring-1 ring-rose-200/20"><?= lang('Finance.externalOutflow') ?>: <?= $formatMoney($ledger_summary['outflow_total'] ?? null) ?></span>
                    </div>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center ring-1 transition group-open:rotate-180">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </summary>
            <div class="overflow-x-auto">
                <table class="finance-reports-table min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide">
                            <th class="px-6 py-4"><?= lang('Finance.reportDate') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.source') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.type') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.reference') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.counterparty') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.method') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.liquidityAccount') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.recordedAt') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.original') ?></th>
                            <th class="px-6 py-4 text-right"><?= lang('Finance.amountBase') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($ledger_rows)): ?>
                            <?php foreach ($ledger_rows as $row): ?>
                                <?php
                                $sourceLabel = match ((string) ($row['event_source'] ?? '')) {
                                    'direct_transaction' => lang('Finance.directTransaction'),
                                    'invoice_settlement' => lang('Finance.invoiceSettlement'),
                                    'expense_settlement' => lang('Finance.expenseSettlement'),
                                    'provider_payout_payment' => lang('Finance.providerPayout'),
                                    'liquidity_transfer' => lang('Finance.liquidityTransfer'),
                                    default => ucfirst(str_replace('_', ' ', (string) ($row['event_source'] ?? ''))),
                                };
                                $typeLabel = match ((string) ($row['event_type'] ?? '')) {
                                    'payment' => lang('Finance.payment'),
                                    'refund' => lang('Finance.refund'),
                                    'void' => lang('Finance.void'),
                                    'expense' => lang('Finance.expenseTransaction'),
                                    'provider_payout_payment' => lang('Finance.providerPayout'),
                                    'transfer_in' => lang('Finance.transferIn'),
                                    'transfer_out' => lang('Finance.transferOut'),
                                    'vendor_refund', 'supplier_refund' => lang('Finance.vendorRefund'),
                                    'adjustment' => lang('Finance.adjustment'),
                                    default => ucfirst(str_replace('_', ' ', (string) ($row['event_type'] ?? ''))),
                                };
                                $paymentMethodKey = (string) ($row['payment_method'] ?? '');
                                $paymentMethodLabel = '-';
                                if ($paymentMethodKey !== '') {
                                    $candidate = lang('Finance.' . $paymentMethodKey);
                                    $paymentMethodLabel = $candidate !== 'Finance.' . $paymentMethodKey ? $candidate : ucfirst(str_replace('_', ' ', $paymentMethodKey));
                                }
                                $liquidityAccountLabel = '-';
                                if (!empty($row['liquidity_account'])) {
                                    $candidate = $liquidityLabels[$row['liquidity_account']] ?? lang('Finance.liquidityAccount.' . $row['liquidity_account']);
                                    $liquidityAccountLabel = is_string($candidate) ? $candidate : (string) $row['liquidity_account'];
                                }
                                $signed = (float) ($row['signed_amount_base'] ?? 0);
                                $rowHref = match (true) {
                                    !empty($row['provider_payout_id']) => workflow_context_url('finance/payouts'),
                                    (string) ($row['event_source'] ?? '') === 'liquidity_transfer' => workflow_context_url('finance/transfers'),
                                    !empty($row['finance_id']) => workflow_context_url('finance/' . (int) $row['finance_id']),
                                    default => null,
                                };
                                ?>
                                <tr class="<?= $rowHref ? 'cursor-pointer transition-colors duration-150 hover:bg-slate-50' : '' ?>"<?= $rowHref ? ' data-href="' . esc($rowHref, 'attr') . '"' : '' ?>>
                                    <td class="px-6 py-4 font-medium"><?= esc($row['report_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4"><?= esc($sourceLabel) ?></td>
                                    <td class="px-6 py-4"><?= esc($typeLabel) ?></td>
                                    <td class="px-6 py-4"><?= esc($row['reference'] ?? '-') ?></td>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($row['patient_label'])): ?>
                                            <div class="font-medium"><?= esc($row['patient_label']) ?></div>
                                            <?php if (!empty($row['patient_number'])): ?><div class="text-xs"><?= esc($row['patient_number']) ?></div><?php endif; ?>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= esc($row['counterparty'] ?? '-') ?></td>
                                    <td class="px-6 py-4"><?= esc($paymentMethodLabel) ?></td>
                                    <td class="px-6 py-4"><?= esc($liquidityAccountLabel) ?></td>
                                    <td class="px-6 py-4"><?= !empty($row['created_at']) ? esc(formatDateTime($row['created_at'])) : '-' ?></td>
                                    <td class="px-6 py-4">
                                        <?php if (isset($row['amount_original']) && $row['amount_original'] !== null): ?>
                                            <?= esc((string) ($row['currency'] ?? '')) ?> <?= number_format((float) $row['amount_original'], 2) ?>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold <?= $signed >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>">
                                        <?= ($row['event_source'] ?? '') === 'provider_payout_payment' ? esc('-' . \App\Services\ProviderCurrencySummary::native($row['amount_original'], $row['currency'])) : ($signed >= 0 ? '+' : '-') . formatCurrency(abs($signed)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="px-6 py-10 text-center"><?= lang('Finance.noFinancialEventsForPeriod') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </details>

        <details class="finance-reports-panel">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5">
                <div>
                    <h2 class="text-xl"><?= lang('Finance.detailsBreakdownTitle') ?></h2>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center ring-1 transition group-open:rotate-180">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </summary>
            <div class="border-t px-6 py-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <div class="border border-emerald-200 p-4"><p class="text-sm text-emerald-50/90"><?= lang('Finance.grossCollections') ?></p><p class="mt-2 v3-section-title"><?= $formatMoney($stats['gross_collections'] ?? 0) ?></p></div>
                    <div class="border border-rose-200 p-4"><p class="text-sm text-rose-50/90"><?= lang('Finance.refunds') ?></p><p class="mt-2 v3-section-title"><?= $formatMoney($stats['refunds_total'] ?? 0) ?></p></div>
                    <div class="border border-amber-200 p-4"><p class="text-sm text-amber-50/95"><?= lang('Finance.expensesPaid') ?></p><p class="mt-2 v3-section-title"><?= $formatMoney($stats['expenses_paid'] ?? 0) ?></p></div>
                    <div class="border p-4"><p class="text-sm"><?= lang('Finance.providerPaidCash') ?></p><p class="mt-2 v3-section-title"><?= esc(\App\Services\ProviderCurrencySummary::format($stats['provider_cash_summary'] ?? [], 'total_provider_paid_cash')) ?></p></div>
                    <div class="border p-4"><p class="text-sm"><?= lang('Finance.netClinicCash') ?></p><p class="mt-2 v3-section-title"><?= $formatMoney($stats['net_clinic_cash'] ?? null) ?></p></div>
                </div>
                <div class="grid xl:grid-cols-[1.05fr_0.95fr]">
                    <section class="border border-emerald-100">
                        <h3 class="text-lg font-bold"><?= lang('Finance.treasuryMovementTitle') ?></h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <?php foreach (['safe_cash', 'bank', 'undeposited_checks'] as $accountKey): ?>
                                <?php $bucket = $treasuryMovement[$accountKey] ?? ['in' => 0, 'out' => 0, 'net' => 0]; ?>
                                <div class="border p-4">
                                    <p class="text-sm"><?= esc($liquidityLabels[$accountKey] ?? lang('Finance.liquidityAccount.' . $accountKey)) ?></p>
                                    <div class="mt-3 space-y-2 text-sm">
                                        <div class="flex items-center justify-between"><span class="text-emerald-700"><?= lang('Finance.inflow') ?></span><span class="font-semibold text-emerald-950"><?= $formatMoney($bucket['in'] ?? null) ?></span></div>
                                        <div class="flex items-center justify-between"><span class="text-rose-700"><?= lang('Finance.outflow') ?></span><span class="font-semibold text-rose-950"><?= $formatMoney($bucket['out'] ?? null) ?></span></div>
                                        <div class="flex items-center justify-between border-t pt-2"><span class=""><?= lang('Finance.net') ?></span><span class="<?= ((float) ($bucket['net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= $formatMoney($bucket['net'] ?? null) ?></span></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <section class="border border-amber-100">
                        <h3 class="text-lg font-bold"><?= lang('Finance.accountsReceivableAging') ?></h3>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <?php foreach ($bucketLabels as $bucketKey => $bucketLabel): ?>
                                <div class="border p-4"><p class="text-xs uppercase tracking-wide text-amber-700"><?= esc($bucketLabel) ?></p><p class="mt-2 text-xl"><?= $formatMoney($ar_aging_summary[$bucketKey] ?? 0) ?></p></div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
                <section class="border">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-lg font-bold"><?= lang('Finance.accountsPayableAging') ?></h3>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="border p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide"><?= lang('Finance.expensePayables') ?></p>
                                <p class="mt-2 text-xl"><?= $formatMoney($ownerSummary['expense_payables'] ?? 0) ?></p>
                            </div>
                            <div class="border p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide"><?= lang('Finance.providerPayables') ?></p>
                                <p class="mt-2 text-xl"><?= esc(\App\Services\ProviderCurrencySummary::format($ownerSummary['provider_currency_summary'] ?? [], 'total_provider_balance')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-6">
                        <?php foreach ($bucketLabels as $bucketKey => $bucketLabel): ?>
                            <div class="border p-4"><p class="text-xs uppercase tracking-wide"><?= esc($bucketLabel) ?></p><p class="mt-2 text-xl"><?= $formatMoney($ap_aging_summary[$bucketKey] ?? 0) ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="border">
                    <h3 class="text-lg font-bold"><?= lang('Finance.monthlyCollectionsTrend') ?></h3>
                    <div class="mt-4 h-72 border p-4"><canvas id="financeRevenueChart"></canvas></div>
                </section>
            </div>
        </details>
    </div>
</div>

<script>
function exportReport() {
    const startRaw = document.querySelector('input[name=\"start_date\"]').value;
    const endRaw = document.querySelector('input[name=\"end_date\"]').value;
    const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startRaw) : startRaw;
    const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endRaw) : endRaw;
    const params = new URLSearchParams({ format: 'csv', start_date: startDate, end_date: endDate, include_all_events: '1' });
    window.location.href = `<?= base_url('finance/export') ?>?${params.toString()}`;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr[data-href]').forEach(function(row) {
        row.addEventListener('click', function(event) {
            if (event.target.closest('a, button, input, select, textarea, label')) return;
            const href = row.getAttribute('data-href');
            if (href) window.location.href = window.workflowContextUrl(href);
        });
    });

    const revenueCtx = document.getElementById('financeRevenueChart');
    if (!revenueCtx) return;

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthly_revenue_labels ?? []) ?>,
            datasets: [{
                label: <?= json_encode(lang('Finance.grossCollections')) ?>,
                data: <?= json_encode($monthly_revenue_values ?? []) ?>,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.12)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#059669'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y ?? 0;
                            return <?= json_encode($currency_symbol ?? '$') ?> + value.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return <?= json_encode($currency_symbol ?? '$') ?> + value; } },
                    grid: { color: 'rgba(15, 23, 42, 0.06)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
</div>
<?= $this->endSection() ?>
