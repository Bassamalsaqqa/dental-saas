<?php helper('asset');include APPPATH.'Views/layouts/partials/view_permissions.php';include APPPATH.'Views/layouts/partials/studio_icons.php';$tr=static fn(string $key,array $params=[])=>lang('Inventory.'.$key,$params); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-directory.css') ?>?v=<?= esc(asset_version('assets/css/studio-directory.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-records.css') ?>?v=<?= esc(asset_version('assets/css/studio-records.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-care.css') ?>?v=<?= esc(asset_version('assets/css/studio-care.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$returnToContext = (string) ($return_to_context ?? ('appointment/' . (int) ($appointment['id'] ?? 0)));
$contextPatient = $appointment_context_patient ?? null;
$linkedExaminations = is_array($appointment_linked_examinations ?? null) ? $appointment_linked_examinations : [];
$linkedTreatments = is_array($appointment_linked_treatments ?? null) ? $appointment_linked_treatments : [];
$checkout = is_array($appointment_checkout ?? null) ? $appointment_checkout : [];
$checkoutCompletion = is_array($appointment_checkout_completion ?? null) ? $appointment_checkout_completion : [];
$moneySummary = is_array($appointment_money_summary ?? null) ? $appointment_money_summary : [];
$invoiceableChargeLines = is_array($appointment_invoiceable_charge_lines ?? null) ? $appointment_invoiceable_charge_lines : [];
$invoiceableChargeLineIds = is_array($checkout['invoiceable_charge_line_ids'] ?? null)
    ? $checkout['invoiceable_charge_line_ids']
    : [];
$invoiceableChargeTotal = (float) ($moneySummary['open_charge_amount'] ?? 0);
$hasMixedInvoiceableCurrencies = !empty($checkout['has_mixed_invoiceable_currencies']);
$checkoutClinicalState = (string) ($checkout['clinical_state'] ?? 'not_recorded');
$checkoutBillingState = (string) ($checkout['billing_state'] ?? 'not_captured');
$checkoutSettlementState = (string) ($checkout['settlement_state'] ?? 'no_invoice');
$checkoutStatePresentation = [
    'clinical' => [
        'not_recorded' => ['label' => lang('UI.checkoutClinicalNotRecorded'), 'class' => 'bg-slate-100 text-slate-700 ring-slate-200', 'icon' => 'fas fa-circle-minus'],
        'needs_review' => ['label' => lang('UI.checkoutClinicalNeedsReview'), 'class' => 'bg-amber-100 text-amber-800 ring-amber-200', 'icon' => 'fas fa-triangle-exclamation'],
        'reviewed' => ['label' => lang('UI.checkoutClinicalReviewed'), 'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-200', 'icon' => 'fas fa-circle-check'],
    ],
    'billing' => [
        'not_captured' => ['label' => lang('UI.checkoutBillingNotCaptured'), 'class' => 'bg-slate-100 text-slate-700 ring-slate-200', 'icon' => 'fas fa-circle-minus'],
        'ready_to_invoice' => ['label' => lang('UI.checkoutBillingReady'), 'class' => 'bg-amber-100 text-amber-800 ring-amber-200', 'icon' => 'fas fa-file-invoice-dollar'],
        'invoiced' => ['label' => lang('UI.checkoutBillingInvoiced'), 'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-200', 'icon' => 'fas fa-circle-check'],
    ],
    'settlement' => [
        'no_invoice' => ['label' => lang('UI.checkoutSettlementNoInvoice'), 'class' => 'bg-slate-100 text-slate-700 ring-slate-200', 'icon' => 'fas fa-circle-minus'],
        'balance_due' => ['label' => lang('UI.checkoutSettlementBalanceDue'), 'class' => 'bg-rose-100 text-rose-800 ring-rose-200', 'icon' => 'fas fa-wallet'],
        'settled' => ['label' => lang('UI.checkoutSettlementSettled'), 'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-200', 'icon' => 'fas fa-circle-check'],
    ],
];
$clinicalPresentation = $checkoutStatePresentation['clinical'][$checkoutClinicalState]
    ?? $checkoutStatePresentation['clinical']['not_recorded'];
$billingPresentation = $checkoutStatePresentation['billing'][$checkoutBillingState]
    ?? $checkoutStatePresentation['billing']['not_captured'];
$settlementPresentation = $checkoutStatePresentation['settlement'][$checkoutSettlementState]
    ?? $checkoutStatePresentation['settlement']['no_invoice'];
$checkoutCompletionIssueLabels = [
    'clinical_work_unresolved' => lang('UI.checkoutIssueClinicalWork'),
    'charges_uninvoiced' => lang('UI.checkoutIssueUninvoicedCharges'),
    'mixed_currency_charges' => lang('UI.checkoutIssueMixedCurrencies'),
    'balance_due' => lang('UI.checkoutIssueBalanceDue'),
    'contextual_link_review' => lang('UI.checkoutIssueContextualLink'),
];
$checkoutCompletionIssueCodes = is_array($checkoutCompletion['issue_codes'] ?? null)
    ? $checkoutCompletion['issue_codes']
    : [];
