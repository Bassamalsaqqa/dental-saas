<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Prescription Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Prescription Form with Glassmorphism -->
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
                                    <i class="fas fa-prescription-bottle-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300">New Prescription</h3>
                                <p class="text-gray-600 font-medium">Fill in the medication details and instructions</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('prescription') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Prescriptions</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('prescription/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <!-- Patient Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-user text-emerald-600"></i>
                                <span>Patient *</span>
                            </label>
                            <div class="relative">
                                <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" 
                                        data-searchable-select 
                                        data-search-url="<?= base_url('api/search/patients') ?>"
                                        data-search-param="q"
                                        data-value-field="id"
                                        data-text-field="text"
                                        data-placeholder="Search patients by name, phone, or ID..."
                                        data-allow-clear="true"
                                        data-minimum-input-length="1"
                                        data-delay="300"
                                        required>
                                    <option value="">Select a patient</option>
                                    <?php if (old('patient_id')): ?>
                                        <?php 
                                        $selectedPatient = null;
                                        foreach ($patients as $patient) {
                                            if ($patient['id'] == old('patient_id')) {
                                                $selectedPatient = $patient;
                                                break;
                                            }
                                        }
                                        if ($selectedPatient): ?>
                                            <option value="<?= $selectedPatient['id'] ?>" selected>
                                                <?= $selectedPatient['first_name'] . ' ' . $selectedPatient['last_name'] ?> (<?= $selectedPatient['phone'] ?>)
                                            </option>
                                        <?php endif; ?>
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

                        <!-- Medicines Section -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 rounded-2xl blur opacity-0 group-hover/section:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/50 to-cyan-50/50">
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
                                    <input type="date" name="prescribed_date" value="<?= old('prescribed_date', date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
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
                                    <input type="date" name="expiry_date" value="<?= old('expiry_date', date('Y-m-d', strtotime('+30 days'))) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl">
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

                        <!-- Instructions Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-teal-600"></i>
                                <span>Instructions</span>
                            </label>
                            <div class="relative">
                                <textarea name="instructions" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Detailed instructions for taking the medication..."><?= old('instructions') ?></textarea>
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
                            <a href="<?= base_url('prescription') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-prescription-bottle-alt mr-2 relative z-10"></i>
                                <span class="relative z-10">Create Prescription</span>
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

function addMedicine() {
    medicineCount++;
    
    // Hide empty state
    document.getElementById('empty-medicines').style.display = 'none';
    
    // Create medicine item
    const medicineItem = document.createElement('div');
    medicineItem.className = 'medicine-item bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300';
    medicineItem.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-white text-sm"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900">Medicine ${medicineCount}</h4>
            </div>
            <button type="button" onclick="removeMedicine(this)" class="group/btn relative p-2 text-red-600 hover:text-red-800 rounded-lg hover:bg-red-50 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 rounded-lg blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                <i class="fas fa-trash relative z-10"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Medicine Name -->
            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2">
                    <i class="fas fa-pills text-blue-600"></i>
                    <span>Medicine Name *</span>
                </label>
                <div class="relative">
                    <input type="text" name="medicines[${medicineCount}][name]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300" placeholder="e.g., Amoxicillin" required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-pills text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            
            <!-- Dosage -->
            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2">
                    <i class="fas fa-weight text-purple-600"></i>
                    <span>Dosage *</span>
                </label>
                <div class="relative">
                    <input type="text" name="medicines[${medicineCount}][dosage]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300" placeholder="e.g., 500mg" required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-capsules text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            
            <!-- Frequency -->
            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2">
                    <i class="fas fa-clock text-amber-600"></i>
                    <span>Frequency *</span>
                </label>
                <div class="relative">
                    <input type="text" name="medicines[${medicineCount}][frequency]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300" placeholder="e.g., Twice daily" required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-repeat text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            
            <!-- Duration -->
            <div class="group/field relative">
                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2">
                    <i class="fas fa-calendar-alt text-rose-600"></i>
                    <span>Duration *</span>
                </label>
                <div class="relative">
                    <input type="text" name="medicines[${medicineCount}][duration]" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all duration-300" placeholder="e.g., 7 days" required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar-check text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
        </div>
    `;
    
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
    const container = document.getElementById('medicines-container');
    
    // Add removal animation
    medicineItem.style.transition = 'all 0.3s ease';
    medicineItem.style.opacity = '0';
    medicineItem.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        container.removeChild(medicineItem);
        
        // Show empty state if no medicines left
        if (container.children.length === 0) {
            document.getElementById('empty-medicines').style.display = 'block';
        }
    }, 300);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const medicines = document.querySelectorAll('.medicine-item');
    if (medicines.length === 0) {
        e.preventDefault();
        alert('Please add at least one medicine to the prescription.');
        return false;
    }
    
    // Validate each medicine
    let isValid = true;
    medicines.forEach((medicine, index) => {
        const name = medicine.querySelector('select[name*="[name]"]');
        const dosage = medicine.querySelector('input[name*="[dosage]"]');
        const frequency = medicine.querySelector('input[name*="[frequency]"]');
        const duration = medicine.querySelector('input[name*="[duration]"]');
        
        if (!name.value || !dosage.value || !frequency.value || !duration.value) {
            isValid = false;
            medicine.style.borderColor = '#ef4444';
            medicine.style.backgroundColor = '#fef2f2';
        } else {
            medicine.style.borderColor = '#d1d5db';
            medicine.style.backgroundColor = '#ffffff';
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all medicine details completely.');
        return false;
    }
});

// Initialize with one medicine
document.addEventListener('DOMContentLoaded', function() {
    addMedicine();
});
</script>
<?= $this->endSection() ?>
