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
?>
<section class="record-panel clinical-money-trail"><header class="clinical-panel-heading"><div><h2><?= esc($moneyLabel('moneyTrailTitle')) ?></h2><p><?= esc($moneyLabel('moneyTrailSubtitle')) ?></p></div><?php if(!empty($patient_id)&&has_permission('finance','view')): ?><a class="button secondary" href="<?= esc(workflow_context_url('patient/'.(int)$patient_id.'?tab=finances',$returnTo),'attr') ?>"><?= esc(lang('UI.menuFinance')) ?></a><?php endif; ?></header><dl class="workspace-counts clinical-money-counts"><?php foreach($stats as $stat): ?><div><dt><?= esc($stat['label']) ?></dt><dd><?= esc($stat['key']==='provider_remaining'?\App\Services\ProviderCurrencySummary::format($summary['provider_currency_summary'],'provider_remaining'):$formatMoney($stat['value'])) ?></dd></div><?php endforeach; ?></dl><?php if($payoutRows!==[]): ?><p class="clinical-money-help"><?= esc(lang('Finance.providerVoidBalanceHelp').' '.lang('Finance.providerCurrencyWarning')) ?></p><?php endif; ?>
<?php if(!$hasRecords): ?><p class="clinical-empty"><?= esc($moneyLabel('moneyTrailEmpty')) ?></p><p class="clinical-money-help"><?= esc($moneyLabel('moneyTrailEmptyHint')) ?></p><?php else: ?>
<?php foreach([['moneyTrailChargeLines',$chargeLines,6,'charges'],['moneyTrailFinanceRecords',$financeRows,6,'finance'],['moneyTrailPaymentsAndProviders',$paymentRows,4,'payments'],['moneyTrailProvider',$payoutRows,4,'providers'],['moneyTrailInventoryUsage',$inventoryRows,4,'inventory']] as [$title,$entries,$window,$kind]):if(!$entries)continue; ?>
<section class="clinical-trail-group"><header class="clinical-panel-heading"><h3><?= esc($moneyLabel($title)) ?></h3><span><?= esc(lang('Studio.loadedWindow',['shown'=>min($window,count($entries)),'count'=>count($entries)])) ?></span></header>
<?php if($kind==='providers'): ?><div class="clinical-provider-rows"><?php foreach(array_slice($entries,0,$window) as $row): ?><article><header><strong><?= esc($providerName($row)) ?></strong><span class="clinical-record-status"><?= esc($providerStatusLabel($row['status']??'')) ?></span></header><dl class="settings-summary"><?php foreach(['provider_share_amount'=>'moneyTrailProviderShare','provider_paid_amount'=>'moneyTrailPaid','provider_balance_due'=>'moneyTrailProviderRemaining'] as $key=>$label): ?><div><dt><?= esc($moneyLabel($label)) ?></dt><dd><?= esc(\App\Services\ProviderCurrencySummary::native($row[$key]??0,$row['currency']??null)) ?></dd></div><?php endforeach; ?></dl><?php if((int)($row['finance_id']??0)>0): ?><a class="button secondary" href="<?= esc(workflow_context_url('finance/'.(int)$row['finance_id'],$returnTo),'attr') ?>"><?= esc(lang('Finance.invoice').' '.($row['transaction_id']??('#'.$row['finance_id']))) ?></a><?php endif; ?></article><?php endforeach; ?></div>
<?php else: ?><table class="studio-grid clinical-trail-table" data-grid-widths="32,28,22,18"><thead><tr><?php foreach(['Studio.recordId','Studio.details','Studio.status','Finance.amount'] as $label): ?><th scope="col"><?= esc(lang($label)) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach(array_slice($entries,0,$window) as $row): ?><tr>
<?php if($kind==='charges'):$financeId=(int)($row['finance_id']??0); ?><td><strong><?= esc($row['procedure_name']??lang('UI.patientNa')) ?></strong></td><td><?php if($financeId>0): ?><a href="<?= esc(workflow_context_url('finance/'.$financeId,$returnTo),'attr') ?>"><?= esc(lang('Finance.invoice').' '.(($row['transaction_id']??'')?:'#'.$financeId)) ?></a><?php endif; ?></td><td><?= esc($row['status']??'') ?></td><td><?= esc($formatMoney($row['net_production_amount']??0)) ?></td>
<?php elseif($kind==='finance'):$financeId=(int)($row['id']??0); ?><td><?php if($financeId>0): ?><a href="<?= esc(workflow_context_url('finance/'.$financeId,$returnTo),'attr') ?>"><?= esc($row['transaction_id']??('#'.$financeId)) ?></a><?php endif; ?><small><?= esc($financeType($row['transaction_type']??'')) ?></small></td><td><?= esc($row['description']??$row['service_type']??lang('UI.patientNa')) ?></td><td><?php if(($row['transaction_type']??'')==='invoice'): ?><span><?= esc($moneyLabel('moneyTrailPaid').' '.$formatMoney(max(0,(float)($row['amount_paid']??0)))) ?></span><small><?= esc($moneyLabel('moneyTrailBalanceDue').' '.$formatMoney(max(0,(float)($row['balance_due']??0)))) ?></small><?php else: ?><?= esc($row['payment_status']??'') ?><?php endif; ?></td><td><?= esc($formatMoney($financeRowAmount($row))) ?></td>
<?php elseif($kind==='payments'): ?><td><?= esc($financeType($row['type']??'payment')) ?><small><?= esc($row['transaction_id']??'') ?></small></td><td><?= !empty($row['payment_date'])?esc(formatDate($row['payment_date'])):esc(lang('UI.patientNa')) ?></td><td><?= esc($paymentVerb($row['payment_method']??'')) ?></td><td><?= esc($formatMoney($row['amount']??0)) ?></td>
<?php else: ?><td><?= esc($row['treatment_name']??$row['treatment_number']??lang('UI.patientNa')) ?></td><td><?= !empty($row['usage_date'])?esc(formatDate($row['usage_date'])):esc(lang('UI.patientNa')) ?></td><td><?= esc($row['notes']??'') ?></td><td><?= esc($formatMoney($row['total_cost']??0)) ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table><?php endif; ?></section><?php endforeach; ?>
<?php endif; ?></section>