$primaryLinkedExamination = $linkedExaminations[0] ?? null;
$currentVisitStage = (string) ($appointment['status'] ?? 'scheduled');
$visitStageOrder = ['scheduled', 'confirmed', 'arrived', 'ready', 'in_chair', 'checkout', 'completed'];
$allowedVisitStages = is_array($appointment_allowed_visit_stages ?? null) ? $appointment_allowed_visit_stages : [];
$visitIsClosed = !empty($appointment_visit_is_closed);
$visitStageLabels = [
    'scheduled' => lang('UI.appointmentStageScheduled'),
    'confirmed' => lang('UI.appointmentStageConfirmed'),
    'arrived' => lang('UI.appointmentStageArrived'),
    'ready' => lang('UI.appointmentStageReady'),
    'in_chair' => lang('UI.appointmentStageInChair'),
    'checkout' => lang('UI.appointmentStageCheckout'),
    'completed' => lang('UI.appointmentStageCompleted'),
    'cancelled' => lang('UI.appointmentStageCancelled'),
    'no_show' => lang('UI.appointmentStageNoShow'),
];
?>

<header class="page-title-row registration-heading"><div><h1><?= esc(trim(($appointment['first_name']??'').' '.($appointment['last_name']??''))) ?></h1><p><?= esc($appointment['appointment_id']) ?> · <?= esc($visitStageLabels[$currentVisitStage]??$currentVisitStage) ?></p></div><nav class="care-actions"><a class="button secondary" href="<?= esc(workflow_back('appointments',$return_to??null),'attr') ?>"><?= esc(lang('Studio.back')) ?></a><a class="button secondary" href="<?= base_url('appointment/'.(int)$appointment['id'].'/print') ?>" target="_blank" rel="noopener"><?= esc(lang('Studio.printPdf')) ?></a><?php if($v4Can('appointments','edit')): ?><a class="button primary" href="<?= base_url('appointment/'.(int)$appointment['id'].'/edit?return_to='.rawurlencode($returnToContext)) ?>"><?= esc(lang('Studio.editRecord')) ?></a><?php endif; ?></nav></header>
<?php if(session('error')): ?><p class="form-notice form-notice--error" role="alert"><?= esc(session('error')) ?></p><?php endif; ?>
<section class="care-panel"><dl class="care-facts"><?php foreach(['appointment_date'=>['Studio.visit_time',formatDate($appointment['appointment_date']).' · '.formatTime($appointment['appointment_time'])],'duration'=>['Studio.duration',(int)$appointment['duration'].' '.lang('Studio.minutes')],'chair_name'=>['Studio.resource',!empty($appointment['provider_user_id'])?\App\Libraries\ProviderDisplayLabel::forOption($appointment['provider_user_id'],$appointment['chair_name']??''):($appointment['chair_name']??'—')],'appointment_type'=>['Studio.visit_type',lang('Studio.type_'.$appointment['appointment_type'])],'phone'=>['Studio.phone',$appointment['phone']??'—'],'created_at'=>['Inventory.created',!empty($appointment['created_at'])?formatDateTime($appointment['created_at']):'—']] as [$label,$value]): ?><div><dt><?= esc(lang($label)) ?></dt><dd><?= esc($value) ?></dd></div><?php endforeach; ?></dl><?php if(!empty($appointment['notes'])): ?><h2 class="care-notes-title"><?= esc(lang('Inventory.notes')) ?></h2><p class="care-preserve-lines"><?= esc($appointment['notes']) ?></p><?php endif; ?></section>
<div class="care-columns"><div>
<section class="care-panel"><header class="care-actions"><h2><?= esc(lang('Studio.clinical')) ?></h2><a class="button secondary" href="<?= base_url('patient/'.(int)$appointment['patient_id']) ?>"><?= esc(lang('Studio.patient')) ?></a></header><div class="care-actions">
<?php if($v4Can('examinations','create')): ?><a class="button primary" href="<?= !empty($primaryLinkedExamination['id'])?esc(workflow_context_url('examination/'.(int)$primaryLinkedExamination['id'],$returnToContext),'attr'):esc(base_url('examination/create?'.http_build_query(['patient_id'=>(int)$appointment['patient_id'],'appointment_id'=>(int)$appointment['id'],'return_to'=>$returnToContext])),'attr') ?>"><?= esc(lang(!empty($primaryLinkedExamination['id'])?'UI.appointmentOpenExamination':'UI.appointmentStartExamination')) ?></a><?php endif; ?>
<?php if($v4Can('treatments','create')&&!empty($primaryLinkedExamination['id'])): ?><a class="button secondary" href="<?= esc(base_url('treatment/create?'.http_build_query(['examination_id'=>(int)$primaryLinkedExamination['id'],'appointment_id'=>(int)$appointment['id'],'return_to'=>$returnToContext])),'attr') ?>"><?= esc(lang('UI.appointmentAddTreatment')) ?></a><?php endif; ?></div><div class="care-item-list">
<?php foreach($linkedExaminations as $row): ?><a href="<?= esc(workflow_context_url('examination/'.(int)$row['id'],$returnToContext),'attr') ?>"><?= esc($row['examination_id']??('#'.$row['id'])) ?><small><?= esc($row['examination_type']??'') ?> · <?= esc($row['examination_date']??'') ?></small></a><?php endforeach; ?><?php foreach($linkedTreatments as $row): ?><a href="<?= esc(workflow_context_url('treatment/'.(int)$row['id'],$returnToContext),'attr') ?>"><?= esc($row['treatment_name']??'') ?><small><?= esc($row['status']??'') ?></small></a><?php endforeach; ?></div></section>
<section class="care-panel"><h2><?= esc(lang('UI.appointmentFinancialTrace')) ?></h2><div class="care-status-grid"><?php foreach([['UI.checkoutClinicalReview',$clinicalPresentation['label']],['UI.checkoutBillingReview',$billingPresentation['label']],['UI.checkoutPaymentReview',$settlementPresentation['label']]] as [$label,$value]): ?><div><span><?= esc(lang($label)) ?></span><strong><?= esc($value) ?></strong></div><?php endforeach; ?></div><dl class="care-facts care-money-grid"><?php foreach(['captured_charges'=>'UI.moneyTrailCapturedWork','charged'=>'UI.moneyTrailCharged','paid'=>'UI.moneyTrailPaid','balance_due'=>'UI.moneyTrailBalanceDue'] as $key=>$label): ?><div><dt><?= esc(lang($label)) ?></dt><dd><?= esc(formatCurrency($moneySummary[$key]??0)) ?></dd></div><?php endforeach; ?></dl><div class="care-actions">
<?php if($v4Can('finance','create')&&$invoiceableChargeLineIds&&!$hasMixedInvoiceableCurrencies): ?><a class="button primary" href="<?= esc(base_url('finance/create?'.http_build_query(['patient_id'=>(int)$appointment['patient_id'],'clinical_charge_line_ids'=>$invoiceableChargeLineIds,'return_to'=>$returnToContext])),'attr') ?>"><?= esc(lang('UI.appointmentCreateInvoiceFromWork')) ?> <?= esc(formatCurrency($invoiceableChargeTotal)) ?></a><?php endif; ?><?php if($v4Can('finance','view')&&(float)($moneySummary['balance_due']??0)>0.009): ?><a class="button secondary" href="<?= esc(workflow_context_url('patient/'.(int)$appointment['patient_id'].'?tab=finances',$returnToContext),'attr') ?>"><?= esc(lang('UI.checkoutRecordPayment')) ?></a><?php endif; ?></div>
<?php if($hasMixedInvoiceableCurrencies): ?><p class="booking-warning"><?= esc(lang('UI.checkoutIssueMixedCurrencies')) ?></p><?php endif; ?></section>
</div><aside class="care-panel"><h2><?= esc(lang('Studio.visitProgress')) ?></h2><div class="care-stages"><?php if($v4Can('appointments','edit')):foreach($allowedVisitStages as $targetStage):$needsCheckoutAcknowledgement=$targetStage==='completed'&&!empty($checkoutCompletion['requires_override']); ?>
<form method="post" action="<?= base_url('appointment/'.(int)$appointment['id'].'/transition') ?>"><?= csrf_field() ?><input name="status" type="hidden" value="<?= esc($targetStage,'attr') ?>"><input name="return_to" type="hidden" value="<?= esc($returnToContext,'attr') ?>">
<?php if($needsCheckoutAcknowledgement): ?><p><?= esc(lang('UI.checkoutCompletionNeedsReview')) ?></p><ul><?php foreach($checkoutCompletionIssueCodes as $code): ?><li><?= esc($checkoutCompletionIssueLabels[$code]??$code) ?></li><?php endforeach; ?></ul><label><input type="checkbox" name="checkout_override" value="1" required <?= old('checkout_override')?'checked':'' ?>><?= esc(lang('UI.checkoutCompletionAcknowledge')) ?></label><div class="form-field"><label for="completion-reason"><?= esc(lang('UI.checkoutCompletionReason')) ?></label><textarea id="completion-reason" name="checkout_override_reason" minlength="10" maxlength="500" required rows="2"><?= esc(old('checkout_override_reason')) ?></textarea></div><?php endif; ?>
<button class="button <?= in_array($targetStage,['cancelled','no_show'],true)?'destructive':'primary' ?>" type="submit"><?= esc(lang('UI.appointmentMoveToStage',['stage'=>$visitStageLabels[$targetStage]??$targetStage])) ?></button></form><?php endforeach;endif; ?><?php if($visitIsClosed): ?><p class="booking-warning"><?= esc(lang('UI.appointmentVisitClosed')) ?></p><?php endif; ?></div></aside></div>
<?= view('appointment/partials/contact_history',['appointment'=>$appointment,'contact_history'=>$contact_history??[],'contact_history_enabled'=>$contact_history_enabled??false,'contact_request_key'=>$contact_request_key??'']) ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><?= $this->endSection() ?>
