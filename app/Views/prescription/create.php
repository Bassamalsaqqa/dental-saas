<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>
[dir="ltr"] .prescription-leading-input {
    padding-left: 0.75rem !important;
    padding-right: 2.5rem !important;
}

[dir="rtl"] .prescription-leading-input {
    padding-right: 2.5rem !important;
    padding-left: 0.75rem !important;
    text-align: right;
}

[dir="ltr"] input[data-clinic-date="1"].prescription-date-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].prescription-date-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}
</style>
<!-- Enhanced Prescription Creation with Advanced Design -->
<div class="v4-task-page"><header class="v4-page-heading"><div><h1><?= esc(lang('Prescription.new_prescription')) ?></h1><p><?= esc(lang('Prescription.new_prescription_description')) ?></p></div><a class="v4-button" href="<?= esc(workflow_back('prescription',$return_to??null)) ?>"><?= esc(lang('V4.back')) ?></a></header><form action="<?= base_url('prescription/store') ?>" method="POST" class="v4-form v4-task-form">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-user text-emerald-600"></i>
                                <span><?= lang('Prescription.patient_required') ?></span>
                            </label>
                            <div class="relative">
                                <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300"
                                        data-searchable-select
                                        data-minimum-input-length="0"
                                        data-limit="5"
                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                        data-search-param="q"
                                        data-value-field="id"
                                        data-text-field="text"
                                        data-placeholder="<?= esc(lang('Prescription.search_patients_placeholder')) ?>"
                                        data-allow-clear="true"
                                        data-minimum-input-length="1"
                                        data-delay="300"
                                        required>
                                    <option value=""><?= lang('Prescription.select_patient') ?></option>
                                    <?php if (!empty($selected_patient)): ?>
                                        <option value="<?= $selected_patient['id'] ?>" selected>
                                            <?= esc($selected_patient['first_name'] . ' ' . $selected_patient['last_name']) ?> (<?= esc($selected_patient['phone'] ?? $selected_patient['patient_id'] ?? lang('Prescription.na')) ?>)
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
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
                            <div class="absolute inset-0 v4-radius opacity-0 transition-opacity duration-300"></div>
                            <div class="relative p-6 v4-radius border border-blue-100">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 v4-radius flex items-center justify-center">
                                            <i class="fas fa-pills text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900"><?= lang('Prescription.medicines') ?></h3>
                                    </div>
                                    <button type="button" onclick="addMedicine()" class="group/btn relative inline-flex items-center px-4 py-2 text-white text-sm font-bold v4-radius transition-all duration-300">
                                        <div class="absolute inset-0 v4-radius opacity-0 transition-opacity duration-300"></div>
                                        <i class="fas fa-plus mr-2 relative z-10"></i>
                                        <span class="relative z-10"><?= lang('Prescription.add_medicine') ?></span>
                                    </button>
                                </div>

                                <!-- Medicines Container -->
                                <div id="medicines-container" class="space-y-4">
                                    <!-- Medicine items will be added here dynamically -->
                                </div>

                                <!-- Empty State -->
                                <div id="empty-medicines" class="text-center py-8">
                                    <div class="w-16 h-16 v4-radius flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-pills v4-text-action text-2xl"></i>
                                    </div>
                                    <p class="text-gray-600 font-medium"><?= lang('Prescription.no_medicines_added') ?></p>
                                    <p class="text-sm text-gray-500"><?= lang('Prescription.click_add_medicine') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Prescribed and Expiry Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar v4-text-action"></i>
                                    <span><?= lang('Prescription.prescribed_date_required') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="prescribed_date" data-clinic-date="1" value="<?= old('prescribed_date', date('Y-m-d')) ?>" class="prescription-date-input w-full py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300" required>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
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
                                    <span><?= lang('Prescription.expiry_date') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date', date('Y-m-d', strtotime('+30 days'))) ?>" class="prescription-date-input w-full py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
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
                                <span><?= lang('Prescription.instructions') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="instructions" rows="4" class="w-full px-4 py-3 v4-surface-fill border-2 border-gray-200 v4-radius focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 resize-none" placeholder="<?= esc(lang('Prescription.instructions_placeholder')) ?>"><?= old('instructions') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
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
                            <a href="<?= esc(workflow_cancel('prescription', $return_to ?? null)) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold v4-radius hover:bg-gradient-to-r hover:border-gray-400 transition-all duration-300">
                                <div class="absolute inset-0 v4-radius opacity-0 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Prescription.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 text-white text-sm font-bold v4-radius transition-all duration-300">
                                <div class="absolute inset-0 v4-radius opacity-0 transition-opacity duration-300"></div>
                                <i class="fas fa-prescription-bottle-alt mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Prescription.create_prescription') ?></span>
                            </button>
                        </div>
                    </form></div>
