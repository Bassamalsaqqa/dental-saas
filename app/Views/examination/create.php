<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">New Examination</h1>
                        <p class="text-sm text-slate-600 mt-1">Complete the core fields first. Add optional clinical details only if needed.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php
                            $showBackToPatient = !empty($selected_patient) && (empty($return_to) || preg_match('#^patient/\\d+(\\?|$)#', (string) $return_to));
                        ?>
                        <?php if ($showBackToPatient): ?>
                            <a href="<?= base_url('patient/' . $selected_patient['id']) ?>" class="inline-flex items-center px-4 py-2 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-lg hover:bg-emerald-50 transition-colors">
                                <i class="fas fa-user mr-2"></i>
                                Back to Patient
                            </a>
                        <?php endif; ?>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <form action="<?= base_url('examination/store') ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    <?php if (!empty($return_to)): ?>
                        <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="patientSelect" class="block text-sm font-semibold text-slate-700 mb-2">Patient *</label>
                            <select
                                name="patient_id"
                                id="patientSelect"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
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
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                <p class="text-red-600 text-sm mt-2"><?= session()->getFlashdata('validation')->getError('patient_id') ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="examination_type" class="block text-sm font-semibold text-slate-700 mb-2">Examination Type *</label>
                            <select name="examination_type" id="examination_type" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" required>
                                <option value="">Select type</option>
                                <option value="initial" <?= old('examination_type') == 'initial' ? 'selected' : '' ?>>Initial</option>
                                <option value="periodic" <?= old('examination_type') == 'periodic' ? 'selected' : '' ?>>Periodic</option>
                                <option value="emergency" <?= old('examination_type') == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                <option value="follow_up" <?= old('examination_type') == 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                            </select>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_type')): ?>
                                <p class="text-red-600 text-sm mt-2"><?= session()->getFlashdata('validation')->getError('examination_type') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label for="examination_date" class="block text-sm font-semibold text-slate-700 mb-2">Examination Date *</label>
                        <input
                            type="text"
                            name="examination_date"
                            id="examination_date"
                            data-clinic-date="1"
                            value="<?= old('examination_date', date('Y-m-d')) ?>"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
                            required
                        >
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_date')): ?>
                            <p class="text-red-600 text-sm mt-2"><?= session()->getFlashdata('validation')->getError('examination_date') ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="chief_complaint" class="block text-sm font-semibold text-slate-700 mb-2">Chief Complaint *</label>
                        <textarea
                            name="chief_complaint"
                            id="chief_complaint"
                            rows="3"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y"
                            placeholder="Describe the patient's main complaint..."
                            required
                        ><?= old('chief_complaint') ?></textarea>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('chief_complaint')): ?>
                            <p class="text-red-600 text-sm mt-2"><?= session()->getFlashdata('validation')->getError('chief_complaint') ?></p>
                        <?php endif; ?>
                    </div>

                    <details class="border border-slate-200 rounded-lg p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-slate-800">Additional Clinical Details (Optional)</summary>
                        <div class="mt-4 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="medical_history" class="block text-sm font-semibold text-slate-700 mb-2">Medical History</label>
                                    <textarea name="medical_history" id="medical_history" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y" placeholder="Medications, allergies, relevant conditions..."><?= old('medical_history') ?></textarea>
                                </div>
                                <div>
                                    <label for="dental_history" class="block text-sm font-semibold text-slate-700 mb-2">Dental History</label>
                                    <textarea name="dental_history" id="dental_history" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y" placeholder="Previous dental treatments and oral history..."><?= old('dental_history') ?></textarea>
                                </div>
                            </div>

                            <div>
                                <label for="clinical_findings" class="block text-sm font-semibold text-slate-700 mb-2">Clinical Findings</label>
                                <textarea name="clinical_findings" id="clinical_findings" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y" placeholder="Objective findings from examination..."><?= old('clinical_findings') ?></textarea>
                            </div>

                            <div>
                                <label for="diagnosis" class="block text-sm font-semibold text-slate-700 mb-2">Diagnosis</label>
                                <textarea name="diagnosis" id="diagnosis" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y" placeholder="Diagnosis based on findings..."><?= old('diagnosis') ?></textarea>
                            </div>

                            <div>
                                <label for="examination_notes" class="block text-sm font-semibold text-slate-700 mb-2">Examination Notes</label>
                                <textarea name="examination_notes" id="examination_notes" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 resize-y" placeholder="Any additional notes..."><?= old('examination_notes') ?></textarea>
                            </div>
                        </div>
                    </details>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200">
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('examination') ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-stethoscope mr-2"></i>
                            Create Examination
                        </button>
                    </div>
                </form>
            </div>
         </div>
     </div>
 </div>
 <?= $this->endSection() ?>
