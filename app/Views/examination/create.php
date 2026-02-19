<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$validation = session()->getFlashdata('validation');
$optionalFieldKeys = [
    'medical_history',
    'dental_history',
    'clinical_findings',
    'diagnosis',
    'examination_notes',
];
$optionalEnabledByOld = false;
foreach ($optionalFieldKeys as $fieldKey) {
    if (old($fieldKey) !== null && old($fieldKey) !== '') {
        $optionalEnabledByOld = true;
        break;
    }
}
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
                                <h1 class="text-2xl font-black text-gray-900 group-hover:text-cyan-900 transition-colors duration-300">New Examination</h1>
                                <p class="text-gray-600 font-medium">Complete core fields first, then enable optional clinical details if needed</p>
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
                                    <span class="relative z-10">Back to Patient</span>
                                </a>
                            <?php endif; ?>
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back</span>
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
                                <span>Required Information</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                <div>
                                    <label for="patientSelect" class="block text-sm font-semibold text-slate-700 mb-2">Patient *</label>
                                    <select
                                        name="patient_id"
                                        id="patientSelect"
                                        class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        data-searchable-select
                                        data-minimum-input-length="0"
                                        data-limit="5"
                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                        data-placeholder="Search patients by name, phone, or ID..."
                                        required
                                    >
                                        <option value="">Select a patient</option>
                                        <?php if (!empty($selected_patient)): ?>
                                            <option value="<?= $selected_patient['id'] ?>" selected>
                                                <?= esc($selected_patient['first_name'] . ' ' . $selected_patient['last_name']) ?> (<?= esc($selected_patient['phone'] ?? $selected_patient['patient_id'] ?? 'N/A') ?>)
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if ($validation && $validation->hasError('patient_id')): ?>
                                        <p class="text-red-600 text-sm mt-2"><?= $validation->getError('patient_id') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="examination_type" class="block text-sm font-semibold text-slate-700 mb-2">Examination Type *</label>
                                    <select name="examination_type" id="examination_type" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" required>
                                        <option value="">Select type</option>
                                        <option value="initial" <?= old('examination_type') == 'initial' ? 'selected' : '' ?>>Initial</option>
                                        <option value="periodic" <?= old('examination_type') == 'periodic' ? 'selected' : '' ?>>Periodic</option>
                                        <option value="emergency" <?= old('examination_type') == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                        <option value="follow_up" <?= old('examination_type') == 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                                    </select>
                                    <?php if ($validation && $validation->hasError('examination_type')): ?>
                                        <p class="text-red-600 text-sm mt-2"><?= $validation->getError('examination_type') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="examination_date" class="block text-sm font-semibold text-slate-700 mb-2">Examination Date *</label>
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
                                <label for="chief_complaint" class="block text-sm font-semibold text-slate-700 mb-2">Chief Complaint *</label>
                                <textarea
                                    name="chief_complaint"
                                    id="chief_complaint"
                                    rows="3"
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y"
                                    placeholder="Describe the patient's main complaint..."
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
                                        <h4 class="text-lg font-bold text-gray-900">Additional Clinical Details</h4>
                                        <p class="text-sm text-gray-500">Optional</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="optionalEnabled" class="sr-only" <?= $optionalEnabledByOld ? 'checked' : '' ?>>
                                    <div id="optionalTrack" class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative">
                                        <div id="optionalDot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-semibold text-gray-700">Enable</span>
                                </label>
                            </div>

                            <div id="optionalSection" class="hidden space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="medical_history" class="block text-sm font-semibold text-slate-700 mb-2">Medical History</label>
                                        <textarea name="medical_history" id="medical_history" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="Medications, allergies, relevant conditions..."><?= old('medical_history') ?></textarea>
                                    </div>
                                    <div>
                                        <label for="dental_history" class="block text-sm font-semibold text-slate-700 mb-2">Dental History</label>
                                        <textarea name="dental_history" id="dental_history" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="Previous dental treatments and oral history..."><?= old('dental_history') ?></textarea>
                                    </div>
                                </div>

                                <div>
                                    <label for="clinical_findings" class="block text-sm font-semibold text-slate-700 mb-2">Clinical Findings</label>
                                    <textarea name="clinical_findings" id="clinical_findings" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="Objective findings from examination..."><?= old('clinical_findings') ?></textarea>
                                </div>

                                <div>
                                    <label for="diagnosis" class="block text-sm font-semibold text-slate-700 mb-2">Diagnosis</label>
                                    <textarea name="diagnosis" id="diagnosis" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="Diagnosis based on findings..."><?= old('diagnosis') ?></textarea>
                                </div>

                                <div>
                                    <label for="examination_notes" class="block text-sm font-semibold text-slate-700 mb-2">Examination Notes</label>
                                    <textarea name="examination_notes" id="examination_notes" rows="4" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 resize-y" placeholder="Any additional notes..."><?= old('examination_notes') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:from-blue-700 hover:to-cyan-700 transition-colors">
                                <i class="fas fa-stethoscope mr-2"></i>
                                Create Examination
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

    function syncOptionalState() {
        if (!optionalToggle || !optionalSection) {
            return;
        }

        if (optionalToggle.checked) {
            optionalSection.classList.remove('hidden');
            optionalTrack?.classList.remove('bg-gray-200');
            optionalTrack?.classList.add('bg-blue-500');
            if (optionalDot) {
                optionalDot.style.transform = 'translateX(20px)';
            }
        } else {
            optionalSection.classList.add('hidden');
            optionalTrack?.classList.remove('bg-blue-500');
            optionalTrack?.classList.add('bg-gray-200');
            if (optionalDot) {
                optionalDot.style.transform = 'translateX(0)';
            }
        }
    }

    if (optionalToggle) {
        optionalToggle.addEventListener('change', syncOptionalState);
        syncOptionalState();
    }
});
</script>
<?= $this->endSection() ?>