<script>
let medicineCount = 0;
const prescriptionI18n = {
    medicineNumber: <?= json_encode(lang('Prescription.medicine_number')) ?>,
    medicineNameRequired: <?= json_encode(lang('Prescription.medicine_name_required')) ?>,
    dosageRequired: <?= json_encode(lang('Prescription.dosage_required')) ?>,
    frequencyRequired: <?= json_encode(lang('Prescription.frequency_required')) ?>,
    durationRequired: <?= json_encode(lang('Prescription.duration_required')) ?>,
    placeholderAmoxicillin: <?= json_encode(lang('Prescription.placeholder_amoxicillin')) ?>,
    placeholder500mg: <?= json_encode(lang('Prescription.placeholder_500mg')) ?>,
    placeholderTwiceDaily: <?= json_encode(lang('Prescription.placeholder_twice_daily')) ?>,
    placeholder7Days: <?= json_encode(lang('Prescription.placeholder_7_days')) ?>,
    alertAddAtLeastOne: <?= json_encode(lang('Prescription.alert_add_at_least_one_medicine_to_prescription')) ?>,
    alertFillAllComplete: <?= json_encode(lang('Prescription.alert_fill_all_medicine_details_completely')) ?>
};

function addMedicine() {
    medicineCount++;

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
    titleH4.textContent = prescriptionI18n.medicineNumber.replace('{number}', medicineCount);

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

    // Helper to create input field
    const createField = (label, iconClass, inputName, placeholder, iconColor) => {
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
        input.className = 'prescription-leading-input w-full py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300';
        input.placeholder = placeholder;
        input.required = true;

        const innerIconContainer = document.createElement('div');
        innerIconContainer.className = document.documentElement.dir === 'rtl'
            ? 'absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none'
            : 'absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none';
        const innerIcon = document.createElement('i');
        innerIcon.className = iconClass + ' text-gray-400 text-xs';
        innerIconContainer.appendChild(innerIcon);

        relativeDiv.appendChild(input);
        relativeDiv.appendChild(innerIconContainer);

        fieldContainer.appendChild(labelElem);
        fieldContainer.appendChild(relativeDiv);
        return fieldContainer;
    };

    gridDiv.appendChild(createField(prescriptionI18n.medicineNameRequired.replace(' *', ''), 'fas fa-pills', `medicines[${medicineCount}][name]`, prescriptionI18n.placeholderAmoxicillin, 'text-blue-600'));
    gridDiv.appendChild(createField(prescriptionI18n.dosageRequired.replace(' *', ''), 'fas fa-weight', `medicines[${medicineCount}][dosage]`, prescriptionI18n.placeholder500mg, 'text-purple-600'));
    gridDiv.appendChild(createField(prescriptionI18n.frequencyRequired.replace(' *', ''), 'fas fa-clock', `medicines[${medicineCount}][frequency]`, prescriptionI18n.placeholderTwiceDaily, 'text-amber-600'));
    gridDiv.appendChild(createField(prescriptionI18n.durationRequired.replace(' *', ''), 'fas fa-calendar-alt', `medicines[${medicineCount}][duration]`, prescriptionI18n.placeholder7Days, 'text-rose-600'));

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
        alert(prescriptionI18n.alertAddAtLeastOne);
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
        alert(prescriptionI18n.alertFillAllComplete);
        return false;
    }
});

// Initialize with one medicine
document.addEventListener('DOMContentLoaded', function() {
    addMedicine();
});
</script>
<?= $this->endSection() ?>


