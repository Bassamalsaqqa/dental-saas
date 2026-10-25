<?php
$isRtl = !empty($is_rtl);
$modalReturnTo = (string) ($return_to ?? 'patients');
$validation = $validation ?? session()->getFlashdata('validation');
$shouldOpenPatientCreateModal = old('source_modal') === 'patient_quick_create';
?>
<?php if ((isset($v3Can) ? $v3Can('patients', 'create') : has_permission('patients', 'create'))): ?>


    <div id="newPatientModal" class="v3-context-drawer patient-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPatientModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="patient-create-modal__backdrop v3-context-drawer__backdrop" data-patient-create-modal-close></div>
        <div class="patient-create-modal__shell v3-context-drawer__shell" data-patient-create-modal-close>
            <form id="newPatientQuickCreateForm" action="<?= base_url('patient/store') ?>" method="POST" class="patient-create-modal__panel v3-context-drawer__panel" data-patient-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="patient_quick_create">

                <div class="v3-context-drawer__header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 id="newPatientModalTitle" class="text-lg font-bold"><?= esc(lang('UI.patientNewPatient')) ?></h2>
                            <a href="<?= base_url('patient/create?' . http_build_query(['return_to' => $modalReturnTo])) ?>" class="mt-1 inline-flex text-xs font-semibold">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition v3-btn v3-btn--icon" data-patient-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="v3-context-drawer__body">
                    <?php if (session('error') && $shouldOpenPatientCreateModal): ?>
                        <div class="border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('patient/partials/create_form_fields', [
                        'validation' => $validation,
                        'is_rtl' => $isRtl,
                        'sectionClass' => 'v3-flow-section',
                    ]) ?>
                </div>

                <div class="v3-context-drawer__footer">
                    <button type="button" data-patient-create-modal-close class="inline-flex items-center justify-center border px-5 py-2.5 text-sm font-semibold transition v3-btn v3-btn--secondary">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center border px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= esc(lang('UI.settingsSave')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <?= esc(lang('UI.patientSaveAndBookAppointment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php /* The form initializer is idempotent and may already be loaded on /patients. */ ?>
    <?= view('patient/partials/create_form_script') ?>
    <script>
    (function () {
        if (window.__patientQuickCreateModalBound) {
            return;
        }
        window.__patientQuickCreateModalBound = true;

        let newPatientModalTrigger = null;
        let newPatientModalCloseTimer = null;

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
            const modal = mountNewPatientModal();
            if (!modal) {
                return;
            }
            newPatientModalTrigger = trigger || document.activeElement;
            if (newPatientModalCloseTimer) {
                clearTimeout(newPatientModalCloseTimer);
                newPatientModalCloseTimer = null;
            }
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('app-drawer-lock');
            document.body.classList.add('app-drawer-lock');
            requestAnimationFrame(function () {
                modal.classList.add('is-open');
            });
            window.setTimeout(function () {
                initializeNewPatientModalContent(modal);
                const firstField = modal.querySelector('#first_name');
                if (firstField && typeof firstField.focus === 'function') {
                    firstField.focus({ preventScroll: true });
                }
            }, 80);
        }

        function closeNewPatientModal() {
            const modal = document.getElementById('newPatientModal');
            if (!modal || modal.classList.contains('hidden')) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('app-drawer-lock');
            document.body.classList.remove('app-drawer-lock');
            newPatientModalCloseTimer = setTimeout(function () {
                modal.classList.add('hidden');
                resetNewPatientModalForm(modal);
                if (newPatientModalTrigger && typeof newPatientModalTrigger.focus === 'function') {
                    newPatientModalTrigger.focus();
                }
                newPatientModalTrigger = null;
            }, 240);
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
