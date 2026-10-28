<?php
$providerRows = [];
$oldTypes = old('provider_type');
if (is_array($oldTypes)) {
    foreach (array_values($oldTypes) as $index => $type) {
        $providerRows[] = [
            'id' => old('provider_payout_id')[$index] ?? '',
            'provider_type' => $type ?: 'user',
            'provider_user_id' => old('provider_user_id')[$index] ?? '',
            'provider_name_snapshot' => old('provider_name_snapshot')[$index] ?? '',
            'split_mode' => old('provider_split_mode')[$index] ?? 'percent',
            'split_value' => old('provider_split_value')[$index] ?? '70',
            'notes' => old('provider_split_notes')[$index] ?? '',
            'mark_paid_now' => (old('provider_mark_paid_now')[$index] ?? '0') === '1',
            'provider_paid_amount' => old('provider_paid_amount')[$index] ?? '',
            'provider_paid_method' => old('provider_paid_method')[$index] ?? 'cash',
            'provider_paid_date' => old('provider_paid_date')[$index] ?? date('Y-m-d'),
        ];
    }
} elseif (!empty($provider_payouts)) {
    foreach (($provider_payouts ?? []) as $row) {
        $providerRows[] = [
            'id' => $row['id'] ?? '',
            'provider_type' => $row['provider_type'] ?? 'user',
            'provider_user_id' => $row['provider_user_id'] ?? '',
            'provider_name_snapshot' => $row['provider_name_snapshot'] ?? '',
            'split_mode' => $row['split_mode'] ?? 'percent',
            'split_value' => $row['split_value'] ?? '70',
            'notes' => $row['notes'] ?? '',
            'mark_paid_now' => false,
            'provider_paid_amount' => '',
            'provider_paid_method' => 'cash',
            'provider_paid_date' => date('Y-m-d'),
        ];
    }
}

if (empty($providerRows)) {
    $providerRows[] = [
        'id' => '',
        'provider_type' => 'user',
        'provider_user_id' => '',
        'provider_name_snapshot' => '',
        'split_mode' => 'percent',
        'split_value' => '70',
        'notes' => '',
        'mark_paid_now' => false,
        'provider_paid_amount' => '',
        'provider_paid_method' => 'cash',
        'provider_paid_date' => date('Y-m-d'),
    ];
}

$splitEnabled = old('provider_split_enabled', !empty($provider_payouts) ? '1' : '');
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
$providerOptions = [];
foreach (($clinic_providers ?? []) as $provider) {
    $providerOptions[] = [
        'id' => (int) ($provider['id'] ?? 0),
        'label' => trim((string) (($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? ''))),
    ];
}
?>
<section class="finance-provider-section" data-provider-split-section data-provider-rows="<?= esc(json_encode($providerRows,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT),'attr') ?>" data-provider-date="<?= esc(date('Y-m-d'),'attr') ?>"><header><div><h2><?= esc(lang('Finance.providerSplit')) ?></h2></div><label class="finance-inline-check"><input type="checkbox" id="<?= esc($fieldId('providerSplitEnabled')) ?>" name="provider_split_enabled" value="1" <?= $splitEnabled?'checked':'' ?>><span><?= esc(lang('Finance.enable')) ?></span></label></header><div id="<?= esc($fieldId('providerSplitFields')) ?>" class="<?= $splitEnabled?'':'hidden' ?>"><dl class="finance-provider-totals"><?php foreach(['splitGrossPreview'=>'grossCollected','splitProviderPreview'=>'totalProviderCompensation','splitClinicPreview'=>'clinicNet'] as $id=>$label): ?><div><dt><?= esc(lang('Finance.'.$label)) ?></dt><dd id="<?= esc($fieldId($id)) ?>">0.00</dd></div><?php endforeach; ?></dl><button type="button" class="button secondary" id="<?= esc($fieldId('addProviderAllocationBtn')) ?>"><?= esc(lang('Finance.addProviderAllocation')) ?></button><div id="<?= esc($fieldId('providerAllocationsList')) ?>" class="finance-provider-list"></div>
<template data-provider-allocation-template><section class="provider-allocation-row"><input type="hidden" name="provider_payout_id[]" value=""><input type="hidden" name="provider_mark_paid_now[]" class="provider-mark-paid-input" value="0"><header><p class="provider-row-share-label"><?= esc(lang('Finance.allocationShare')) ?>: <strong class="provider-row-share-value">0.00</strong></p><button type="button" class="provider-remove-row button destructive"><?= esc(lang('Finance.removeAllocation')) ?></button></header><div class="form-grid">
<div class="form-field"><label><?= esc(lang('Finance.providerType')) ?><select name="provider_type[]" class="provider-type-select"><option value="user"><?= esc(lang('Finance.clinicUser')) ?></option><option value="guest"><?= esc(lang('Finance.guestProvider')) ?></option></select></label></div>
<div class="provider-user-wrap form-field"><label><?= esc(lang('Finance.providerUser')) ?><select name="provider_user_id[]" class="provider-user-select"><option value=""><?= esc(lang('Finance.selectUser')) ?></option><?php foreach($providerOptions as $option): ?><option value="<?= (int)$option['id'] ?>"><?= esc($option['label'].' · #'.$option['id']) ?></option><?php endforeach; ?></select></label></div><div class="provider-guest-wrap form-field hidden"><label><?= esc(lang('Finance.guestProviderName')) ?><input type="text" name="provider_name_snapshot[]" class="provider-guest-input"></label></div>
<div class="form-field"><label><?= esc(lang('Finance.splitMode')) ?><select name="provider_split_mode[]" class="provider-split-mode"><option value="percent"><?= esc(lang('Finance.percentage')) ?></option><option value="fixed"><?= esc(lang('Finance.fixedAmount')) ?></option></select></label></div><div class="form-field"><label><?= esc(lang('Finance.splitValue')) ?><input type="number" name="provider_split_value[]" step="0.01" min="0" value="0" class="provider-split-value"></label></div><div class="form-field form-field--wide"><label><?= esc(lang('Finance.allocationNotes')) ?><textarea name="provider_split_notes[]" rows="2"></textarea></label></div></div>
<div class="finance-provider-payment"><label class="provider-pay-toggle finance-inline-check"><input type="checkbox" class="provider-paid-toggle"><span><?= esc(lang('Finance.markProviderPaidNow')) ?></span></label><div class="provider-paid-fields form-grid hidden"><div class="form-field"><label><?= esc(lang('Finance.paidAmount')) ?><input type="number" name="provider_paid_amount[]" step="0.01" min="0" class="provider-paid-amount"></label></div><div class="form-field"><label><?= esc(lang('Finance.method')) ?><select name="provider_paid_method[]" class="provider-paid-method"><?php foreach(['cash'=>'cash','card'=>'card','bank_transfer'=>'bankTransfer','check'=>'check','other'=>'other'] as $value=>$label): ?><option value="<?= $value ?>"><?= esc(lang('Finance.'.$label)) ?></option><?php endforeach; ?></select></label></div><div class="form-field"><label><?= esc(lang('Finance.paidDate')) ?><input type="date" name="provider_paid_date[]" value="<?= esc(date('Y-m-d'),'attr') ?>" class="provider-paid-date" dir="ltr"></label></div></div></div></section></template></div></section>
<script src="<?= base_url('assets/js/studio-provider-allocations.js') ?>?v=<?= esc(asset_version('assets/js/studio-provider-allocations.js')) ?>" defer></script>
