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
?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= esc($tr('examination_details')) ?></h1>
                <p class="text-gray-600 text-lg"><?= esc($tr('examination_id_label')) ?>: <?= $examination['examination_id'] ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <?php if (!empty($examination['patient_id'])): ?>
                    <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-user mr-2"></i><?= esc($tr('back_to_patient')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('back_to_examinations')) ?>
                </a>
                <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-edit mr-2"></i><?= esc($tr('edit_examination')) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-8"> 

    <!-- Patient Information -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('patient_information')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('patient_info_subtitle')) ?></p>
                </div>
            </div>
            <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i><?= esc($tr('view_patient')) ?>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('patient_name')) ?></label>
                <p class="text-gray-900 font-semibold text-lg"><?= $examination['first_name'] . ' ' . $examination['last_name'] ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('patient_number')) ?></label>
                <p class="text-gray-900 font-semibold text-lg"><?= $examination['patient_number'] ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('examination_date')) ?></label>
                <p class="text-gray-900 font-semibold text-lg"><?= formatDateTime($examination['examination_date']) ?></p>
            </div>
        </div>
        </div>
    </div>

    <!-- Examination Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-stethoscope text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('examination_information')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('examination_information_subtitle')) ?></p>
                </div>
            </div>
            <?php $doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? '')); ?>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('detail_examination_type')) ?></label>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $examination['examination_type'] == 'emergency' ? 'bg-red-100 text-red-800' : 
                                    ($examination['examination_type'] == 'initial' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($examination['examination_type'] == 'periodic' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) ?>">
                                <?= esc($typeLabels[$examination['examination_type']] ?? $tr('type_follow_up')) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('status')) ?></label>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($examination['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                    ($examination['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?>">
                                <?= esc($statusLabels[$examination['status']] ?? $tr('status_pending')) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('created_by')) ?></label>
                        <div class="mt-2">
                            <span class="text-gray-900 font-semibold"><?= $doctorName !== '' ? 'Dr. ' . esc($doctorName) : esc($tr('unknown')) ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('chief_complaint')) ?></label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['chief_complaint'] ?></p>
                    </div>
                </div>
                <?php if (!empty($examination['history_of_present_illness'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('history_of_present_illness')) ?></label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['history_of_present_illness'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>

        <!-- Medical History -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-history text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('medical_dental_history')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('medical_dental_history_subtitle')) ?></p>
                </div>
            </div>
            <div class="space-y-6">
                <?php
                $healthUpdateRows = [
                    [
                        'enabled' => !empty($examination['health_update_medical_changes'] ?? 0),
                        'label' => $tr('health_medical_changes'),
                        'icon' => 'fas fa-notes-medical',
                        'icon_class' => 'text-emerald-600',
                    ],
                    [
                        'enabled' => !empty($examination['health_update_medication_changes'] ?? 0),
                        'label' => $tr('health_medication_changes'),
                        'icon' => 'fas fa-pills',
                        'icon_class' => 'text-blue-600',
                    ],
                    [
                        'enabled' => !empty($examination['health_update_allergies_reactions'] ?? 0),
                        'label' => $tr('health_allergies_reactions'),
                        'icon' => 'fas fa-allergies',
                        'icon_class' => 'text-red-600',
                    ],
                    [
                        'enabled' => !empty($examination['health_update_pregnancy'] ?? 0),
                        'label' => $tr('health_pregnancy'),
                        'icon' => 'fas fa-baby',
                        'icon_class' => 'text-fuchsia-600',
                    ],
                    [
                        'enabled' => !empty($examination['health_update_current_symptoms'] ?? 0),
                        'label' => $tr('health_current_symptoms'),
                        'icon' => 'fas fa-triangle-exclamation',
                        'icon_class' => 'text-amber-600',
                    ],
                ];
                $hasStructuredHealthUpdate = false;
                foreach ($healthUpdateRows as $healthUpdateRow) {
                    if (!empty($healthUpdateRow['enabled'])) {
                        $hasStructuredHealthUpdate = true;
                        break;
                    }
                }
                ?>
                <?php if ($hasStructuredHealthUpdate || !empty($examination['health_update_details'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('health_update_since_last_visit')) ?></label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg space-y-3">
                        <?php if ($hasStructuredHealthUpdate): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <?php foreach ($healthUpdateRows as $healthUpdateRow): ?>
                                    <?php if (empty($healthUpdateRow['enabled'])) continue; ?>
                                    <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 flex items-center justify-between gap-2">
                                        <span class="text-sm font-medium text-amber-900 inline-flex items-center gap-2">
                                            <i class="<?= esc($healthUpdateRow['icon']) ?> <?= esc($healthUpdateRow['icon_class']) ?> w-4 text-center"></i>
                                            <span><?= esc($healthUpdateRow['label']) ?></span>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900"><?= esc($tr('yes')) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($examination['health_update_details'])): ?>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1"><?= esc($tr('health_update_details')) ?></label>
                                <p class="text-gray-900 text-sm"><?= nl2br(esc($examination['health_update_details'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($examination['medical_history'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= ($hasStructuredHealthUpdate || !empty($examination['health_update_details'])) ? esc($tr('legacy_medical_history')) : esc($tr('medical_history')) ?></label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['medical_history'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($examination['dental_history'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600"><?= esc($tr('dental_history')) ?></label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['dental_history'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (empty($examination['medical_history']) && empty($examination['dental_history']) && !$hasStructuredHealthUpdate && empty($examination['health_update_details'])): ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_medical_dental_history_recorded')) ?></p>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    <!-- Clinical Findings & Diagnosis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('clinical_findings')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('clinical_findings_subtitle')) ?></p>
                    </div>
                </div>
            <?php if (!empty($examination['clinical_findings'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['clinical_findings'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_clinical_findings_recorded')) ?></p>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-diagnoses text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('diagnosis')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('diagnosis_subtitle')) ?></p>
                    </div>
                </div>
            <?php if (!empty($examination['diagnosis'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['diagnosis'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-diagnoses text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_diagnosis_recorded')) ?></p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Treatment Plan & Prognosis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('treatment_plan')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('treatment_plan_subtitle')) ?></p>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg space-y-4">
                    <?php if (!empty($latest_contract)): ?>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500"><?= esc($tr('latest_contract')) ?></p>
                                <p class="text-gray-900 font-semibold">
                                    <?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?>
                                    <?php if (!empty($latest_contract['created_at'])): ?>
                                        Â· <?= formatDate($latest_contract['created_at']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="<?= base_url('contracts/' . $latest_contract['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <?= esc($tr('view')) ?>
                                </a>
                                <?php if (($latest_contract['status'] ?? 'draft') === 'draft' && !empty($signature_enabled)): ?>
                                    <a href="<?= base_url('contracts/' . $latest_contract['id'] . '?return=examination/' . $examination['id'] . '#signatureSection') ?>"
                                       class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-md hover:bg-emerald-700">
                                        <i class="fas fa-signature mr-2"></i><?= esc($tr('sign_plan')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-700">
                            <?= esc($tr('treatment_plan_hint')) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (has_permission('treatments', 'create')): ?>
                        <a href="<?= base_url('contracts/create/' . $examination['patient_id'] . '?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-file-signature mr-2"></i><?= !empty($latest_contract) ? esc($tr('add_new_plan')) : esc($tr('add_treatment_plan')) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('prognosis')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('prognosis_subtitle')) ?></p>
                    </div>
                </div>
            <?php if (!empty($examination['prognosis'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['prognosis'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_prognosis_recorded')) ?></p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recommendations & Next Appointment -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-lightbulb text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('recommendations')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('recommendations_subtitle')) ?></p>
                    </div>
                </div>
            <?php if (!empty($examination['recommendations'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['recommendations'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_recommendations_recorded')) ?></p>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('next_appointment')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('next_appointment_subtitle')) ?></p>
                    </div>
                </div>
            <?php if (!empty($next_appointment)): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar text-blue-600"></i>
                            <div>
                                <p class="text-gray-900 font-semibold">
                                    <?= formatDate($next_appointment['appointment_date']) ?>
                                    <?= formatTime($next_appointment['appointment_time']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= esc($tr('appointment_summary', [
                                        'type' => ucfirst(str_replace('_', ' ', $next_appointment['appointment_type'] ?? 'follow_up')),
                                        'status' => ucfirst($next_appointment['status'] ?? 'scheduled'),
                                    ])) ?>
                                </p>
                            </div>
                        </div>
                        <a href="<?= base_url('appointment/' . $next_appointment['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?= esc($tr('view')) ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500"><?= esc($tr('no_next_appointment_scheduled')) ?></p>
                </div>
            <?php endif; ?>

            <?php if (has_permission('appointments', 'create')): ?>
                <div class="mt-6">
                    <a href="<?= base_url('appointment/create?patient_id=' . $examination['patient_id'] . '&appointment_type=follow_up&examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-plus mr-2"></i><?= esc($tr('schedule_follow_up')) ?>
                    </a>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Examination Notes -->
    <?php if (!empty($examination['examination_notes'])): ?>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-sticky-note text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('examination_notes')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('examination_notes_subtitle')) ?></p>
                </div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-900"><?= $examination['examination_notes'] ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Odontogram Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-tooth text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('dental_chart_odontogram')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('dental_chart_subtitle')) ?></p>
                </div>
            </div>
            <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?examination_id=' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-tooth mr-2"></i><?= esc($tr('view_full_odontogram')) ?>
            </a>
        </div>
        <?php if (!empty($odontogram)): ?>
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <p class="text-gray-900 font-medium"><?= esc($tr('odontogram_data_available')) ?></p>
                </div>
                <p class="text-gray-600"><?= esc($tr('odontogram_data_hint')) ?></p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tooth text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-4"><?= esc($tr('no_odontogram_data_recorded')) ?></p>
                <a href="<?= base_url('odontogram/' . $examination['patient_id'] . '?examination_id=' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i><?= esc($tr('create_odontogram')) ?>
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Treatments Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-procedures text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($tr('related_treatments')) ?></h2>
                    <p class="text-sm text-gray-500"><?= esc($tr('related_treatments_subtitle')) ?></p>
                </div>
            </div>
            <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-plus mr-2"></i><?= esc($tr('add_treatment')) ?>
            </a>
        </div>
        <?php if (!empty($treatments)): ?>
            <div class="space-y-4">
                <?php foreach ($treatments as $treatment): ?>
                    <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-dental-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-procedures text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= $treatment['treatment_name'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $treatment['treatment_description'] ?? '' ?></p>
                                </div>
                            </div>
                            <a href="<?= base_url('treatment/' . $treatment['id']) ?>" class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="<?= esc($tr('view_treatment_details')) ?>">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-procedures text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-4"><?= esc($tr('no_treatments_recorded')) ?></p>
                <a href="<?= base_url('treatment/create?examination_id=' . $examination['id'] . '&return_to=examination/' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i><?= esc($tr('add_treatment')) ?>
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('back_to_list')) ?>
        </a>
        <a href="<?= base_url('examination/' . $examination['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            <i class="fas fa-edit mr-2"></i><?= esc($tr('edit_examination')) ?>
        </a>
        <?php if ($examination['status'] != 'completed'): ?>
            <button onclick="completeExamination(<?= $examination['id'] ?>)" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-check mr-2"></i><?= esc($tr('complete_examination')) ?>
            </button>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Complete Examination Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-lg">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($tr('complete_examination')) ?></h3>
                <p class="text-gray-600"><?= esc($tr('complete_examination_subtitle')) ?></p>
            </div>
            
            <form id="completeForm">
                <?= csrf_field() ?>
                <input type="hidden" id="examinationId" name="examination_id">
                
                <div class="mb-6">
                    <label for="examination_notes" class="block text-sm font-medium text-gray-700 mb-2"><?= esc($tr('final_notes')) ?></label>
                    <textarea id="examination_notes" name="examination_notes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="4" 
                              placeholder="<?= esc($tr('final_notes_placeholder')) ?>"></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="closeCompleteModal()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-times mr-2"></i><?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-check mr-2"></i><?= esc($tr('complete_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function completeExamination(examinationId) {
    document.getElementById('examinationId').value = examinationId;
    const modal = document.getElementById('completeModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeCompleteModal() {
    const modal = document.getElementById('completeModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('completeForm').reset();
}

// Handle form submission
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
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            closeCompleteModal();
            location.reload();
        } else {
            alert('<?= esc($tr('error_prefix'), 'js') ?> ' + data.message);
        }
    });
});

// Close modal when clicking outside
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompleteModal();
    }
});
</script>
<?= $this->endSection() ?>



