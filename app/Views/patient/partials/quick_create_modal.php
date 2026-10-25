<?php
$isRtl = !empty($is_rtl);
$modalReturnTo = (string) ($return_to ?? 'patients');
$validation = $validation ?? session()->getFlashdata('validation');
$shouldOpenPatientCreateModal = old('source_modal') === 'patient_quick_create';
?>
<?php if (has_permission('patients', 'create')): ?>
<div id="newPatientModal" class="patient-create-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPatientModalTitle" dir="<?= $isRtl?'rtl':'ltr' ?>">
<div class="patient-create-modal__backdrop" data-patient-create-modal-close></div><div class="patient-create-modal__shell" data-patient-create-modal-close>
<form id="newPatientQuickCreateForm" action="<?= base_url('patient/store') ?>" method="POST" class="patient-create-modal__panel" data-patient-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
<?= csrf_field() ?><input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>"><input type="hidden" name="source_modal" value="patient_quick_create">
<header class="v4-modal-header"><div><h2 id="newPatientModalTitle"><?= esc(lang('UI.patientNewPatient')) ?></h2><a class="v4-text-link" href="<?= base_url('patient/create?' . http_build_query(['return_to'=>$modalReturnTo])) ?>"><?= esc(lang('UI.openFullPage')) ?></a></div><button type="button" class="v4-icon-button" data-patient-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
<div class="v4-modal-body"><?php if(session('error')&&$shouldOpenPatientCreateModal): ?><div class="v4-form-error"><?= esc(session('error')) ?></div><?php endif; ?><?= view('patient/partials/create_form_fields',['validation'=>$validation,'is_rtl'=>$isRtl,'sectionClass'=>'v4-form-section']) ?></div>
<footer class="v4-modal-footer"><button type="button" data-patient-create-modal-close class="v4-button"><?= esc(lang('UI.cancel')) ?></button><button type="submit" name="post_submit_action" value="save_only" class="v4-button"><?= esc(lang('UI.settingsSave')) ?></button><button type="submit" name="post_submit_action" value="save_and_book" class="v4-button v4-button--primary"><?= esc(lang('UI.patientSaveAndBookAppointment')) ?></button></footer>
</form></div></div>

    <?php /* The form initializer is idempotent and may already be loaded on /patients. */ ?>
    <?= view('patient/partials/create_form_script') ?>
    <script>
    (function () {
        if (window.__patientQuickCreateModalBound) {
            return;
        }
        window.__patientQuickCreateModalBound = true;

        let initialIntake = '';
        const intakeState = () => { const form=document.getElementById('newPatientQuickCreateForm'); return form ? JSON.stringify([...new FormData(form)].filter(([name])=>name!==document.querySelector('meta[name="csrf-name"]')?.content)) : ''; };


        function mountNewPatientModal() {
            const modal = document.getElementById('newPatientModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-patient-create-modal-close], a[data-patient-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        closeNewPatientModal();
                    });
                });
            }
            return modal;
        }

        function initializeNewPatientModalContent(modal) {
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
            if (typeof window.initPatientCreateForms === 'function') {
                window.initPatientCreateForms(modal);
            }
            return Promise.allSettled(tasks);
        }

        function resetNewPatientModalForm(modal) {
            const form = modal ? modal.querySelector('[data-patient-create-form]') : null;
            if (!form) {
                return;
            }

            form.reset();
            form.querySelectorAll('select').forEach(function (field) {
                if (window.jQuery && window.jQuery(field).data('select2')) {
                    window.jQuery(field).val('').trigger('change');
                } else {
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (field) {
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
            if (typeof form.__syncPatientCreateForm === 'function') {
                form.__syncPatientCreateForm();
            } else if (typeof window.initPatientCreateForms === 'function') {
                window.initPatientCreateForms(form);
            }
        }

        function openNewPatientModal(trigger) {
            const modal=mountNewPatientModal(); if(!modal) return;
            initialIntake=intakeState();
            window.AppModal.open(modal,{trigger:trigger||document.activeElement,focusSelector:'#first_name',onOpen:()=>{initializeNewPatientModalContent(modal).then(()=>{initialIntake=intakeState();});}});
        }
        function closeNewPatientModal() {
            const modal=document.getElementById('newPatientModal');
            if(!window.AppModal.isOpen(modal)) return;
            if(initialIntake&&intakeState()!==initialIntake&&!window.confirm(<?= json_encode(lang('V4.discardIntake'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)) return;
            window.AppModal.close(modal,{onReset:()=>resetNewPatientModalForm(modal)});
        }

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-patient-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openNewPatientModal(openTrigger);
                return;
            }

            const closeTrigger = event.target.closest('[data-patient-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeNewPatientModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.target.closest?.('.select2-search')) return;
            if (event.key !== 'Escape') {
                return;
            }
            const modal = document.getElementById('newPatientModal');
            if (modal && !modal.classList.contains('hidden')) {
                event.preventDefault();
                closeNewPatientModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (<?= $shouldOpenPatientCreateModal ? 'true' : 'false' ?>) {
                openNewPatientModal(document.querySelector('[data-patient-create-modal-open]'));
            }
        });
    })();
    </script>
<?php endif; ?>
