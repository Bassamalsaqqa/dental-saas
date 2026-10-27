<?php
$initialOdontogram = [];
if (!empty($snapshot['data_snapshot_json'])) {
    $decoded = json_decode($snapshot['data_snapshot_json'], true);
    if (is_array($decoded)) {
        $initialOdontogram = $decoded;
    }
}
if (empty($initialOdontogram)) {
    $initialOdontogram = $odontogram ?? [];
}
$normalizedInitial = [];
foreach ($initialOdontogram as $row) {
    if (!isset($row['tooth_number'])) {
        continue;
    }
    $normalizedInitial[] = [
        'tooth_number' => (int) $row['tooth_number'],
        'condition_type' => $row['condition_type'] ?? 'healthy',
        'condition_description' => $row['condition_description'] ?? '',
        'treatment_notes' => $row['treatment_notes'] ?? '',
        'treatment_status' => $row['treatment_status'] ?? 'pending'
    ];
}
$items = $items ?? [];
$contract = $contract ?? null;
$mode = $mode ?? 'create';
$returnTo = $return_to ?? null;
$actionUrl = $mode === 'edit' && $contract ? base_url('contracts/' . $contract['id'] . '/update') : base_url('contracts/create/' . $patient['id']);
$hasContractTerms = !empty($contract['terms_conditions'] ?? '')
    || !empty($contract['payment_terms'] ?? '')
    || !empty($contract['duration_plan'] ?? '')
    || !empty($contract['notes'] ?? '');
?>

<?php helper('asset');$cb=static fn($key)=>esc(lang('Studio.cb'.$key)); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-contract-builder.css') ?>?v=<?= esc(asset_version('assets/css/studio-contract-builder.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-contract-chart.css') ?>?v=<?= esc(asset_version('assets/css/studio-contract-chart.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= $cb($mode==='edit'?'EditTitle':'CreateTitle') ?>
</h1>
<a class="button secondary" href="<?= esc(workflow_back('patient/'.$patient['id'],$returnTo)) ?>">
<?= $cb('Back') ?>
</a>
</header>
<div class="contract-build-context">
<strong>
<?= esc($patient['first_name'].' '.$patient['last_name']) ?>
</strong>
<span>
<?= esc($patient['patient_id']??$patient['id']) ?>
</span>
<span>
<?= $cb('Draft') ?>
</span>
</div>
<form class="studio-form contract-build" id="contractForm" action="<?= $actionUrl ?>" method="post">
<?= csrf_field() ?>
<?php if ($mode === 'edit'): ?>
<input type="hidden" name="draft_revision" value="<?= esc($draft_revision ?? '') ?>">
<?php endif; ?>
<div id="contractRemovedItems" hidden></div>
<input type="hidden" name="odontogram_json" id="odontogramJson">
<input type="hidden" name="odontogram_svg" id="odontogramSvg">
<?php if(!empty($returnTo)): ?>
<input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
<?php endif; ?>
<?php if(!empty($examination_id)): ?>
<input type="hidden" name="examination_id" value="<?= (int)$examination_id ?>">
<?php endif; ?>
<p id="contract-item-preservation-note" class="form-notice form-notice--error" role="alert" hidden><?= $cb('ExistingItemsWarning') ?></p><div class="contract-build-layout">
<div class="contract-build-main">
<section class="form-section">
<header>
<h2>
<?= $cb('Chart') ?>
</h2>
<p>
<?= $cb('ChartHelp') ?>
</p>
</header>
<div class="contract-chart-tools">
<button class="button secondary" type="button" id="legendToggleBtn">
<span id="legendToggleIcon" aria-hidden="true">
</span>
<span id="legendToggleText">
<?= $cb('ShowLegend') ?>
</span>
</button>
<button class="button secondary" id="batchModeToggleBtn" type="button" data-contract-action="batch-toggle">
<span id="batchModeToggleText">
<?= $cb('BatchSelect') ?>
</span>
</button>
<div id="batchTopControls" class="hidden">
<span id="batchTopCount">
<?= $cb('NoneSelected') ?>
</span>
<button class="button secondary" type="button" id="batchApplyBtn" data-contract-action="batch-open" disabled>
<?= $cb('Apply') ?>
</button>
<button class="button secondary" type="button" id="batchClearBtn" data-contract-action="batch-clear" >
<?= $cb('Clear') ?>
</button>
<button class="button secondary" type="button" id="batchExitBtn" data-contract-action="batch-exit">
<?= $cb('Exit') ?>
</button>
</div>
</div>
<div id="legendContent" class="contract-condition-legend hidden">
<?php foreach($condition_types as $key=>$label): ?>
<span>
<?= esc($label) ?>
</span>
<?php endforeach; ?>
</div>
<div id="odontogramSvgWrapper">
<?php
                                        $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                                        if (is_file($svgPath)) {
                                            echo file_get_contents($svgPath);
                                        } else {
                                            echo '<p class="text-sm text-gray-500">Odontogram SVG not found.</p>';
                                        }
                                        ?>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $cb('Conditions') ?>
