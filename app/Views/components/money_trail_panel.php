<?php
$trail = $money_trail ?? [];
$summary = $trail['summary'] ?? [];
$financeRows = $trail['finance_rows'] ?? [];
$paymentRows = $trail['payment_rows'] ?? [];
$payoutRows = $trail['provider_payouts'] ?? [];
$inventoryRows = $trail['inventory_usage_rows'] ?? [];
$hasRecords = !empty($trail['has_records']);
$isRtl = !empty($is_rtl);
$returnTo = $return_to ?? null;

$moneyLabel = static fn(string $key): string => lang('UI.' . $key);
$formatMoney = static fn($value): string => formatCurrency((float) ($value ?? 0));
$financeType = static function (?string $type): string {
    $type = trim((string) $type);
    if ($type === '') {
        return lang('UI.patientNa');
    }
    $label = lang('Finance.' . $type);
    return $label === 'Finance.' . $type ? ucfirst(str_replace('_', ' ', $type)) : $label;
};
$providerName = static function (array $row): string {
    $name = trim((string) (($row['provider_first_name'] ?? '') . ' ' . ($row['provider_last_name'] ?? '')));
    if ($name !== '') {
        return $name;
    }
    $snapshot = trim((string) ($row['provider_name_snapshot'] ?? ''));
    return $snapshot !== '' ? $snapshot : lang('UI.moneyTrailProvider');
};
$stats = [
    ['key' => 'charged', 'label' => $moneyLabel('moneyTrailCharged'), 'value' => $summary['charged'] ?? 0, 'tone' => 'slate'],
    ['key' => 'paid', 'label' => $moneyLabel('moneyTrailPaid'), 'value' => $summary['paid'] ?? 0, 'tone' => 'emerald'],
    ['key' => 'balance_due', 'label' => $moneyLabel('moneyTrailBalanceDue'), 'value' => $summary['balance_due'] ?? 0, 'tone' => ((float) ($summary['balance_due'] ?? 0) > 0.009 ? 'amber' : 'sky')],
    ['key' => 'provider_remaining', 'label' => $moneyLabel('moneyTrailProviderRemaining'), 'value' => $summary['provider_remaining'] ?? 0, 'tone' => ((float) ($summary['provider_remaining'] ?? 0) > 0.009 ? 'violet' : 'slate')],
];
if ((float) ($summary['inventory_cost'] ?? 0) > 0.009) {
    $stats[] = ['key' => 'inventory_cost', 'label' => $moneyLabel('moneyTrailInventoryCost'), 'value' => $summary['inventory_cost'], 'tone' => 'rose'];
}
$toneClasses = [
    'slate' => 'border-slate-200 bg-slate-50 text-slate-800',
    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
    'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
    'sky' => 'border-sky-200 bg-sky-50 text-sky-800',
    'violet' => 'border-violet-200 bg-violet-50 text-violet-800',
    'rose' => 'border-rose-200 bg-rose-50 text-rose-800',
];
?>

