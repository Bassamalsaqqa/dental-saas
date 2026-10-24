<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);

$typeLabels = ['initial' => $tr('type_initial'), 'periodic' => $tr('type_periodic'), 'emergency' => $tr('type_emergency'), 'follow_up' => $tr('type_follow_up')];
$statusLabels = ['pending' => $tr('status_pending'), 'in_progress' => $tr('status_in_progress'), 'completed' => $tr('status_completed'), 'cancelled' => $tr('status_cancelled')];
$typeKey = strtolower((string)($examination['examination_type'] ?? 'follow_up'));
$statusKey = strtolower((string)($examination['status'] ?? 'pending'));
$typeLabel = $typeLabels[$typeKey] ?? $tr('type_follow_up');
$statusLabel = $statusLabels[$statusKey] ?? $tr('status_pending');
$doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? ''));
$patientName = trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));
$patientGender = strtolower((string)($examination['gender'] ?? 'other'));
$treatmentCount = is_array($treatments ?? null) ? count($treatments) : 0;
$invoiceableChargeLines = $examination_invoiceable_charge_lines ?? [];
$invoiceableChargeLineIds = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $invoiceableChargeLines);
$invoiceableChargeLineIds = array_values(array_filter($invoiceableChargeLineIds, static fn(int $id): bool => $id > 0));
$invoiceableChargeTotal = array_reduce(
    $invoiceableChargeLines,
    static fn(float $carry, array $row): float => $carry + max(0, (float) ($row['net_production_amount'] ?? 0)),
    0.0
);
$odontogramConditionCount = (int)($odontogram_stats['total_conditions'] ?? (is_array($odontogram ?? null) ? count($odontogram) : 0));
$returnToContext = 'examination/' . (int) ($examination['id'] ?? 0);
$clinicalNoteState = is_array($clinical_note_state ?? null) ? $clinical_note_state : ['signed' => false, 'signature' => null, 'addenda' => []];
$clinicalNoteSigned = !empty($clinicalNoteState['signed']);
$clinicalNoteSignature = $clinicalNoteState['signature'] ?? null;
$clinicalNoteAddenda = $clinicalNoteState['addenda'] ?? [];

$statusTone = match ($statusKey) {
    'completed'  => ['bg' => 'rgba(16,185,129,0.22)', 'border' => 'rgba(167,243,208,0.4)'],
    'in_progress' => ['bg' => 'rgba(59,130,246,0.22)', 'border' => 'rgba(147,197,253,0.4)'],
    'cancelled'   => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
    'pending'     => ['bg' => 'rgba(251,191,36,0.22)', 'border' => 'rgba(253,230,138,0.4)'],
    default       => ['bg' => 'rgba(255,255,255,0.12)', 'border' => 'rgba(255,255,255,0.2)'],
};

$heroTheme  = 'patient-hero-card-neutral'; $avatarTone = 'patient-avatar-badge-neutral';
if ($patientGender === 'female') { $heroTheme = 'patient-hero-card-female'; $avatarTone = 'patient-avatar-badge-female'; }
elseif ($patientGender === 'male') { $heroTheme = 'patient-hero-card-male'; $avatarTone = 'patient-avatar-badge-male'; }

