<script>
document.addEventListener('DOMContentLoaded', function () {
    const patientForm = document.querySelector('[data-patient-create-form]');
    if (!patientForm || patientForm.dataset.patientCreateBound === '1') {
        return;
    }
    patientForm.dataset.patientCreateBound = '1';

    const optionalToggle = patientForm.querySelector('#optionalEnabled');
    const optionalSection = patientForm.querySelector('#optionalSection');
    const optionalTrack = patientForm.querySelector('#optionalTrack');
    const optionalDot = patientForm.querySelector('#optionalDot');
    const genderSelect = patientForm.querySelector('#gender');
    const phoneInput = patientForm.querySelector('#phone');

    function validatePhoneField(showError = false) {
        if (!phoneInput) {
            return true;
        }

        const value = (phoneInput.value || '').trim();
        const isEmpty = value === '';
        const isValid = /^\d{9,15}$/.test(value);
        const shouldShowError = showError && !isValid;

        phoneInput.classList.toggle('border-red-500', shouldShowError);
        phoneInput.classList.toggle('border-gray-300', !shouldShowError);

        if (isEmpty && !showError) {
            return false;
        }

        return isValid;
    }

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

    function syncMedicalAlertToggle(toggle) {
        if (!toggle) {
            return;
        }

        var trackId = toggle.dataset.trackId;
        var dotId = toggle.dataset.dotId;
        var detailTarget = toggle.dataset.detailTarget;
        if (!trackId || !dotId || !detailTarget) {
            return;
        }

        var track = patientForm.querySelector('#' + CSS.escape(trackId));
        var dot = patientForm.querySelector('#' + CSS.escape(dotId));
        var detailWrap = patientForm.querySelector('#' + CSS.escape(detailTarget));

        if (toggle.checked) {
            if (track) {
                track.classList.remove('bg-gray-200');
                track.classList.add('bg-blue-500');
            }
            if (dot) {
                window.applyToggleDotState?.(dot, true);
            }
            if (detailWrap) {
                detailWrap.classList.remove('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach(function (field) {
                    field.disabled = false;
                });
            }
        } else {
            if (track) {
                track.classList.remove('bg-blue-500');
                track.classList.add('bg-gray-200');
            }
            if (dot) {
                window.applyToggleDotState?.(dot, false);
            }
            if (detailWrap) {
                detailWrap.classList.add('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach(function (field) {
                    field.disabled = true;
                    field.value = '';
                });
            }
        }
    }

    function syncPregnancyVisibility() {
        const pregnancyItems = patientForm.querySelectorAll('[data-pregnancy-only="1"]');
        const gender = (genderSelect?.value || '').toLowerCase();
        const showPregnancy = gender === '' || gender === 'female';

        pregnancyItems.forEach(function (item) {
            const toggle = item.querySelector('.medical-alert-toggle');
            const hiddenInputs = item.querySelectorAll('input[type="hidden"]');
            const detailInputs = item.querySelectorAll('[data-medical-alert-detail-wrap] input, [data-medical-alert-detail-wrap] textarea');

            if (showPregnancy) {
                item.classList.remove('hidden');
                if (toggle) {
                    toggle.disabled = false;
                }
                hiddenInputs.forEach(function (input) { input.disabled = false; });
                syncMedicalAlertToggle(toggle);
                return;
            }

            item.classList.add('hidden');
            if (toggle) {
                toggle.checked = false;
                toggle.disabled = true;
                syncMedicalAlertToggle(toggle);
            }
            hiddenInputs.forEach(function (input) { input.disabled = true; });
            detailInputs.forEach(function (input) {
                input.disabled = true;
                input.value = '';
            });
        });
    }

    optionalToggle?.addEventListener('change', syncOptionalState);
    syncOptionalState();

    if (phoneInput) {
        phoneInput.addEventListener('input', function () { validatePhoneField(false); });
        phoneInput.addEventListener('blur', function () { validatePhoneField(false); });
        validatePhoneField(false);
    }

    patientForm.addEventListener('submit', function (event) {
        if (!validatePhoneField(true)) {
            event.preventDefault();
            phoneInput?.focus();
        }
    });

    patientForm.querySelectorAll('.medical-alert-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            syncMedicalAlertToggle(toggle);
        });
        syncMedicalAlertToggle(toggle);
    });

    genderSelect?.addEventListener('change', syncPregnancyVisibility);
    if (window.jQuery && genderSelect) {
        window.jQuery(genderSelect).on('select2:select select2:clear change.select2', syncPregnancyVisibility);
    }
    syncPregnancyVisibility();
});
</script>
