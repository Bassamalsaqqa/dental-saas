<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Prescription Edit with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Prescription Edit Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-edit text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300">Edit Prescription</h3>
                                <p class="text-gray-600 font-medium">Update prescription details and instructions</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('prescription') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Prescriptions</span>
                            </a>
                            <a href="<?= base_url('prescription/' . $prescription['id']) ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-eye mr-2 relative z-10"></i>
                                <span class="relative z-10">View Details</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('prescription/' . $prescription['id'] . '/update') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <!-- Patient and Medication Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-emerald-600"></i>
                                    <span>Patient *</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Search patients by name, phone, or ID...</option>
                                        <?php 
                                        $selectedPatientId = old('patient_id', $prescription['patient_id']);
                                        $selectedPatient = null;
                                        foreach ($patients as $patient) {
                                            if ($patient['id'] == $selectedPatientId) {
                                                $selectedPatient = $patient;
                                                break;
                                            }
                                        }
                                        if ($selectedPatient): ?>
                                            <option value="<?= $selectedPatient['id'] ?>" selected>
                                                <?= $selectedPatient['first_name'] . ' ' . $selectedPatient['last_name'] ?> (<?= $selectedPatient['phone'] ?>)
                                            </option>
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

                        </div>

                        <!-- Medicines Section -->
                        <div class="group/field relative">
                            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-6 border border-blue-200">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-pills text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">Medicines</h3>
                                    </div>
                                    <button type="button" onclick="addMedicine()" class="group/btn relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                        <i class="fas fa-plus mr-2 relative z-10"></i>
                                        <span class="relative z-10">Add Medicine</span>
                                    </button>
                                </div>

                                <!-- Medicines Container -->
                                <div id="medicines-container" class="space-y-4">
                                    <!-- Medicine items will be added here dynamically -->
                                </div>

                                <!-- Empty State -->
                                <div id="empty-medicines" class="text-center py-8">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-cyan-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-pills text-blue-500 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-600 font-medium">No medicines added yet</p>
                                    <p class="text-sm text-gray-500">Click "Add Medicine" to start adding medications</p>
                                </div>
                            </div>
                        </div>

                        <!-- Prescribed and Expiry Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-indigo-600"></i>
                                    <span>Prescribed Date *</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="prescribed_date" value="<?= old('prescribed_date', $prescription['prescribed_date']) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('prescribed_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('prescribed_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-times text-red-600"></i>
                                    <span>Expiry Date</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="expiry_date" value="<?= old('expiry_date', $prescription['expiry_date']) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('expiry_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('expiry_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-info-circle text-teal-600"></i>
                                <span>Status *</span>
                            </label>
                            <div class="relative">
                                <select name="status" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" required>
                                    <option value="active" <?= (old('status', $prescription['status']) == 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="expired" <?= (old('status', $prescription['status']) == 'expired') ? 'selected' : '' ?>>Expired</option>
                                    <option value="cancelled" <?= (old('status', $prescription['status']) == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    <option value="pending" <?= (old('status', $prescription['status']) == 'pending') ? 'selected' : '' ?>>Pending</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('status')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('status') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Instructions Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-teal-600"></i>
                                <span>Instructions</span>
                            </label>
                            <div class="relative">
                                <textarea name="instructions" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Detailed instructions for taking the medication..."><?= old('instructions', $prescription['instructions']) ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('instructions')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('instructions') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('prescription/' . $prescription['id']) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10">Update Prescription</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let medicineCount = 0;

// Parse existing medicines from the prescription data
<?php 
$existingMedicines = json_decode($prescription['medication_name'], true);
if (is_array($existingMedicines) && !empty($existingMedicines)): 
?>
    // Load existing medicines
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($existingMedicines as $index => $medicine): ?>
            addMedicineWithData(<?= $index + 1 ?>, '<?= addslashes($medicine['name'] ?? '') ?>', '<?= addslashes($medicine['dosage'] ?? '') ?>', '<?= addslashes($medicine['frequency'] ?? '') ?>', '<?= addslashes($medicine['duration'] ?? '') ?>');
        <?php endforeach; ?>
    });
<?php endif; ?>

function addMedicine() {
    medicineCount++;
    addMedicineWithData(medicineCount, '', '', '', '');
}

