<?php
$isRtl = !empty($is_rtl);
$modalReturnTo = (string) ($return_to ?? 'patients');
$validation = $validation ?? session()->getFlashdata('validation');
$shouldOpenPatientCreateModal = old('source_modal') === 'patient_quick_create';
?>
<?php if (has_permission('patients', 'create')): ?>
    <style>
    .patient-create-modal {
        visibility: hidden;
        pointer-events: none;
        transition: visibility 0s linear 240ms;
    }
    .patient-create-modal.is-open {
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .patient-create-modal__backdrop {
        opacity: 0;
        transition: opacity 220ms ease;
    }
    .patient-create-modal.is-open .patient-create-modal__backdrop {
        opacity: 1;
    }
    .patient-create-modal__panel {
        display: grid !important;
        grid-template-rows: auto minmax(0, 1fr) auto;
        opacity: 0;
        overflow: clip;
        transform: scale(0.86);
        transform-origin: center center;
        height: min(92dvh, 52rem);
        transition: opacity 180ms ease, transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .patient-create-modal.is-open .patient-create-modal__panel {
        opacity: 1;
        transform: scale(1);
    }
    .patient-create-modal .select2-container {
        width: 100% !important;
    }
    .patient-create-modal [data-medical-alert-item] {
        align-self: start;
    }
    @media (prefers-reduced-motion: reduce) {
        .patient-create-modal__backdrop,
        .patient-create-modal__panel {
            transition: none;
        }
    }
    @media (max-width: 640px) {
        .patient-create-modal__shell {
            align-items: stretch;
            padding: 0;
        }
        .patient-create-modal__panel {
            width: 100vw;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }
    }
    </style>

    <div id="newPatientModal" class="app-scale-modal patient-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPatientModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="patient-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-patient-create-modal-close></div>
        <div class="patient-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-patient-create-modal-close>
            <form id="newPatientQuickCreateForm" action="<?= base_url('patient/store') ?>" method="POST" class="patient-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-patient-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="patient_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 id="newPatientModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientNewPatient')) ?></h2>
                            <a href="<?= base_url('patient/create?' . http_build_query(['return_to' => $modalReturnTo])) ?>" class="mt-1 inline-flex text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-patient-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
                    <?php if (session('error') && $shouldOpenPatientCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('patient/partials/create_form_fields', [
                        'validation' => $validation,
                        'is_rtl' => $isRtl,
                        'sectionClass' => 'rounded-2xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm',
                    ]) ?>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-patient-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-5 py-2.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 hover:shadow-md">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= esc(lang('UI.settingsSave')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-emerald-700 hover:to-teal-700 hover:shadow-xl">
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
