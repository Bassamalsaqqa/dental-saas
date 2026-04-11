<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$typeLabels = [
    'initial' => $tr('type_initial'),
    'periodic' => $tr('type_periodic'),
    'emergency' => $tr('type_emergency'),
    'follow_up' => $tr('type_follow_up'),
];
$statusLabels = [
    'pending' => $tr('status_pending'),
    'in_progress' => $tr('status_in_progress'),
    'completed' => $tr('status_completed'),
    'cancelled' => $tr('status_cancelled'),
];
$typeKey = strtolower((string) ($examination['examination_type'] ?? 'follow_up'));
$statusKey = strtolower((string) ($examination['status'] ?? 'pending'));
$typeLabel = $typeLabels[$typeKey] ?? $tr('type_follow_up');
$statusLabel = $statusLabels[$statusKey] ?? $tr('status_pending');
$doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? ''));
$patientName = trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));

$statusToneMap = [
    'pending' => 'bg-amber-100 text-amber-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-emerald-100 text-emerald-800',
    'cancelled' => 'bg-slate-200 text-slate-700',
];
$typeToneMap = [
    'initial' => 'bg-violet-100 text-violet-800',
    'periodic' => 'bg-emerald-100 text-emerald-800',
    'emergency' => 'bg-red-100 text-red-800',
    'follow_up' => 'bg-sky-100 text-sky-800',
];

