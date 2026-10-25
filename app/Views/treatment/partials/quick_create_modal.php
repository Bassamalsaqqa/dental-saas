<?php
$modalReturnTo = (string) ($return_to ?? 'treatment');
$modalSelectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$modalSelectedExaminationId = $selected_examination_id ?? null;
$modalSelectedAppointmentId = $selected_appointment_id ?? null;
$modalContextual = !empty($contextual);
$modalShouldOpen = old('source_modal') === 'treatment_quick_create';
$modalFullPageQuery = ['return_to' => $modalReturnTo];
if (!empty($modalSelectedExaminationId)) {
    $modalFullPageQuery['examination_id'] = $modalSelectedExaminationId;
}
if (!empty($modalSelectedPatient['id'])) {
    $modalFullPageQuery['patient_id'] = $modalSelectedPatient['id'];
}
if (!empty($modalSelectedAppointmentId)) {
    $modalFullPageQuery['appointment_id'] = (int) $modalSelectedAppointmentId;
}
$modalFullPageHref = base_url('treatment/create?' . http_build_query($modalFullPageQuery));
?>
<?php if (has_permission('treatments', 'create')): ?>
<div id="newTreatmentModal" data-confirm-discard="<?= esc(lang('V4.discardEntry'),'attr') ?>" class="treatment-create-modal v4-entry-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newTreatmentModalTitle" dir="<?= !empty($isRtl) ? 'rtl' : 'ltr' ?>">
        <div class="treatment-create-modal__backdrop v4-entry-modal__backdrop" data-treatment-create-modal-close></div>
        <div class="treatment-create-modal__shell v4-entry-modal__shell" data-treatment-create-modal-close>
            <form id="newTreatmentQuickCreateForm" action="<?= base_url('treatment/store') ?>" method="POST" class="treatment-create-modal__panel v4-entry-modal__panel v4-form" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="appointment_id" value="<?= esc($modalSelectedAppointmentId ?? '') ?>">
                <input type="hidden" name="source_modal" value="treatment_quick_create">

