<?= $this->extend('layouts/main_auth') ?>

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
<div class="v3-record-flow v3-appointment-record">


    <div class="v3-flow-body">

                <div class="group relative">

            <div class="v3-flow-document">
                <!-- Header -->
                <div class="v3-flow-command">
                    <div class="v3-appointment-command-content">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl transition-colors"><?= esc(lang('UI.appointmentDetails')) ?></h3>
                                <p class="font-medium"><?= esc(lang('UI.appointmentIdLabel')) ?> <?= $appointment['appointment_id'] ?></p>
                            </div>
                        </div>

                        <div class="v3-appointment-command-actions">
                            <?php if (!empty($appointment['patient_id'])): ?>
                                <a href="<?= workflow_context_url('patient/' . $appointment['patient_id'], $return_to ?? null) ?>" class="relative inline-flex items-center px-6 py-3 text-sm font-bold">

                                    <i class="fas fa-user mr-2 relative z-10"></i>
                                    <span class="relative z-10"><?= esc(lang('UI.patientProfile')) ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= esc(workflow_back('appointment', $return_to ?? null)) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.back')) ?></span>
                            </a>
                            <a href="<?= base_url('appointment/' . $appointment['id'] . '/print') ?>" target="_blank" class="relative inline-flex items-center px-6 py-3 text-sm font-bold">

                                <i class="fas fa-print mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.appointmentPrint')) ?></span>
                            </a>
                            <a href="<?= workflow_context_url('appointment/' . $appointment['id'] . '/edit', $return_to ?? null) ?>" class="relative inline-flex items-center px-6 py-3 text-sm font-bold">

                                <i class="fas fa-edit mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.appointmentEdit')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <?php
                    $bookedByName = trim(($appointment['creator_first_name'] ?? '') . ' ' . ($appointment['creator_last_name'] ?? ''));
                    $assignedDoctor = trim((string) ($appointment['chair_name'] ?? ''));
                ?>
                <div class="">
                    <div class="v3-appointment-detail-grid">
                        <!-- Patient Information -->
                        <div class="">
                            <div class="relative">

                                <div class="relative border">
                                    <h4 class="text-lg font-bold mb-4 flex items-center space-x-2">
                                        <i class="fas fa-user"></i>
                                        <span><?= esc(lang('UI.appointmentPatientInfo')) ?></span>
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentName')) ?></span>
                                            <span class="font-bold"><?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentPatientIdLabel')) ?></span>
                                            <span class="font-bold"><?= $appointment['patient_number'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentPhoneLabel')) ?></span>
                                            <span class="font-bold"><?= $appointment['phone'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentEmailLabel')) ?></span>
                                            <span class="font-bold"><?= $appointment['email'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment Details -->
                            <div class="relative">

                                <div class="relative border">
                                    <h4 class="text-lg font-bold mb-4 flex items-center space-x-2">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= esc(lang('UI.appointmentInfoSection')) ?></span>
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentTypeLabel')) ?></span>
                                            <span class="font-bold capitalize"><?= str_replace('_', ' ', $appointment['appointment_type']) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentDoctorLabel')) ?></span>
                                            <span class="font-bold"><?= esc($assignedDoctor !== '' ? $assignedDoctor : lang('UI.appointmentDoctorDefault')) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentDateLabel')) ?></span>
                                            <span class="font-bold"><?= formatDate($appointment['appointment_date']) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentTimeLabel')) ?></span>
                                            <span class="font-bold"><?= formatTime($appointment['appointment_time']) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentDurationLabel')) ?></span>
                                            <span class="font-bold"><?= $appointment['duration'] ?> <?= esc(lang('UI.patientMinutes')) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentStatusLabel')) ?></span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?php
                                                switch($appointment['status']) {
                                                    case 'scheduled':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'confirmed':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'arrived':
                                                        echo 'bg-cyan-100 text-cyan-800';
                                                        break;
                                                    case 'ready':
                                                        echo 'bg-teal-100 text-teal-800';
                                                        break;
                                                    case 'in_chair':
                                                        echo 'bg-indigo-100 text-indigo-800';
                                                        break;
                                                    case 'checkout':
                                                        echo 'bg-violet-100 text-violet-800';
                                                        break;
                                                    case 'completed':
                                                        echo 'bg-emerald-100 text-emerald-800';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'no_show':
                                                        echo 'bg-orange-100 text-orange-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                                <?= esc($visitStageLabels[$currentVisitStage] ?? ucfirst(str_replace('_', ' ', $currentVisitStage))) ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium"><?= esc(lang('UI.appointmentBookedByLabel')) ?></span>
                                            <span class="font-bold"><?= esc($bookedByName !== '' ? $bookedByName : lang('UI.appointmentUnknownCreator')) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes and Actions -->
                        <div class="">
                            <!-- Notes -->
                            <div class="relative">

                                <div class="relative border">
                                    <h4 class="text-lg font-bold mb-4 flex items-center space-x-2">
                                        <i class="fas fa-sticky-note"></i>
                                        <span><?= esc(lang('UI.patientNotesTitle')) ?></span>
                                    </h4>
                                    <div class="">
                                        <?php if (!empty($appointment['notes'])): ?>
                                            <p class="whitespace-pre-wrap"><?= $appointment['notes'] ?></p>
                                        <?php else: ?>
                                            <p class="italic"><?= esc(lang('UI.appointmentNoNotesAvailable')) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="border">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center">
                                        <i class="fas fa-route"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-lg"><?= esc(lang('UI.appointmentVisitProgress')) ?></h4>
                                        <p class="mt-1 text-sm font-semibold leading-6"><?= esc(lang('UI.appointmentVisitProgressHint')) ?></p>
                                    </div>
                                </div>

                                <ol class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    <?php foreach ($visitStageOrder as $stageIndex => $stage): ?>
                                        <?php
                                        $currentIndex = array_search($currentVisitStage, $visitStageOrder, true);
                                        $isReached = $currentIndex !== false && $stageIndex <= $currentIndex;
                                        $isCurrent = $stage === $currentVisitStage;
                                        ?>
                                        <li class="rounded-xl px-3 py-2.5 <?= $isCurrent ? 'v3-stage-current' : ($isReached ? 'v3-stage-reached' : 'v3-stage-pending') ?>">
                                            <span class="block text-[11px]" dir="ltr"><?= str_pad((string) ($stageIndex + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                            <span class="mt-0.5 block text-sm font-extrabold"><?= esc($visitStageLabels[$stage]) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>

                                <?php if (has_permission('appointments', 'edit')): ?>
                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <?php foreach ($allowedVisitStages as $targetStage): ?>
                                            <?php
                                            $isTerminalAction = in_array($targetStage, ['cancelled', 'no_show'], true);
                                            $buttonClass = $isTerminalAction
                                                ? 'v3-stage-action-terminal'
                                                : 'v3-stage-action-primary';
                                            ?>
                                            <?php $needsCheckoutAcknowledgement = $targetStage === 'completed' && !empty($checkoutCompletion['requires_override']); ?>
                                            <form method="post" action="<?= base_url('appointment/' . (int) $appointment['id'] . '/transition') ?>" class="<?= $needsCheckoutAcknowledgement ? 'w-full rounded-2xl bg-amber-50 p-4 ring-1 ring-amber-200' : '' ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="<?= esc($targetStage, 'attr') ?>">
                                                <input type="hidden" name="return_to" value="<?= esc($returnToContext, 'attr') ?>">
                                                <?php if ($needsCheckoutAcknowledgement): ?>
                                                    <div class="mb-4">
                                                        <p class="text-sm font-extrabold text-amber-950"><?= esc(lang('UI.checkoutCompletionNeedsReview')) ?></p>
                                                        <ul class="mt-2 grid gap-1.5 text-sm font-semibold text-amber-900 sm:grid-cols-2">
                                                            <?php foreach ($checkoutCompletionIssueCodes as $issueCode): ?>
                                                                <li class="flex items-start gap-2"><i class="fas fa-circle-exclamation mt-1 text-xs"></i><span><?= esc($checkoutCompletionIssueLabels[$issueCode] ?? $issueCode) ?></span></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                    <label class="flex items-start gap-3 p-3 text-sm font-bold ring-1 ring-amber-200">
                                                        <input type="checkbox" name="checkout_override" value="1" class="mt-0.5 h-4 w-4 rounded text-amber-600 focus:ring-amber-500" required <?= old('checkout_override') ? 'checked' : '' ?>>
                                                        <span><?= esc(lang('UI.checkoutCompletionAcknowledge')) ?></span>
                                                    </label>
                                                    <label class="mt-3 block text-sm font-bold">
                                                        <span><?= esc(lang('UI.checkoutCompletionReason')) ?></span>
                                                        <textarea name="checkout_override_reason" minlength="10" maxlength="500" rows="2" required class="mt-2 w-full border border-amber-200 px-3 py-2.5 text-sm font-medium focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/15"><?= esc(old('checkout_override_reason')) ?></textarea>
                                                    </label>
                                                <?php endif; ?>
                                                <button type="submit" class="<?= $needsCheckoutAcknowledgement ? 'mt-3 ' : '' ?>v3-stage-action <?= $buttonClass ?>">
                                                    <?= esc(lang('UI.appointmentMoveToStage', ['stage' => $visitStageLabels[$targetStage] ?? $targetStage])) ?>
                                                </button>
                                            </form>
                                        <?php endforeach; ?>
                                        <?php if ($visitIsClosed): ?>
                                            <p class="w-full px-4 py-3 text-sm font-bold ring-1">
                                                <i class="fas fa-lock me-2"></i><?= esc(lang('UI.appointmentVisitClosed')) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="max-w-2xl">
                                <p class="text-xs uppercase tracking-[0.18em]"><?= esc(lang('UI.checkoutEyebrow')) ?></p>
                                <h4 class="mt-1 text-xl"><?= esc(lang('UI.checkoutTitle')) ?></h4>
                                <p class="mt-2 text-sm font-medium leading-6">
                                    <?= esc(lang('UI.checkoutHelp')) ?>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php if (has_permission('examinations', 'create')): ?>
                                    <?php if (!empty($primaryLinkedExamination['id'])): ?>
                                        <a href="<?= esc(workflow_context_url('examination/' . (int) $primaryLinkedExamination['id'], $returnToContext)) ?>" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-bold transition">
                                            <i class="fas fa-stethoscope me-2"></i><?= esc(lang('UI.appointmentOpenExamination')) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= esc(base_url('examination/create?' . http_build_query(['patient_id' => (int) ($appointment['patient_id'] ?? 0), 'appointment_id' => (int) $appointment['id'], 'return_to' => $returnToContext])), 'attr') ?>" data-examination-create-modal-open class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-bold transition">
                                            <i class="fas fa-stethoscope me-2"></i><?= esc(lang('UI.appointmentStartExamination')) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (has_permission('treatments', 'create') && !empty($primaryLinkedExamination['id'])): ?>
                                    <a href="<?= esc(base_url('treatment/create?' . http_build_query(['examination_id' => (int) $primaryLinkedExamination['id'], 'appointment_id' => (int) $appointment['id'], 'return_to' => $returnToContext])), 'attr') ?>" data-treatment-create-modal-open class="inline-flex items-center justify-center border border-pink-200 bg-pink-50 px-4 py-2.5 text-sm font-bold text-pink-800 transition hover:bg-pink-100">
                                        <i class="fas fa-tooth me-2"></i><?= esc(lang('UI.appointmentAddTreatment')) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds) && !$hasMixedInvoiceableCurrencies): ?>
                                    <button type="button" data-app-drawer-open="appointmentInvoiceDrawer" class="inline-flex items-center justify-center border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-900 transition hover:bg-amber-100">
                                        <i class="fas fa-file-invoice-dollar me-2"></i><?= esc(lang('UI.appointmentCreateInvoiceFromWork')) ?>
                                        <span class="ms-2 rounded-lg px-2 py-0.5 text-xs" dir="ltr"><?= esc(formatCurrency($invoiceableChargeTotal)) ?></span>
                                    </button>
                                <?php endif; ?>
                                <?php if (has_permission('finance', 'view') && (float) ($moneySummary['balance_due'] ?? 0) > 0.009): ?>
                                    <a href="<?= esc(workflow_context_url('patient/' . (int) ($appointment['patient_id'] ?? 0) . '?tab=finances', $returnToContext)) ?>" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-bold transition">
                                        <i class="fas fa-money-bill-wave me-2"></i><?= esc(lang('UI.checkoutRecordPayment')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-3">
                            <div class="p-4 ring-1">
                                <p class="text-xs uppercase tracking-wide"><?= esc(lang('UI.checkoutClinicalReview')) ?></p>
                                <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 <?= esc($clinicalPresentation['class']) ?>">
                                    <i class="<?= esc($clinicalPresentation['icon']) ?>"></i><?= esc($clinicalPresentation['label']) ?>
                                </span>
                            </div>
                            <div class="p-4 ring-1 ring-amber-100">
                                <p class="text-xs uppercase tracking-wide text-amber-700"><?= esc(lang('UI.checkoutBillingReview')) ?></p>
                                <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 <?= esc($billingPresentation['class']) ?>">
                                    <i class="<?= esc($billingPresentation['icon']) ?>"></i><?= esc($billingPresentation['label']) ?>
                                </span>
                            </div>
                            <div class="p-4 ring-1">
                                <p class="text-xs uppercase tracking-wide"><?= esc(lang('UI.checkoutPaymentReview')) ?></p>
                                <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 <?= esc($settlementPresentation['class']) ?>">
                                    <i class="<?= esc($settlementPresentation['icon']) ?>"></i><?= esc($settlementPresentation['label']) ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($hasMixedInvoiceableCurrencies): ?>
                            <div class="mt-4 bg-amber-50 p-4 text-sm font-bold text-amber-900 ring-1 ring-amber-200">
                                <i class="fas fa-triangle-exclamation me-2"></i><?= esc(lang('UI.checkoutMixedCurrencies')) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (($checkout['link_basis'] ?? '') !== 'explicit' && ($linkedExaminations !== [] || $linkedTreatments !== [])): ?>
                            <div class="mt-4 p-4 text-sm font-semibold ring-1">
                                <i class="fas fa-circle-info me-2"></i><?= esc(lang('UI.checkoutContextualLinkNote')) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
                            <div class="border p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h5 class="text-sm"><?= esc(lang('UI.appointmentLinkedExaminations')) ?></h5>
                                    <span class="px-2.5 py-1 text-xs"><?= count($linkedExaminations) ?></span>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <?php if ($linkedExaminations): ?>
                                        <?php foreach (array_slice($linkedExaminations, 0, 3) as $exam): ?>
                                            <a href="<?= esc(workflow_context_url('examination/' . (int) $exam['id'], $returnToContext)) ?>" class="block border p-3 text-sm transition">
                                                <span class="block font-bold"><?= esc($exam['examination_id'] ?? ('#' . (int) $exam['id'])) ?></span>
                                                <span class="mt-1 block text-xs font-semibold"><?= esc(ucwords(str_replace('_', ' ', (string) ($exam['examination_type'] ?? '')))) ?> · <?= esc(ucwords(str_replace('_', ' ', (string) ($exam['status'] ?? '')))) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="border border-dashed p-3 text-sm font-medium"><?= esc(lang('UI.appointmentNoLinkedClinicalWork')) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="border border-pink-100 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h5 class="text-sm text-pink-950"><?= esc(lang('UI.appointmentLinkedTreatments')) ?></h5>
                                    <span class="px-2.5 py-1 text-xs text-pink-700"><?= count($linkedTreatments) ?></span>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <?php if ($linkedTreatments): ?>
                                        <?php foreach (array_slice($linkedTreatments, 0, 3) as $treatment): ?>
                                            <a href="<?= esc(workflow_context_url('treatment/' . (int) $treatment['id'], $returnToContext)) ?>" class="block border p-3 text-sm transition">
                                                <span class="block font-bold"><?= esc($treatment['treatment_name'] ?? $treatment['treatment_id'] ?? ('#' . (int) $treatment['id'])) ?></span>
                                                <span class="mt-1 block text-xs font-semibold"><?= esc($treatment['treatment_id'] ?? '') ?> · <?= esc(ucwords(str_replace('_', ' ', (string) ($treatment['status'] ?? '')))) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="border border-dashed border-pink-200 p-3 text-sm font-medium"><?= esc(lang('UI.appointmentNoLinkedTreatments')) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="border p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h5 class="text-sm"><?= esc(lang('UI.appointmentFinancialTrace')) ?></h5>
                                    <?php if (!empty($invoiceableChargeLineIds)): ?>
                                        <span class="bg-amber-100 px-2.5 py-1 text-xs text-amber-800"><?= esc(lang('UI.moneyTrailOpenChargeLines', ['count' => count($invoiceableChargeLineIds)])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="p-3">
                                        <p class="text-xs font-bold"><?= esc(lang('UI.moneyTrailCapturedWork')) ?></p>
                                        <p class="mt-1 text-base" dir="ltr"><?= esc(formatCurrency($moneySummary['captured_charges'] ?? 0)) ?></p>
                                    </div>
                                    <div class="p-3">
                                        <p class="text-xs font-bold"><?= esc(lang('UI.moneyTrailCharged')) ?></p>
                                        <p class="mt-1 text-base" dir="ltr"><?= esc(formatCurrency($moneySummary['charged'] ?? 0)) ?></p>
                                    </div>
                                    <div class="p-3">
                                        <p class="text-xs font-bold"><?= esc(lang('UI.moneyTrailPaid')) ?></p>
                                        <p class="mt-1 text-base" dir="ltr"><?= esc(formatCurrency($moneySummary['paid'] ?? 0)) ?></p>
                                    </div>
                                    <div class="p-3">
                                        <p class="text-xs font-bold"><?= esc(lang('UI.moneyTrailBalanceDue')) ?></p>
                                        <p class="mt-1 text-base text-amber-700" dir="ltr"><?= esc(formatCurrency($moneySummary['balance_due'] ?? 0)) ?></p>
                                    </div>
                                </div>
                                <?php if (empty($linkedExaminations)): ?>
                                    <p class="mt-3 text-xs font-semibold"><?= esc(lang('UI.appointmentFinancialTraceEmpty')) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('appointment/partials/contact_history', ['appointment'=>$appointment,'contact_history'=>$contact_history??[],'contact_history_enabled'=>$contact_history_enabled??false,'contact_request_key'=>$contact_request_key??'']) ?>

<?= view('examination/partials/quick_create_modal', [
    'is_rtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => true,
    'selected_patient' => $contextPatient,
    'selected_appointment_id' => (int) $appointment['id'],
]) ?>
<?php if (!empty($primaryLinkedExamination['id'])): ?>
    <?= view('treatment/partials/quick_create_modal', [
        'isRtl' => $isRtl,
        'return_to' => $returnToContext,
        'contextual' => true,
        'selected_patient' => $contextPatient,
        'patients' => [],
        'treatment_types' => $treatment_types ?? [],
        'inventory_items' => $inventory_items ?? [],
        'clinic_providers' => $clinic_providers ?? [],
        'validation' => session()->getFlashdata('validation'),
        'selected_examination_id' => (int) $primaryLinkedExamination['id'],
        'selected_appointment_id' => (int) $appointment['id'],
    ]) ?>
<?php endif; ?>
<?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds) && !$hasMixedInvoiceableCurrencies): ?>
    <?php
    $appointmentInvoiceDrawerId = 'appointmentInvoiceDrawer';
    $appointmentInvoiceFormId = 'appointmentInvoiceDrawerForm';
    ob_start();
    ?>
    <form id="<?= esc($appointmentInvoiceFormId) ?>"
          method="post"
          action="<?= base_url('finance/store') ?>"
          enctype="multipart/form-data"
          data-finance-calc
          data-provider-split
          data-base-currency="<?= esc($appointment_finance_base_currency ?? settings()->get('currency', 'ILS')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
        <?= view('finance/partials/form_fields', [
            'contextual' => true,
            'id_prefix' => $appointmentInvoiceDrawerId,
            'selected_patient' => $contextPatient,
            'patients' => !empty($contextPatient) ? [$contextPatient] : [],
            'transaction_type_preset' => 'invoice',
            'lock_transaction_type' => true,
            'base_currency' => $appointment_finance_base_currency ?? settings()->get('currency', 'ILS'),
            'currency_options' => $appointment_finance_currency_options ?? ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'liquidity_account_options' => $appointment_finance_liquidity_options ?? [],
            'clinic_providers' => $clinic_providers ?? [],
            'clinical_charge_lines' => $invoiceableChargeLines,
            'selected_clinical_charge_line_ids' => $invoiceableChargeLineIds,
            'is_rtl' => $isRtl,
        ]) ?>
    </form>
    <?php $appointmentInvoiceDrawerBody = (string) ob_get_clean(); ?>
    <?= view('components/app_drawer', [
        'id' => $appointmentInvoiceDrawerId,
        'title' => lang('UI.appointmentCreateInvoiceFromWork'),
        'description' => lang('UI.appointmentCreateInvoiceFromWorkHelp'),
        'icon' => 'fas fa-file-invoice-dollar',
        'tone' => 'bg-amber-600',
        'size' => 'wide',
        'body' => $appointmentInvoiceDrawerBody,
        'footer' => '<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">'
            . '<button type="button" class="inline-flex items-center justify-center border px-4 py-2.5 text-sm font-bold" data-app-drawer-close="' . esc($appointmentInvoiceDrawerId) . '">' . esc(lang('UI.cancel')) . '</button>'
            . '<a href="' . esc(workflow_context_url('finance/create?' . http_build_query([
                'patient_id' => (int) ($appointment['patient_id'] ?? 0),
                'transaction_type' => 'invoice',
                'clinical_charge_line_ids' => $invoiceableChargeLineIds,
            ], '', '&', PHP_QUERY_RFC3986), $returnToContext)) . '" class="inline-flex items-center justify-center border border-amber-200 px-4 py-2.5 text-sm font-bold text-amber-800 hover:bg-amber-50">' . esc(lang('UI.openFullPage')) . '</a>'
            . '<button type="submit" form="' . esc($appointmentInvoiceFormId) . '" class="inline-flex items-center justify-center bg-amber-600 px-5 py-2.5 text-sm font-bold hover:bg-amber-700">' . esc(lang('Finance.createTransaction')) . '</button>'
            . '</div>',
    ]) ?>
    <script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>

<script>
function confirmAppointment(id) {
    if (confirm('<?= esc(lang('UI.appointmentConfirmPrompt'), 'js') ?>')) {
        fetch(`<?= base_url('appointment') ?>/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('UI.appointmentErrorPrefix'), 'js') ?>' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('UI.appointmentConfirmError'), 'js') ?>');
        });
    }
}

function completeAppointment(id) {
    if (confirm('<?= esc(lang('UI.appointmentCompletePrompt'), 'js') ?>')) {
        fetch(`<?= base_url('appointment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('UI.appointmentErrorPrefix'), 'js') ?>' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('UI.appointmentCompleteError'), 'js') ?>');
        });
    }
}

function cancelAppointment(id) {
    if (confirm('<?= esc(lang('UI.appointmentCancelPrompt'), 'js') ?>')) {
        fetch(`<?= base_url('appointment') ?>/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('UI.appointmentErrorPrefix'), 'js') ?>' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('UI.appointmentCancelError'), 'js') ?>');
        });
    }
}
</script>
<?= $this->endSection() ?>