$healthUpdateRows = [
    ['enabled' => !empty($examination['health_update_medical_changes'] ?? 0), 'label' => $tr('health_medical_changes'), 'icon' => 'fas fa-notes-medical', 'tone' => 'text-emerald-700 bg-emerald-50 border-emerald-200'],
    ['enabled' => !empty($examination['health_update_medication_changes'] ?? 0), 'label' => $tr('health_medication_changes'), 'icon' => 'fas fa-pills', 'tone' => 'text-blue-700 bg-blue-50 border-blue-200'],
    ['enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0), 'label' => $tr('health_allergies_reactions'), 'icon' => 'fas fa-allergies', 'tone' => 'text-red-700 bg-red-50 border-red-200'],
    ['enabled' => !empty($examination['health_update_pregnancy'] ?? 0), 'label' => $tr('health_pregnancy'), 'icon' => 'fas fa-baby', 'tone' => 'text-fuchsia-700 bg-fuchsia-50 border-fuchsia-200'],
    ['enabled' => !empty($examination['health_update_current_symptoms'] ?? 0), 'label' => $tr('health_current_symptoms'), 'icon' => 'fas fa-triangle-exclamation', 'tone' => 'text-amber-700 bg-amber-50 border-amber-200'],
];
$activeHealthUpdates = array_values(array_filter($healthUpdateRows, static fn($row) => !empty($row['enabled'])));

$summaryCards = [
    ['label' => $tr('detail_examination_type'), 'value' => $typeLabel, 'tone' => $typeToneMap[$typeKey] ?? 'bg-sky-100 text-sky-800'],
    ['label' => $tr('status'), 'value' => $statusLabel, 'tone' => $statusToneMap[$statusKey] ?? 'bg-amber-100 text-amber-800'],
    ['label' => $tr('examination_date'), 'value' => !empty($examination['examination_date']) ? formatDateTime($examination['examination_date']) : $tr('na'), 'tone' => null],
    ['label' => $tr('created_by'), 'value' => $doctorName !== '' ? $doctorName : $tr('unknown'), 'tone' => null],
];

$recordMeta = [
    ['label' => $tr('examination_id_label'), 'value' => $examination['examination_id'] ?? $tr('na')],
    ['label' => $tr('patient_number'), 'value' => $examination['patient_number'] ?? $tr('na')],
];
?>

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-8">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.18em] text-slate-400">
                        <span><?= esc($tr('examinations_registry')) ?></span>
                        <span>/</span>
                        <span class="text-teal-700"><?= esc($examination['examination_id'] ?? ('EX-' . ($examination['id'] ?? ''))) ?></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="font-headline text-3xl font-black tracking-tight text-cyan-950"><?= esc($tr('examination_details')) ?></h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] <?= esc($statusToneMap[$statusKey] ?? 'bg-amber-100 text-amber-800') ?>">
                            <?= esc($statusLabel) ?>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500"><?= esc($tr('examination_summary_help')) ?></p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <?php if (!empty($examination['patient_id'])): ?>
                        <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                            <i class="fas fa-user text-sm"></i>
                            <span><?= esc($tr('back_to_patient')) ?></span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                        <i class="fas fa-arrow-left rtl-back-icon text-sm"></i>
                        <span><?= esc($tr('back_to_examinations')) ?></span>
                    </a>
                    <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-edit text-sm"></i>
                        <span><?= esc($tr('edit_examination')) ?></span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-4">
                    <section class="rounded-xl bg-slate-100 p-6">
                        <h2 class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-slate-400"><?= esc($tr('patient_information')) ?></h2>
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-white text-xl font-black text-cyan-900 shadow-sm">
                                <?= esc(mb_substr($patientName !== '' ? $patientName : 'P', 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-cyan-950"><?= esc($patientName !== '' ? $patientName : $tr('na')) ?></p>
                                <p class="text-sm text-slate-500"><?= esc($tr('patient_number')) ?>: <?= esc($examination['patient_number'] ?? $tr('na')) ?></p>
                            </div>
                        </div>
                        <div class="space-y-4 border-t border-slate-200 pt-6">
                            <?php foreach ($recordMeta as $meta): ?>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="text-slate-400"><?= esc($meta['label']) ?></span>
                                    <span class="font-medium text-cyan-900"><?= esc($meta['value']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6">
                            <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800">
                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                <span><?= esc($tr('view_patient')) ?></span>
                            </a>
                        </div>
                    </section>

                    <section class="rounded-xl bg-slate-100 p-6">
                        <h2 class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-slate-400"><?= esc($tr('examination_information')) ?></h2>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <?php foreach ($summaryCards as $card): ?>
                                <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($card['label']) ?></p>
                                    <?php if (!empty($card['tone'])): ?>
                                        <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold <?= esc($card['tone']) ?>"><?= esc($card['value']) ?></span>
                                    <?php else: ?>
                                        <p class="mt-2 text-sm font-semibold text-cyan-950"><?= esc($card['value']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="space-y-6 xl:col-span-8">
                    <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-xl font-bold text-cyan-950"><?= esc($tr('clinical_summary')) ?></h2>
                            <p class="mt-1 text-sm text-slate-500"><?= esc($tr('clinical_summary_help')) ?></p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-5">
                                <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('chief_complaint')) ?></p>
                                <p class="text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['chief_complaint'] ?? $tr('na'))) ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-5">
                                <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('history_of_present_illness')) ?></p>
                                <p class="text-sm leading-7 text-slate-700"><?= !empty($examination['history_of_present_illness']) ? nl2br(esc($examination['history_of_present_illness'])) : esc($tr('na')) ?></p>
                            </div>
                        </div>
                    </section>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('medical_dental_history')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc($tr('medical_dental_history_subtitle')) ?></p>
                            </div>
                            <div class="space-y-5 p-6">
                                <?php if (!empty($activeHealthUpdates)): ?>
                                    <div>
                                        <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('health_update_since_last_visit')) ?></p>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($activeHealthUpdates as $item): ?>
                                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold <?= esc($item['tone']) ?>">
                                                    <i class="<?= esc($item['icon']) ?> text-xs"></i>
                                                    <span><?= esc($item['label']) ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($examination['health_update_details'])): ?>
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('health_update_details')) ?></p>
                                        <p class="text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['health_update_details'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($examination['medical_history'])): ?>
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('medical_history')) ?></p>
                                        <p class="text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['medical_history'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($examination['dental_history'])): ?>
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('dental_history')) ?></p>
                                        <p class="text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['dental_history'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($activeHealthUpdates) && empty($examination['health_update_details']) && empty($examination['medical_history']) && empty($examination['dental_history'])): ?>
                                    <p class="text-sm text-slate-500"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('diagnosis')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc($tr('diagnosis_subtitle')) ?></p>
                            </div>
                            <div class="space-y-5 p-6">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('clinical_findings')) ?></p>
                                    <p class="text-sm leading-7 text-slate-700"><?= !empty($examination['clinical_findings']) ? nl2br(esc($examination['clinical_findings'])) : esc($tr('no_clinical_findings_recorded')) ?></p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('diagnosis')) ?></p>
                                    <p class="text-sm leading-7 text-slate-700"><?= !empty($examination['diagnosis']) ? nl2br(esc($examination['diagnosis'])) : esc($tr('no_diagnosis_recorded')) ?></p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('care_plan_heading')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc($tr('care_plan_help')) ?></p>
                            </div>
                            <div class="space-y-5 p-6">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('treatment_plan')) ?></p>
                                    <?php if (!empty($latest_contract)): ?>
                                        <p class="text-sm font-semibold text-cyan-950"><?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?></p>
                                        <p class="mt-2 text-sm text-slate-600"><?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?></p>
                                        <div class="mt-3">
                                            <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800">
                                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                                <span><?= esc($tr('view')) ?></span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-sm leading-7 text-slate-700"><?= esc($tr('treatment_plan_hint')) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('prognosis')) ?></p>
                                    <p class="text-sm leading-7 text-slate-700"><?= !empty($examination['prognosis']) ? nl2br(esc($examination['prognosis'])) : esc($tr('no_prognosis_recorded')) ?></p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('recommendations')) ?></p>
                                    <p class="text-sm leading-7 text-slate-700"><?= !empty($examination['recommendations']) ? nl2br(esc($examination['recommendations'])) : esc($tr('no_recommendations_recorded')) ?></p>
                                </div>
                                <?php if (has_permission('treatments', 'create')): ?>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <?php if (!empty($latest_contract)): ?>
                                            <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                                <i class="fas fa-file-signature text-sm"></i>
                                                <span><?= esc($tr('add_new_plan')) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 sm:col-span-2">
                                                <i class="fas fa-file-signature text-sm"></i>
                                                <span><?= esc($tr('add_treatment_plan')) ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('follow_up_actions')) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= esc($tr('follow_up_actions_help')) ?></p>
                            </div>
                            <div class="space-y-5 p-6">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('next_appointment')) ?></p>
                                    <?php if (!empty($next_appointment)): ?>
                                        <p class="text-sm font-semibold text-cyan-950"><?= formatDate($next_appointment['appointment_date']) ?> <?= formatTime($next_appointment['appointment_time']) ?></p>
                                        <p class="mt-2 text-sm text-slate-600"><?= esc($tr('appointment_summary', [
                                            'type' => ucfirst(str_replace('_', ' ', $next_appointment['appointment_type'] ?? 'follow_up')),
                                            'status' => ucfirst($next_appointment['status'] ?? 'scheduled'),
                                        ])) ?></p>
                                    <?php else: ?>
                                        <p class="text-sm text-slate-700"><?= esc($tr('no_next_appointment_scheduled')) ?></p>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($examination['examination_notes'])): ?>
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($tr('examination_notes')) ?></p>
                                        <p class="text-sm leading-7 text-slate-700"><?= nl2br(esc($examination['examination_notes'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <?php if (has_permission('appointments', 'create')): ?>
                                        <a href="<?= base_url('appointment/create?patient_id=' . $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                            <i class="fas fa-calendar-plus text-sm"></i>
                                            <span><?= esc($tr('schedule_follow_up')) ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($examination['status'] != 'completed'): ?>
                                        <button type="button" onclick="completeExamination(<?= $examination['id'] ?>)" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                            <i class="fas fa-check text-sm"></i>
                                            <span><?= esc($tr('complete_examination')) ?></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-cyan-950"><?= esc($tr('linked_records_heading')) ?></h2>
                            <p class="mt-1 text-sm text-slate-500"><?= esc($tr('linked_records_help')) ?></p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-base font-semibold text-cyan-950"><?= esc($tr('dental_chart_odontogram')) ?></p>
                                        <p class="mt-1 text-sm text-slate-500"><?= esc($tr('dental_chart_subtitle')) ?></p>
                                    </div>
                                    <i class="fas fa-tooth text-blue-600"></i>
                                </div>
                                <p class="text-sm leading-7 text-slate-700"><?= !empty($odontogram) ? esc($tr('odontogram_data_hint')) : esc($tr('no_odontogram_data_recorded')) ?></p>
                                <div class="mt-4">
                                    <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?examination_id=' . $examination['id']) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800">
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                        <span><?= esc(!empty($odontogram) ? $tr('view_full_odontogram') : $tr('create_odontogram')) ?></span>
                                    </a>
                                </div>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-base font-semibold text-cyan-950"><?= esc($tr('related_treatments')) ?></p>
                                        <p class="mt-1 text-sm text-slate-500"><?= esc($tr('related_treatments_subtitle')) ?></p>
                                    </div>
                                    <i class="fas fa-procedures text-emerald-600"></i>
                                </div>
                                <?php if (!empty($treatments)): ?>
                                    <div class="space-y-3">
                                        <?php foreach ($treatments as $treatment): ?>
                                            <div class="rounded-lg bg-white px-4 py-3 shadow-sm">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-cyan-950"><?= esc($treatment['treatment_name']) ?></p>
                                                        <?php if (!empty($treatment['treatment_description'])): ?>
                                                            <p class="mt-1 text-sm text-slate-600"><?= esc($treatment['treatment_description']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?= base_url('treatment/' . $treatment['id']) ?>" class="text-sm font-semibold text-cyan-700 hover:text-cyan-800"><?= esc($tr('view')) ?></a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-slate-700"><?= esc($tr('no_treatments_recorded')) ?></p>
                                <?php endif; ?>
                                <div class="mt-4">
                                    <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800">
                                        <i class="fas fa-plus text-xs"></i>
                                        <span><?= esc($tr('add_treatment')) ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
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
                <button type="button" onclick="closeCompleteModal()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                    <i class="fas fa-times text-sm"></i>
                    <span><?= esc($tr('cancel')) ?></span>
                </button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    <i class="fas fa-check text-sm"></i>
                    <span><?= esc($tr('complete_examination')) ?></span>
                </button>
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

    fetch(`<?= base_url('examination') ?>/${examinationId}/complete`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (data.success) {
            closeCompleteModal();
            location.reload();
            return;
        }

        if (window.showToast) {
            window.showToast('error', <?= json_encode(lang('UI.error')) ?>, <?= json_encode($tr('error_prefix')) ?> + (data.message || ''));
        } else {
            alert('<?= esc($tr('error_prefix'), 'js') ?> ' + data.message);
        }
    })
    .catch(() => {
        if (window.showToast) {
            window.showToast('error', <?= json_encode(lang('UI.error')) ?>, <?= json_encode($tr('error_loading_data')) ?>);
        }
    });
});

document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompleteModal();
    }
});
</script>
<?= $this->endSection() ?>
