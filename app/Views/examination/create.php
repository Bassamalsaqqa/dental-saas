<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$validation = session()->getFlashdata('validation');
$optionalFieldKeys = [
    'dental_history',
    'clinical_findings',
    'diagnosis',
    'examination_notes',
    'health_update_medical_changes',
    'health_update_medication_changes',
    'health_update_allergies_reactions',
    'health_update_pregnancy',
    'health_update_current_symptoms',
    'health_update_details',
];
$optionalEnabledByOld = false;
foreach ($optionalFieldKeys as $fieldKey) {
    if (old($fieldKey) !== null && old($fieldKey) !== '') {
        $optionalEnabledByOld = true;
        break;
    }
}
$healthToggleChecked = static function (string $key): bool {
    $value = old($key);
    return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true);
};
$selectedPatientGender = strtolower((string) ($selected_patient['gender'] ?? ''));
?>

<style>
.toggle-track {
    transition: background-color 0.2s ease;
}

.toggle-dot {
    transition: transform 0.2s ease;
}
</style>

<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-cyan-500/10 group-hover:shadow-cyan-500/20 transition-all duration-500 overflow-hidden">
                <div class="p-8 border-b border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-stethoscope text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-2xl font-black text-gray-900 group-hover:text-cyan-900 transition-colors duration-300"><?= esc($tr('new_examination_title')) ?></h1>
                                <p class="text-gray-600 font-medium"><?= esc($tr('new_examination_subtitle')) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <?php
                            $showBackToPatient = !empty($selected_patient) && (empty($return_to) || preg_match('#^patient/\\d+(\\?|$)#', (string) $return_to));
                            ?>
                            <?php if ($showBackToPatient): ?>
                                <a href="<?= base_url('patient/' . $selected_patient['id']) ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white text-sm font-bold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-indigo-500/25">
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-blue-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                    <i class="fas fa-user mr-2 relative z-10"></i>
                                    <span class="relative z-10"><?= esc($tr('back_to_patient')) ?></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= esc(workflow_back('examination')) ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left rtl-back-icon mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc($tr('back')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form action="<?= base_url('examination/store') ?>" method="POST" class="space-y-6">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 shadow-lg p-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-5 flex items-center space-x-2">
                                <i class="fas fa-asterisk text-xs text-blue-600"></i>
                                <span><?= esc($tr('required_information')) ?></span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                <div>
                                    <label for="patientSelect" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('patient_required')) ?></label>
                                    <select
                                        name="patient_id"
                                        id="patientSelect"
                                        class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        data-searchable-select
                                        data-minimum-input-length="0"
                                        data-limit="5"
                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                        data-placeholder="<?= esc($tr('search_patients_placeholder')) ?>"
                                        required
                                    >
                                        <option value=""><?= esc($tr('select_patient')) ?></option>
                                        <?php if (!empty($selected_patient)): ?>
                                            <option value="<?= $selected_patient['id'] ?>" data-gender="<?= esc($selected_patient['gender'] ?? '') ?>" selected>
                                                <?= esc($selected_patient['first_name'] . ' ' . $selected_patient['last_name']) ?> (<?= esc($selected_patient['phone'] ?? $selected_patient['patient_id'] ?? $tr('na')) ?>)
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if ($validation && $validation->hasError('patient_id')): ?>
                                        <p class="text-red-600 text-sm mt-2"><?= $validation->getError('patient_id') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="examination_type" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('examination_type_required')) ?></label>
                                    <select name="examination_type" id="examination_type" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" required>
                                        <option value=""><?= esc($tr('select_type')) ?></option>
                                        <option value="initial" <?= old('examination_type') == 'initial' ? 'selected' : '' ?>><?= esc($tr('type_initial')) ?></option>
                                        <option value="periodic" <?= old('examination_type') == 'periodic' ? 'selected' : '' ?>><?= esc($tr('type_periodic')) ?></option>
                                        <option value="emergency" <?= old('examination_type') == 'emergency' ? 'selected' : '' ?>><?= esc($tr('type_emergency')) ?></option>
                                        <option value="follow_up" <?= old('examination_type') == 'follow_up' ? 'selected' : '' ?>><?= esc($tr('type_follow_up')) ?></option>
                                    </select>
                                    <?php if ($validation && $validation->hasError('examination_type')): ?>
                                        <p class="text-red-600 text-sm mt-2"><?= $validation->getError('examination_type') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="examination_date" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('examination_date_required')) ?></label>
                                    <input
                                        type="text"
                                        name="examination_date"
                                        id="examination_date"
                                        data-clinic-date="1"
                                        value="<?= old('examination_date', date('Y-m-d')) ?>"
                                        class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        required
                                    >
                                    <?php if ($validation && $validation->hasError('examination_date')): ?>
                                        <p class="text-red-600 text-sm mt-2"><?= $validation->getError('examination_date') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="chief_complaint" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('chief_complaint_required')) ?></label>
                                <textarea
                                    name="chief_complaint"
                                    id="chief_complaint"
                                    rows="3"
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y"
                                    placeholder="<?= esc($tr('chief_complaint_placeholder')) ?>"
                                    required
                                ><?= old('chief_complaint') ?></textarea>
                                <?php if ($validation && $validation->hasError('chief_complaint')): ?>
                                    <p class="text-red-600 text-sm mt-2"><?= $validation->getError('chief_complaint') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 shadow-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center text-white">
                                        <i class="fas fa-notes-medical"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900"><?= esc($tr('additional_clinical_details')) ?></h4>
                                        <p class="text-sm text-gray-500"><?= esc($tr('optional')) ?></p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="optionalEnabled" class="sr-only" <?= $optionalEnabledByOld ? 'checked' : '' ?>>
                                    <div id="optionalTrack" class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative">
                                        <div id="optionalDot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-semibold text-gray-700"><?= esc($tr('enable')) ?></span>
                                </label>
                            </div>

                            <div id="optionalSection" class="hidden space-y-5">
                                <div class="rounded-xl border border-slate-200 bg-white p-4 md:p-5">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <h5 class="text-sm font-semibold text-slate-800"><?= esc($tr('health_update_since_last_visit')) ?></h5>
                                        <span class="text-xs text-slate-500"><?= esc($tr('quick_check')) ?></span>
                                    </div>
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        <?php
                                        $healthUpdateItems = [
                                            ['key' => 'health_update_medical_changes', 'label' => $tr('health_q_medical_changes'), 'icon' => 'fas fa-notes-medical', 'icon_class' => 'text-emerald-500'],
                                            ['key' => 'health_update_medication_changes', 'label' => $tr('health_q_medication_changes'), 'icon' => 'fas fa-pills', 'icon_class' => 'text-blue-500'],
                                            ['key' => 'health_update_allergies_reactions', 'label' => $tr('health_q_allergies_reactions'), 'icon' => 'fas fa-allergies', 'icon_class' => 'text-red-500'],
                                            ['key' => 'health_update_pregnancy', 'label' => $tr('health_q_pregnancy'), 'icon' => 'fas fa-baby', 'icon_class' => 'text-fuchsia-500', 'pregnancy_only' => true],
                                            ['key' => 'health_update_current_symptoms', 'label' => $tr('health_q_current_symptoms'), 'icon' => 'fas fa-triangle-exclamation', 'icon_class' => 'text-amber-500'],
                                        ];
                                        ?>
                                        <?php foreach ($healthUpdateItems as $item): ?>
                                            <?php $isChecked = $healthToggleChecked($item['key']); ?>
                                            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-3" data-health-update-item <?= !empty($item['pregnancy_only']) ? 'data-pregnancy-only="1"' : '' ?>>
                                                <div class="flex items-center justify-between gap-3">
                                                    <label for="<?= esc($item['key']) ?>" class="text-sm font-medium text-gray-700 leading-snug pr-2 flex items-start gap-2">
                                                        <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class']) ?> mt-0.5 w-4 text-center"></i>
                                                        <span><?= esc($item['label']) ?></span>
                                                    </label>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-xs font-semibold text-gray-500"><?= esc($tr('no')) ?></span>
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                                            <input type="checkbox"
                                                                   id="<?= esc($item['key']) ?>"
                                                                   name="<?= esc($item['key']) ?>"
                                                                   value="1"
                                                                   class="sr-only health-update-toggle"
                                                                   data-track-id="<?= esc($item['key']) ?>_track"
                                                                   data-dot-id="<?= esc($item['key']) ?>_dot"
                                                                   <?= $isChecked ? 'checked' : '' ?>>
                                                            <div id="<?= esc($item['key']) ?>_track" class="toggle-track w-11 h-6 <?= $isChecked ? 'bg-blue-500' : 'bg-gray-200' ?> rounded-full relative">
                                                                <div id="<?= esc($item['key']) ?>_dot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full" style="transform: <?= $isChecked ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></div>
                                                            </div>
                                                        </label>
                                                        <span class="text-xs font-semibold text-blue-700"><?= esc($tr('yes')) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                    $showHealthUpdateDetails = $healthToggleChecked('health_update_medical_changes')
                                        || $healthToggleChecked('health_update_medication_changes')
                                        || $healthToggleChecked('health_update_allergies_reactions')
                                        || $healthToggleChecked('health_update_pregnancy')
                                        || $healthToggleChecked('health_update_current_symptoms');
                                    ?>
                                    <div id="healthUpdateDetailsWrap" class="mt-4 <?= $showHealthUpdateDetails ? '' : 'hidden' ?>">
                                        <label for="health_update_details" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('health_update_details')) ?></label>
                                        <textarea
                                            name="health_update_details"
                                            id="health_update_details"
                                            rows="3"
                                            class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y"
                                            placeholder="<?= esc($tr('health_update_details_placeholder')) ?>"
                                            <?= $showHealthUpdateDetails ? '' : 'disabled' ?>
                                        ><?= old('health_update_details') ?></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="dental_history" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('dental_history')) ?></label>
                                        <textarea name="dental_history" id="dental_history" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="<?= esc($tr('dental_history_placeholder')) ?>"><?= old('dental_history') ?></textarea>
                                    </div>
                                    <div>
                                        <label for="clinical_findings" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('clinical_findings')) ?></label>
                                        <textarea name="clinical_findings" id="clinical_findings" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="<?= esc($tr('clinical_findings_placeholder')) ?>"><?= old('clinical_findings') ?></textarea>
                                    </div>
                                </div>

                                <div>
                                    <label for="diagnosis" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('diagnosis')) ?></label>
                                    <textarea name="diagnosis" id="diagnosis" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="<?= esc($tr('diagnosis_placeholder')) ?>"><?= old('diagnosis') ?></textarea>
                                </div>

                                <div>
                                    <label for="examination_notes" class="block text-sm font-semibold text-slate-700 mb-2"><?= esc($tr('examination_notes')) ?></label>
                                    <textarea name="examination_notes" id="examination_notes" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="<?= esc($tr('examination_notes_placeholder')) ?>"><?= old('examination_notes') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200">
                            <a href="<?= esc(workflow_cancel('examination', $return_to ?? null)) ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                <?= esc($tr('cancel')) ?>
                            </a>
                            <button type="submit" name="after_save" value="odontogram" class="inline-flex items-center justify-center px-6 py-2.5 border border-cyan-200 bg-cyan-50 text-cyan-800 text-sm font-semibold rounded-lg hover:bg-cyan-100 transition-colors">
                                <i class="fas fa-tooth mr-2"></i>
                                <?= esc($tr('create_and_open_chart')) ?>
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:from-blue-700 hover:to-cyan-700 transition-colors">
                                <i class="fas fa-stethoscope mr-2"></i>
                                <?= esc($tr('create_examination')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const optionalToggle = document.getElementById('optionalEnabled');
    const optionalSection = document.getElementById('optionalSection');
    const optionalTrack = document.getElementById('optionalTrack');
    const optionalDot = document.getElementById('optionalDot');
    const patientSelect = document.getElementById('patientSelect');
    const healthDetailsWrap = document.getElementById('healthUpdateDetailsWrap');
    const healthDetailsInput = document.getElementById('health_update_details');

    function syncOptionalState() {
        if (!optionalToggle || !optionalSection) {
            return;
        }

        if (optionalToggle.checked) {
            optionalSection.classList.remove('hidden');
            optionalTrack?.classList.remove('bg-gray-200');
            optionalTrack?.classList.add('bg-blue-500');
            if (optionalDot) {
                window.applyToggleDotState?.(optionalDot, true);
            }
        } else {
            optionalSection.classList.add('hidden');
            optionalTrack?.classList.remove('bg-blue-500');
            optionalTrack?.classList.add('bg-gray-200');
            if (optionalDot) {
                window.applyToggleDotState?.(optionalDot, false);
            }
        }
    }

    if (optionalToggle) {
        optionalToggle.addEventListener('change', syncOptionalState);
        syncOptionalState();
    }

    function syncHealthToggleVisual(toggle) {
        if (!toggle) return;
        const track = document.getElementById(toggle.dataset.trackId || '');
        const dot = document.getElementById(toggle.dataset.dotId || '');
        if (toggle.checked) {
            track?.classList.remove('bg-gray-200');
            track?.classList.add('bg-blue-500');
            window.applyToggleDotState?.(dot, true);
        } else {
            track?.classList.remove('bg-blue-500');
            track?.classList.add('bg-gray-200');
            window.applyToggleDotState?.(dot, false);
        }
    }

    function syncHealthUpdateDetailsVisibility() {
        const toggles = Array.from(document.querySelectorAll('.health-update-toggle')).filter((toggle) => !toggle.disabled);
        const anyYes = toggles.some((toggle) => toggle.checked);
        if (!healthDetailsWrap || !healthDetailsInput) return;

        if (anyYes) {
            healthDetailsWrap.classList.remove('hidden');
            healthDetailsInput.disabled = false;
        } else {
            healthDetailsWrap.classList.add('hidden');
            healthDetailsInput.disabled = true;
            healthDetailsInput.value = '';
        }
    }

    function syncPregnancyQuestionVisibility() {
        const selectedGender = (patientSelect?.selectedOptions?.[0]?.dataset?.gender || '<?= esc($selectedPatientGender) ?>').toLowerCase();
        const showPregnancy = selectedGender === '' || selectedGender === 'female';

        document.querySelectorAll('[data-pregnancy-only="1"]').forEach(function (item) {
            const toggle = item.querySelector('.health-update-toggle');
            const hiddenInput = item.querySelector('input[type="hidden"]');
            if (showPregnancy) {
                item.classList.remove('hidden');
                if (toggle) toggle.disabled = false;
                if (hiddenInput) hiddenInput.disabled = false;
            } else {
                item.classList.add('hidden');
                if (toggle) {
                    toggle.checked = false;
                    toggle.disabled = true;
                    syncHealthToggleVisual(toggle);
                }
                if (hiddenInput) hiddenInput.disabled = true;
            }
        });
        syncHealthUpdateDetailsVisibility();
    }

    document.querySelectorAll('.health-update-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            syncHealthToggleVisual(toggle);
            syncHealthUpdateDetailsVisibility();
        });
        syncHealthToggleVisual(toggle);
    });

    patientSelect?.addEventListener('change', syncPregnancyQuestionVisibility);
    syncPregnancyQuestionVisibility();
    syncHealthUpdateDetailsVisibility();
});
</script>
<?= $this->endSection() ?>


