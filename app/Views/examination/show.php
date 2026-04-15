<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$typeLabels = ['initial' => $tr('type_initial'), 'periodic' => $tr('type_periodic'), 'emergency' => $tr('type_emergency'), 'follow_up' => $tr('type_follow_up')];
$statusLabels = ['pending' => $tr('status_pending'), 'in_progress' => $tr('status_in_progress'), 'completed' => $tr('status_completed'), 'cancelled' => $tr('status_cancelled')];
$typeKey = strtolower((string) ($examination['examination_type'] ?? 'follow_up'));
$statusKey = strtolower((string) ($examination['status'] ?? 'pending'));
$typeLabel = $typeLabels[$typeKey] ?? $tr('type_follow_up');
$statusLabel = $statusLabels[$statusKey] ?? $tr('status_pending');
$doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? ''));
$patientName = trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));
$patientGenderValue = strtolower((string) ($examination['gender'] ?? 'other'));
$patientHeroThemeClass = 'patient-hero-card-neutral';
$patientAvatarToneClass = 'patient-avatar-badge-neutral';
$patientAvatarIconClass = 'fas fa-user';
if ($patientGenderValue === 'female') {
    $patientHeroThemeClass = 'patient-hero-card-female';
    $patientAvatarToneClass = 'patient-avatar-badge-female';
    $patientAvatarIconClass = 'fas fa-person-dress';
} elseif ($patientGenderValue === 'male') {
    $patientHeroThemeClass = 'patient-hero-card-male';
    $patientAvatarToneClass = 'patient-avatar-badge-male';
    $patientAvatarIconClass = 'fas fa-person';
}
$healthUpdateRows = [
    ['enabled' => !empty($examination['health_update_medical_changes'] ?? 0), 'label' => $tr('health_medical_changes'), 'icon' => 'fas fa-notes-medical', 'tone' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    ['enabled' => !empty($examination['health_update_medication_changes'] ?? 0), 'label' => $tr('health_medication_changes'), 'icon' => 'fas fa-pills', 'tone' => 'bg-blue-50 text-blue-800 border-blue-200'],
    ['enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0), 'label' => $tr('health_allergies_reactions'), 'icon' => 'fas fa-allergies', 'tone' => 'bg-red-50 text-red-800 border-red-200'],
    ['enabled' => !empty($examination['health_update_pregnancy'] ?? 0), 'label' => $tr('health_pregnancy'), 'icon' => 'fas fa-baby', 'tone' => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-200'],
    ['enabled' => !empty($examination['health_update_current_symptoms'] ?? 0), 'label' => $tr('health_current_symptoms'), 'icon' => 'fas fa-triangle-exclamation', 'tone' => 'bg-amber-50 text-amber-800 border-amber-200'],
];
$activeHealthUpdates = array_values(array_filter($healthUpdateRows, static fn($row) => !empty($row['enabled'])));
$heroInfoCapsules = [
    ['label' => $tr('patient_number'), 'display_label' => '', 'value' => $examination['patient_number'] ?? $tr('na'), 'icon' => 'fas fa-id-card', 'class' => 'patient-hero-chip-ltr'],
    ['label' => $tr('detail_examination_type'), 'display_label' => '', 'value' => $typeLabel, 'icon' => 'fas fa-stethoscope', 'class' => ''],
    ['label' => $tr('examination_date'), 'display_label' => '', 'value' => !empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na'), 'icon' => 'fas fa-calendar-day', 'class' => ''],
    ['label' => $tr('created_by'), 'display_label' => '', 'value' => $doctorName !== '' ? $doctorName : $tr('unknown'), 'icon' => 'fas fa-user-doctor', 'class' => ''],
];
$treatmentCount = is_array($treatments ?? null) ? count($treatments) : 0;
$odontogramConditionCount = (int) ($odontogram_stats['total_conditions'] ?? (is_array($odontogram ?? null) ? count($odontogram) : 0));
$odontogramNeedsTreatmentCount = (int) ($odontogram_stats['needs_treatment_count'] ?? 0);
$clinicalFlags = $activeHealthUpdates;
if (!empty($examination['medical_history'])) { $clinicalFlags[] = ['enabled' => true, 'label' => $tr('medical_history'), 'icon' => 'fas fa-heart-pulse', 'tone' => 'bg-slate-100 text-slate-800 border-slate-200']; }
if (!empty($examination['dental_history'])) { $clinicalFlags[] = ['enabled' => true, 'label' => $tr('dental_history'), 'icon' => 'fas fa-tooth', 'tone' => 'bg-slate-100 text-slate-800 border-slate-200']; }
?>
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <section class="patient-hero-card <?= esc($patientHeroThemeClass) ?> overflow-hidden rounded-[28px] border border-slate-200">
                <div class="patient-hero-overlay"></div>
                <div class="relative z-10 p-6 sm:p-8">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/90">
                                <span><?= esc($tr('examinations_registry')) ?></span>
                                <span class="h-1 w-1 rounded-full bg-blue-200"></span>
                                <span><?= esc($examination['examination_id'] ?? ('EX-' . ($examination['id'] ?? ''))) ?></span>
                            </div>
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                                <div class="patient-avatar-badge <?= esc($patientAvatarToneClass) ?>">
                                    <i class="<?= esc($patientAvatarIconClass) ?>"></i>
                                </div>
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($patientName !== '' ? $patientName : $tr('examination_details')) ?></h1>
                                            <span class="inline-flex items-center rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                                                <?= esc($statusLabel) ?>
                                            </span>
                                        </div>
                                        <p class="max-w-3xl text-sm leading-6 text-blue-100/90"><?= esc($tr('examination_summary_help')) ?></p>
                                    </div>
                                    <div class="patient-hero-chip-grid">
                                        <?php foreach ($heroInfoCapsules as $capsule): ?>
                                            <?php
                                            $capsuleClass = trim('patient-hero-chip ' . ($capsule['class'] ?? ''));
                                            $capsuleValue = (string) ($capsule['value'] ?? $tr('na'));
                                            $capsuleLabel = (string) ($capsule['label'] ?? '');
                                            $capsuleDisplayLabel = array_key_exists('display_label', $capsule) ? (string) $capsule['display_label'] : $capsuleLabel;
                                            $capsuleIcon = (string) ($capsule['icon'] ?? 'fas fa-circle');
                                            ?>
                                            <div class="<?= esc($capsuleClass) ?>" title="<?= esc($capsuleLabel . ': ' . $capsuleValue) ?>">
                                                <span class="patient-hero-chip-icon"><i class="<?= esc($capsuleIcon) ?>"></i></span>
                                                <span class="patient-hero-chip-text">
                                                    <?php if ($capsuleDisplayLabel !== ''): ?><span class="patient-hero-chip-label"><?= esc($capsuleDisplayLabel) ?></span><?php endif; ?>
                                                    <span class="patient-hero-chip-value"><?= esc($capsuleValue) ?></span>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full space-y-2 xl:w-[21rem]">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="patient-header-action patient-header-action-primary"><i class="fas fa-pen text-sm"></i><span><?= esc($tr('edit_examination')) ?></span></a>
                                <?php if (!empty($examination['patient_id'])): ?>
                                    <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-user text-sm"></i><span><?= esc($tr('back_to_patient')) ?></span></a>
                                <?php endif; ?>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="patient-header-action patient-header-action-soft"><i class="fas fa-arrow-left rtl-back-icon text-sm"></i><span><?= esc($tr('back_to_examinations')) ?></span></a>
                                <div class="patient-hero-chip patient-hero-static-chip" title="<?= esc($tr('detail_examination_type') . ': ' . $typeLabel) ?>">
                                    <span class="patient-hero-chip-icon"><i class="fas fa-clipboard-check"></i></span>
                                    <span class="patient-hero-chip-text">
                                        <span class="patient-hero-chip-value"><?= esc($typeLabel) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-8">
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><span class="h-6 w-1.5 rounded-full bg-cyan-700"></span><div><h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('clinical_summary')) ?></h2><p class="mt-1 text-sm text-slate-500"><?= esc($tr('clinical_summary_help')) ?></p></div></div>
                        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('chief_complaint')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['chief_complaint'] ?? $tr('na'))) ?></p></div>
                            <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('history_of_present_illness')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= !empty($examination['history_of_present_illness']) ? nl2br(esc($examination['history_of_present_illness'])) : esc($tr('na')) ?></p></div>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><span class="h-6 w-1.5 rounded-full bg-cyan-700"></span><div><h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('clinical_findings')) ?> &amp; <?= esc($tr('diagnosis')) ?></h2><p class="mt-1 text-sm text-slate-500"><?= esc($tr('diagnosis_subtitle')) ?></p></div></div>
                        <div class="space-y-6 p-6">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('clinical_findings')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= !empty($examination['clinical_findings']) ? nl2br(esc($examination['clinical_findings'])) : esc($tr('no_clinical_findings_recorded')) ?></p></div>
                                <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('diagnosis')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= !empty($examination['diagnosis']) ? nl2br(esc($examination['diagnosis'])) : esc($tr('no_diagnosis_recorded')) ?></p></div>
                            </div>
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-cyan-950"><?= esc($tr('dental_chart_odontogram')) ?></p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            <?= $odontogramConditionCount > 0 ? esc($tr('odontogram_conditions_recorded', ['count' => $odontogramConditionCount])) : esc($tr('no_odontogram_data_recorded')) ?>
                                        </p>
                                    </div>
                                    <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50"><i class="fas fa-tooth text-sm"></i><span><?= esc(!empty($odontogram) ? $tr('view_full_odontogram') : $tr('create_odontogram')) ?></span></a>
                                </div>
                                <?php if ($odontogramConditionCount > 0): ?>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"><?= esc($odontogramConditionCount) ?> <?= esc($tr('odontogram_data_available')) ?></span>
                                        <?php if ($odontogramNeedsTreatmentCount > 0): ?>
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"><?= esc($odontogramNeedsTreatmentCount) ?> <?= esc($tr('needs_treatment')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3"><span class="h-6 w-1.5 rounded-full bg-cyan-700"></span><div><h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('care_plan_heading')) ?></h2><p class="mt-1 text-sm text-slate-500"><?= esc($tr('care_plan_help')) ?></p></div></div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                            <div class="rounded-xl border border-cyan-100 bg-cyan-50/60 p-4">
                                <div class="flex h-full flex-col gap-4">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-cyan-700"><?= esc($tr('treatment_plan')) ?></p>
                                        <?php if (!empty($latest_contract)): ?>
                                            <p class="mt-2 text-sm font-semibold text-cyan-950"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                                            <p class="mt-1 text-sm text-slate-600"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                        <?php else: ?>
                                            <p class="mt-2 text-sm leading-6 text-slate-700"><?= esc($tr('treatment_plan_hint')) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if (!empty($latest_contract)): ?>
                                            <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-200 transition hover:bg-cyan-50"><i class="fas fa-arrow-up-right-from-square text-sm"></i><span><?= esc($tr('view')) ?></span></a>
                                        <?php elseif (has_permission('treatments', 'create')): ?>
                                            <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-200 transition hover:bg-cyan-50"><i class="fas fa-plus text-sm"></i><span><?= esc($tr('add_treatment_plan')) ?></span></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700"><?= esc($tr('related_treatments')) ?></p>
                                        <p class="mt-2 text-sm text-slate-600"><?= $treatmentCount > 0 ? esc($tr('treatments_recorded', ['count' => $treatmentCount])) : esc($tr('no_treatments_recorded')) ?></p>
                                    </div>
                                    <?php if (has_permission('treatments', 'create')): ?>
                                        <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50"><i class="fas fa-plus text-sm"></i><span><?= esc($tr('add_treatment')) ?></span></a>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($treatments)): ?>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <?php foreach ($treatments as $treatment): ?>
                                            <a href="<?= base_url('treatment/' . $treatment['id']) ?>" class="inline-flex max-w-full items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-950 ring-1 ring-emerald-100 transition hover:bg-emerald-50"><?= esc($treatment['treatment_name']) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('prognosis')) ?></p><p class="mt-2 text-sm leading-6 text-slate-700"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : esc($tr('no_prognosis_recorded')) ?></p></div>
                            <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('recommendations')) ?></p><p class="mt-2 text-sm leading-6 text-slate-700"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : esc($tr('no_recommendations_recorded')) ?></p></div>
                            <?php if (!empty($examination['examination_notes'])): ?>
                                <div class="rounded-xl bg-slate-50 p-4 md:col-span-2"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('examination_notes')) ?></p><p class="mt-2 text-sm leading-6 text-slate-700"><?= nl2br(esc($examination['examination_notes'])) ?></p></div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
                <div class="space-y-6 xl:col-span-4">
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <span class="h-6 w-1.5 rounded-full bg-cyan-700"></span>
                                <div>
                                    <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('workflow_heading')) ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 p-6">
                            <?php if ($examination['status'] != 'completed'): ?>
                                <form method="post" action="<?= base_url('examination/' . $examination['id'] . '/complete') ?>" class="block">
                                    <?= csrf_field() ?>
                                    <?php if (!empty($return_to)): ?>
                                        <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-800"><i class="fas fa-check-circle text-sm"></i><span><?= esc($tr('finalize_assessment')) ?></span></button>
                                </form>
                            <?php endif; ?>
                            <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-800 ring-1 ring-cyan-200 transition hover:bg-cyan-50"><i class="fas fa-file-signature text-sm"></i><span><?= esc(!empty($latest_contract) ? $tr('add_new_plan') : $tr('add_treatment_plan')) ?></span></a>
                                <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50"><i class="fas fa-plus text-sm"></i><span><?= esc($tr('add_treatment')) ?></span></a>
                            <?php endif; ?>
                            <?php if (has_permission('appointments', 'create')): ?>
                                <a href="<?= base_url('appointment/create?patient_id=' . $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50"><i class="fas fa-calendar-plus text-sm"></i><span><?= esc($tr('schedule_follow_up')) ?></span></a>
                            <?php endif; ?>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500"><?= esc($tr('medical_dental_history')) ?></h2></div>
                        <div class="space-y-4 p-6">
                            <?php if (!empty($clinicalFlags)): ?>
                                <?php foreach ($clinicalFlags as $flag): ?>
                                    <div class="rounded-xl border p-4 <?= esc($flag['tone']) ?>"><div class="flex items-center gap-3"><i class="<?= esc($flag['icon']) ?>"></i><span class="text-sm font-semibold"><?= esc($flag['label']) ?></span></div></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-500"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($examination['health_update_details'])): ?>
                                <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('health_update_details')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['health_update_details'])) ?></p></div>
                            <?php endif; ?>
                            <?php if (!empty($examination['medical_history'])): ?>
                                <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('medical_history')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['medical_history'])) ?></p></div>
                            <?php endif; ?>
                            <?php if (!empty($examination['dental_history'])): ?>
                                <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('dental_history')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['dental_history'])) ?></p></div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</div>