<section class="rounded-2xl border border-emerald-100 bg-white shadow-sm overflow-hidden" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="border-b border-emerald-100 bg-gradient-to-r from-emerald-50 via-white to-sky-50 px-4 py-3 sm:px-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900"><?= esc($moneyLabel('moneyTrailTitle')) ?></h3>
                <p class="mt-0.5 text-xs text-slate-500"><?= esc($moneyLabel('moneyTrailSubtitle')) ?></p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700">
                <i class="fas fa-link"></i>
                <span><?= esc($moneyLabel('moneyTrailLinkedRecords')) ?></span>
            </span>
        </div>
    </div>

    <div class="p-4 sm:p-5">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php foreach ($stats as $stat): ?>
                <div class="rounded-xl border p-3 <?= esc($toneClasses[$stat['tone']] ?? $toneClasses['slate']) ?>">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-wide opacity-75"><?= esc($stat['label']) ?></p>
                    <p class="mt-1 text-lg font-extrabold" dir="ltr"><?= esc($formatMoney($stat['value'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$hasRecords): ?>
            <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center">
                <i class="fas fa-route mb-2 text-2xl text-slate-300"></i>
                <p class="text-sm font-semibold text-slate-700"><?= esc($moneyLabel('moneyTrailEmpty')) ?></p>
                <p class="mt-1 text-xs text-slate-500"><?= esc($moneyLabel('moneyTrailEmptyHint')) ?></p>
            </div>
        <?php else: ?>
            <div class="mt-5 grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <h4 class="text-sm font-bold text-slate-900"><?= esc($moneyLabel('moneyTrailFinanceRecords')) ?></h4>
                    <div class="mt-3 space-y-2">
                        <?php if (!empty($financeRows)): ?>
                            <?php foreach (array_slice($financeRows, 0, 6) as $row): ?>
                                <?php
                                $financeId = (int) ($row['id'] ?? 0);
                                $reference = (string) ($row['transaction_id'] ?? ('#' . $financeId));
                                $href = $financeId > 0 ? workflow_context_url('finance/' . $financeId, $returnTo) : '#';
                                ?>
                                <a href="<?= esc($href) ?>" class="flex items-center justify-between gap-3 rounded-lg border border-white bg-white px-3 py-2 text-sm shadow-sm hover:border-emerald-200 hover:bg-emerald-50/60">
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-800"><?= esc($reference) ?> · <?= esc($financeType($row['transaction_type'] ?? '')) ?></span>
                                        <span class="block truncate text-xs text-slate-500"><?= esc($row['description'] ?? $row['service_type'] ?? lang('UI.patientNa')) ?></span>
                                    </span>
                                    <span class="shrink-0 font-bold text-slate-900" dir="ltr"><?= esc($formatMoney($row['total_amount'] ?? $row['amount'] ?? 0)) ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-slate-500"><?= esc($moneyLabel('moneyTrailNoFinanceRecords')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <h4 class="text-sm font-bold text-slate-900"><?= esc($moneyLabel('moneyTrailPaymentsAndProviders')) ?></h4>
                    <div class="mt-3 space-y-2">
                        <?php foreach (array_slice($paymentRows, 0, 4) as $row): ?>
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-white bg-white px-3 py-2 text-sm shadow-sm">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-slate-800"><?= esc($financeType($row['type'] ?? 'payment')) ?> · <?= esc($row['transaction_id'] ?? '') ?></span>
                                    <span class="block truncate text-xs text-slate-500"><?= !empty($row['payment_date']) ? formatDate($row['payment_date']) : esc(lang('UI.patientNa')) ?></span>
                                </span>
                                <span class="shrink-0 font-bold text-emerald-700" dir="ltr"><?= esc($formatMoney($row['amount'] ?? 0)) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($payoutRows, 0, 4) as $row): ?>
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-white bg-white px-3 py-2 text-sm shadow-sm">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-slate-800"><?= esc($providerName($row)) ?></span>
                                    <span class="block truncate text-xs text-slate-500"><?= esc($moneyLabel('moneyTrailProviderShare')) ?> · <?= esc($row['status'] ?? '') ?></span>
                                </span>
                                <span class="shrink-0 font-bold text-violet-700" dir="ltr"><?= esc($formatMoney($row['provider_share_amount'] ?? 0)) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($paymentRows) && empty($payoutRows)): ?>
                            <p class="text-sm text-slate-500"><?= esc($moneyLabel('moneyTrailNoPaymentsOrProviders')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($inventoryRows)): ?>
                <div class="mt-4 rounded-xl border border-rose-100 bg-rose-50/60 p-4">
                    <h4 class="text-sm font-bold text-rose-900"><?= esc($moneyLabel('moneyTrailInventoryUsage')) ?></h4>
                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                        <?php foreach (array_slice($inventoryRows, 0, 4) as $row): ?>
                            <div class="rounded-lg border border-white bg-white px-3 py-2 text-sm shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="truncate font-semibold text-slate-800"><?= esc($row['treatment_name'] ?? $row['treatment_number'] ?? lang('UI.patientNa')) ?></span>
                                    <span class="font-bold text-rose-700" dir="ltr"><?= esc($formatMoney($row['total_cost'] ?? 0)) ?></span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500"><?= !empty($row['usage_date']) ? formatDate($row['usage_date']) : esc(lang('UI.patientNa')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
