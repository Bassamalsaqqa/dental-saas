<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
    $formatMoney = static fn($amount) => formatCurrency((float) $amount);
    $bucketLabels = [
        'current' => lang('Finance.currentBucket'),
        'days_1_30' => lang('Finance.days1To30'),
        'days_31_60' => lang('Finance.days31To60'),
        'days_61_90' => lang('Finance.days61To90'),
        'days_91_plus' => lang('Finance.days91Plus'),
    ];
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 py-6">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <section class="overflow-hidden rounded-3xl border border-white/40 bg-white/85 shadow-2xl shadow-slate-900/5 backdrop-blur-xl">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 py-6 text-white">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <div class="space-y-2">
                            <h1 class="text-3xl font-black tracking-tight"><?= lang('Finance.financialReports') ?></h1>
                            <p class="max-w-3xl text-sm text-slate-200/90"><?= lang('Finance.financialReportsHelp') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="<?= base_url('finance') ?>" class="inline-flex items-center rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <?= lang('Finance.backToFinance') ?>
                        </a>
                        <button type="button" onclick="exportReport()" class="inline-flex items-center rounded-2xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-400">
                            <i class="fas fa-file-csv mr-2"></i>
                            <?= lang('Finance.exportCashCsv') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-200/80 bg-slate-50/80 px-6 py-4">
                <form method="GET" action="<?= base_url('finance/reports') ?>" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.startDate') ?></label>
                        <input type="text" name="start_date" data-clinic-date="1" value="<?= esc($start_date) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.endDate') ?></label>
                        <input type="text" name="end_date" data-clinic-date="1" value="<?= esc($end_date) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            <i class="fas fa-filter mr-2"></i>
                            <?= lang('Finance.filter') ?>
                        </button>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-rotate-right mr-2"></i>
                            <?= lang('Finance.reset') ?>
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid gap-4 border-b border-slate-200/70 bg-emerald-50/60 px-6 py-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200 bg-white/80 p-4">
                    <p class="text-sm font-bold text-emerald-900"><?= lang('Finance.cashBasisReport') ?></p>
                    <p class="mt-1 text-sm text-slate-600"><?= lang('Finance.cashBasisReportHelp') ?></p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-white/80 p-4">
                    <p class="text-sm font-bold text-amber-900"><?= lang('Finance.accountsReceivableAging') ?></p>
                    <p class="mt-1 text-sm text-slate-600"><?= esc(lang('Finance.accountsReceivableAgingHelp', ['date' => $end_date])) ?></p>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.grossCollections') ?></p>
                        <p class="mt-3 text-3xl font-black text-emerald-950"><?= $formatMoney($stats['gross_collections'] ?? 0) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= lang('Finance.grossCollectionsHelp') ?></p>
                    </div>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.refunds') ?></p>
                        <p class="mt-3 text-3xl font-black text-rose-950"><?= $formatMoney($stats['refunds_total'] ?? 0) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= lang('Finance.refundsHelp') ?></p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700"><?= lang('Finance.expensesPaid') ?></p>
                        <p class="mt-3 text-3xl font-black text-amber-950"><?= $formatMoney($stats['expenses_paid'] ?? 0) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= lang('Finance.expensesPaidHelp') ?></p>
                    </div>
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700"><?= lang('Finance.providerPaidCash') ?></p>
                        <p class="mt-3 text-3xl font-black text-indigo-950"><?= $formatMoney($stats['provider_paid_cash_total'] ?? 0) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= lang('Finance.providerPaidCashHelp') ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-700"><?= lang('Finance.netClinicCash') ?></p>
                        <p class="mt-3 text-3xl font-black text-slate-950"><?= $formatMoney($stats['net_clinic_cash'] ?? 0) ?></p>
                        <p class="mt-2 text-sm text-slate-600"><?= lang('Finance.netClinicCashHelp') ?></p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900"><?= lang('Finance.monthlyCollectionsTrend') ?></h2>
                                <p class="mt-1 text-sm text-slate-600"><?= lang('Finance.monthlyCollectionsTrendHelp') ?></p>
                            </div>
                        </div>
                        <div class="h-72 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <canvas id="financeRevenueChart"></canvas>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-bold text-slate-900"><?= lang('Finance.accountsReceivableAging') ?></h2>
                            <p class="mt-1 text-sm text-slate-600"><?= esc(lang('Finance.accountsReceivableAgingSummaryHelp', ['date' => $end_date])) ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <?php foreach ($bucketLabels as $bucketKey => $bucketLabel): ?>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= esc($bucketLabel) ?></p>
                                    <p class="mt-2 text-xl font-black text-slate-900"><?= $formatMoney($ar_aging_summary[$bucketKey] ?? 0) ?></p>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-span-2 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.outstandingInvoices') ?></p>
                                        <p class="mt-2 text-xl font-black text-rose-950"><?= number_format((int) ($ar_aging_summary['invoice_count'] ?? 0)) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.balanceDueLabel') ?></p>
                                        <p class="mt-2 text-xl font-black text-rose-950"><?= $formatMoney($ar_aging_summary['total_balance_due'] ?? 0) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-bold text-slate-900"><?= lang('Finance.transactionSummary') ?></h2>
                            <p class="mt-1 text-sm text-slate-600"><?= lang('Finance.transactionSummaryHelp') ?></p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="py-3 pr-4"><?= lang('Finance.type') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.count') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.total') ?></th>
                                        <th class="py-3 text-right"><?= lang('Finance.average') ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!empty($stats['transaction_summary'])): ?>
                                        <?php foreach ($stats['transaction_summary'] as $summary): ?>
                                            <?php
                                                $typeKey = (string) ($summary['transaction_type'] ?? 'other');
                                                $labelMap = [
                                                    'payment' => lang('Finance.payment'),
                                                    'refund' => lang('Finance.refund'),
                                                    'void' => lang('Finance.void'),
                                                    'expense' => lang('Finance.expenseTransaction'),
                                                    'provider_payout_payment' => lang('Finance.providerPayout'),
                                                ];
                                            ?>
                                            <tr>
                                                <td class="py-3 pr-4 font-medium text-slate-900"><?= esc($labelMap[$typeKey] ?? ucfirst(str_replace('_', ' ', $typeKey))) ?></td>
                                                <td class="py-3 pr-4 text-slate-700"><?= number_format((int) ($summary['count'] ?? 0)) ?></td>
                                                <td class="py-3 pr-4 text-slate-700"><?= $formatMoney($summary['total_amount'] ?? 0) ?></td>
                                                <td class="py-3 text-right font-semibold text-slate-900"><?= $formatMoney($summary['average_amount'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-10 text-center text-slate-500"><?= lang('Finance.noTransactionsForPeriod') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900"><?= lang('Finance.arAgingDetail') ?></h2>
                                <p class="mt-1 text-sm text-slate-600"><?= lang('Finance.arAgingDetailHelp') ?></p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= esc(lang('Finance.openInvoicesTop', ['count' => count($ar_aging_detail ?? [])])) ?></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="py-3 pr-4"><?= lang('Finance.invoice') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.patient') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.dueDate') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.daysPastDue') ?></th>
                                        <th class="py-3 pr-4"><?= lang('Finance.status') ?></th>
                                        <th class="py-3 text-right"><?= lang('Finance.balanceDueLabel') ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!empty($ar_aging_detail)): ?>
                                        <?php foreach ($ar_aging_detail as $row): ?>
                                            <tr>
                                                <td class="py-3 pr-4 font-medium text-slate-900"><?= esc($row['transaction_id'] ?? ('#' . ($row['id'] ?? ''))) ?></td>
                                                <td class="py-3 pr-4 text-slate-700">
                                                    <?= esc(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?>
                                                    <?php if (!empty($row['patient_number'])): ?>
                                                        <span class="text-xs text-slate-400">(<?= esc($row['patient_number']) ?>)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 pr-4 text-slate-700"><?= esc($row['due_date'] ?? '-') ?></td>
                                                <td class="py-3 pr-4 text-slate-700"><?= number_format((int) ($row['days_past_due'] ?? 0)) ?></td>
                                                <td class="py-3 pr-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"><?= esc($bucketLabels[$row['aging_bucket'] ?? 'current'] ?? ($row['aging_bucket'] ?? 'Current')) ?></span></td>
                                                <td class="py-3 text-right font-semibold text-rose-700"><?= $formatMoney($row['balance_due'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="py-10 text-center text-slate-500"><?= lang('Finance.noOutstandingInvoicesForDate') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900"><?= lang('Finance.unifiedFinancialLedger') ?></h2>
                            <p class="mt-1 text-sm text-slate-600"><?= lang('Finance.unifiedLedgerHelp') ?></p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= number_format((int) ($ledger_summary['count'] ?? 0)) ?> <?= lang('Finance.eventsLabel') ?></span>
                    </div>

                    <div class="mb-4 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700"><?= lang('Finance.inflow') ?></p>
                            <p class="mt-2 text-2xl font-black text-emerald-950"><?= $formatMoney($ledger_summary['inflow_total'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700"><?= lang('Finance.outflow') ?></p>
                            <p class="mt-2 text-2xl font-black text-rose-950"><?= $formatMoney($ledger_summary['outflow_total'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-700"><?= lang('Finance.net') ?></p>
                            <p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney($ledger_summary['net_total'] ?? 0) ?></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="py-3 pr-4"><?= lang('Finance.reportDate') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.recordedAt') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.source') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.type') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.reference') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.patient') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.counterparty') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.method') ?></th>
                                    <th class="py-3 pr-4"><?= lang('Finance.original') ?></th>
                                    <th class="py-3 text-right"><?= lang('Finance.amountBase') ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($ledger_rows)): ?>
                                    <?php foreach ($ledger_rows as $row): ?>
                                        <?php
                                            $sourceLabel = match ((string) ($row['event_source'] ?? '')) {
                                                'direct_transaction' => lang('Finance.directTransaction'),
                                                'invoice_settlement' => lang('Finance.invoiceSettlement'),
                                                'provider_payout_payment' => lang('Finance.providerPayout'),
                                                default => ucfirst(str_replace('_', ' ', (string) ($row['event_source'] ?? ''))),
                                            };
                                            $typeLabel = match ((string) ($row['event_type'] ?? '')) {
                                                'payment' => lang('Finance.payment'),
                                                'refund' => lang('Finance.refund'),
                                                'void' => lang('Finance.void'),
                                                'expense' => lang('Finance.expenseTransaction'),
                                                'provider_payout_payment' => lang('Finance.providerPayout'),
                                                default => ucfirst(str_replace('_', ' ', (string) ($row['event_type'] ?? ''))),
                                            };
                                            $paymentMethodKey = (string) ($row['payment_method'] ?? '');
                                            $paymentMethodLabel = '-';
                                            if ($paymentMethodKey !== '') {
                                                $candidate = lang('Finance.' . $paymentMethodKey);
                                                $paymentMethodLabel = $candidate !== 'Finance.' . $paymentMethodKey
                                                    ? $candidate
                                                    : ucfirst(str_replace('_', ' ', $paymentMethodKey));
                                            }
                                            $signed = (float) ($row['signed_amount_base'] ?? 0);
                                        ?>
                                        <tr>
                                            <td class="py-3 pr-4 font-medium text-slate-900"><?= esc($row['report_date'] ?? '-') ?></td>
                                            <td class="py-3 pr-4 text-slate-600"><?= !empty($row['created_at']) ? esc(formatDateTime($row['created_at'])) : '-' ?></td>
                                            <td class="py-3 pr-4 text-slate-700"><?= esc($sourceLabel) ?></td>
                                            <td class="py-3 pr-4 text-slate-700"><?= esc($typeLabel) ?></td>
                                            <td class="py-3 pr-4 text-slate-700"><?= esc($row['reference'] ?? '-') ?></td>
                                            <td class="py-3 pr-4 text-slate-700">
                                                <?php if (!empty($row['patient_label'])): ?>
                                                    <div class="font-medium text-slate-900"><?= esc($row['patient_label']) ?></div>
                                                    <?php if (!empty($row['patient_number'])): ?><div class="text-xs text-slate-400">#<?= esc($row['patient_number']) ?></div><?php endif; ?>
                                                <?php else: ?>-
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 pr-4 text-slate-700"><?= esc($row['counterparty'] ?? '-') ?></td>
                                            <td class="py-3 pr-4 text-slate-700"><?= esc($paymentMethodLabel) ?></td>
                                            <td class="py-3 pr-4 text-slate-700">
                                                <?php if (isset($row['amount_original']) && $row['amount_original'] !== null): ?>
                                                    <?= esc((string) ($row['currency'] ?? '')) ?> <?= number_format((float) $row['amount_original'], 2) ?>
                                                <?php else: ?>-
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 text-right font-semibold <?= $signed >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>">
                                                <?= ($signed >= 0 ? '+' : '-') . formatCurrency(abs($signed)) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="py-10 text-center text-slate-500"><?= lang('Finance.noFinancialEventsForPeriod') ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>
    </div>
</div>

<script>
function exportReport() {
    const startRaw = document.querySelector('input[name="start_date"]').value;
    const endRaw = document.querySelector('input[name="end_date"]').value;
    const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startRaw) : startRaw;
    const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endRaw) : endRaw;
    const params = new URLSearchParams({
        format: 'csv',
        start_date: startDate,
        end_date: endDate,
        include_all_events: '1',
    });
    window.location.href = `<?= base_url('finance/export') ?>?${params.toString()}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const revenueCtx = document.getElementById('financeRevenueChart');
    if (!revenueCtx) {
        return;
    }

    const labels = <?= json_encode($monthly_revenue_labels ?? []) ?>;
    const dataPoints = <?= json_encode($monthly_revenue_values ?? []) ?>;
    const currencySymbol = <?= json_encode($currency_symbol ?? '$') ?>;

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: <?= json_encode(lang('Finance.grossCollections')) ?>,
                data: dataPoints,
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
                            return currencySymbol + value.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return currencySymbol + value;
                        }
                    },
                    grid: { color: 'rgba(15, 23, 42, 0.06)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
