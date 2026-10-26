<?php
$analysisRows=[];
foreach($ledger_rows??[] as $row){
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

$analysisRows[]=['href'=>$rowHref,'values'=>[(string)($row['report_date']??'—'),$sourceLabel,$typeLabel,(string)($row['reference']??'—'),trim(($row['patient_label']??'').' '.($row['patient_number']??''))?:'—',(string)($row['counterparty']??'—'),$paymentMethodLabel,$liquidityAccountLabel,!empty($row['created_at'])?formatDateTime($row['created_at']):'—',isset($row['amount_original'])?$row['currency'].' '.number_format((float)$row['amount_original'],2):'—',($row['event_source']??'')==='provider_payout_payment'?'-'.\App\Services\ProviderCurrencySummary::native($row['amount_original'],$row['currency']):($signed>=0?'+':'-').formatCurrency(abs($signed))]];
}
?>
<section class="record-panel analysis-ledger"><header class="analysis-section-heading"><div><h2><?= esc(lang('Finance.externalCashEvents')) ?></h2><p><?= esc(lang('Studio.analysisLedgerHelp')) ?></p></div><span><?= count($analysisRows) ?> <?= esc(lang('Finance.externalCashEvents')) ?></span></header><div class="analysis-event-tools"><label><?= esc(lang('Finance.search')) ?><input type="search" data-analysis-search></label><label><?= esc(lang('Studio.showRecords')) ?><select data-analysis-size><?php foreach([25,50,100] as $size): ?><option value="<?= $size ?>"><?= $size ?></option><?php endforeach; ?></select></label></div>
<table class="studio-grid analysis-event-table" data-grid-widths="10,12,10,12,16,16,10,12,14,12,14"><thead><tr><?php foreach(['reportDate','source','type','reference','patient','counterparty','method','liquidityAccount','recordedAt','original'] as $key): ?><th scope="col"><?= esc(lang('Finance.'.$key)) ?></th><?php endforeach; ?><th scope="col"><?= esc(lang('Studio.analysisMovementAmount')) ?></th></tr></thead><tbody><?php foreach(array_slice($analysisRows,0,25) as $record): ?><tr <?= $record['href']?'data-href="'.esc($record['href'],'attr').'"':'' ?>><?php foreach($record['values'] as $index=>$value): ?><td><?php if($index===3&&$record['href']): ?><a href="<?= esc($record['href'],'attr') ?>"><?= esc($value) ?></a><?php else: ?><span><?= esc($value) ?></span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if(!$analysisRows): ?><tr><td colspan="11"><?= esc(lang('Finance.noFinancialEventsForPeriod')) ?></td></tr><?php endif; ?></tbody></table><footer class="money-pagination"><p data-analysis-range><?= min(25,count($analysisRows)).' / '.count($analysisRows) ?></p><div><button type="button" class="button secondary" data-analysis-prev disabled><?= esc(lang('Finance.previous')) ?></button><span data-analysis-page>1</span><button type="button" class="button secondary" data-analysis-next <?= count($analysisRows)>25?'':'disabled' ?>><?= esc(lang('Finance.next')) ?></button></div></footer>
<script type="application/json" id="analysis-rows"><?= json_encode(['rows'=>$analysisRows,'empty'=>lang('Finance.noFinancialEventsForPeriod')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script></section>
