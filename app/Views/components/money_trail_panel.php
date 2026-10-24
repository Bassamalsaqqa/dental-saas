<?php
$trail = $money_trail ?? [];
$summary = $trail['summary'] ?? [];
$chargeLines = $trail['charge_lines'] ?? [];
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
$financeRowAmount = static function (array $row): float {
    return isset($row['total_amount']) ? (float) $row['total_amount'] : (float) ($row['amount'] ?? 0);
};
$paymentVerb = static function (?string $type): string {
    $type = trim((string) $type);
    if ($type === '') {
        $type = 'payment';
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
$providerStatusLabel = static function (?string $status): string {
    $status = trim((string) $status);
    if ($status === '') {
        return lang('UI.patientNa');
    }

    $label = lang('Finance.' . $status);
    return $label === 'Finance.' . $status ? ucfirst(str_replace('_', ' ', $status)) : $label;
};
$providerStatusClass = static function (?string $status): string {
    return match (trim((string) $status)) {
        'paid' => 'v3-bg-paid v3-text-primary',
        'partial' => 'v3-bg-waiting v3-text-warning',
        'void' => 'v3-bg-soft v3-text-muted',
        default => 'v3-bg-alert v3-text-danger',
    };
};
$stats = [
    ['key' => 'captured_charges', 'label' => $moneyLabel('moneyTrailCapturedWork'), 'value' => $summary['captured_charges'] ?? 0, 'tone' => 'indigo'],
    ['key' => 'charged', 'label' => $moneyLabel('moneyTrailCharged'), 'value' => $summary['charged'] ?? 0, 'tone' => 'slate'],
    ['key' => 'paid', 'label' => $moneyLabel('moneyTrailPaid'), 'value' => $summary['paid'] ?? 0, 'tone' => 'emerald'],
    ['key' => 'balance_due', 'label' => $moneyLabel('moneyTrailBalanceDue'), 'value' => $summary['balance_due'] ?? 0, 'tone' => ((float) ($summary['balance_due'] ?? 0) > 0.009 ? 'amber' : 'sky')],
    ['key' => 'provider_remaining', 'label' => $moneyLabel('moneyTrailProviderRemaining'), 'value' => $summary['provider_remaining'] ?? 0, 'tone' => ((float) ($summary['provider_remaining'] ?? 0) > 0.009 ? 'violet' : 'slate')],
];
if ((float) ($summary['inventory_cost'] ?? 0) > 0.009) {
    $stats[] = ['key' => 'inventory_cost', 'label' => $moneyLabel('moneyTrailInventoryCost'), 'value' => $summary['inventory_cost'], 'tone' => 'rose'];
}
$toneClasses = [
    'slate' => 'v3-border-default v3-bg-panel v3-text-ink',
    'emerald' => 'v3-border-default v3-bg-paid v3-text-primary',
    'amber' => 'v3-border-default v3-bg-waiting v3-text-warning',
    'sky' => 'v3-border-default v3-bg-scheduled v3-text-primary',
    'violet' => 'v3-border-default v3-bg-chair v3-text-chair',
    'rose' => 'v3-border-default v3-bg-alert v3-text-danger',
    'indigo' => 'v3-border-default v3-bg-chair v3-text-chair',
];
?>

<section class="v3-radius border v3-border-default v3-bg-panel v3-elevation-none overflow-hidden" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="border-b v3-border-default bg-gradient-to-r from-emerald-50 via-white to-sky-50 px-4 py-3 sm:px-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold v3-text-ink"><?= esc($moneyLabel('moneyTrailTitle')) ?></h3>
                <p class="mt-0.5 text-xs v3-text-muted"><?= esc($moneyLabel('moneyTrailSubtitle')) ?></p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border v3-border-default v3-bg-panel px-3 py-1 text-xs font-semibold v3-text-primary">
                <i class="fas fa-link"></i>
                <span><?= esc($moneyLabel('moneyTrailLinkedRecords')) ?></span>
            </span>
        </div>
    </div>

    <div class="p-4 sm:p-5">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?php foreach ($stats as $stat): ?>
                <div class="v3-radius border p-3 <?= esc($toneClasses[$stat['tone']] ?? $toneClasses['slate']) ?>">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-wide opacity-75"><?= esc($stat['label']) ?></p>
                    <p class="mt-1 text-lg font-extrabold" dir="ltr"><?= esc($stat['key'] === 'provider_remaining' ? \App\Services\ProviderCurrencySummary::format($summary['provider_currency_summary'], 'provider_remaining') : $formatMoney($stat['value'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($payoutRows !== []): ?>
            <p class="mt-2 text-xs v3-text-muted"><?= esc(lang('Finance.providerVoidBalanceHelp') . ' ' . lang('Finance.providerCurrencyWarning')) ?></p>
        <?php endif; ?>

        <?php if (!$hasRecords): ?>
            <div class="mt-4 v3-radius border border-dashed v3-border-default v3-bg-panel px-4 py-5 text-center">
                <i class="fas fa-route mb-2 text-2xl v3-text-muted"></i>
                <p class="text-sm font-semibold v3-text-ink"><?= esc($moneyLabel('moneyTrailEmpty')) ?></p>
                <p class="mt-1 text-xs v3-text-muted"><?= esc($moneyLabel('moneyTrailEmptyHint')) ?></p>
            </div>
        <?php else: ?>
            <div class="mt-5 grid gap-4 xl:grid-cols-2">
                <?php if (!empty($chargeLines)): ?>
                <div class="v3-radius border v3-border-default v3-bg-chair p-4 xl:col-span-2">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h4 class="text-sm font-bold v3-text-chair"><?= esc($moneyLabel('moneyTrailChargeLines')) ?></h4>
                        <?php if ((int) ($summary['open_charge_lines'] ?? 0) > 0): ?>
                            <span class="text-xs font-semibold v3-text-chair"><?= esc(lang('UI.moneyTrailOpenChargeLines', ['count' => (int) $summary['open_charge_lines']])) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                        <?php foreach (array_slice($chargeLines, 0, 6) as $row): ?>
                            <?php
                            $chargeFinanceId = (int) ($row['finance_id'] ?? 0);
                            $chargeTransactionId = trim((string) ($row['transaction_id'] ?? ''));
                            $chargeInvoiceHref = $chargeFinanceId > 0 ? workflow_context_url('finance/' . $chargeFinanceId, $returnTo) : '';
                            ?>
                            <div class="flex flex-col gap-2 rounded-lg border v3-border-default v3-bg-panel px-3 py-2 text-sm v3-elevation-none">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold v3-text-ink"><?= esc($row['procedure_name'] ?? lang('UI.patientNa')) ?></span>
                                        <?php if ($chargeInvoiceHref !== ''): ?>
                                            <a href="<?= esc($chargeInvoiceHref) ?>" class="mt-1 inline-flex items-center gap-1 rounded-full border v3-border-default v3-bg-paid px-2 py-0.5 text-[11px] font-semibold v3-text-primary v3-hover-primary v3-btn v3-btn--secondary">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                                <span><?= esc(lang('Finance.invoice')) ?> <?= esc($chargeTransactionId !== '' ? $chargeTransactionId : ('#' . $chargeFinanceId)) ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <span class="block truncate text-xs v3-text-muted"><?= esc($row['status'] ?? '') ?><?= !empty($row['transaction_id']) ? ' · ' . esc($row['transaction_id']) : '' ?></span>
                                    </span>
                                    <span class="shrink-0 font-bold v3-text-chair" dir="ltr"><?= esc($formatMoney($row['net_production_amount'] ?? 0)) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="v3-radius border v3-border-default v3-bg-panel p-4">
                    <h4 class="text-sm font-bold v3-text-ink"><?= esc($moneyLabel('moneyTrailFinanceRecords')) ?></h4>
                    <div class="mt-3 space-y-2">
                        <?php if (!empty($financeRows)): ?>
                            <?php foreach (array_slice($financeRows, 0, 6) as $row): ?>
                                <?php
                                $financeId = (int) ($row['id'] ?? 0);
                                $reference = (string) ($row['transaction_id'] ?? ('#' . $financeId));
                                $href = $financeId > 0 ? workflow_context_url('finance/' . $financeId, $returnTo) : '#';
                                $rowTotal = $financeRowAmount($row);
                                $rowPaid = max(0, (float) ($row['amount_paid'] ?? 0));
                                $rowBalance = max(0, (float) ($row['balance_due'] ?? 0));
                                ?>
                                <a href="<?= esc($href) ?>" class="flex items-center justify-between gap-3 rounded-lg border v3-border-default v3-bg-panel px-3 py-2 text-sm v3-elevation-none v3-border-default v3-hover-primary">
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold v3-text-ink"><?= esc($reference) ?> · <?= esc($financeType($row['transaction_type'] ?? '')) ?></span>
                                        <span class="block truncate text-xs v3-text-muted"><?= esc($row['description'] ?? $row['service_type'] ?? lang('UI.patientNa')) ?></span>
                                        <?php if (($row['transaction_type'] ?? '') === 'invoice'): ?>
                                            <span class="mt-1 block truncate text-[11px] font-semibold <?= $rowBalance > 0.009 ? 'v3-text-warning' : 'v3-text-primary' ?>">
                                                <?= esc($moneyLabel('moneyTrailPaid')) ?> <?= esc($formatMoney($rowPaid)) ?>
                                                · <?= esc($moneyLabel('moneyTrailBalanceDue')) ?> <?= esc($formatMoney($rowBalance)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="shrink-0 font-bold v3-text-ink" dir="ltr"><?= esc($formatMoney($rowTotal)) ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm v3-text-muted"><?= esc($moneyLabel('moneyTrailNoFinanceRecords')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="v3-radius border v3-border-default v3-bg-panel p-4">
                    <h4 class="text-sm font-bold v3-text-ink"><?= esc($moneyLabel('moneyTrailPaymentsAndProviders')) ?></h4>
                    <div class="mt-3 space-y-2">
                        <?php foreach (array_slice($paymentRows, 0, 4) as $row): ?>
                            <?php
                            $paymentType = strtolower((string) ($row['type'] ?? 'payment'));
                            $paymentTone = in_array($paymentType, ['refund', 'void'], true) ? 'v3-text-danger' : 'v3-text-primary';
                            ?>
                            <div class="flex items-center justify-between gap-3 rounded-lg border v3-border-default v3-bg-panel px-3 py-2 text-sm v3-elevation-none">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold v3-text-ink"><?= esc($financeType($row['type'] ?? 'payment')) ?> · <?= esc($row['transaction_id'] ?? '') ?></span>
                                    <span class="block truncate text-xs v3-text-muted">
                                        <?= !empty($row['payment_date']) ? formatDate($row['payment_date']) : esc(lang('UI.patientNa')) ?>
                                        <?php if (!empty($row['payment_method'])): ?>
                                            · <?= esc($paymentVerb($row['payment_method'])) ?>
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <span class="shrink-0 font-bold <?= esc($paymentTone) ?>" dir="ltr"><?= esc($formatMoney($row['amount'] ?? 0)) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($payoutRows, 0, 4) as $row): ?>
                            <?php
                            $providerFinanceId = (int) ($row['finance_id'] ?? 0);
                            $providerTransactionId = trim((string) ($row['transaction_id'] ?? ''));
                            $providerHref = $providerFinanceId > 0 ? workflow_context_url('finance/' . $providerFinanceId, $returnTo) : '';
                            ?>
                            <div class="flex flex-col gap-2 rounded-lg border v3-border-default v3-bg-panel px-3 py-2 text-sm v3-elevation-none">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold v3-text-ink"><?= esc($providerName($row)) ?></span>
                                    <span class="mt-1 inline-flex w-fit items-center rounded-full px-2 py-0.5 text-[11px] font-bold <?= esc($providerStatusClass($row['status'] ?? '')) ?>"><?= esc($providerStatusLabel($row['status'] ?? '')) ?></span>
                                    <span class="block truncate text-xs v3-text-muted"><?= esc($moneyLabel('moneyTrailProviderShare')) ?></span>
                                </span>
                                <span class="shrink-0 font-bold v3-text-chair" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_share_amount'] ?? 0, $row['currency'] ?? null)) ?></span>
                                <div class="mt-2 grid gap-1 text-xs v3-text-muted sm:grid-cols-2">
                                    <span><?= esc($moneyLabel('moneyTrailPaid')) ?>: <strong class="v3-text-primary" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_paid_amount'] ?? 0, $row['currency'] ?? null)) ?></strong></span>
                                    <span><?= esc($moneyLabel('moneyTrailProviderRemaining')) ?>: <strong class="<?= ((float) ($row['provider_balance_due'] ?? 0) > 0.009) ? 'v3-text-danger' : 'v3-text-primary' ?>" dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_balance_due'] ?? 0, $row['currency'] ?? null)) ?></strong></span>
                                </div>
                                <?php if ($providerHref !== ''): ?>
                                    <a href="<?= esc($providerHref) ?>" class="mt-2 inline-flex items-center gap-1 rounded-full border v3-border-default v3-bg-chair px-2 py-0.5 text-[11px] font-semibold v3-text-chair v3-hover-primary v3-btn v3-btn--secondary">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                        <span><?= esc(lang('Finance.invoice')) ?> <?= esc($providerTransactionId !== '' ? $providerTransactionId : ('#' . $providerFinanceId)) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($paymentRows) && empty($payoutRows)): ?>
                            <p class="text-sm v3-text-muted"><?= esc($moneyLabel('moneyTrailNoPaymentsOrProviders')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($inventoryRows)): ?>
                <div class="mt-4 v3-radius border v3-border-default v3-bg-alert p-4">
                    <h4 class="text-sm font-bold v3-text-danger"><?= esc($moneyLabel('moneyTrailInventoryUsage')) ?></h4>
                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                        <?php foreach (array_slice($inventoryRows, 0, 4) as $row): ?>
                            <div class="rounded-lg border v3-border-default v3-bg-panel px-3 py-2 text-sm v3-elevation-none">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="truncate font-semibold v3-text-ink"><?= esc($row['treatment_name'] ?? $row['treatment_number'] ?? lang('UI.patientNa')) ?></span>
                                    <span class="font-bold v3-text-danger" dir="ltr"><?= esc($formatMoney($row['total_cost'] ?? 0)) ?></span>
                                </div>
                                <p class="mt-0.5 text-xs v3-text-muted"><?= !empty($row['usage_date']) ? formatDate($row['usage_date']) : esc(lang('UI.patientNa')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
