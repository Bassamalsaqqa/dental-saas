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


<?php helper('asset');$traceWindow=static fn(int $shown,int $available):string=>lang('Studio.traceWindow',['shown'=>$shown,'available'=>$available]); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-theme.css') ?>?v=<?= esc(asset_version('assets/css/studio-theme.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-money-trail.css') ?>?v=<?= esc(asset_version('assets/css/studio-money-trail.css')) ?>">
<section class="money-trail" dir="<?= $isRtl?'rtl':'ltr' ?>" aria-label="<?= esc($moneyLabel('moneyTrailTitle'),'attr') ?>"><header><h3><?= esc($moneyLabel('moneyTrailTitle')) ?></h3><p><?= esc($moneyLabel('moneyTrailSubtitle')) ?></p></header>
<dl class="trace-summary"><?php foreach($stats as $stat): ?><div><dt><?= esc($stat['label']) ?></dt><dd dir="ltr"><?= esc($stat['key']==='provider_remaining'?\App\Services\ProviderCurrencySummary::format($summary['provider_currency_summary'],'provider_remaining'):$formatMoney($stat['value'])) ?></dd></div><?php endforeach; ?></dl>
<?php if($payoutRows!==[]): ?><p class="trace-notice"><?= esc(lang('Finance.providerVoidBalanceHelp').' '.lang('Finance.providerCurrencyWarning')) ?></p><?php endif; ?>
<?php if(!$hasRecords): ?><div class="trace-empty"><h4><?= esc($moneyLabel('moneyTrailEmpty')) ?></h4><p><?= esc($moneyLabel('moneyTrailEmptyHint')) ?></p></div><?php else: ?>
<?php if($chargeLines!==[]): ?><details class="trace-group" open><summary><span><?= esc($moneyLabel('moneyTrailChargeLines')) ?></span><span><?= count($chargeLines) ?></span></summary><div class="trace-group-body"><p class="trace-window"><?= esc($traceWindow(min(6,count($chargeLines)),count($chargeLines))) ?></p><?php if((int)($summary['open_charge_lines']??0)>0): ?><p class="trace-notice"><?= esc(lang('UI.moneyTrailOpenChargeLines',['count'=>(int)$summary['open_charge_lines']])) ?></p><?php endif; ?>
<table class="studio-grid trace-table" data-grid-widths="38,20,22,20" aria-label="<?= esc($moneyLabel('moneyTrailChargeLines'),'attr') ?>"><thead><tr><th scope="col"><?= esc(lang('Studio.traceProcedure')) ?></th><th scope="col"><?= esc(lang('Finance.status')) ?></th><th scope="col"><?= esc(lang('Finance.invoice')) ?></th><th scope="col"><?= esc(lang('Finance.amount')) ?></th></tr></thead><tbody><?php foreach(array_slice($chargeLines,0,6) as $row):$chargeFinanceId=(int)($row['finance_id']??0);$chargeTransactionId=trim((string)($row['transaction_id']??''));$chargeInvoiceHref=$chargeFinanceId>0?workflow_context_url('finance/'.$chargeFinanceId,$returnTo):''; ?><tr><td><?= esc($row['procedure_name']??lang('UI.patientNa')) ?></td><td><?= esc($row['status']??'') ?></td><td><?php if($chargeInvoiceHref!==''): ?><a href="<?= esc($chargeInvoiceHref) ?>"><?= esc($chargeTransactionId!==''?$chargeTransactionId:('#'.$chargeFinanceId)) ?></a><?php else: ?><?= esc(lang('UI.patientNa')) ?><?php endif; ?></td><td dir="ltr"><?= esc($formatMoney($row['net_production_amount']??0)) ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<details class="trace-group" open><summary><span><?= esc($moneyLabel('moneyTrailFinanceRecords')) ?></span><span><?= count($financeRows) ?></span></summary><div class="trace-group-body"><p class="trace-window"><?= esc($traceWindow(min(6,count($financeRows)),count($financeRows))) ?></p><?php if($financeRows!==[]): ?><table class="studio-grid trace-table" data-grid-widths="25,27,16,16,16" aria-label="<?= esc($moneyLabel('moneyTrailFinanceRecords'),'attr') ?>"><thead><tr><th scope="col"><?= esc(lang('Studio.traceRecord')) ?></th><th scope="col"><?= esc(lang('Finance.description')) ?></th><th scope="col"><?= esc(lang('Finance.total')) ?></th><th scope="col"><?= esc($moneyLabel('moneyTrailPaid')) ?></th><th scope="col"><?= esc($moneyLabel('moneyTrailBalanceDue')) ?></th></tr></thead><tbody><?php foreach(array_slice($financeRows,0,6) as $row):$financeId=(int)($row['id']??0);$reference=(string)($row['transaction_id']??('#'.$financeId));$href=$financeId>0?workflow_context_url('finance/'.$financeId,$returnTo):'#';$rowTotal=$financeRowAmount($row);$rowPaid=max(0,(float)($row['amount_paid']??0));$rowBalance=max(0,(float)($row['balance_due']??0)); ?><tr><td><a href="<?= esc($href) ?>"><?= esc($reference) ?></a><small><?= esc($financeType($row['transaction_type']??'')) ?></small></td><td><?= esc($row['description']??$row['service_type']??lang('UI.patientNa')) ?></td><td dir="ltr"><?= esc($formatMoney($rowTotal)) ?></td><td><?php if(($row['transaction_type']??'')==='invoice'): ?><span dir="ltr"><?= esc($formatMoney($rowPaid)) ?></span><?php else: ?><?= esc(lang('UI.patientNa')) ?><?php endif; ?></td><td><?php if(($row['transaction_type']??'')==='invoice'): ?><span dir="ltr"><?= esc($formatMoney($rowBalance)) ?></span><?php else: ?><?= esc(lang('UI.patientNa')) ?><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><p class="trace-empty"><?= esc($moneyLabel('moneyTrailNoFinanceRecords')) ?></p><?php endif; ?></div></details>
<details class="trace-group" open><summary><span><?= esc(lang('Studio.traceReceipts')) ?></span><span><?= count($paymentRows) ?></span></summary><div class="trace-group-body"><p class="trace-window"><?= esc($traceWindow(min(4,count($paymentRows)),count($paymentRows))) ?></p><?php if($paymentRows!==[]): ?><table class="studio-grid trace-table" data-grid-widths="23,27,18,16,16" aria-label="<?= esc(lang('Studio.traceReceipts'),'attr') ?>"><thead><tr><th scope="col"><?= esc(lang('Finance.type')) ?></th><th scope="col"><?= esc(lang('Studio.traceRecord')) ?></th><th scope="col"><?= esc(lang('Finance.date')) ?></th><th scope="col"><?= esc(lang('Finance.paymentMethod')) ?></th><th scope="col"><?= esc(lang('Finance.amount')) ?></th></tr></thead><tbody><?php foreach(array_slice($paymentRows,0,4) as $row): ?><tr><td><?= esc($financeType($row['type']??'payment')) ?></td><td><?= esc($row['transaction_id']??'') ?></td><td><?= !empty($row['payment_date'])?formatDate($row['payment_date']):esc(lang('UI.patientNa')) ?></td><td><?= !empty($row['payment_method'])?esc($paymentVerb($row['payment_method'])):esc(lang('UI.patientNa')) ?></td><td dir="ltr"><?= esc($formatMoney($row['amount']??0)) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div></details>
<?php if($payoutRows!==[]): ?><details class="trace-group"><summary><span><?= esc(lang('Studio.traceProviders')) ?></span><span><?= count($payoutRows) ?></span></summary><div class="trace-group-body"><p class="trace-window"><?= esc($traceWindow(min(4,count($payoutRows)),count($payoutRows))) ?></p><table class="studio-grid trace-table" data-grid-widths="24,14,20,14,14,14" aria-label="<?= esc(lang('Studio.traceProviders'),'attr') ?>"><thead><tr><th scope="col"><?= esc($moneyLabel('moneyTrailProvider')) ?></th><th scope="col"><?= esc(lang('Finance.status')) ?></th><th scope="col"><?= esc(lang('Finance.invoice')) ?></th><th scope="col"><?= esc($moneyLabel('moneyTrailProviderShare')) ?></th><th scope="col"><?= esc($moneyLabel('moneyTrailPaid')) ?></th><th scope="col"><?= esc(lang('Studio.traceRecordedBalance')) ?></th></tr></thead><tbody><?php foreach(array_slice($payoutRows,0,4) as $row):$providerFinanceId=(int)($row['finance_id']??0);$providerTransactionId=trim((string)($row['transaction_id']??''));$providerHref=$providerFinanceId>0?workflow_context_url('finance/'.$providerFinanceId,$returnTo):''; ?><tr><td><?= esc($providerName($row)) ?></td><td><?= esc($providerStatusLabel($row['status']??'')) ?></td><td><?php if($providerHref!==''): ?><a href="<?= esc($providerHref) ?>"><?= esc($providerTransactionId!==''?$providerTransactionId:('#'.$providerFinanceId)) ?></a><?php endif; ?></td><td dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_share_amount']??0,$row['currency']??null)) ?></td><td dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_paid_amount']??0,$row['currency']??null)) ?></td><td dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($row['provider_balance_due']??0,$row['currency']??null)) ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<?php if(empty($paymentRows)&&empty($payoutRows)): ?><p class="trace-empty"><?= esc($moneyLabel('moneyTrailNoPaymentsOrProviders')) ?></p><?php endif; ?>
<?php if($inventoryRows!==[]): ?><details class="trace-group"><summary><span><?= esc($moneyLabel('moneyTrailInventoryUsage')) ?></span><span><?= count($inventoryRows) ?></span></summary><div class="trace-group-body"><p class="trace-window"><?= esc($traceWindow(min(4,count($inventoryRows)),count($inventoryRows))) ?></p><table class="studio-grid trace-table" data-grid-widths="48,26,26" aria-label="<?= esc($moneyLabel('moneyTrailInventoryUsage'),'attr') ?>"><thead><tr><th scope="col"><?= esc(lang('Studio.traceProcedure')) ?></th><th scope="col"><?= esc(lang('Finance.date')) ?></th><th scope="col"><?= esc(lang('Finance.amount')) ?></th></tr></thead><tbody><?php foreach(array_slice($inventoryRows,0,4) as $row): ?><tr><td><?= esc($row['treatment_name']??$row['treatment_number']??lang('UI.patientNa')) ?></td><td><?= !empty($row['usage_date'])?formatDate($row['usage_date']):esc(lang('UI.patientNa')) ?></td><td dir="ltr"><?= esc($formatMoney($row['total_cost']??0)) ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<?php endif; ?></section>
<script>document.addEventListener('DOMContentLoaded',()=>{if(!document.querySelector('script[src*="/studio-grid.js"]')){const grid=document.createElement('script');grid.src=<?= json_encode(base_url('assets/js/studio-grid.js'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;document.body.append(grid);}for(const group of document.querySelectorAll('.money-trail details'))group.addEventListener('toggle',()=>window.dispatchEvent(new Event('resize')));for(const table of document.querySelectorAll('.money-trail table'))for(const row of table.tBodies[0]?.rows||[]){const link=row.querySelector('a[href]');if(!link||link.getAttribute('href')==='#')continue;row.dataset.traceHref=link.href;row.tabIndex=0;row.addEventListener('click',event=>{if(event.target.closest('a,button,input,select,textarea')||getSelection()?.toString())return;if(event.ctrlKey||event.metaKey||event.shiftKey){window.open(link.href,'_blank','noopener');return;}link.click();});row.addEventListener('keydown',event=>{if(event.target===row&&['Enter',' '].includes(event.key)){event.preventDefault();link.click();}});}},{once:true});</script>
