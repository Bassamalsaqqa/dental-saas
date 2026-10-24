<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<!-- Enhanced Prescription Creation with Advanced Design -->
<div class="denta-prescription-workspace min-h-screen v3-surface">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Prescription Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative v3-surface v3-corner group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b v3-border">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="v3-icon relative w-12 h-12 v3-corner flex items-center justify-center transition-all duration-300">
                                    <i class="fas fa-prescription-bottle-alt v3-text-on-primary text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black v3-text-ink v3-hover-text-primary transition-colors duration-300"><?= lang('Prescription.new_prescription') ?></h3>
                                <p class="v3-text-muted font-medium"><?= lang('Prescription.new_prescription_description') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="<?= esc(workflow_back('prescription', $return_to ?? null)) ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 v3-border v3-text-ink text-sm font-bold v3-radius v3-border-default transition-all duration-300 v3-btn v3-btn--secondary">

                                <i class="fas fa-arrow-left rtl-back-icon mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Prescription.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('prescription/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center space-x-2">
                                <i class="fas fa-user v3-text-primary"></i>
                                <span><?= lang('Prescription.patient_required') ?></span>
                            </label>
                            <div class="relative">
                                <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-select"
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
                                    <i class="fas fa-chevron-down v3-text-muted"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Medicines Section -->
                        <div class="group/section relative">

                            <div class="v3-surface-heading relative p-6 v3-corner border v3-border">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="v3-icon w-10 h-10 v3-radius flex items-center justify-center">
                                            <i class="fas fa-pills v3-text-on-primary"></i>
                                        </div>
                                        <h3 class="text-xl font-bold v3-text-ink"><?= lang('Prescription.medicines') ?></h3>
                                    </div>
                                    <button type="button" onclick="addMedicine()" class="v3-action v3-action-primary group/btn relative inline-flex items-center px-4 py-2 text-sm font-bold v3-radius transition-all duration-300 v3-btn v3-btn--secondary">

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
                                    <div class="v3-icon w-16 h-16 v3-corner flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-pills v3-accent-text text-2xl"></i>
                                    </div>
                                    <p class="v3-text-muted font-medium"><?= lang('Prescription.no_medicines_added') ?></p>
                                    <p class="text-sm v3-text-muted"><?= lang('Prescription.click_add_medicine') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Prescribed and Expiry Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar v3-accent-text"></i>
                                    <span><?= lang('Prescription.prescribed_date_required') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="prescribed_date" data-clinic-date="1" value="<?= old('prescribed_date', date('Y-m-d')) ?>" class="prescription-date-input w-full py-3 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-input" required>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar v3-text-muted"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('prescribed_date')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('prescribed_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-times v3-text-danger"></i>
                                    <span><?= lang('Prescription.expiry_date') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date', date('Y-m-d', strtotime('+30 days'))) ?>" class="prescription-date-input w-full py-3 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 v3-input">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar v3-text-muted"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('expiry_date')): ?>
                                    <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('expiry_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Instructions Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold v3-text-ink mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note v3-text-primary"></i>
                                <span><?= lang('Prescription.instructions') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="instructions" rows="4" class="w-full px-4 py-3 v3-surface border-2 v3-border v3-radius focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 resize-none v3-textarea" placeholder="<?= esc(lang('Prescription.instructions_placeholder')) ?>"><?= old('instructions') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> v3-text-muted text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('instructions')): ?>
                                <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('instructions') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t v3-border">
                            <a href="<?= esc(workflow_cancel('prescription', $return_to ?? null)) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 v3-border v3-text-ink text-sm font-bold v3-radius v3-border-default transition-all duration-300 v3-btn v3-btn--secondary">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Prescription.cancel') ?></span>
                            </a>
                            <button type="submit" class="v3-action v3-action-primary group/btn relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-radius transition-all duration-300 v3-btn v3-btn--primary">

                                <i class="fas fa-prescription-bottle-alt mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Prescription.create_prescription') ?></span>
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
    medicineItem.className = 'medicine-item v3-bg-panel v3-radius border v3-border-default p-4 v3-elevation-none hover:v3-elevation-none transition-all duration-300';

    // Header section
    const headerDiv = document.createElement('div');
    headerDiv.className = 'flex items-center justify-between mb-4';

    const titleContainer = document.createElement('div');
    titleContainer.className = 'flex items-center space-x-3';

    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center';
    const pillIcon = document.createElement('i');
    pillIcon.className = 'fas fa-pills v3-text-on-primary text-sm';
    iconContainer.appendChild(pillIcon);

    const titleH4 = document.createElement('h4');
    titleH4.className = 'text-lg font-semibold v3-text-ink';
    titleH4.textContent = prescriptionI18n.medicineNumber.replace('{number}', medicineCount);

    titleContainer.appendChild(iconContainer);
    titleContainer.appendChild(titleH4);

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'group/btn relative p-2 v3-text-danger v3-hover-text-danger rounded-lg v3-hover-danger transition-all duration-300';
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
        labelElem.className = 'block text-sm font-bold v3-text-ink mb-2 flex items-center space-x-2';
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
        input.className = 'prescription-leading-input w-full py-2 v3-bg-panel border v3-border-default rounded-lg focus:outline-none focus:ring-2 v3-focus-control v3-focus-control transition-all duration-300';
        input.placeholder = placeholder;
        input.required = true;

        const innerIconContainer = document.createElement('div');
        innerIconContainer.className = document.documentElement.dir === 'rtl'
            ? 'absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none'
            : 'absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none';
        const innerIcon = document.createElement('i');
        innerIcon.className = iconClass + ' v3-text-muted text-xs';
        innerIconContainer.appendChild(innerIcon);

        relativeDiv.appendChild(input);
        relativeDiv.appendChild(innerIconContainer);

        fieldContainer.appendChild(labelElem);
        fieldContainer.appendChild(relativeDiv);
        return fieldContainer;
    };

    gridDiv.appendChild(createField(prescriptionI18n.medicineNameRequired.replace(' *', ''), 'fas fa-pills', `medicines[${medicineCount}][name]`, prescriptionI18n.placeholderAmoxicillin, 'v3-text-primary'));
    gridDiv.appendChild(createField(prescriptionI18n.dosageRequired.replace(' *', ''), 'fas fa-weight', `medicines[${medicineCount}][dosage]`, prescriptionI18n.placeholder500mg, 'v3-text-chair'));
    gridDiv.appendChild(createField(prescriptionI18n.frequencyRequired.replace(' *', ''), 'fas fa-clock', `medicines[${medicineCount}][frequency]`, prescriptionI18n.placeholderTwiceDaily, 'v3-text-warning'));
    gridDiv.appendChild(createField(prescriptionI18n.durationRequired.replace(' *', ''), 'fas fa-calendar-alt', `medicines[${medicineCount}][duration]`, prescriptionI18n.placeholder7Days, 'v3-text-danger'));

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