function addMedicineWithData(count, name, dosage, frequency, duration) {
    // Hide empty state
    document.getElementById('empty-medicines').style.display = 'none';
    
    // Create medicine item
    const medicineItem = document.createElement('div');
    medicineItem.className = 'medicine-item bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300';
    
    // Header section
    const headerDiv = document.createElement('div');
    headerDiv.className = 'flex items-center justify-between mb-4';
    
    const titleContainer = document.createElement('div');
    titleContainer.className = 'flex items-center space-x-3';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center';
    const pillIcon = document.createElement('i');
    pillIcon.className = 'fas fa-pills text-white text-sm';
    iconContainer.appendChild(pillIcon);
    
    const titleH4 = document.createElement('h4');
    titleH4.className = 'text-lg font-semibold text-gray-900';
    titleH4.textContent = 'Medicine ' + count;
    
    titleContainer.appendChild(iconContainer);
    titleContainer.appendChild(titleH4);
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'group/btn relative p-2 text-red-600 hover:text-red-800 rounded-lg hover:bg-red-50 transition-all duration-300';
    removeBtn.onclick = function() {
        removeMedicine(this);
    };
    
    const btnBg = document.createElement('div');
    btnBg.className = 'absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 rounded-lg blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300';
    const trashIcon = document.createElement('i');
    trashIcon.className = 'fas fa-trash relative z-10';
    
    removeBtn.appendChild(btnBg);
    removeBtn.appendChild(trashIcon);
    
    headerDiv.appendChild(titleContainer);
    headerDiv.appendChild(removeBtn);
    medicineItem.appendChild(headerDiv);
    
    // Grid section
    const gridDiv = document.createElement('div');
    gridDiv.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4';
    
    const createField = (label, iconClass, inputName, val, placeholder, iconColor) => {
        const fieldContainer = document.createElement('div');
        fieldContainer.className = 'group/field relative';
        
        const labelElem = document.createElement('label');
        labelElem.className = 'block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2';
        const labelIcon = document.createElement('i');
        labelIcon.className = iconClass + ' ' + iconColor;
        const labelSpan = document.createElement('span');
        labelSpan.textContent = label + ' *';
        labelElem.appendChild(labelIcon);
        labelElem.appendChild(labelSpan);
        
        const relativeDiv = document.createElement('div');
        relativeDiv.className = 'relative';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.name = inputName;
        input.value = val;
        input.className = 'w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300';
        input.placeholder = placeholder;
        input.required = true;
        
        const innerIconContainer = document.createElement('div');
        innerIconContainer.className = 'absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none';
        const innerIcon = document.createElement('i');
        innerIcon.className = iconClass + ' text-gray-400 text-xs';
        innerIconContainer.appendChild(innerIcon);
        
        relativeDiv.appendChild(input);
        relativeDiv.appendChild(innerIconContainer);
        
        fieldContainer.appendChild(labelElem);
        fieldContainer.appendChild(relativeDiv);
        return fieldContainer;
    };
    
    gridDiv.appendChild(createField('Medicine Name', 'fas fa-pills', `medicines[${count}][name]`, name, 'e.g., Amoxicillin', 'text-blue-600'));
    gridDiv.appendChild(createField('Dosage', 'fas fa-weight', `medicines[${count}][dosage]`, dosage, 'e.g., 500mg', 'text-purple-600'));
    gridDiv.appendChild(createField('Frequency', 'fas fa-clock', `medicines[${count}][frequency]`, frequency, 'e.g., Twice daily', 'text-amber-600'));
    gridDiv.appendChild(createField('Duration', 'fas fa-calendar-alt', `medicines[${count}][duration]`, duration, 'e.g., 7 days', 'text-rose-600'));
    
    medicineItem.appendChild(gridDiv);
    
    // Add to container
    document.getElementById('medicines-container').appendChild(medicineItem);
    
    // Add animation
    medicineItem.style.opacity = '0';
    medicineItem.style.transform = 'translateY(20px)';
    setTimeout(() => {
        medicineItem.style.transition = 'all 0.3s ease';
        medicineItem.style.opacity = '1';
        medicineItem.style.transform = 'translateY(0)';
    }, 10);
}

function removeMedicine(button) {
    const medicineItem = button.closest('.medicine-item');
    medicineItem.style.transition = 'all 0.3s ease';
    medicineItem.style.opacity = '0';
    medicineItem.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        medicineItem.remove();
        
        // Show empty state if no medicines left
        const container = document.getElementById('medicines-container');
        if (container.children.length === 0) {
            document.getElementById('empty-medicines').style.display = 'block';
        }
    }, 300);
}

// Initialize Select2 for patient field
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for patient search
    $('#patientSelect').select2({
        ajax: {
            url: '<?= base_url('api/search/patients') ?>',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    }),
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search patients by name, phone, or ID...',
        allowClear: true,
        minimumInputLength: 1,
        width: '100%'
    });

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const medicinesContainer = document.getElementById('medicines-container');
            const medicineItems = medicinesContainer.querySelectorAll('.medicine-item');
            
            if (medicineItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one medicine.');
                return false;
            }
            
            // Validate each medicine
            let isValid = true;
            medicineItems.forEach((item, index) => {
                const name = item.querySelector('input[name*="[name]"]').value.trim();
                const dosage = item.querySelector('input[name*="[dosage]"]').value.trim();
                const frequency = item.querySelector('input[name*="[frequency]"]').value.trim();
                const duration = item.querySelector('input[name*="[duration]"]').value.trim();
                
                if (!name || !dosage || !frequency || !duration) {
                    isValid = false;
                    item.style.border = '2px solid #ef4444';
                    setTimeout(() => {
                        item.style.border = '1px solid #e5e7eb';
                    }, 3000);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all medicine details.');
                return false;
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
