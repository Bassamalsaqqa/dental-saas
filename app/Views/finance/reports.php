<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$formatMoney = static fn($amount) => formatCurrency((float) $amount);
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

<div class="space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">
        <section>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-chart-line text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900"><?= lang('Finance.financialReports') ?></h1>
                            <p class="text-xs text-gray-500"><?= lang('Finance.financialReportsHelp') ?></p>
                        </div>
                    </div>
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-2 rtl-back-icon"></i>
                        <?= lang('Finance.backToFinance') ?>
                    </a>
                </div>
                <form method="GET" action="<?= base_url('finance/reports') ?>" class="px-6 py-4 flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[160px] max-w-xs space-y-1">
                        <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.startDate') ?></label>
                        <input type="text" name="start_date" data-clinic-date="1" value="<?= esc($start_date) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div class="flex-1 min-w-[160px] max-w-xs space-y-1">
                        <label class="block text-xs font-medium text-gray-600"><?= lang('Finance.endDate') ?></label>
                        <input type="text" name="end_date" data-clinic-date="1" value="<?= esc($end_date) ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white rounded-lg px-4 py-2 text-sm font-medium">
                            <?= lang('Finance.filter') ?>
                        </button>
                        <button type="button" onclick="exportReport()" class="bg-white border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-50">
                            <?= lang('Finance.exportCashCsv') ?>
                        </button>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex justify-center bg-white border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-50">
                            <?= lang('Finance.reset') ?>
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section class="mb-8">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.ownerSummaryTitle') ?></h2>
                <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Finance.ownerSummaryHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date])) ?></p>
            </div>
            <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
                <span class="font-semibold text-slate-800"><?= lang('Finance.reportBasisLabel') ?>:</span>
                <?= lang('Finance.reportBasisHelp') ?>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 sm:gap-6 items-stretch">
                <?php
                $summaryCards = [
                    ['icon' => 'fa-arrow-down-wide-short', 'title' => lang('Finance.moneyIn'), 'value' => $ownerSummary['money_in'] ?? 0, 'help' => lang('Finance.moneyInHelp'), 'accent' => 'green'],
                    ['icon' => 'fa-arrow-up-wide-short', 'title' => lang('Finance.moneyOut'), 'value' => $ownerSummary['money_out'] ?? 0, 'help' => lang('Finance.moneyOutHelp'), 'accent' => 'rose'],
                    ['icon' => 'fa-file-invoice-dollar', 'title' => lang('Finance.customersOweUs'), 'value' => $ownerSummary['customers_owe_us'] ?? 0, 'help' => lang('Finance.customersOweUsHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date]), 'accent' => 'amber'],
                    ['icon' => 'fa-hand-holding-dollar', 'title' => lang('Finance.weOweOthers'), 'value' => $ownerSummary['we_owe_others'] ?? 0, 'help' => lang('Finance.weOweOthersHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date]), 'accent' => 'indigo'],
                    ['icon' => 'fa-scale-balanced', 'title' => lang('Finance.netPosition'), 'value' => $ownerSummary['net_position'] ?? 0, 'help' => lang('Finance.netPositionHelp'), 'accent' => 'slate'],
                ];
                $accentMap = [
                    'green' => ['badge' => 'bg-emerald-100 text-emerald-700', 'value' => 'text-emerald-950'],
                    'rose' => ['badge' => 'bg-rose-100 text-rose-700', 'value' => 'text-rose-950'],
                    'amber' => ['badge' => 'bg-amber-100 text-amber-700', 'value' => 'text-amber-950'],
                    'indigo' => ['badge' => 'bg-indigo-100 text-indigo-700', 'value' => 'text-indigo-950'],
                    'slate' => ['badge' => 'bg-slate-100 text-slate-700', 'value' => ((float) ($ownerSummary['net_position'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700'],
                ];
                ?>
                <?php foreach ($summaryCards as $card): ?>
                    <?php $accent = $accentMap[$card['accent']]; ?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= esc($card['title']) ?></p>
                                <p class="mt-3 text-3xl font-black <?= esc($accent['value']) ?>"><?= $formatMoney($card['value']) ?></p>
                            </div>
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl <?= esc($accent['badge']) ?>">
                                <i class="fas <?= esc($card['icon']) ?> text-base"></i>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500"><?= esc($card['help']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.apAgingDetail') ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.apAgingDetailHelp') ?></p>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800"><?= esc(lang('Finance.openPayablesTop', ['count' => count($ap_aging_detail ?? [])])) ?></span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-4"><?= lang('Finance.expense') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.vendor') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.dueDate') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.daysPastDue') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-right"><?= lang('Finance.balanceDueLabel') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php if (!empty($ap_aging_detail)): ?>
                            <?php foreach ($ap_aging_detail as $row): ?>
                                <tr class="cursor-pointer transition-colors duration-150 hover:bg-indigo-50/70" data-href="<?= esc(base_url('finance/' . (int) ($row['id'] ?? 0)), 'attr') ?>">
                                    <td class="px-6 py-4 font-semibold text-gray-900"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($row['counterparty_name'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($row['due_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= number_format((int) ($row['days_past_due'] ?? 0)) ?></td>
                                    <td class="px-6 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= esc($bucketLabels[$row['aging_bucket'] ?? 'current'] ?? ($row['aging_bucket'] ?? 'Current')) ?></span></td>
                                    <td class="px-6 py-4 text-right font-semibold text-indigo-700"><?= $formatMoney($row['balance_due'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500"><?= lang('Finance.noOutstandingPayablesForDate') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <section class="mb-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.treasuryOverviewTitle') ?></h2>
                    <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Finance.treasuryOverviewHelp', ['date' => $end_date])) ?></p>
                </div>
                <a href="<?= base_url('finance/transfers') ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-right-left mr-2"></i><?= lang('Finance.treasuryTransfers') ?>
                </a>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= esc($liquidityLabels['safe_cash'] ?? lang('Finance.liquidityAccount.safe_cash')) ?></p>
                    <p class="mt-2 text-3xl font-black text-emerald-950"><?= $formatMoney($treasuryClosing['safe_cash'] ?? 0) ?></p>
                    <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.closingSafeCashHelp') ?></p>
                </div>
                <div class="rounded-xl border border-blue-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700"><?= esc($liquidityLabels['bank'] ?? lang('Finance.liquidityAccount.bank')) ?></p>
                    <p class="mt-2 text-3xl font-black text-blue-950"><?= $formatMoney($treasuryClosing['bank'] ?? 0) ?></p>
                    <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.closingBankCashHelp') ?></p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700"><?= esc($liquidityLabels['undeposited_checks'] ?? lang('Finance.liquidityAccount.undeposited_checks')) ?></p>
                    <p class="mt-2 text-3xl font-black text-amber-950"><?= $formatMoney($treasuryClosing['undeposited_checks'] ?? 0) ?></p>
                    <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.closingChecksOnHandHelp') ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-700"><?= lang('Finance.totalLiquidFunds') ?></p>
                    <p class="mt-2 text-3xl font-black text-slate-950"><?= $formatMoney($treasuryClosing['total_liquid_funds'] ?? 0) ?></p>
                    <p class="mt-2 text-xs text-gray-500"><?= lang('Finance.totalLiquidFundsHelp') ?></p>
                </div>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <?php foreach (['safe_cash', 'bank', 'undeposited_checks'] as $accountKey): ?>
                    <?php $bucket = $treasuryMovement[$accountKey] ?? ['in' => 0, 'out' => 0, 'net' => 0]; ?>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900"><?= esc($liquidityLabels[$accountKey] ?? lang('Finance.liquidityAccount.' . $accountKey)) ?></h3>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-500"><?= lang('Finance.periodMovement') ?></span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-lg border border-emerald-200 bg-white p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.inflow') ?></p>
                                <p class="mt-2 text-lg font-black text-emerald-950"><?= $formatMoney($bucket['in'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-lg border border-rose-200 bg-white p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.outflow') ?></p>
                                <p class="mt-2 text-lg font-black text-rose-950"><?= $formatMoney($bucket['out'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-700"><?= lang('Finance.net') ?></p>
                                <p class="mt-2 text-lg font-black <?= ((float) ($bucket['net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= $formatMoney($bucket['net'] ?? 0) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="rounded-xl border border-slate-300 bg-slate-900 p-5 text-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold"><?= lang('Finance.totalLiquidMovement') ?></h3>
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-200"><?= lang('Finance.selectedPeriod') ?></span>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-200"><?= lang('Finance.inflow') ?></p>
                            <p class="mt-2 text-lg font-black"><?= $formatMoney($treasuryMovement['totals']['in'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-200"><?= lang('Finance.outflow') ?></p>
                            <p class="mt-2 text-lg font-black"><?= $formatMoney($treasuryMovement['totals']['out'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-200"><?= lang('Finance.net') ?></p>
                            <p class="mt-2 text-lg font-black"><?= $formatMoney($treasuryMovement['totals']['net'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500">
            <div class="border-b border-white/20 px-6 py-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.arAgingDetail') ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.arAgingDetailHelp') ?></p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800"><?= esc(lang('Finance.openInvoicesTop', ['count' => count($ar_aging_detail ?? [])])) ?></span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-4"><?= lang('Finance.invoice') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.patient') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.dueDate') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.daysPastDue') ?></th>
                            <th class="px-6 py-4"><?= lang('Finance.status') ?></th>
                            <th class="px-6 py-4 text-right"><?= lang('Finance.balanceDueLabel') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php if (!empty($ar_aging_detail)): ?>
                            <?php foreach ($ar_aging_detail as $row): ?>
                                <tr class="cursor-pointer transition-colors duration-150 hover:bg-amber-50/70" data-href="<?= esc(base_url('finance/' . (int) ($row['id'] ?? 0)), 'attr') ?>">
                                    <td class="px-6 py-4 font-semibold text-gray-900"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-medium text-gray-900"><?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></div>
                                        <?php if (!empty($row['patient_number'])): ?><div class="text-xs text-gray-400"><?= esc($row['patient_number']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($row['due_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= number_format((int) ($row['days_past_due'] ?? 0)) ?></td>
                                    <td class="px-6 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= esc($bucketLabels[$row['aging_bucket'] ?? 'current'] ?? ($row['aging_bucket'] ?? 'Current')) ?></span></td>
                                    <td class="px-6 py-4 text-right font-semibold text-rose-700"><?= $formatMoney($row['balance_due'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500"><?= lang('Finance.noOutstandingInvoicesForDate') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.unifiedFinancialLedger') ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.unifiedLedgerHelp') ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= number_format((int) ($ledger_summary['count'] ?? 0)) ?> <?= lang('Finance.eventsLabel') ?></span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"><?= lang('Finance.inflow') ?>: <?= $formatMoney($ledger_summary['inflow_total'] ?? 0) ?></span>
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"><?= lang('Finance.outflow') ?>: <?= $formatMoney($ledger_summary['outflow_total'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
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
                    <tbody class="divide-y divide-gray-100 bg-white">
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
                                    !empty($row['provider_payout_id']) => base_url('finance/payouts'),
                                    (string) ($row['event_source'] ?? '') === 'liquidity_transfer' => base_url('finance/transfers'),
                                    !empty($row['finance_id']) => base_url('finance/' . (int) $row['finance_id']),
                                    default => null,
                                };
                                ?>
                                <tr class="<?= $rowHref ? 'cursor-pointer transition-colors duration-150 hover:bg-slate-50' : '' ?>"<?= $rowHref ? ' data-href="' . esc($rowHref, 'attr') . '"' : '' ?>>
                                    <td class="px-6 py-4 font-medium text-gray-900"><?= esc($row['report_date'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($sourceLabel) ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($typeLabel) ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($row['reference'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?php if (!empty($row['patient_label'])): ?>
                                            <div class="font-medium text-gray-900"><?= esc($row['patient_label']) ?></div>
                                            <?php if (!empty($row['patient_number'])): ?><div class="text-xs text-gray-400"><?= esc($row['patient_number']) ?></div><?php endif; ?>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($row['counterparty'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($paymentMethodLabel) ?></td>
                                    <td class="px-6 py-4 text-gray-700"><?= esc($liquidityAccountLabel) ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?= !empty($row['created_at']) ? esc(formatDateTime($row['created_at'])) : '-' ?></td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?php if (isset($row['amount_original']) && $row['amount_original'] !== null): ?>
                                            <?= esc((string) ($row['currency'] ?? '')) ?> <?= number_format((float) $row['amount_original'], 2) ?>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold <?= $signed >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>">
                                        <?= ($signed >= 0 ? '+' : '-') . formatCurrency(abs($signed)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="px-6 py-10 text-center text-gray-500"><?= lang('Finance.noFinancialEventsForPeriod') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <details class="group rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-5">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.detailsBreakdownTitle') ?></h2>
                    <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.detailsBreakdownHelp') ?></p>
                </div>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition-colors group-open:bg-green-100 group-open:text-green-600">
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 group-open:rotate-180"></i>
                </span>
            </summary>
            <div class="space-y-6 border-t border-gray-200 px-6 py-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.grossCollections') ?></p><p class="mt-2 text-2xl font-black text-emerald-950"><?= $formatMoney($stats['gross_collections'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.refunds') ?></p><p class="mt-2 text-2xl font-black text-rose-950"><?= $formatMoney($stats['refunds_total'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.expensesPaid') ?></p><p class="mt-2 text-2xl font-black text-amber-950"><?= $formatMoney($stats['expenses_paid'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.providerPaidCash') ?></p><p class="mt-2 text-2xl font-black text-indigo-950"><?= $formatMoney($stats['provider_paid_cash_total'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.netClinicCash') ?></p><p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney($stats['net_clinic_cash'] ?? 0) ?></p></div>
                </div>
                <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <section class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-lg font-bold text-gray-900"><?= lang('Finance.treasuryMovementTitle') ?></h3>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.treasuryMovementHelp') ?></p>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <?php foreach (['safe_cash', 'bank', 'undeposited_checks'] as $accountKey): ?>
                                <?php $bucket = $treasuryMovement[$accountKey] ?? ['in' => 0, 'out' => 0, 'net' => 0]; ?>
                                <div class="rounded-lg border border-gray-200 bg-white p-4">
                                    <p class="text-sm font-semibold text-gray-700"><?= esc($liquidityLabels[$accountKey] ?? lang('Finance.liquidityAccount.' . $accountKey)) ?></p>
                                    <div class="mt-3 space-y-2 text-sm">
                                        <div class="flex items-center justify-between"><span class="text-emerald-700"><?= lang('Finance.inflow') ?></span><span class="font-semibold text-emerald-950"><?= $formatMoney($bucket['in'] ?? 0) ?></span></div>
                                        <div class="flex items-center justify-between"><span class="text-rose-700"><?= lang('Finance.outflow') ?></span><span class="font-semibold text-rose-950"><?= $formatMoney($bucket['out'] ?? 0) ?></span></div>
                                        <div class="flex items-center justify-between border-t border-gray-100 pt-2"><span class="text-gray-600"><?= lang('Finance.net') ?></span><span class="font-black <?= ((float) ($bucket['net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= $formatMoney($bucket['net'] ?? 0) ?></span></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <section class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-lg font-bold text-gray-900"><?= lang('Finance.accountsReceivableAging') ?></h3>
                        <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Finance.accountsReceivableAgingSummaryHelp', ['date' => $end_date])) ?></p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <?php foreach ($bucketLabels as $bucketKey => $bucketLabel): ?>
                                <div class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= esc($bucketLabel) ?></p><p class="mt-2 text-xl font-black text-gray-900"><?= $formatMoney($ar_aging_summary[$bucketKey] ?? 0) ?></p></div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
                <section class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><?= lang('Finance.accountsPayableAging') ?></h3>
                            <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Finance.accountsPayableAgingSummaryHelp', ['date' => $end_date])) ?></p>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="rounded-lg border border-indigo-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700"><?= lang('Finance.expensePayables') ?></p>
                                <p class="mt-2 text-xl font-black text-indigo-950"><?= $formatMoney($ownerSummary['expense_payables'] ?? 0) ?></p>
                            </div>
                            <div class="rounded-lg border border-blue-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700"><?= lang('Finance.providerPayables') ?></p>
                                <p class="mt-2 text-xl font-black text-blue-950"><?= $formatMoney($ownerSummary['provider_payables'] ?? 0) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-6">
                        <?php foreach ($bucketLabels as $bucketKey => $bucketLabel): ?>
                            <div class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= esc($bucketLabel) ?></p><p class="mt-2 text-xl font-black text-gray-900"><?= $formatMoney($ap_aging_summary[$bucketKey] ?? 0) ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-lg font-bold text-gray-900"><?= lang('Finance.monthlyCollectionsTrend') ?></h3>
                    <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.monthlyCollectionsTrendHelp') ?></p>
                    <div class="mt-4 h-72 rounded-xl border border-gray-200 bg-white p-4"><canvas id="financeRevenueChart"></canvas></div>
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
            if (href) window.location.href = href;
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
<?= $this->endSection() ?>