</h2>
<p id="conditionsSummary">
</p>
</header>
<div class="contract-build-scroll grid-scroll" tabindex="0" role="region" aria-label="<?= $cb('Conditions') ?>">
<table class="contract-conditions-table studio-grid" data-grid-widths="8,8,16,15,18,18,10,7">
<thead>
<tr>
<?php foreach(['Tooth','FDI','Position','Condition','Description','Treatment','Status','Actions'] as $key): ?>
<th scope="col">
<?= $cb($key) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody id="teethTableBody">
</tbody>
</table>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $cb('Items') ?>
</h2>
<button type="button" class="button secondary" id="addItemBtn">
<?= $cb('AddItem') ?>
</button>
</header>
<div class="contract-build-scroll grid-scroll" tabindex="0" role="region" aria-label="<?= $cb('Items') ?>">
<table class="contract-items-table studio-grid" data-grid-widths="14,9,39,10,14,14">
<thead>
<tr>
<?php foreach(['Code','Tooth','Description','Quantity','Price','Actions'] as $key): ?>
<th scope="col">
<?= $cb($key) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody id="itemsBody">
</tbody>
</table>
</div>
</section>
</div>
<aside class="contract-build-side">
<section class="form-section">
<h2>
<?= $cb('FinancialTerms') ?>
</h2>
<div class="contract-build-money">
<div class="form-field">
<label for="contract-currency">
<?= $cb('Currency') ?>
</label>
<?php $baseCurrency=$base_currency??'ILS';$selectedCurrency=$contract['currency']??$baseCurrency; ?>
<select id="contract-currency" name="currency">
<?php foreach($currency_options??['ILS'] as $option): ?>
<option value="<?= esc($option) ?>" <?= $selectedCurrency===$option?'selected':'' ?>>
<?= esc($option) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="contractTotalInput">
<?= $cb('Total') ?>
</label>
<input type="number" step="0.01" min="0" name="total_cost" id="contractTotalInput" value="<?= esc(number_format((float)($contract['total_cost']??0),2,'.','')) ?>">
</div>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $cb('Terms') ?>
</h2>
<button type="button" class="button secondary" id="contractTermsToggleBtn">
<span id="contractTermsToggleIcon" aria-hidden="true">
</span>
<span id="contractTermsToggleText">
<?= $cb($hasContractTerms?'Hide':'Show') ?>
</span>
</button>
</header>
<div id="contractTermsContent" class="contract-build-terms <?= $hasContractTerms?'':'hidden' ?>">
<?php foreach(['terms_conditions'=>'TermsConditions','payment_terms'=>'PaymentTerms','duration_plan'=>'Duration','notes'=>'Notes'] as $name=>$key): ?>
<div class="form-field">
<label for="contract-<?= $name ?>">
<?= $cb($key) ?>
</label>
<?php if($name==='duration_plan'): ?>
<input id="contract-<?= $name ?>" name="<?= $name ?>" value="<?= esc($contract[$name]??'','attr') ?>">
<?php else: ?>
<textarea id="contract-<?= $name ?>" name="<?= $name ?>" rows="3">
<?= esc($contract[$name]??'') ?>
</textarea>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<footer class="contract-build-save">
<button class="button primary" type="submit">
<?= $cb($mode==='edit'?'SaveChanges':'SaveDraft') ?>
</button>
</footer>
</aside>
</div>
</form>
<div id="toothModal" class="contract-build-modal hidden" role="dialog" aria-modal="true" aria-labelledby="toothModalTitle">
<div class="contract-build-modal-panel" id="modalContent">
<header>
<h2 id="toothModalTitle">
<?= $cb('Tooth') ?> <span id="modalToothNumber">
</span>
</h2>
<button class="button secondary" type="button" data-contract-action="tooth-close">
<?= $cb('Close') ?>
</button>
</header>
<form class="studio-form" id="toothForm">
<input type="hidden" id="toothNumber" name="tooth_number">
<div class="form-field">
<label for="condition_type">
<?= $cb('Condition') ?>
</label>
<select id="condition_type" name="condition_type" required>
<option value="">
<?= $cb('SelectCondition') ?>
</option>
<?php foreach($condition_types as $key=>$label): ?>
<option value="<?= esc($key) ?>">
<?= esc($label) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="condition_description">
<?= $cb('Description') ?>
</label>
<textarea id="condition_description" name="condition_description" rows="3">
</textarea>
</div>
<div class="form-field">
<label for="treatment_notes">
<?= $cb('TreatmentNotes') ?>
</label>
<textarea id="treatment_notes" name="treatment_notes" rows="3">
</textarea>
</div>
<div class="form-field">
<label for="treatment_status">
<?= $cb('Status') ?>
</label>
<select id="treatment_status" name="treatment_status">
<?php foreach(['pending'=>'Pending','in_progress'=>'InProgress','completed'=>'Completed','needs_attention'=>'NeedsAttention'] as $value=>$key): ?>
<option value="<?= $value ?>">
<?= $cb($key) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<footer>
<button class="button secondary" type="button" data-contract-action="tooth-close">
<?= $cb('Cancel') ?>
</button>
<button class="button secondary" type="button" data-contract-action="tooth-remove">
<?= $cb('Reset') ?>
</button>
<button class="button primary" type="submit" >
<?= $cb('Apply') ?>
</button>
</footer>
</form>
</div>
</div>
<div id="batchModal" class="contract-build-modal hidden" role="dialog" aria-modal="true" aria-labelledby="batchModalTitle">
<div class="contract-build-modal-panel" id="batchModalContent">
<header>
<h2 id="batchModalTitle">
<?= $cb('BatchTitle') ?> <span id="batchModalCount">
</span>
</h2>
<button class="button secondary" type="button" data-contract-action="batch-close">
<?= $cb('Close') ?>
</button>
</header>
<form class="studio-form" id="batchForm">
<div class="form-field">
<label for="batch_condition_type">
<?= $cb('Condition') ?>
</label>
<select id="batch_condition_type" name="condition_type" required>
<option value="">
<?= $cb('SelectCondition') ?>
</option>
<?php foreach($condition_types as $key=>$label): ?>
<option value="<?= esc($key) ?>">
<?= esc($label) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="batch_condition_description">
<?= $cb('Description') ?>
</label>
<textarea id="batch_condition_description" name="condition_description" rows="3">
</textarea>
</div>
<div class="form-field">
<label for="batch_treatment_notes">
<?= $cb('TreatmentNotes') ?>
</label>
<textarea id="batch_treatment_notes" name="treatment_notes" rows="3">
</textarea>
</div>
<div class="form-field">
<label for="batch_treatment_status">
<?= $cb('Status') ?>
</label>
<select id="batch_treatment_status" name="treatment_status">
<?php foreach(['pending'=>'Pending','in_progress'=>'InProgress','completed'=>'Completed','needs_attention'=>'NeedsAttention'] as $value=>$key): ?>
<option value="<?= $value ?>">
<?= $cb($key) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<footer>
<button class="button secondary" type="button" data-contract-action="batch-close">
<?= $cb('Cancel') ?>
</button>
<button class="button secondary" type="button" data-contract-action="batch-reset">
<?= $cb('Reset') ?>
</button>
<button class="button primary" type="submit" id="batchSubmitBtn">
<?= $cb('Apply') ?>
</button>
</footer>
</form>
</div>
</div>
<?php $contractConfig=['value0'=>json_decode(json_encode(['batchOn'=>lang('Studio.cbBatchOn'),'batchSelect'=>lang('Studio.cbBatchSelect'),'selected'=>lang('Studio.cbSelected'),'conditions'=>lang('Studio.cbConditionsSummary'),'healthy'=>lang('Studio.cbAllHealthy'),'noConditions'=>lang('Studio.cbNoConditions'),'noItems'=>lang('Studio.cbNoItems'),'remove'=>lang('Studio.cbRemove'),'edit'=>lang('Studio.cbEdit'),'show'=>lang('Studio.cbShow'),'hide'=>lang('Studio.cbHide'),'showLegend'=>lang('Studio.cbShowLegend'),'hideLegend'=>lang('Studio.cbHideLegend')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE),true),'value1'=>json_decode(json_encode($condition_types),true),'value2'=>json_decode(json_encode($normalizedInitial),true),'value3'=>json_decode(json_encode($items),true)]; ?>
<script type="application/json" id="contract-builder-config"><?= json_encode($contractConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= base_url('assets/js/studio-contract-builder.js') ?>?v=<?= esc(asset_version('assets/js/studio-contract-builder.js')) ?>" defer></script>


<script src="<?= base_url('assets/js/studio-contract-builder-access.js') ?>?v=<?= esc(asset_version('assets/js/studio-contract-builder-access.js')) ?>" defer></script>
<?= $this->endSection() ?>