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
$statusToneMap = ['pending' => 'bg-amber-100 text-amber-800', 'in_progress' => 'bg-sky-100 text-sky-800', 'completed' => 'bg-emerald-100 text-emerald-800', 'cancelled' => 'bg-slate-200 text-slate-700'];
$typeToneMap = ['initial' => 'bg-violet-100 text-violet-800', 'periodic' => 'bg-emerald-100 text-emerald-800', 'emergency' => 'bg-red-100 text-red-800', 'follow_up' => 'bg-cyan-100 text-cyan-800'];
$healthUpdateRows = [
    ['enabled' => !empty($examination['health_update_medical_changes'] ?? 0), 'label' => $tr('health_medical_changes'), 'icon' => 'fas fa-notes-medical', 'tone' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    ['enabled' => !empty($examination['health_update_medication_changes'] ?? 0), 'label' => $tr('health_medication_changes'), 'icon' => 'fas fa-pills', 'tone' => 'bg-blue-50 text-blue-800 border-blue-200'],
    ['enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0), 'label' => $tr('health_allergies_reactions'), 'icon' => 'fas fa-allergies', 'tone' => 'bg-red-50 text-red-800 border-red-200'],
    ['enabled' => !empty($examination['health_update_pregnancy'] ?? 0), 'label' => $tr('health_pregnancy'), 'icon' => 'fas fa-baby', 'tone' => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-200'],
    ['enabled' => !empty($examination['health_update_current_symptoms'] ?? 0), 'label' => $tr('health_current_symptoms'), 'icon' => 'fas fa-triangle-exclamation', 'tone' => 'bg-amber-50 text-amber-800 border-amber-200'],
];
$activeHealthUpdates = array_values(array_filter($healthUpdateRows, static fn($row) => !empty($row['enabled'])));
$patientMeta = [
    ['label' => $tr('patient_number'), 'value' => $examination['patient_number'] ?? $tr('na')],
    ['label' => $tr('examination_date'), 'value' => !empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na')],
    ['label' => $tr('created_by'), 'value' => $doctorName !== '' ? $doctorName : $tr('unknown')],
];
$clinicalFlags = $activeHealthUpdates;
if (!empty($examination['medical_history'])) { $clinicalFlags[] = ['enabled' => true, 'label' => $tr('medical_history'), 'icon' => 'fas fa-heart-pulse', 'tone' => 'bg-slate-100 text-slate-800 border-slate-200']; }
if (!empty($examination['dental_history'])) { $clinicalFlags[] = ['enabled' => true, 'label' => $tr('dental_history'), 'icon' => 'fas fa-tooth', 'tone' => 'bg-slate-100 text-slate-800 border-slate-200']; }
?>
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="xl:col-span-8">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex items-start gap-5">
                                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-700 to-sky-600 text-2xl font-black text-white shadow-lg shadow-cyan-900/15"><?= esc(mb_substr($patientName !== '' ? $patientName : 'P', 0, 1)) ?></div>
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                        <span><?= esc($tr('examinations_registry')) ?></span><span>/</span><span class="text-cyan-700"><?= esc($examination['examination_id'] ?? ('EX-' . ($examination['id'] ?? ''))) ?></span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h1 class="font-headline text-3xl font-black tracking-tight text-cyan-950"><?= esc($patientName !== '' ? $patientName : $tr('examination_details')) ?></h1>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] <?= esc($statusToneMap[$statusKey] ?? 'bg-amber-100 text-amber-800') ?>"><?= esc($statusLabel) ?></span>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] <?= esc($typeToneMap[$typeKey] ?? 'bg-cyan-100 text-cyan-800') ?>"><?= esc($typeLabel) ?></span>
                                    </div>
                                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"><?= esc($tr('examination_summary_help')) ?></p>
                                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <?php foreach ($patientMeta as $meta): ?>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($meta['label']) ?></p>
                                                <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc($meta['value']) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 lg:max-w-sm lg:justify-end">
                                <?php if (!empty($examination['patient_id'])): ?>
                                    <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i class="fas fa-user text-sm"></i><span><?= esc($tr('back_to_patient')) ?></span></a>
                                <?php endif; ?>
                                <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i class="fas fa-arrow-left rtl-back-icon text-sm"></i><span><?= esc($tr('back_to_examinations')) ?></span></a>
                                <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i class="fas fa-pen text-sm"></i><span><?= esc($tr('edit_examination')) ?></span></a>
                                <?php if ($examination['status'] != 'completed'): ?>
                                    <button type="button" onclick="completeExamination(<?= $examination['id'] ?>)" class="inline-flex items-center gap-2 rounded-xl bg-cyan-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-800"><i class="fas fa-check-circle text-sm"></i><span><?= esc($tr('complete_examination')) ?></span></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xl:col-span-4">
                    <div class="flex h-full flex-col justify-between rounded-2xl bg-gradient-to-br from-cyan-800 to-sky-700 p-6 text-white shadow-sm">
                        <div>
                            <div class="flex items-center justify-between gap-3"><p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/70"><?= esc($tr('care_plan_heading')) ?></p><i class="fas fa-file-signature text-white/60"></i></div>
                            <?php if (!empty($latest_contract)): ?>
                                <p class="mt-4 text-3xl font-black tracking-tight"><?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                <p class="mt-2 text-sm leading-6 text-white/80"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                            <?php else: ?>
                                <p class="mt-4 text-2xl font-black tracking-tight"><?= esc($tr('treatment_plan')) ?></p>
                                <p class="mt-2 text-sm leading-6 text-white/80"><?= esc($tr('treatment_plan_hint')) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <?php if (!empty($latest_contract)): ?>
                                <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"><i class="fas fa-arrow-up-right-from-square text-sm"></i><span><?= esc($tr('view')) ?></span></a>
                            <?php endif; ?>
                            <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-800 transition hover:bg-cyan-50"><i class="fas fa-plus text-sm"></i><span><?= esc(!empty($latest_contract) ? $tr('add_new_plan') : $tr('add_treatment_plan')) ?></span></a>
                            <?php endif; ?>
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
                                    <div><p class="text-sm font-semibold text-cyan-950"><?= esc($tr('dental_chart_odontogram')) ?></p><p class="mt-1 text-sm text-slate-500"><?= esc($tr('dental_chart_subtitle')) ?></p></div>
                                    <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?examination_id=' . $examination['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50"><i class="fas fa-tooth text-sm"></i><span><?= esc(!empty($odontogram) ? $tr('view_full_odontogram') : $tr('create_odontogram')) ?></span></a>
                                </div>
                                <p class="mt-4 text-sm leading-7 text-slate-700"><?= !empty($odontogram) ? esc($tr('odontogram_data_hint')) : esc($tr('no_odontogram_data_recorded')) ?></p>
                            </div>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3"><span class="h-6 w-1.5 rounded-full bg-cyan-700"></span><div><h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('care_plan_heading')) ?></h2><p class="mt-1 text-sm text-slate-500"><?= esc($tr('care_plan_help')) ?></p></div></div>
                            <?php if (has_permission('treatments', 'create')): ?>
                                <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i class="fas fa-plus text-sm"></i><span><?= esc($tr('add_treatment')) ?></span></a>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-1 gap-6 p-6 xl:grid-cols-2">
                            <div class="space-y-4">
                                <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('treatment_plan')) ?></p><?php if (!empty($latest_contract)): ?><p class="mt-3 text-sm font-semibold text-cyan-950"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p><p class="mt-2 text-sm text-slate-600"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p><?php else: ?><p class="mt-3 text-sm leading-7 text-slate-700"><?= esc($tr('treatment_plan_hint')) ?></p><?php endif; ?></div>
                                <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('prognosis')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : esc($tr('no_prognosis_recorded')) ?></p></div>
                            </div>
                            <div class="space-y-4">
                                <div class="rounded-xl bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('recommendations')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : esc($tr('no_recommendations_recorded')) ?></p></div>
                                <div class="rounded-xl bg-cyan-900 p-5 text-white"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60"><?= esc($tr('latest_contract')) ?></p><?php if (!empty($latest_contract)): ?><p class="mt-3 text-lg font-bold"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p><div class="mt-4"><a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15"><i class="fas fa-arrow-up-right-from-square text-sm"></i><span><?= esc($tr('view')) ?></span></a></div><?php else: ?><p class="mt-3 text-sm leading-7 text-white/80"><?= esc($tr('treatment_plan_hint')) ?></p><?php endif; ?></div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="space-y-6 xl:col-span-4">
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
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500"><?= esc($tr('follow_up_actions')) ?></h2></div>
                        <div class="space-y-4 p-6">
                            <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('next_appointment')) ?></p><?php if (!empty($next_appointment)): ?><p class="mt-3 text-sm font-semibold text-cyan-950"><?= formatDate($next_appointment['appointment_date']) ?> <?= formatTime($next_appointment['appointment_time']) ?></p><p class="mt-2 text-sm text-slate-600"><?= esc($tr('appointment_summary', ['type' => ucfirst(str_replace('_', ' ', $next_appointment['appointment_type'] ?? 'follow_up')), 'status' => ucfirst($next_appointment['status'] ?? 'scheduled')])) ?></p><?php else: ?><p class="mt-3 text-sm text-slate-700"><?= esc($tr('no_next_appointment_scheduled')) ?></p><?php endif; ?></div>
                            <?php if (!empty($examination['examination_notes'])): ?>
                                <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($tr('examination_notes')) ?></p><p class="mt-3 text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['examination_notes'])) ?></p></div>
                            <?php endif; ?>
                            <div class="grid grid-cols-1 gap-3">
                                <?php if (has_permission('appointments', 'create')): ?>
                                    <a href="<?= base_url('appointment/create?patient_id=' . $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i class="fas fa-calendar-plus text-sm"></i><span><?= esc($tr('schedule_follow_up')) ?></span></a>
                                <?php endif; ?>
                                <?php if ($examination['status'] != 'completed'): ?>
                                    <button type="button" onclick="completeExamination(<?= $examination['id'] ?>)" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"><i class="fas fa-check text-sm"></i><span><?= esc($tr('complete_examination')) ?></span></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500"><?= esc($tr('linked_records_heading')) ?></h2></div>
                        <div class="space-y-4 p-6">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-semibold text-cyan-950"><?= esc($tr('related_treatments')) ?></p><p class="mt-1 text-sm text-slate-500"><?= esc($tr('related_treatments_subtitle')) ?></p></div><i class="fas fa-procedures text-emerald-600"></i></div>
                                <?php if (!empty($treatments)): ?>
                                    <div class="mt-4 space-y-3">
                                        <?php foreach ($treatments as $treatment): ?>
                                            <a href="<?= base_url('treatment/' . $treatment['id']) ?>" class="block rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100 transition hover:bg-slate-50"><p class="text-sm font-semibold text-cyan-950"><?= esc($treatment['treatment_name']) ?></p><?php if (!empty($treatment['treatment_description'])): ?><p class="mt-1 text-sm text-slate-600"><?= esc($treatment['treatment_description']) ?></p><?php endif; ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="mt-4 text-sm text-slate-700"><?= esc($tr('no_treatments_recorded')) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</div>
<div id="completeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-xl font-bold text-cyan-950"><?= esc($tr('complete_examination')) ?></h3>
            <p class="mt-1 text-sm text-slate-500"><?= esc($tr('complete_examination_subtitle')) ?></p>
        </div>
        <form id="completeForm" class="space-y-5 p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="examinationId" name="examination_id">
            <div>
                <label for="examination_notes" class="mb-2 block text-sm font-medium text-slate-700"><?= esc($tr('final_notes')) ?></label>
                <textarea id="examination_notes" name="examination_notes" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="<?= esc($tr('final_notes_placeholder')) ?>"></textarea>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeCompleteModal()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300"><i class="fas fa-times text-sm"></i><span><?= esc($tr('cancel')) ?></span></button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"><i class="fas fa-check text-sm"></i><span><?= esc($tr('complete_examination')) ?></span></button>
            </div>
        </form>
    </div>
</div>
<script>
function completeExamination(examinationId) {
    document.getElementById('examinationId').value = examinationId;
    const modal = document.getElementById('completeModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeCompleteModal() {
    const modal = document.getElementById('completeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('completeForm').reset();
}
document.getElementById('completeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const examinationId = formData.get('examination_id');
    fetch(`<?= base_url('examination') ?>/${examinationId}/complete`, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token && window.refreshCsrfToken) window.refreshCsrfToken(data.csrf_token);
            if (data.success) { closeCompleteModal(); location.reload(); return; }
            if (window.showToast) window.showToast('error', <?= json_encode(lang('UI.error')) ?>, <?= json_encode($tr('error_prefix')) ?> + (data.message || ''));
            else alert('<?= esc($tr('error_prefix'), 'js') ?> ' + data.message);
        })
        .catch(() => {
            if (window.showToast) window.showToast('error', <?= json_encode(lang('UI.error')) ?>, <?= json_encode($tr('error_loading_data')) ?>);
        });
});
document.getElementById('completeModal').addEventListener('click', function(e) { if (e.target === this) closeCompleteModal(); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeCompleteModal(); });
</script>
<?= $this->endSection() ?>