<style>
    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .patient-hero-card-male {
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        --hero-orb-primary: rgba(255, 255, 255, 0.18);
        --hero-orb-secondary: rgba(125, 211, 252, 0.2);
    }

    .patient-hero-card-female {
        --hero-start: #8a245a;
        --hero-mid: #c03579;
        --hero-end: #7c3aed;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(251, 207, 232, 0.24);
    }

    .patient-hero-card-neutral {
        --hero-start: #334155;
        --hero-mid: #2563eb;
        --hero-end: #0f766e;
        --hero-orb-primary: rgba(255, 255, 255, 0.16);
        --hero-orb-secondary: rgba(153, 246, 228, 0.22);
    }

    [dir="rtl"] .patient-hero-card {
        --hero-angle: 225deg;
    }

    .patient-hero-overlay {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 88% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(120deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
        pointer-events: none;
    }

    [dir="rtl"] .patient-hero-overlay {
        background:
            radial-gradient(circle at 88% 18%, var(--hero-orb-primary), transparent 30%),
            radial-gradient(circle at 12% 20%, var(--hero-orb-secondary), transparent 26%),
            linear-gradient(240deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
    }

    .patient-avatar-badge {
        display: inline-flex;
        width: 5.5rem;
        height: 5.5rem;
        align-items: center;
        justify-content: center;
        border-radius: 1.75rem;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(191, 219, 254, 0.18));
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.26), 0 16px 32px -24px rgba(15, 23, 42, 0.8);
        flex-shrink: 0;
    }

    .patient-avatar-badge i {
        font-size: 2.55rem;
        line-height: 1;
        display: inline-block;
    }

    .patient-avatar-badge-male {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(191, 219, 254, 0.18));
    }

    .patient-avatar-badge-female {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(244, 114, 182, 0.18));
    }

    .patient-avatar-badge-neutral {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.24), rgba(94, 234, 212, 0.16));
    }

    .patient-hero-chip-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.85rem;
    }

    @media (min-width: 640px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1100px) {
        .patient-hero-chip-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .patient-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 2.65rem;
        width: 100%;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        padding: 0.52rem 0.82rem;
        color: #eff6ff;
        backdrop-filter: blur(12px);
        text-align: start;
    }

    .patient-hero-chip-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        flex-shrink: 0;
        font-size: 0.78rem;
    }

    .patient-hero-chip-text {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        min-width: 0;
    }

    .patient-hero-chip-label {
        flex-shrink: 0;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.2;
        color: rgba(219, 234, 254, 0.84);
    }

    .patient-hero-chip-value {
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.76rem;
        font-weight: 500;
        line-height: 1.25;
        color: #ffffff;
    }

    .patient-hero-chip-ltr .patient-hero-chip-value {
        direction: ltr;
    }

    .patient-hero-static-chip {
        pointer-events: none;
    }

    .patient-header-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.58rem;
        min-height: 2.65rem;
        border-radius: 0.95rem;
        padding: 0.58rem 0.78rem;
        font-size: 0.76rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        text-align: center;
    }

    .patient-header-action:hover {
        transform: translateY(-1px);
    }

    .patient-header-action-soft,
    .patient-header-action-primary {
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.12);
        color: #eff6ff;
        box-shadow: none;
    }

    .patient-header-action-soft:hover,
    .patient-header-action-primary:hover {
        background: rgba(255, 255, 255, 0.17);
    }

    @media (max-width: 767px) {
        .patient-avatar-badge {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1.35rem;
            font-size: 1.55rem;
        }
    }
</style>
<?= $this->endSection() ?>