$healthFlags = array_values(array_filter([
    ['enabled' => !empty($examination['health_update_medical_changes'] ?? 0),    'label' => $tr('health_medical_changes'),     'icon' => 'fa-notes-medical',       'color' => 'emerald'],
    ['enabled' => !empty($examination['health_update_medication_changes'] ?? 0), 'label' => $tr('health_medication_changes'),   'icon' => 'fa-pills',               'color' => 'blue'],
    ['enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0),'label' => $tr('health_allergies_reactions'),  'icon' => 'fa-allergies',           'color' => 'rose'],
    ['enabled' => !empty($examination['health_update_pregnancy'] ?? 0),          'label' => $tr('health_pregnancy'),            'icon' => 'fa-baby',                'color' => 'fuchsia'],
    ['enabled' => !empty($examination['health_update_current_symptoms'] ?? 0),   'label' => $tr('health_current_symptoms'),     'icon' => 'fa-triangle-exclamation','color' => 'amber'],
], fn($r) => !empty($r['enabled'])));
?>



<div class="denta-examination-workspace exam-page-shell v3-module-workspace">
<div class="v3-module-content">

    <!-- ===== Hero Header ===== -->
    <section class="v3-record-banner"><div class="v3-record-heading"><h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($patientName ?: $tr('examination_details')) ?></h1><span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold text-white" style="background:<?= esc($statusTone['bg']) ?>;border-color:<?= esc($statusTone['border']) ?>;"><?= esc($statusLabel) ?></span><div class="v3-record-meta">
                                <div class="patient-hero-chip patient-hero-chip-ltr" title="<?= esc($tr('patient_number') . ': ' . ($examination['patient_number'] ?? '')) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-id-card"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('patient_number')) ?></span><span class="patient-hero-chip-value"><?= esc($examination['patient_number'] ?? $tr('na')) ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('detail_examination_type') . ': ' . $typeLabel) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-stethoscope"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('detail_examination_type')) ?></span><span class="patient-hero-chip-value"><?= esc($typeLabel) ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('examination_date') . ': ' . (!empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-calendar-day"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('examination_date')) ?></span><span class="patient-hero-chip-value"><?= !empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na') ?></span></span>
                                </div>
                                <div class="patient-hero-chip" title="<?= esc($tr('created_by') . ': ' . ($doctorName ?: $tr('unknown'))) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-user-doctor"></i></span>
                                    <span class="patient-hero-chip-text"><span class="patient-hero-chip-label"><?= esc($tr('created_by')) ?></span><span class="patient-hero-chip-value"><?= esc($doctorName ?: $tr('unknown')) ?></span></span>
                                </div>
                            </div></div><div class="v3-record-actions">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php if (!$clinicalNoteSigned): ?>
                        <a href="<?= workflow_context_url('examination/' . $examination['id'] . '/edit', $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-pen"></i><span><?= esc($tr('edit_examination')) ?></span></a>
                        <?php endif; ?>
                        <?php if (!empty($examination['patient_id'])): ?>
                        <a href="<?= workflow_context_url('patient/' . $examination['patient_id'], $return_to ?? null) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-user"></i><span><?= esc(lang('UI.patientProfile')) ?></span></a>
                        <?php endif; ?>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <a href="<?= esc(workflow_back('examination', $return_to ?? null)) ?>" class="patient-header-action patient-header-action-soft"><i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><span><?= esc($tr('back')) ?></span></a>
                    </div>
                    <?php if ($statusKey !== 'completed'): ?>
                    <div class="grid gap-2 sm:grid-cols-1">
                        <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>">
                            <?= csrf_field() ?>
                            <?php if (!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
                            <button type="submit" class="patient-header-action patient-header-action-emerald w-full"><i class="fas fa-check-circle"></i><span><?= esc($tr('finalize_assessment')) ?></span></button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div></section>

    <section class="exam-snapshot-section mb-6 p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="exam-snapshot-eyebrow"><?= esc($tr('examination_details')) ?></p>
                <h2 class="exam-snapshot-title"><?= esc($tr('examination_summary_help')) ?></h2>
            </div>
            <?php if (!empty($invoiceableChargeLineIds)): ?>
                <button type="button" data-app-drawer-open="examinationInvoiceDrawer" class="v3-action v3-action-primary inline-flex w-fit items-center justify-center gap-2 rounded-xl px-3.5 py-2.5 text-sm font-black transition">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span><?= esc($tr('create_invoice_from_work')) ?></span>
                </button>
            <?php endif; ?>
        </div>
        <div class="exam-snapshot-grid">
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('detail_status')) ?></p>
                <p class="exam-snapshot-value"><?= esc($statusLabel) ?></p>
                <p class="exam-snapshot-meta"><?= esc($tr('examination_date')) ?>: <?= !empty($examination['examination_date']) ? esc(formatDate($examination['examination_date'])) : esc($tr('na')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('related_treatments')) ?></p>
                <p class="exam-snapshot-value"><?= esc((string) $treatmentCount) ?></p>
                <p class="exam-snapshot-meta"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('dental_chart_odontogram')) ?></p>
                <p class="exam-snapshot-value"><?= esc((string) $odontogramConditionCount) ?></p>
                <p class="exam-snapshot-meta"><?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?></p>
            </div>
            <div class="exam-snapshot-card">
                <p class="exam-snapshot-label"><?= esc($tr('create_invoice_from_work')) ?></p>
                <p class="exam-snapshot-value" dir="ltr"><?= esc(formatCurrency($invoiceableChargeTotal)) ?></p>
                <p class="exam-snapshot-meta"><?= esc($tr('open_full_finance_page')) ?></p>
            </div>
        </div>
    </section>

    <!-- ===== Content ===== -->
    <div class="exam-workspace-section grid grid-cols-1 gap-6 xl:grid-cols-12">

        <!-- LEFT COLUMN -->
        <div class="xl:col-span-8 space-y-6">
            <div class="exam-section-heading flex items-center gap-3">
                <span class="exam-section-heading-icon"><i class="fas fa-notes-medical"></i></span>
                <div>
                    <p><?= esc($tr('clinical_summary')) ?></p>
                    <h2><?= esc($tr('care_plan_help')) ?></h2>
                </div>
            </div>

            <!-- Chief Complaint + HPI -->
            <div class="exam-card exam-card-clinical">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-clipboard-list text-sm"></i></span>
                    <span><?= esc($tr('clinical_summary')) ?></span>
                </div>
                <div class="exam-card-body">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="exam-text-tile exam-text-tile-blue">
                            <p class="exam-field-label"><?= esc($tr('chief_complaint')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['chief_complaint']) ? nl2br(esc($examination['chief_complaint'])) : '<span class="text-gray-400">' . esc($tr('na')) . '</span>' ?></div>
                        </div>
                        <div class="exam-text-tile exam-text-tile-indigo">
                            <p class="exam-field-label"><?= esc($tr('history_of_present_illness')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['history_of_present_illness']) ? nl2br(esc($examination['history_of_present_illness'])) : '<span class="text-gray-400">' . esc($tr('na')) . '</span>' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Findings + Diagnosis -->
            <div class="exam-card exam-card-findings">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#d1fae5;color:#059669;"><i class="fas fa-magnifying-glass text-sm"></i></span>
                    <span><?= esc($tr('clinical_findings')) ?> &amp; <?= esc($tr('diagnosis')) ?></span>
                </div>
                <div class="exam-card-body space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="exam-text-tile exam-text-tile-violet">
                            <p class="exam-field-label"><?= esc($tr('clinical_findings')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['clinical_findings']) ? nl2br(esc($examination['clinical_findings'])) : '<span class="text-gray-400">' . esc($tr('no_clinical_findings_recorded')) . '</span>' ?></div>
                        </div>
                        <div class="exam-text-tile exam-text-tile-emerald">
                            <p class="exam-field-label"><?= esc($tr('diagnosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['diagnosis']) ? nl2br(esc($examination['diagnosis'])) : '<span class="text-gray-400">' . esc($tr('no_diagnosis_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <div class="exam-odontogram-callout rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= esc($tr('dental_chart_odontogram')) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?></p>
                            </div>
                            <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], $returnToContext) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-semibold text-sky-700 border border-gray-200 hover:bg-sky-50 hover:border-sky-200 transition-all shadow-sm">
                                <i class="fas fa-tooth"></i><span><?= esc($odontogramConditionCount > 0 ? $tr('view_full_odontogram') : $tr('create_odontogram')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Care Plan -->
            <div class="exam-card exam-card-careplan">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clipboard-check text-sm"></i></span>
                    <span><?= esc($tr('care_plan_heading')) ?></span>
                </div>
                <div class="exam-card-body space-y-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Treatment Plan card -->
                        <div class="rounded-xl border p-4 transition-shadow" style="border-color:#cffafe;background:#f0fdfa;">
                            <p class="exam-field-label"><?= esc($tr('treatment_plan')) ?></p>
                            <?php if (!empty($latest_contract)): ?>
                                <p class="mt-2 text-base font-black leading-snug text-gray-900"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                                <p class="mt-1 text-sm font-bold text-gray-500"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-arrow-up-right-from-square"></i><?= esc($tr('view')) ?></a>
                            <?php else: ?>
                                <p class="mt-2 text-sm font-bold leading-relaxed text-gray-500"><?= esc($tr('treatment_plan_hint')) ?></p>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '') ?>" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 transition-colors"><i class="fas fa-plus"></i><?= esc($tr('add_treatment_plan')) ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <!-- Treatments card -->
                        <div class="rounded-xl border p-4 transition-shadow" style="border-color:#d1fae5;background:#ecfdf5;">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="exam-field-label"><?= esc($tr('related_treatments')) ?></p>
                                    <p class="mt-2 text-sm font-bold leading-relaxed text-gray-500"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
                                </div>
                                <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= workflow_context_url('treatment/create?examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-treatment-create-modal-open class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200 hover:bg-emerald-50 transition-all shadow-sm"><i class="fas fa-plus text-[10px]"></i><?= esc($tr('add_treatment')) ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($treatments)): ?>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <?php foreach ($treatments as $t): ?>
                                <a href="<?= base_url('treatment/' . $t['id']) ?>" class="exam-treatment-chip inline-flex rounded-lg bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all"><?= esc($t['treatment_name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Prognosis -->
                        <div class="exam-text-tile exam-text-tile-amber">
                            <p class="exam-field-label"><?= esc($tr('prognosis')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : '<span class="text-gray-400">' . esc($tr('no_prognosis_recorded')) . '</span>' ?></div>
                        </div>
                        <!-- Recommendations -->
                        <div class="exam-text-tile exam-text-tile-sky">
                            <p class="exam-field-label"><?= esc($tr('recommendations')) ?></p>
                            <div class="exam-field-value"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : '<span class="text-gray-400">' . esc($tr('no_recommendations_recorded')) . '</span>' ?></div>
                        </div>
                    </div>
                    <?php if (!empty($examination['examination_notes'])): ?>
                    <div class="exam-notes-panel">
                        <p class="exam-field-label"><?= esc($tr('examination_notes')) ?></p>
                        <div class="exam-field-value"><?= nl2br(esc($examination['examination_notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?= view('examination/partials/clinical_note_governance', [
                'examination' => $examination,
                'status_key' => $statusKey,
                'clinical_note_signed' => $clinicalNoteSigned,
                'clinical_note_signature' => $clinicalNoteSignature,
                'clinical_note_addenda' => $clinicalNoteAddenda,
            ]) ?>
        </div>

        <!-- RIGHT COLUMN -->
        <aside class="xl:col-span-4 xl:sticky xl:top-6 space-y-6">
            <div class="exam-section-heading exam-section-heading--context flex items-center gap-3">
                <span class="exam-section-heading-icon"><i class="fas fa-route"></i></span>
                <div>
                    <p><?= esc($tr('workflow_heading')) ?></p>
                    <h2><?= esc($tr('workflow_help')) ?></h2>
                </div>
            </div>

            <!-- Quick Workflow -->
            <div class="exam-card exam-card-workflow">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bolt text-sm"></i></span>
                    <span><?= esc($tr('workflow_heading')) ?></span>
                </div>
                <div class="exam-card-body exam-workflow-stack">
                    <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], $returnToContext) ?>" class="exam-action-primary" style="background:#2563eb;color:#fff;">
                        <i class="fas fa-tooth text-sm"></i>
                        <?= esc($tr('open_visit_chart')) ?>
                    </a>
                    <?php if ($statusKey !== 'completed'): ?>
                    <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
                        <button type="submit" class="exam-action-primary" style="background:#059669;color:#fff;"><?= esc($tr('finalize_assessment')) ?></button>
                    </form>
                    <?php endif; ?>
                    <?php if (has_permission('treatments', 'create')): ?>
                    <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '') ?>" class="exam-action-secondary"><i class="fas fa-file-signature text-sm"></i><?= esc(!empty($latest_contract) ? $tr('add_new_plan') : $tr('add_treatment_plan')) ?></a>
                    <a href="<?= workflow_context_url('treatment/create?examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-treatment-create-modal-open class="exam-action-secondary"><i class="fas fa-plus text-sm"></i><?= esc($tr('add_treatment')) ?></a>
                    <?php endif; ?>
                    <?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds)): ?>
                    <button type="button" data-app-drawer-open="examinationInvoiceDrawer" class="exam-action-secondary exam-action-secondary-warning">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                        <span><?= esc($tr('create_invoice_from_work')) ?></span>
                        <span class="ms-auto text-xs font-black" dir="ltr"><?= esc(formatCurrency($invoiceableChargeTotal)) ?></span>
                    </button>
                    <?php endif; ?>
                    <?php if (has_permission('appointments', 'create')): ?>
                    <a href="<?= workflow_context_url('appointment/create?patient_id=' . (int) $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . (int) $examination['id'], $returnToContext) ?>" data-appointment-create-modal-open class="exam-action-secondary"><i class="fas fa-calendar-plus text-sm"></i><?= esc($tr('schedule_follow_up')) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="exam-money-trail-shell">
                <?= view('components/money_trail_panel', [
                    'money_trail' => $money_trail ?? [],
                    'is_rtl' => $isRtl,
                    'return_to' => $returnToContext ?? null,
                ]) ?>
            </div>

            <!-- Health Flags -->
            <div class="exam-card exam-card-history">
                <div class="exam-card-header">
                    <span class="exam-icon-circle" style="background:#ffe4e6;color:#e11d48;"><i class="fas fa-heart-pulse text-sm"></i></span>
                    <span><?= esc($tr('medical_dental_history')) ?></span>
                </div>
                <div class="exam-card-body flex flex-col gap-3">
                    <?php if (!empty($healthFlags)): ?>
                        <?php foreach ($healthFlags as $f): ?>
                        <div class="health-flag">
                            <span class="health-flag-dot" style="background:var(--tw-<?= $f['color'] ?>-500, #94a3b8);"></span>
                            <i class="fas <?= $f['icon'] ?> text-gray-500"></i>
                            <span class="text-sm font-semibold text-gray-800"><?= esc($f['label']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($examination['medical_history'])): ?>
                    <div class="exam-history-text-block">
                        <p class="exam-field-label"><?= esc($tr('medical_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['medical_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($examination['dental_history'])): ?>
                    <div class="exam-history-text-block">
                        <p class="exam-field-label"><?= esc($tr('dental_history')) ?></p>
                        <div class="exam-field-value text-sm"><?= nl2br(esc($examination['dental_history'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (empty($healthFlags) && empty($examination['medical_history']) && empty($examination['dental_history'])): ?>
                    <p class="exam-history-empty text-sm py-2"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

</div>
</div>
<?= view('treatment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => true,
    'selected_patient' => $selected_treatment_patient ?? null,
    'patients' => [],
    'treatment_types' => $treatment_types ?? [],
    'inventory_items' => $inventory_items ?? [],
    'clinic_providers' => $clinic_providers ?? [],
    'validation' => session()->getFlashdata('validation'),
    'selected_examination_id' => $selected_examination_id ?? ($examination['id'] ?? null),
    'selected_appointment_id' => $examination['appointment_id'] ?? null,
]) ?>
<?php if (has_permission('finance', 'create') && !empty($invoiceableChargeLineIds)): ?>
    <?php
    $examinationInvoiceDrawerId = 'examinationInvoiceDrawer';
    $examinationInvoiceFormId = 'examinationInvoiceDrawerForm';
    ob_start();
    ?>
    <form id="<?= esc($examinationInvoiceFormId) ?>"
          method="post"
          action="<?= base_url('finance/store') ?>"
          enctype="multipart/form-data"
          data-finance-calc
          data-provider-split
          data-base-currency="<?= esc($examination_finance_base_currency ?? settings()->get('currency', 'ILS')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
        <?= view('finance/partials/form_fields', [
            'contextual' => true,
            'id_prefix' => $examinationInvoiceDrawerId,
            'selected_patient' => $selected_treatment_patient ?? null,
            'patients' => !empty($selected_treatment_patient) ? [$selected_treatment_patient] : [],
            'transaction_type_preset' => 'invoice',
            'lock_transaction_type' => true,
            'base_currency' => $examination_finance_base_currency ?? settings()->get('currency', 'ILS'),
            'currency_options' => $examination_finance_currency_options ?? ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'liquidity_account_options' => $examination_finance_liquidity_options ?? [],
            'clinic_providers' => $clinic_providers ?? [],
            'clinical_charge_lines' => $invoiceableChargeLines,
            'selected_clinical_charge_line_ids' => $invoiceableChargeLineIds,
            'is_rtl' => $isRtl,
        ]) ?>
    </form>
    <?php $examinationInvoiceDrawerBody = (string) ob_get_clean(); ?>
    <?= view('components/app_drawer', [
        'id' => $examinationInvoiceDrawerId,
        'title' => $tr('create_invoice_from_work'),
        'description' => $tr('create_invoice_from_work_help'),
        'icon' => 'fas fa-file-invoice-dollar',
        'tone' => 'bg-amber-600',
        'size' => 'wide',
        'body' => $examinationInvoiceDrawerBody,
        'footer' => '<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">'
            . '<button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-app-drawer-close="' . esc($examinationInvoiceDrawerId) . '">' . esc(lang('UI.cancel')) . '</button>'
            . '<a href="' . esc(workflow_context_url('finance/create?' . http_build_query([
                'patient_id' => (int) ($examination['patient_id'] ?? 0),
                'transaction_type' => 'invoice',
                'clinical_charge_line_ids' => $invoiceableChargeLineIds,
            ], '', '&', PHP_QUERY_RFC3986), $returnToContext)) . '" class="inline-flex items-center justify-center rounded-xl border border-amber-200 px-4 py-2.5 text-sm font-bold text-amber-800 hover:bg-amber-50">' . esc($tr('open_full_finance_page')) . '</a>'
            . '<button type="submit" form="' . esc($examinationInvoiceFormId) . '" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-700">' . esc(lang('Finance.createTransaction')) . '</button>'
            . '</div>',
    ]) ?>
    <script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>
<?= view('appointment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $returnToContext,
    'contextual' => true,
    'selected_patient' => $selected_appointment_patient ?? null,
    'chair_options' => $appointment_chair_options ?? [],
    'requested_date' => date('Y-m-d'),
    'appointment_type_prefill' => $appointment_type_prefill ?? 'follow_up',
    'examination_id' => $examination['id'] ?? null,
]) ?>
<?= $this->endSection() ?>
