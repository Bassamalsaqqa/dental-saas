<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$formatMoney = static fn($amount) => formatCurrency((float) $amount);
$ownerSummary = $owner_summary ?? [];
$bucketLabels = [
    'current' => lang('Finance.currentBucket'),
    'days_1_30' => lang('Finance.days1To30'),
    'days_31_60' => lang('Finance.days31To60'),
    'days_61_90' => lang('Finance.days61To90'),
    'days_91_plus' => lang('Finance.days91Plus'),
];
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden py-6">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-green-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>
    <div class="container mx-auto relative z-10 px-4 py-6 space-y-8">
        <section class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg">
            <div class="px-6 py-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900"><?= lang('Finance.financialReports') ?></h1>
                            <p class="mt-2 max-w-3xl text-sm text-gray-600"><?= lang('Finance.financialReportsHelp') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2 rtl-back-icon"></i>
                            <?= lang('Finance.backToFinance') ?>
                        </a>
                        <button type="button" onclick="exportReport()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-semibold rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-file-csv mr-2"></i>
                            <?= lang('Finance.exportCashCsv') ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/20 px-6 py-5">
                <form method="GET" action="<?= base_url('finance/reports') ?>" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700"><?= lang('Finance.startDate') ?></label>
                        <input type="text" name="start_date" data-clinic-date="1" value="<?= esc($start_date) ?>" class="w-full rounded-xl border border-white/30 bg-white/80 px-4 py-3 text-sm text-gray-900 shadow-lg focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700"><?= lang('Finance.endDate') ?></label>
                        <input type="text" name="end_date" data-clinic-date="1" value="<?= esc($end_date) ?>" class="w-full rounded-xl border border-white/30 bg-white/80 px-4 py-3 text-sm text-gray-900 shadow-lg focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/20">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-semibold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-filter mr-2"></i>
                            <?= lang('Finance.filter') ?>
                        </button>
                        <a href="<?= base_url('finance/reports') ?>" class="inline-flex items-center px-4 py-3 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-rotate-right mr-2"></i>
                            <?= lang('Finance.reset') ?>
                        </a>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    <span class="font-semibold"><?= lang('Finance.cashBasisReport') ?>:</span>
                    <?= lang('Finance.cashBasisReportHelp') ?>
                </div>
            </div>
        </section>

        <section class="mb-8">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.ownerSummaryTitle') ?></h2>
                <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Finance.ownerSummaryHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date])) ?></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-stretch">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-arrow-down-wide-short text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.moneyIn') ?></p>
                            <p class="text-3xl font-black text-green-900"><?= $formatMoney($ownerSummary['money_in'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.moneyInHelp') ?></p>
                        </div>
                    </div>
                </div>
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-rose-500/10 group-hover:shadow-rose-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-rose-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-rose-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-arrow-up-wide-short text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.moneyOut') ?></p>
                            <p class="text-3xl font-black text-rose-900"><?= $formatMoney($ownerSummary['money_out'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.moneyOutHelp') ?></p>
                        </div>
                    </div>
                </div>
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-yellow-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.customersOweUs') ?></p>
                            <p class="text-3xl font-black text-amber-900"><?= $formatMoney($ownerSummary['customers_owe_us'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('Finance.customersOweUsHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date])) ?></p>
                        </div>
                    </div>
                </div>
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-blue-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-hand-holding-dollar text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.weOweOthers') ?></p>
                            <p class="text-3xl font-black text-indigo-900"><?= $formatMoney($ownerSummary['we_owe_others'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('Finance.weOweOthersHelp', ['date' => $ownerSummary['as_of_date'] ?? $end_date])) ?></p>
                        </div>
                    </div>
                </div>
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-500/20 to-gray-700/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-700 to-slate-900 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-scale-balanced text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= lang('Finance.netPosition') ?></p>
                            <p class="text-3xl font-black <?= ((float) ($ownerSummary['net_position'] ?? 0)) >= 0 ? 'text-slate-900' : 'text-rose-700' ?>"><?= $formatMoney($ownerSummary['net_position'] ?? 0) ?></p>
                            <p class="text-xs text-gray-500 font-medium"><?= lang('Finance.netPositionHelp') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500">
            <div class="border-b border-white/20 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
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

        <section class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-gray-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-500">
            <div class="border-b border-white/20 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.unifiedFinancialLedger') ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.unifiedLedgerHelp') ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
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
                                    $paymentMethodLabel = $candidate !== 'Finance.' . $paymentMethodKey ? $candidate : ucfirst(str_replace('_', ' ', $paymentMethodKey));
                                }
                                $signed = (float) ($row['signed_amount_base'] ?? 0);
                                $rowHref = !empty($row['provider_payout_id'])
                                    ? base_url('finance/payouts')
                                    : (!empty($row['finance_id']) ? base_url('finance/' . (int) $row['finance_id']) : null);
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
                            <tr><td colspan="10" class="px-6 py-10 text-center text-gray-500"><?= lang('Finance.noFinancialEventsForPeriod') ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <details class="group rounded-xl border border-gray-200 bg-white shadow-lg">
            <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-5">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= lang('Finance.detailsBreakdownTitle') ?></h2>
                    <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.detailsBreakdownHelp') ?></p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    <i class="fas fa-chevron-down transition-transform duration-200 group-open:rotate-180"></i>
                </span>
            </summary>
            <div class="space-y-6 border-t border-gray-200 px-6 py-6">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.grossCollections') ?></p><p class="mt-2 text-2xl font-black text-emerald-950"><?= $formatMoney($stats['gross_collections'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.refunds') ?></p><p class="mt-2 text-2xl font-black text-rose-950"><?= $formatMoney($stats['refunds_total'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.expensesPaid') ?></p><p class="mt-2 text-2xl font-black text-amber-950"><?= $formatMoney($stats['expenses_paid'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.providerPaidCash') ?></p><p class="mt-2 text-2xl font-black text-indigo-950"><?= $formatMoney($stats['provider_paid_cash_total'] ?? 0) ?></p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.netClinicCash') ?></p><p class="mt-2 text-2xl font-black text-slate-950"><?= $formatMoney($stats['net_clinic_cash'] ?? 0) ?></p></div>
                </div>
                <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <section class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-lg font-bold text-gray-900"><?= lang('Finance.safeCashMovementTitle') ?></h3>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Finance.safeCashMovementHelp') ?></p>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-emerald-200 bg-white p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.safeCashIn') ?></p><p class="mt-2 text-2xl font-black text-emerald-950"><?= $formatMoney($stats['safe_cash_in'] ?? 0) ?></p></div>
                            <div class="rounded-lg border border-rose-200 bg-white p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.safeCashOut') ?></p><p class="mt-2 text-2xl font-black text-rose-950"><?= $formatMoney($stats['safe_cash_out'] ?? 0) ?></p></div>
                            <div class="rounded-lg border border-slate-200 bg-white p-4"><p class="text-sm font-semibold text-gray-700"><?= lang('Finance.safeCashNet') ?></p><p class="mt-2 text-2xl font-black <?= ((float) ($stats['safe_cash_net'] ?? 0)) >= 0 ? 'text-slate-950' : 'text-rose-700' ?>"><?= $formatMoney($stats['safe_cash_net'] ?? 0) ?></p></div>
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
