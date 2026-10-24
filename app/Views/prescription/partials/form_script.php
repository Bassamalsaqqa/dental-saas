<script>
window.prescriptionFormI18n = window.prescriptionFormI18n || {
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

window.initPrescriptionCreateForms = function(root) {
    const scope = root || document;
    scope.querySelectorAll('[data-prescription-create-form]').forEach(function(form) {
        if (form.dataset.prescriptionCreateBound === '1') {
            return;
        }
        form.dataset.prescriptionCreateBound = '1';

        const i18n = window.prescriptionFormI18n;
        const container = form.querySelector('[data-prescription-medicines-container]');
        const emptyState = form.querySelector('[data-prescription-empty-medicines]');
        const addButton = form.querySelector('[data-prescription-add-medicine]');
        let medicineCount = 0;

        const syncEmptyState = function() {
            if (emptyState) {
                emptyState.style.display = container && container.children.length > 0 ? 'none' : 'block';
            }
        };

        const createField = function(label, iconClass, inputName, placeholder, iconColor) {
            const fieldContainer = document.createElement('div');
            fieldContainer.className = 'group/field relative';

            const labelElem = document.createElement('label');
            labelElem.className = 'mb-2 flex items-center gap-2 text-sm font-bold v3-text-ink';

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
            input.className = 'prescription-leading-input w-full rounded-lg border v3-border-default v3-bg-panel py-2 transition-all duration-300 v3-focus-control focus:outline-none focus:ring-2 v3-focus-control';
            input.placeholder = placeholder;
            input.required = true;

            const innerIconContainer = document.createElement('div');
            innerIconContainer.className = document.documentElement.dir === 'rtl'
                ? 'pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3'
                : 'pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3';

            const innerIcon = document.createElement('i');
            innerIcon.className = iconClass + ' text-xs v3-text-muted';
            innerIconContainer.appendChild(innerIcon);

            relativeDiv.appendChild(input);
            relativeDiv.appendChild(innerIconContainer);
            fieldContainer.appendChild(labelElem);
            fieldContainer.appendChild(relativeDiv);

            return fieldContainer;
        };

        const addMedicine = function() {
            if (!container) {
                return;
            }

            medicineCount += 1;
            const medicineItem = document.createElement('div');
            medicineItem.className = 'medicine-item v3-radius border v3-border-default v3-bg-panel p-4 v3-elevation-none transition-all duration-300 hover:v3-elevation-none';

            const headerDiv = document.createElement('div');
            headerDiv.className = 'mb-4 flex items-center justify-between';

            const titleContainer = document.createElement('div');
            titleContainer.className = 'flex items-center gap-3';

            const iconContainer = document.createElement('div');
            iconContainer.className = 'flex h-8 w-8 items-center justify-center rounded-lg v3-bg-panel v3-bg-panel v3-bg-panel';

            const pillIcon = document.createElement('i');
            pillIcon.className = 'fas fa-pills text-sm v3-text-on-primary';
            iconContainer.appendChild(pillIcon);

            const titleH4 = document.createElement('h4');
            titleH4.className = 'text-lg font-semibold v3-text-ink';
            titleH4.textContent = i18n.medicineNumber.replace('{number}', medicineCount);

            titleContainer.appendChild(iconContainer);
            titleContainer.appendChild(titleH4);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'group/btn relative rounded-lg p-2 v3-text-danger transition-all duration-300 v3-hover-danger v3-hover-text-danger';
            removeBtn.addEventListener('click', function() {
                medicineItem.style.transition = 'all 0.3s ease';
                medicineItem.style.opacity = '0';
                medicineItem.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    medicineItem.remove();
                    syncEmptyState();
                }, 300);
            });

            const trashIcon = document.createElement('i');
            trashIcon.className = 'fas fa-trash relative z-10';
            removeBtn.appendChild(trashIcon);

            headerDiv.appendChild(titleContainer);
            headerDiv.appendChild(removeBtn);
            medicineItem.appendChild(headerDiv);

            const gridDiv = document.createElement('div');
            gridDiv.className = 'grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4';
            gridDiv.appendChild(createField(i18n.medicineNameRequired.replace(' *', ''), 'fas fa-pills', 'medicines[' + medicineCount + '][name]', i18n.placeholderAmoxicillin, 'v3-text-primary'));
            gridDiv.appendChild(createField(i18n.dosageRequired.replace(' *', ''), 'fas fa-weight', 'medicines[' + medicineCount + '][dosage]', i18n.placeholder500mg, 'v3-text-chair'));
            gridDiv.appendChild(createField(i18n.frequencyRequired.replace(' *', ''), 'fas fa-clock', 'medicines[' + medicineCount + '][frequency]', i18n.placeholderTwiceDaily, 'v3-text-warning'));
            gridDiv.appendChild(createField(i18n.durationRequired.replace(' *', ''), 'fas fa-calendar-alt', 'medicines[' + medicineCount + '][duration]', i18n.placeholder7Days, 'v3-text-danger'));
            medicineItem.appendChild(gridDiv);

            container.appendChild(medicineItem);
            syncEmptyState();
            medicineItem.style.opacity = '0';
            medicineItem.style.transform = 'translateY(20px)';
            setTimeout(function() {
                medicineItem.style.transition = 'all 0.3s ease';
                medicineItem.style.opacity = '1';
                medicineItem.style.transform = 'translateY(0)';
            }, 10);
        };

        form.prescriptionAddMedicine = addMedicine;
        form.prescriptionReset = function() {
            medicineCount = 0;
            if (container) {
                container.replaceChildren();
            }
            addMedicine();
        };

        addButton?.addEventListener('click', addMedicine);
        form.addEventListener('submit', function(event) {
            const medicines = form.querySelectorAll('.medicine-item');
            if (medicines.length === 0) {
                event.preventDefault();
                alert(i18n.alertAddAtLeastOne);
                return false;
            }

            let isValid = true;
            medicines.forEach(function(medicine) {
                const name = medicine.querySelector('input[name*="[name]"], select[name*="[name]"]');
                const dosage = medicine.querySelector('input[name*="[dosage]"]');
                const frequency = medicine.querySelector('input[name*="[frequency]"]');
                const duration = medicine.querySelector('input[name*="[duration]"]');

                if (!name?.value || !dosage?.value || !frequency?.value || !duration?.value) {
                    isValid = false;
                    medicine.style.borderColor = '#ef4444';
                    medicine.style.backgroundColor = '#fef2f2';
                } else {
                    medicine.style.borderColor = '#d1d5db';
                    medicine.style.backgroundColor = '#ffffff';
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert(i18n.alertFillAllComplete);
                return false;
            }
        });

        addMedicine();
    });
};

document.addEventListener('DOMContentLoaded', function() {
    window.initPrescriptionCreateForms(document);
});
</script>
