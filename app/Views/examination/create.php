<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Examination Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-purple-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Enhanced Examination Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-emerald-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-teal-500/10 group-hover:shadow-teal-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-stethoscope text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-teal-900 transition-colors duration-300">New Examination</h3>
                                <p class="text-gray-600 font-medium">Fill in the details to create the examination record</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('examination') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Examinations</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('examination/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        
                        <!-- Patient Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-teal-600"></i>
                                    <span>Patient *</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patient_id" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-search-url="<?= base_url('api/search/patients') ?>"
                                            data-placeholder="Search patients by name, phone, or ID..."
                                            required>
                                        <option value="">Select a patient</option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" <?= (old('patient_id', $selected_patient_id ?? '') == $patient['id']) ? 'selected' : '' ?>>
                                                    <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> 
                                                    (<?= esc($patient['patient_id']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-stethoscope text-emerald-600"></i>
                                    <span>Examination Type *</span>
                                </label>
                                <div class="relative">
                                    <select name="examination_type" id="examination_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select type</option>
                                        <option value="initial" <?= old('examination_type') == 'initial' ? 'selected' : '' ?>>Initial</option>
                                        <option value="periodic" <?= old('examination_type') == 'periodic' ? 'selected' : '' ?>>Periodic</option>
                                        <option value="emergency" <?= old('examination_type') == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                        <option value="follow_up" <?= old('examination_type') == 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('examination_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-emerald-600"></i>
                                    <span>Examination Date *</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="examination_date" id="examination_date" 
                                           value="<?= old('examination_date', date('Y-m-d')) ?>" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('examination_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock text-amber-600"></i>
                                    <span>Examination Time</span>
                                </label>
                                <div class="relative">
                                    <input type="time" name="examination_time" id="examination_time" 
                                           value="<?= old('examination_time', date('H:i')) ?>" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chief Complaint -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-comment-medical text-orange-600"></i>
                                <span>Chief Complaint *</span>
                            </label>
                            <div class="relative">
                                <textarea name="chief_complaint" id="chief_complaint" rows="3" 
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                          placeholder="Describe the patient's main complaint..." required><?= old('chief_complaint') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('chief_complaint')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('chief_complaint') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Medical & Dental History -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-history text-purple-600"></i>
                                    <span>Medical History</span>
                                </label>
                                <div class="relative">
                                    <textarea name="medical_history" id="medical_history" rows="4" 
                                              class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                              placeholder="Patient's medical history, medications, allergies..."><?= old('medical_history') ?></textarea>
                                    <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tooth text-pink-600"></i>
                                    <span>Dental History</span>
                                </label>
                                <div class="relative">
                                    <textarea name="dental_history" id="dental_history" rows="4" 
                                              class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                              placeholder="Previous dental treatments, oral hygiene habits..."><?= old('dental_history') ?></textarea>
                                    <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clinical Assessment -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-search text-red-600"></i>
                                <span>Clinical Findings</span>
                            </label>
                            <div class="relative">
                                <textarea name="clinical_findings" id="clinical_findings" rows="4" 
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                          placeholder="Objective findings from the examination..."><?= old('clinical_findings') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis & Treatment Plan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-diagnoses text-indigo-600"></i>
                                    <span>Diagnosis</span>
                                </label>
                                <div class="relative">
                                    <textarea name="diagnosis" id="diagnosis" rows="4" 
                                              class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                              placeholder="Clinical diagnosis based on findings..."><?= old('diagnosis') ?></textarea>
                                    <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clipboard-list text-cyan-600"></i>
                                    <span>Treatment Plan</span>
                                </label>
                                <div class="relative">
                                    <textarea name="treatment_plan" id="treatment_plan" rows="4" 
                                              class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                              placeholder="Proposed treatment plan and procedures..."><?= old('treatment_plan') ?></textarea>
                                    <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-teal-600"></i>
                                <span>Examination Notes</span>
                            </label>
                            <div class="relative">
                                <textarea name="examination_notes" id="examination_notes" rows="4" 
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                          placeholder="Additional notes and observations..."><?= old('examination_notes') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('examination') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-teal-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-teal-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-teal-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-stethoscope mr-2 relative z-10"></i>
                                <span class="relative z-10">Create Examination</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const examinationDate = document.getElementById('examination_date');
    const examinationTime = document.getElementById('examination_time');
    
    // Auto-populate examination date with today's date if not set
    if (!examinationDate.value) {
        examinationDate.value = new Date().toISOString().split('T')[0];
    }
    
    // Auto-populate examination time with current time if not set
    if (!examinationTime.value) {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        examinationTime.value = timeString;
    }
    
    // Add form field interactions
    initializeFormEnhancements();
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['patient_id', 'examination_date', 'examination_type', 'chief_complaint'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            field.classList.add('border-red-500', 'ring-red-500/50');
            isValid = false;
        } else {
            field.classList.remove('border-red-500', 'ring-red-500/50');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Initialize form enhancements
function initializeFormEnhancements() {
    // Add focus effects to form fields
    const formFields = document.querySelectorAll('input, select, textarea');
    
    formFields.forEach(field => {
        field.addEventListener('focus', function() {
            this.classList.add('ring-4', 'ring-opacity-20');
        });
        
        field.addEventListener('blur', function() {
            this.classList.remove('ring-4', 'ring-opacity-20');
        });
    });
}
</script>
<?= $this->endSection() ?>