<header class="v4-modal-header"><div><h2 id="newTreatmentModalTitle"><?= esc(lang('Treatment.new_treatment')) ?></h2><a href="<?= esc($modalFullPageHref, 'attr') ?>" data-treatment-full-page-link data-default-href="<?= esc($modalFullPageHref, 'attr') ?>" class="v4-text-link">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a></div><button type="button" class="v4-icon-button" data-treatment-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="v4-form-section">
                        <?= view('treatment/partials/form_fields', [
                            'contextual' => $modalContextual,
                            'selected_patient' => $modalSelectedPatient,
                            'patients' => $modalContextual ? [] : ($patients ?? []),
                            'treatment_types' => $treatment_types ?? [],
                            'inventory_items' => $inventory_items ?? [],
                            'clinic_providers' => $clinic_providers ?? [],
                            'validation' => $validation ?? null,
                            'selected_examination_id' => $modalSelectedExaminationId,
                            'id_prefix' => 'quick_treatment_charge',
                        ]) ?>
                    </div>
                </div>

                <div class="v4-modal-footer">
                    <button type="button" data-treatment-create-modal-close class="v4-button">
                        <?= esc(lang('Treatment.cancel')) ?>
                    </button>
                    <button type="submit" class="v4-button v4-button--primary">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc(lang('Treatment.create_treatment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        if (window.__treatmentQuickCreateModalBound) {
            return;
        }
        window.__treatmentQuickCreateModalBound = true;

        let treatmentModalTrigger = null;
        let treatmentModalCloseTimer = null;

        function mountTreatmentModal() {
            const modal = document.getElementById('newTreatmentModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-treatment-create-modal-close], a[data-treatment-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeTreatmentModal();
                    });
                });
            }
            return modal;
        }

        function initializeTreatmentModalContent(modal) {
            if (!modal) {
                return Promise.resolve();
            }
            const tasks = [];
            if (typeof window.initAppSelects === 'function') {
                tasks.push(Promise.resolve(window.initAppSelects(modal)));
            }
            if (typeof window.initializeClinicDateInputs === 'function') {
                window.initializeClinicDateInputs(modal);
            }
            if (typeof window.initContextualTreatmentForms === 'function') {
                window.initContextualTreatmentForms(modal);
            }
            return Promise.allSettled(tasks);
        }

        function getTreatmentPatientSelect(modal) {
            return modal ? modal.querySelector('select[name="patient_id"]') : null;
        }

        function updateTreatmentFullPageLink(modal, patientId) {
            const link = modal ? modal.querySelector('[data-treatment-full-page-link]') : null;
            if (!link) {
                return;
            }

            const defaultHref = link.dataset.defaultHref || link.getAttribute('href') || '<?= esc($modalFullPageHref, 'js') ?>';
            if (!patientId) {
                link.setAttribute('href', defaultHref);
                return;
            }

            try {
                const url = new URL(defaultHref, window.location.origin);
                url.searchParams.set('patient_id', patientId);
                if (!url.searchParams.get('return_to')) {
                    url.searchParams.set('return_to', '<?= esc($modalReturnTo, 'js') ?>');
                }
                link.setAttribute('href', url.toString());
            } catch (error) {
                link.setAttribute('href', '<?= base_url('treatment/create') ?>?patient_id=' + encodeURIComponent(patientId) + '&return_to=<?= rawurlencode($modalReturnTo) ?>');
            }
        }

        function triggerTreatmentFieldChange(field) {
            if (!field) {
                return;
            }
            field.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && window.jQuery(field).hasClass('select2-hidden-accessible')) {
                window.jQuery(field).trigger('change.select2');
            }
        }

        function clearDynamicTreatmentPatient(modal) {
            const patientSelect = getTreatmentPatientSelect(modal);
            if (patientSelect) {
                patientSelect.querySelectorAll('option[data-dynamic-patient-option="1"]').forEach(function (option) {
                    option.remove();
                });
                patientSelect.value = '';
                triggerTreatmentFieldChange(patientSelect);
            }
            updateTreatmentFullPageLink(modal, '');
        }

        function applyTreatmentTriggerContext(modal, trigger) {
            const patientSelect = getTreatmentPatientSelect(modal);
            const patientId = trigger && trigger.dataset ? (trigger.dataset.contextPatientId || '') : '';
            if (!patientSelect || !patientId) {
                updateTreatmentFullPageLink(modal, patientId);
                return;
            }

            const patientName = trigger.dataset.contextPatientName || '';
            const patientCode = trigger.dataset.contextPatientCode || '';
            const patientLabel = [patientName, patientCode].filter(Boolean).join(' - ') || ('Patient #' + patientId);
            let option = Array.from(patientSelect.options).find(function (candidate) {
                return candidate.value === patientId;
            });
            if (!option) {
                option = new Option(patientLabel, patientId, true, true);
                option.dataset.dynamicPatientOption = '1';
                patientSelect.add(option);
            }
            option.text = patientLabel;
            option.selected = true;
            patientSelect.value = patientId;
            triggerTreatmentFieldChange(patientSelect);
            updateTreatmentFullPageLink(modal, patientId);
        }

        function resetTreatmentModalForm(modal) {
            const form = modal ? modal.querySelector('form') : null;
            if (!form) {
                return;
            }
            form.reset();
            const usageContainer = form.querySelector('[data-usage-items-container]');
            if (usageContainer) {
                const rows = Array.from(usageContainer.querySelectorAll('.usage-item'));
                rows.slice(1).forEach(function (row) { row.remove(); });
                rows.slice(0, 1).forEach(function (row) {
                    row.querySelectorAll('select, input').forEach(function (field) {
                        field.value = '';
                        if (field.classList.contains('total-cost')) {
                            field.dataset.value = '0';
                        }
                        if (window.jQuery && field.tagName === 'SELECT' && window.jQuery(field).data('select2')) {
                            window.jQuery(field).val('').trigger('change');
                        }
                    });
                });
            }
            form.querySelectorAll('[data-usage-grand-total]').forEach(function (field) { field.textContent = '0.00'; });
            clearDynamicTreatmentPatient(modal);
            form.querySelectorAll('select, input[type="checkbox"], input[type="radio"]').forEach(triggerTreatmentFieldChange);
            if (typeof window.initContextualTreatmentForms === 'function') {
                window.initContextualTreatmentForms(modal);
            }
        }

        function openTreatmentModal(trigger){const modal=mountTreatmentModal();if(!modal)return;applyTreatmentTriggerContext(modal,trigger);window.AppModal.open(modal,{trigger:trigger||document.activeElement,onOpen:initializeTreatmentModalContent,focusSelector:'.select2-selection,input:not([type=hidden]):not([disabled]),button'});}

        function closeTreatmentModal(){const modal=document.getElementById('newTreatmentModal');if(!window.AppModal.isOpen(modal))return;window.AppModal.close(modal,{onReset:()=>resetTreatmentModalForm(modal)});}

        window.openTreatmentModal = openTreatmentModal;
        window.closeTreatmentModal = closeTreatmentModal;

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-treatment-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openTreatmentModal(openTrigger);
                return;
            }

            const closeTrigger = event.target.closest('[data-treatment-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeTreatmentModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.target.closest?.('.select2-search')) return;
        if (event.key !== 'Escape') {
                return;
            }
            const modal = document.getElementById('newTreatmentModal');
            if (modal && !modal.classList.contains('hidden')) {
                event.preventDefault();
                closeTreatmentModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (<?= $modalShouldOpen ? 'true' : 'false' ?>) {
                openTreatmentModal(document.getElementById('newTreatmentModalTrigger'));
            }
        });
    })();
    </script>
<?php endif; ?>
