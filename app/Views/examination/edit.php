<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Examination</h1>
        <p class="text-gray-600 mt-1">Update examination record - <?= esc($examination['examination_id']) ?></p>
    </div>

    <!-- Examination Form -->
    <div class="bg-white rounded-lg shadow-lg">
        <form action="<?= base_url('examination/' . $examination['id'] . '/update') ?>" method="POST" class="p-8 space-y-8">
            <?= csrf_field() ?>
            
            <!-- Patient Selection Section -->
            <div class="border-b border-gray-200 pb-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Patient Selection</h2>
                        <p class="text-sm text-gray-500">Select the patient for this examination</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="lg:col-span-2">
                        <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Patient <span class="text-red-500">*</span>
                        </label>
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-search-url="<?= base_url('api/search/patients') ?>"
                                            data-placeholder="Search patients by name, phone, or ID..."
                                            required>
                                        <option value="">Select a patient</option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" selected>
                                                    <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                            <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('validation')->getError('patient_id') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="examination_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Examination Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="examination_date" id="examination_date" 
                           value="<?= old('examination_date', $examination['examination_date']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_date')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('validation')->getError('examination_date') ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="examination_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Examination Type <span class="text-red-500">*</span>
                    </label>
                    <select name="examination_type" id="examination_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select type...</option>
                        <option value="initial" <?= old('examination_type', $examination['examination_type']) == 'initial' ? 'selected' : '' ?>>Initial</option>
                        <option value="periodic" <?= old('examination_type', $examination['examination_type']) == 'periodic' ? 'selected' : '' ?>>Periodic</option>
                        <option value="emergency" <?= old('examination_type', $examination['examination_type']) == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                        <option value="follow_up" <?= old('examination_type', $examination['examination_type']) == 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                    </select>
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('examination_type')): ?>
                        <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('validation')->getError('examination_type') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" <?= old('status', $examination['status']) == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= old('status', $examination['status']) == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= old('status', $examination['status']) == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= old('status', $examination['status']) == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <!-- Chief Complaint -->
            <div>
                <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-2">
                    Chief Complaint <span class="text-red-500">*</span>
                </label>
                <textarea name="chief_complaint" id="chief_complaint" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the patient's main complaint..." required><?= old('chief_complaint', $examination['chief_complaint']) ?></textarea>
                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('chief_complaint')): ?>
                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('validation')->getError('chief_complaint') ?></p>
                <?php endif; ?>
            </div>

            <!-- History of Present Illness -->
            <div>
                <label for="history_of_present_illness" class="block text-sm font-medium text-gray-700 mb-2">
                    History of Present Illness
                </label>
                <textarea name="history_of_present_illness" id="history_of_present_illness" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Detailed history of the current condition..."><?= old('history_of_present_illness', $examination['history_of_present_illness']) ?></textarea>
            </div>

            <!-- Medical and Dental History -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="medical_history" class="block text-sm font-medium text-gray-700 mb-2">
                        Medical History
                    </label>
                    <textarea name="medical_history" id="medical_history" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Patient's medical history, medications, allergies..."><?= old('medical_history', $examination['medical_history']) ?></textarea>
                </div>

                <div>
                    <label for="dental_history" class="block text-sm font-medium text-gray-700 mb-2">
                        Dental History
                    </label>
                    <textarea name="dental_history" id="dental_history" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Previous dental treatments, oral hygiene habits..."><?= old('dental_history', $examination['dental_history']) ?></textarea>
                </div>
            </div>

            <!-- Clinical Findings -->
            <div>
                <label for="clinical_findings" class="block text-sm font-medium text-gray-700 mb-2">
                    Clinical Findings
                </label>
                <textarea name="clinical_findings" id="clinical_findings" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Objective findings from the examination..."><?= old('clinical_findings', $examination['clinical_findings']) ?></textarea>
            </div>

            <!-- Diagnosis and Treatment Plan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-2">
                        Diagnosis
                    </label>
                    <textarea name="diagnosis" id="diagnosis" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Clinical diagnosis based on findings..."><?= old('diagnosis', $examination['diagnosis']) ?></textarea>
                </div>

                <div>
                    <label for="treatment_plan" class="block text-sm font-medium text-gray-700 mb-2">
                        Treatment Plan
                    </label>
                    <textarea name="treatment_plan" id="treatment_plan" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Proposed treatment plan and procedures..."><?= old('treatment_plan', $examination['treatment_plan']) ?></textarea>
                </div>
            </div>

            <!-- Prognosis and Recommendations -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="prognosis" class="block text-sm font-medium text-gray-700 mb-2">
                        Prognosis
                    </label>
                    <textarea name="prognosis" id="prognosis" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Expected outcome and prognosis..."><?= old('prognosis', $examination['prognosis']) ?></textarea>
                </div>

                <div>
                    <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-2">
                        Recommendations
                    </label>
                    <textarea name="recommendations" id="recommendations" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Patient care recommendations..."><?= old('recommendations', $examination['recommendations']) ?></textarea>
                </div>
            </div>

            <!-- Next Appointment -->
            <div>
                <label for="next_appointment" class="block text-sm font-medium text-gray-700 mb-2">
                    Next Appointment
                </label>
                <input type="date" name="next_appointment" id="next_appointment" 
                       value="<?= old('next_appointment', $examination['next_appointment']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Examination Notes -->
            <div>
                <label for="examination_notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Examination Notes
                </label>
                <textarea name="examination_notes" id="examination_notes" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Additional notes and observations..."><?= old('examination_notes', $examination['examination_notes']) ?></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-12 pt-6 border-t border-gray-200">
                <a href="<?= base_url('examination/' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Examination
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['patient_id', 'examination_date', 'examination_type', 'chief_complaint'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
</script>
<?= $this->endSection() ?>
